<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __("Game: #$id") }}
        </h2>

        @if (in_array($user->id, [$playerX->id, $playerO->id ?? null]))
            <h3>Your (id:{{ $user->id }}) side: <strong>{{ $yourSide }}</strong></h3>
        @endif

        @if (empty($playerO))
            <h4 class="text-orange-300">Awaiting for opponent to join!</h4>
            @push('meta')
                <meta http-equiv="refresh" content="{{ App\Models\Game::REFRESH_TIME_LONGER }}">
            @endpush
        @else

            @if ($yourSide == App\Models\Game::SIDE_X)
                <h4>Opponent: {{ $playerO->name ?? null }} (id:{{ $playerO->id ?? null }})</h4>
            @else
                <h4>Opponent: {{ $playerX->name }} (id:{{ $playerX->id }})</h4>
            @endif

            @if ($gameStatus != App\Models\Game::STATUS_FINISHED)

                @if ($yourSide === $nextMove)
                    <h5 class="text-green-600">Now it's your turn!</h5>
                @else
                    <h5 class="text-red-600">Wait for opponent move!</h5>
                    @push('meta')
                        <meta http-equiv="refresh" content="{{ App\Models\Game::REFRESH_TIME_SHORTER }}">
                    @endpush
                @endif

            @else
                <h5>Game Over!</h5>
                @if ($gameResult == App\Models\Game::RESULT_DRAW)
                    <h6 class="text-orange-500">DRAW!</h6>
                @else
                    @if ($winner == $user->id)
                    <h6 class="text-green-500">You won!</h6>
                    @else
                    <h6 class="text-red-500">You lost!</h6>
                    @endif

                @endif
            @endif
        @endif
    </x-slot>

    @include('board')

</x-app-layout>
