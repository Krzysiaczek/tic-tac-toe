<?php

use App\Models\Game;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_x')->nullable()->constrained('users');
            $table->foreignId('player_o')->nullable()->constrained('users');
            $table->enum('status', Game::STATUS);
            $table->integer('winner')->nullable()->constrained('users');
            $table->enum('result', Game::RESULT)->nullable()->default(null);
            $table->string('board');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
