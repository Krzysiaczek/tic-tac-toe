<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
                @if ($games->count() > 0)
                <ul class="p-9">
                    @foreach ($games as $game)
                      <li><a href="/games/{{ $game->id }}">game #{{ $game->id }}, {{ $game->status }}, last move at {{ $game->updated_at }}</a></li>
                    @endforeach
                </ul>
                @elseif (count($awaitingPlayers) > 1)
                    <h5 class="text-green-600 mx-5">There are {{ count($awaitingPlayers) - 1 }} players awaiting!</h5>
                    <button class="rounded-full w-fit bg-green-400 px-3 py-2 m-5 font-bold">Start new game</button>
                    @push('meta')
                        <meta http-equiv="refresh" content="5">
                    @endpush
                @else
                    <h5 class="text-red-600 m-6 font-bold">There are no opponents online, please wait!</h5>
                    @push('meta')
                        <meta http-equiv="refresh" content="5">
                    @endpush
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
