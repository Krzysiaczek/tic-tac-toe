<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __("Game: #$id") }}
        </h2>

        @if (in_array($user->id, [$playerX->id, $playerO->id]))
            <h3>You (id: {{ $user->id }}) are playing on side: {{ $yourSide }}</h3>
        @endif

        @if ($yourSide == 'X')
            <h4>Opponent: {{ $playerO->name }}  (id:{{ $playerO->id }})</h4>
        @else
            <h4>Opponent: {{ $playerX->name }}  (id:{{ $playerX->id }})</h4>
        @endif

        @if ($yourSide === $nextMove)
            <h5 class="text-green-600">Now it's your turn!</h5>
        @else
            <h5 class="text-red-600">Wait for opponent move!</h5>
            @push('meta')
                <meta http-equiv="refresh" content="2">
            @endpush
        @endif
        {{-- <h5>Next move: {{ $nextMove }}</h5> --}}
    </x-slot>

    <div class="py-12">
        <div class="mx-auto w-20">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-3 place-items-center w-18 mx-auto font-semibold text-2xl gap">
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
                                    {{-- @if ($nextMove === 'X')
                                    <input type="hidden" name="player-id" value="{{ $playerX->id }}" />
                                    @else
                                    <input type="hidden" name="player-id" value="{{ $playerO->id }}" />
                                    @endif --}}
                                    <input type="submit" value="." class="">

                                </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if ($errors->any())
            <div class="w-32">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li class="text-red-500 text-xs">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
