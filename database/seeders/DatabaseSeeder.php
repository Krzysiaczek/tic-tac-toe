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

    protected const BOARD_SIGNS = [null, 'X', 'O'];

    protected static string|null $gameResult;



    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $inactivePlayers = self::generatePlayers('away');
        $activePlayers = self::generatePlayers('playing');
        self::generatePlayers('awaiting');

        self::generateGames('in progress', $activePlayers);
        self::generateGames('finished', $inactivePlayers);

        // TODO do we need to identify abandoned games ??? game: in progress + user: away ???
        // or check last update time
    }

    protected static function generateGames(string $status, array &$players, int $number = self::GAMES_AMOUNT): void
    {
        if (!in_array($status, Game::STATUS)) {
            throw new DataException('Unknown game status requested!');
        }

        for ($i = 0; $i < self::GAMES_AMOUNT; $i++) {
            $winner = null;
            $pairOfPlayers = self::getTwoRandomPlayersFrom($players);

            if ($status === 'finished') {
                switch (self::pickWinningSideOrDraw()) {
                    case 'X':
                        $winner = $pairOfPlayers[0];
                        break;
                    case 'Y':
                        $winner = $pairOfPlayers[1];
                        break;
                    default:
                        $winner = null;
                }

                dump($winner);

                if (!empty($winner)) {
                    $result = Game::RESULT[2];  // WON
                } else {
                    $result = Game::RESULT[1];  // DRAW
                }
            } else {
                $result = Game::RESULT[0];      // UNKOWN
            }

            Game::factory()->create([
                'player_x'  => $pairOfPlayers[0],
                'player_o'  => $pairOfPlayers[1],
                'status'    => $status,
                'board'     => serialize(self::generateRandomBoard($status === 'finished')),
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
        //TODO check if the game is in running stage that there is only one for the given pair
        // or maybe this could happen if the abandoned the game and start another?

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
        // dump($board);
        self::draw($board);
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
