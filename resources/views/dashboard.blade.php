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
                            <li>
                                <a href="/games/{{ $game->id }}" class="underline underline-offset-2 text-blue-600">game #{{ $game->id }}</a>
                                {{ $game->status }}, last move {{ $game->updated_at->diffForHumans() }}
                            </li>
                        @endforeach
                    </ul>
                @endif
                @push('meta')
                    <meta http-equiv="refresh" content="{{ App\Models\Game::REFRESH_TIME_LONGER }}">
                @endpush
                @if (count($awaitingPlayers) > 1)
                    <h5 class="text-green-600 ml-8">There are {{ count($awaitingPlayers) - 1 }} players online awaiting!
                    </h5>
                    <form method="POST" action="/games/create">
                        @csrf
                        <button class="rounded-full w-fit bg-blue-600 text-white px-3 py-2 m-5 font-bold">Start new game</button>
                    </form>
                @else
                    <h5 class="text-red-600 m-6 font-bold">There are no opponents online, please wait!</h5>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
