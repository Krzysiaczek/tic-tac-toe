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

// TODO: add '/games' prefix/group
Route::middleware(['auth', 'verified'])->prefix('games')->group(function () {
    // Route::get('/games', [GamesController::class, 'index'])->name('games.index');
    Route::post('/create', [GamesController::class, 'create'])->name('games.index');
    Route::get('/{gameId}', [GamesController::class, 'show'])->whereNumber('gameId')->name('games.show');
    Route::post('/{gameId}/move', [GamesController::class, 'move'])->whereNumber('gameId')->name('games.move');
});

Route::get('/dashboard', function () {

    $games = Game::where('player_x', Auth::id())->orWhere('player_o', Auth::id());

    return view('dashboard', [
        'games' => $games->get(),
        'awaitingPlayers' => User::where('status', User::STATUS[1])
            // ->andWhere('update_at', 'timediff') // TODO: add timediff condition from last activity
            ->get()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
