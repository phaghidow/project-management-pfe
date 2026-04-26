<div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 mb-6">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 py-4">
            {{-- Home/Dashboard --}}
            <li class="flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                </a>
            </li>

            {{-- Breadcrumbs --}}
            @foreach($breadcrumbs as $index => $crumb)
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 flex-shrink-0 text-gray-400 ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        @if($crumb['current'] ?? false)
                            <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white truncate max-w-xs sm:max-w-none">{{ $crumb['label'] }}</span>
                        @else
                            <a href="{{ $crumb['url'] }}" class="ml-2 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white truncate max-w-xs sm:max-w-none">{{ $crumb['label'] }}</a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </nav>
</div>

@if(empty($breadcrumbs))
    {{-- Hide if no breadcrumbs --}}
    <div style="display: none;"></div>
@endif

