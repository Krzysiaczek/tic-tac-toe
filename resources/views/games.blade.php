<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __("Game: #$id") }}
        </h2>

        @if (in_array($user->id, [$playerX->id, $playerO->id ?? null]))
            <h3>Your (id:{{ $user->id }}) side: <strong>{{ $yourSide }}</strong></h3>
        @endif

        @if ($yourSide == 'X')
            @if (empty($playerO))
                <h4 class="text-orange-300">Awaiting for opponent to join!</h4>
                @push('meta')
                    <meta http-equiv="refresh" content="{{ App\Models\Game::REFRESH_TIME_LONGER }}">
                @endpush
            @else
                <h4>Opponent: {{ $playerO->name ?? null }} (id:{{ $playerO->id ?? null }})</h4>
            @endif
        @else
            <h4>Opponent: {{ $playerX->name }} (id:{{ $playerX->id }})</h4>
        @endif

        @if ($gameStatus != App\Models\Game::STATUS[2])

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
            @if ($winner == $user->id)
                <h6 class="text-green-500">You won!</h6>
            @else
                <h6 class="text-red-500">You lost!</h6>
            @endif
            <h6></h6>
        @endif
    </x-slot>

    <div class="py-12">
        <div class="mx-auto w-32">
            <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow-gray-700 shadow-md">
                <div class="text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-3 place-items-center w-24 mx-auto font-semibold text-4xl gap-x-5">
                        @foreach ($board as $key => $elem)
                            <div class="">
                                @if ($elem)
                                    {{ $elem }}
                                @else
                                    <form method="POST" action="/games/{{ $id }}/move">
                                        @csrf
                                        <input type="hidden" name="side" value="{{ $nextMove }}" />
                                        <input type="hidden" name="board-index" value="{{ $key }}" />
                                        <input type="hidden" name="player-id" value="{{ $user->id }}" />
                                        <input type="submit" value="." class="w-9 hover:bg-blue-200"
                                               style="cursor: pointer;">
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="w-32 my-3">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li class="text-red-500 text-xs">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
    <script>
        var msg = '{{Session::get('alert')}}';
        var exist = '{{Session::has('alert')}}';
        if(exist){
          alert(msg);
        }
      </script>
</x-app-layout>
