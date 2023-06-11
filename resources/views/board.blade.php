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
