<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller as BaseController;

class GamesController extends BaseController
{
    public function index()
    {
        return view('dashboard');
    }

    public function create()
    {
        $game = Game::where('status', Game::STATUS_AWAITING)
            ->whereNull('player_o')
            ->whereNotNull('player_x')
            // ->whereNot('player_x', Auth::id())
            ->orderBy('created_at', 'DESC')
            ->first();

        // there is already a game to join
        if (!empty($game->id)) {
            // check if this is current user created that game
            if ($game->player_x == Auth::id()) {
                // not much to do
                $id = $game->id;
            } else {
                // add user to another one awaiting to start
                $game->player_o = Auth::id();
                $game->status = Game::STATUS_IN_PROGRESS;
                if ($game->save()) {
                    $id = $game->id;
                } else {
                    // TODO: Add logs
                    abort(500, 'UPS, something went wrong! SORRY');
                }
            }
        } else {
            $id = Game::create([
                'board' => serialize([null, null, null, null, null, null, null, null, null]),
                'player_x' => Auth::id()
            ])->id;
        }
        return to_route('games.show', ['gameId' => $id]);
    }

    public function show(): View
    {
        $game =  Game::findOrFail(request('gameId'));

        $this->checkPlayerBelongsToThisGame($game, true);

        return view('games', [
            'user'       => request()->user(),
            'id'         => request('gameId'),
            'board'      => unserialize($game->board),
            'playerX'    => $game->playerX,
            'playerO'    => $game->playerO,
            'nextMove'   => $this->nextMove($game), // which side is next move X or O
            'yourSide'   => $game->player_x === Auth::id() ? Game::SIDE_X : Game::SIDE_O, // player's side X or O
            'gameStatus' => $game->status,
            'gameResult' => $game->result,
            'winner'     => $game->winner,
        ]);
    }

    public function move()
    {
        request()->validate([
            'player-id'    => 'required|integer',
            'board-index'  => 'required|integer',
            'side'         => 'required|string|',
        ]);

        $game = Game::findOrFail(request('gameId'));
        $board = unserialize($game->board);

        //TODO Figure out a better way for custom validations
        $this->checkGameIsNotFinished($game);
        $this->checkPlayerBelongsToThisGame($game);
        $this->checkIfRequestedPlaceOnBoardIsStillAvailable($board);
        $this->checkThisIsPlayerMove($game);

        $board = $this->updateGameBoard($game, $board);

        if ($this->checkIfSideIsWinner(request('side'), $board)) {
            $this->finishTheGame($game, Game::RESULT_WON, Auth::id());
            // return redirect()->back()->with('alert', 'You won!');
        } elseif ($this->checkResultIsDraw($board)) {
            $this->finishTheGame($game, Game::RESULT_DRAW);
        }

        return back();
    }

    protected function updateGameBoard(Game $game, array $board): array
    {
        $board[request('board-index')] = request('side');
        $game->board = serialize($board);
        $game->update();

        return $board;
    }

    // TODO: to investigate why this method could not sit in a Game Model?
    // it throws App\Models\Game::nextMove must return a relationship instance.
    protected function nextMove($game): string
    {
        $board = unserialize($game->board);
        $count = 0;
        foreach ($board as $key => $cell) {
            if (!empty($cell)) {
                $count++;
            }
        }

        return $count % 2 === 0 ? Game::SIDE_X : Game::SIDE_O;
    }

    // TODO: Those methods should be moved to some custom Validation class, I guess
    protected function checkIfRequestedPlaceOnBoardIsStillAvailable($board)
    {
        if (!empty($board[request('board-index')])) {
            throw ValidationException::withMessages(['board-index' => 'Invalid move!']);
        }
    }

    protected function checkGameIsNotFinished($game)
    {
        if ($game->status !== Game::STATUS_IN_PROGRESS) {
            throw ValidationException::withMessages(['status' => 'Game is not in progress!']);
        }
    }

    protected function checkPlayerBelongsToThisGame($game, $throwHttpException = false)
    {
        if (!in_array(Auth::id(), [$game->player_x, $game->player_o])) {
            $message = 'This is not your game!';
            if ($throwHttpException) {
                // 403 maybe would be more appropriate for the context
                // but would uncover the entry point for attackers
                abort(404, $message);
            } else {
                throw ValidationException::withMessages(['player-id' => $message]);
            }
        }
    }

    protected function checkThisIsPlayerMove($game)
    {
        $nextMove = ucfirst(request('side'));
        if (
            $nextMove === Game::SIDE_X && $game->player_x !== (int) request('player-id') ||
            $nextMove === Game::SIDE_O && $game->player_o !== (int) request('player-id')
        ) {
            throw ValidationException::withMessages(['player-id' => 'This is not your turn!']);
        }
    }

    protected function checkIfSideIsWinner(string $side, array $board): bool
    {
        $winner = false;
        if ($this->countSide($board, $side) < 3) {
            return $winner;
        }
        $sidePositions = array_intersect($board, [$side]);

        // search for winning pattern
        foreach (Game::WINNING_PATTERNS as $pattern) {
            $searchPattern = array_intersect(array_keys($sidePositions), $pattern);

            // declare winner only if the pattern is covered in full => 3 moves
            if (!empty($searchPattern) && count($searchPattern) >= 3) {
                $winner = true;
            }
        }

        return $winner;
    }

    protected function finishTheGame(Game $game, string $result, int|null $winner = null): void
    {
        $game->status = Game::STATUS_FINISHED;
        $game->result = $result;
        if (!empty($winner)) {
            $game->winner = $winner;
        }
        $game->save();
    }

    protected function countSide(array $board, string $side = ''): int
    {
        //count total number of moves
        if (empty($side)) {
            return count(array_intersect($board, [Game::SIDE_X, Game::SIDE_O]));
        }

        //count only moves by given side
        return count(array_intersect($board, [$side]));
    }

    protected function checkResultIsDraw(array $board): bool
    {
        return $this->countSide($board) === 9;
    }
}
