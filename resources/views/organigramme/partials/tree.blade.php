<li class="relative flex flex-col items-center">

    {{-- NODE --}}
    <div class="bg-white border shadow-md rounded-lg px-4 py-2 min-w-37.5 text-center hover:shadow-lg transition">
        <div class="font-semibold text-gray-700">
            {{ $structure->name }}
        </div>

        @if($structure->type)
            <div class="text-xs text-gray-400">
                {{ $structure->type }}
            </div>
        @endif
    </div>

    {{-- CONNECTEUR VERTICAL --}}
    @if($structure->children && $structure->children->count())
        <div class="w-px h-6 bg-gray-300"></div>

        {{-- BRANCHES WRAPPER --}}
        <ul class="flex gap-8 pt-4 relative">

            @foreach($structure->children as $child)
                @include('organigramme.partials.tree', [
                    'structure' => $child
                ])
            @endforeach

        </ul>
    @endif

</li>