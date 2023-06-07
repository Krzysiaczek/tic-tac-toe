<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/games', [GamesController::class, 'index'])->name('games.index');
    Route::get('/games/{gameId}', [GamesController::class, 'show'])->whereNumber('gameId')->name('games.show');
    Route::any('/games/{gameId}/move', [GamesController::class, 'move'])->whereNumber('gameId')->name('games.move');
});

Route::get('/dashboard', function () {

    $games = Game::where('player_x', Auth::id())->orWhere('player_o', Auth::id());

    return view('dashboard', [
        'games' => $games->get(),
        'awaitingPlayers' => User::where('status', User::STATUS[1])->get()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
