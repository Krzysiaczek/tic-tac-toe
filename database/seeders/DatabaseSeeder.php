<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Seeder;
use Dflydev\DotAccessData\Exception\DataException;

class DatabaseSeeder extends Seeder
{
    protected const USERS_AMOUNT = 15;
    protected const GAMES_AMOUNT = 15;

    protected const BOARD_SIGNS = [null, Game::SIDE_X, Game::SIDE_O];

    protected static string|null $gameResult;



    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $inactivePlayers = self::generatePlayers(User::STATUS_AWAY);
        $activePlayers = self::generatePlayers(User::STATUS_PLAYING);
        self::generatePlayers(User::STATUS_WAITING);

        self::generateGames(Game::STATUS_IN_PROGRESS, $activePlayers);
        self::generateGames(Game::STATUS_FINISHED, $inactivePlayers);
    }

    protected static function generateGames(string $status, array &$players, int $number = self::GAMES_AMOUNT): void
    {
        if (!in_array($status, Game::STATUS)) {
            throw new DataException('Unknown game status requested!');
        }

        for ($i = 0; $i < self::GAMES_AMOUNT; $i++) {
            $winner = null;
            $pairOfPlayers = self::getTwoRandomPlayersFrom($players);

            if ($status === Game::STATUS_FINISHED) {
                switch (self::pickWinningSideOrDraw()) {
                    case Game::SIDE_X:
                        $winner = $pairOfPlayers[0];
                        break;
                    case Game::SIDE_O:
                        $winner = $pairOfPlayers[1];
                        break;
                    default:
                        $winner = null;
                }

                if (!empty($winner)) {
                    $result = Game::RESULT_WON;
                } else {
                    $result = Game::RESULT_DRAW;
                }
            } else {
                $result = Game::RESULT_UNKNOWN;
            }

            Game::factory()->create([
                'player_x'  => $pairOfPlayers[0],
                'player_o'  => $pairOfPlayers[1],
                'status'    => $status,
                'board'     => serialize(self::generateRandomBoard($status === Game::STATUS_FINISHED)),
                'winner'    => $winner,
                'result'    => $result
            ]);
        }
    }

    protected static function pickWinningSideOrDraw(): string|null
    {
        return self::$gameResult = self::BOARD_SIGNS[rand(0, 2)];
    }

    protected static function generatePlayers(string $status, int $number = self::USERS_AMOUNT): array
    {
        if (!in_array($status, User::STATUS)) {
            throw new DataException('Unknown user status requested!');
        }

        return User::factory($number)->create(['status' => $status])->toArray();
    }

    /**
     * playerX and playerO can't be the same person
     */
    protected static function getTwoRandomPlayersFrom(&$players): array
    {
        $pairOfPlayers = array_rand($players, 2);

        return [
            $players[$pairOfPlayers[0]]['id'],
            $players[$pairOfPlayers[1]]['id'],
        ];
    }

    protected static function generateRandomBoard(bool $finished = false): array
    {
        $board = [];
        $winningPattern = [];

        if ($finished) {
            $winningPattern = !empty(self::$gameResult) ? self::pickWinningPattern() : [];
        }

        for ($i = 0; $i < 9; $i++) {
            if (!empty(self::$gameResult) && in_array($i, $winningPattern)) {
                $board[$i] = self::$gameResult;
            } else {
                $board[$i] = self::BOARD_SIGNS[rand(0, 2)];
            }
        }

        return $board;
    }

    protected static function pickWinningPattern(): array
    {
        return Game::WINNING_PATTERNS[rand(0, count(Game::WINNING_PATTERNS) - 1)];
    }

    protected static function draw($board): void
    {
        foreach ($board as $key => $value) {
            echo $value ?? '.';
            if ($key % 3 === 2) {
                echo PHP_EOL;
            }
        }
        echo PHP_EOL;
    }
}
