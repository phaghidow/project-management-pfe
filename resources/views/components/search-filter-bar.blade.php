@props([
    'route',
    'filterRoute' => null,
    'searchPlaceholder' => 'Rechercher...',
    'filters' => [],
    'filterOptions' => [],
    'showReset' => true,
    'searchFieldName' => 'q',
])

@php
    $currentRoute = $filterRoute ?? route($route);
    $searchValue = request($searchFieldName, '');
@endphp

<form action="{{ $currentRoute }}" method="GET" class="space-y-4 p-4 bg-white rounded-lg border border-gray-200">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Search Input -->
        <div class="col-span-1 md:col-span-2 lg:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Rechercher
            </label>
            <input
                type="text"
                name="{{ $searchFieldName }}"
                value="{{ $searchValue }}"
                placeholder="{{ $searchPlaceholder }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
        </div>

        <!-- Additional Filters -->
        @foreach($filterOptions as $filterName => $options)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ $options['label'] ?? ucfirst($filterName) }}
                </label>
                @if($options['type'] === 'select')
                    <select
                        name="{{ $filterName }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- Tous --</option>
                        @foreach($options['values'] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request($filterName) === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                @elseif($options['type'] === 'date')
                    <input
                        type="date"
                        name="{{ $filterName }}"
                        value="{{ request($filterName, '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                @endif
            </div>
        @endforeach
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center gap-2 pt-4 border-t border-gray-200">
        <button
            type="submit"
            class="inline-flex items-center justify-center px-4 py-2 bg-primary-500 text-white font-medium rounded-md hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition-colors !text-white"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            Filtrer
        </button>

        @if($showReset && (request()->filled($searchFieldName) || request()->filled(array_keys($filterOptions))))
            <a
                href="{{ $currentRoute }}"
                class="inline-flex items-center px-4 py-2 bg-[#F8F9FC] text-[#1A202C] font-medium rounded-md hover:bg-[#E2E8F0] transition-colors"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Réinitialiser
            </a>
        @endif
    </div>
</form>
