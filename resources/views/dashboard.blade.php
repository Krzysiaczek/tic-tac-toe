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
                                <a href="/games/{{ $game->id }}"
                                   class="underline underline-offset-2 text-blue-600">game #{{ $game->id }}</a>
                                {{ $game->status }},
                                @if ($game->status == App\Models\Game::STATUS_FINISHED &&
                                    $game->result == App\Models\Game::RESULT_WON
                                )
                                    @if ($game->winner == Auth::id())
                                        <span class="text-green-500">you won</span>,
                                    @else
                                        <span class="text-red-500">you lost</span>,
                                    @endif
                                @elseif($game->status == App\Models\Game::STATUS_FINISHED)
                                    <span class="text-orange-500">draw</span>,
                                @endif
                                last move {{ $game->updated_at->diffForHumans() }}
                            </li>
                        @endforeach
                    </ul>
                @endif
                @push('meta')
                    <meta http-equiv="refresh" content="{{ App\Models\Game::REFRESH_TIME_LONGER }}">
                @endpush
                @if ($awaitingPlayers > 1)
                    <h5 class="text-green-600 ml-8">Number of players waiting online: {{ $awaitingPlayers - 1 }}</h5>
                    <form method="POST" action="/games/create">
                        @csrf

                        <x-primary-button class="my-5 ml-8">
                            Start a new game
                        </x-primary-button>
                    </form>
                @else
                    <h5 class="text-red-600 m-6 font-bold">There are no opponents online, please wait!</h5>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
