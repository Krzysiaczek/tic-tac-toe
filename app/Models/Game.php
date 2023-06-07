<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    // protected $fillable = [];
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public const STATUS = ['awaiting', 'in progress', 'finished'];

    public const RESULT = [null, 'draw', 'won'];

    public const WINNING_PATTERNS = [
        [0, 1, 2], // rows
        [3, 4, 5],
        [6, 7, 8],
        [0, 3, 6], // columns
        [1, 4, 7],
        [2, 5, 8],
        [0, 4, 8], // bevels
        [2, 4, 6]
    ];

    public const REFRESH_TIME_LONGER = 5;
    public const REFRESH_TIME_SHORTER = 2;

    public function players()
    {
        return [
            'x' => $this->playerX(),
            'o' => $this->playerO()

        ];
    }

    public function playerX()
    {
        return $this->belongsTo(User::class, 'player_x');
    }

    public function playerO()
    {
        return $this->belongsTo(User::class, 'player_o');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner');
    }
}
