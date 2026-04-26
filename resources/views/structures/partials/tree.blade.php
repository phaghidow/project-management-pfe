<li class="relative flex flex-col items-center structures-tree">

    {{-- NODE --}}
    <div class="node-box">
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
        <div class="vertical-connector"></div>

        {{-- BRANCHES WRAPPER --}}
        <ul>

            @foreach($structure->children as $child)
                @include('structures.partials.tree', [
                    'structure' => $child
                ])
            @endforeach

        </ul>
    @endif

</li>
