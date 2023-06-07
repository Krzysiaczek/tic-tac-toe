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
        $game = Game::where('status', 'awaiting')
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
                $game->status = Game::STATUS[1];
                if ($game->save()) {
                    $id = $game->id;
                } else {
                    abort(500, 'UPS, something went wrong! SORRY');
                }
            }
        } else {
            $id = Game::create([
                'board' => serialize([null, null, null, null,null, null, null, null, null]),
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
            'user' => request()->user(),
            'id'    => request('gameId'),
            'board' => unserialize($game->board),
            'playerX' => $game->playerX,
            'playerO' => $game->playerO,
            'nextMove' => $this->nextMove($game),
            'yourSide' => $game->player_x === Auth::id() ? 'X' : 'O',
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

        $this->updateGameBoard($game, $board);

        return back();
    }

    protected function updateGameBoard($game, $board)
    {
        $board[request('board-index')] = request('side');
        $game->board = serialize($board);
        $game->update();
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

        return $count % 2 === 0 ? 'X' : 'O';
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
        if ($game->status !== Game::STATUS[1]) {
            throw ValidationException::withMessages(['status' => 'Game is not ' . Game::STATUS[1] . '!']);
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
        // dd($nextMove, $game->player_x, $game->player_o, request('player-id'));
        if (
            $nextMove === 'X' && $game->player_x !== (int) request('player-id') ||
            $nextMove === 'O' && $game->player_o !== (int) request('player-id')
        ) {
            throw ValidationException::withMessages(['player-id' => 'This is not your turn!']);
        }
    }
}
