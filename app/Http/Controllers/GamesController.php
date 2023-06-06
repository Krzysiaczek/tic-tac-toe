<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\ValidationException;

class GamesController extends BaseController
{
    // use AuthorizesRequests, ValidatesRequests;

    public function index(Request $request)
    {
        return view('dashboard');
    }

    public function show(): View
    {
        $game =  Game::findOrFail(request('gameId'));

        return view('games', [
            'user' => request()->user(),
            'id'    => request('gameId'),
            'board' => unserialize($game->board),
            'playerX' => $game->playerX,
            'playerO' => $game->playerO,
            'nextMove' => $this->nextMove($game),
            // 'yourSide' => $game->player_x === $request->user()->id ? 'X' : 'O',
        ]);
    }

    public function move()
    {
        //TODO Figure out how to add custom validations
        request()->validate([
            'player-id'    => 'required|integer',
            'board-index'  => 'required|integer',
            'side'         => 'required|string|',
        ]);

        $game = Game::findOrFail(request('gameId'));
        $board = unserialize($game->board);

        $this->checkGameIsNotFinished($game);
        $this->checkPlayerBelongsToThisGame($game);
        $this->checkIfRequestedPlaceOnBoardIsStillAvailable($board);
        // $this->checkThisIsPlayerMove($game);     // disabled temporarily for testing

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

    // TODO: Those methods should be moved to some custom Validation class
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

    protected function checkPlayerBelongsToThisGame($game)
    {
        if (!in_array(request('player-id'), [$game->player_x, $game->player_o])) {
            throw ValidationException::withMessages(['player-id' => 'This is not your game!']);
        }
    }

    protected function checkThisIsPlayerMove($game)
    {
        $nextMove = ucfirst(request('side'));
        if (
            $nextMove === 'X' && $game->player_x !== request('player-id') ||
            $nextMove === 'O' && $game->player_o !== request('player-id')
        ) {
            throw ValidationException::withMessages(['player-id' => 'This is not your move!']);
        }
    }
}
