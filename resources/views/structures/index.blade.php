<x-app-layout>
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row justify-between items-start mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Gestion des Structures</h1>
                <p class="text-lg text-gray-600 mt-2">Organigramme dynamique avec hiérarchie</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('structures.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transition-all">
                    + Ajouter structure
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-8 bg-green-50 border border-green-200 rounded-xl p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path>
                    </svg>
                    <span class="text-green-800 font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"></path>
                    </svg>
                    <span class="text-red-800 font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Dynamic Tree --}}
            <div class="lg:col-span-2">
                <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Arbre hiérarchique</h2>
                        <button id="refresh-tree" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Actualiser
                        </button>
                    </div>
                    <div id="tree-container" class="structures-tree space-y-4 min-h-[400px]">
                        <div class="text-center py-12 text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <p>Chargement de l'organigramme...</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Flat List --}}
            <div>
                <div class="bg-white shadow-xl rounded-2xl p-6 sticky top-6 h-fit">
                    <h3 class="text-xl font-bold mb-4">Liste rapide</h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($structures as $structure)
                            <div class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-semibold text-gray-900 text-sm">{{ $structure->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $structure->type }} • Niv. {{ $structure->level }}</div>
                                    </div>
                                    <div class="opacity-0 group-hover:opacity-100 transition flex gap-1">
                                        <a href="{{ route('structures.edit', $structure) }}" class="text-blue-600 hover:text-blue-800 text-xs p-1 hover:bg-blue-100 rounded">Édit</a>
                                        <form action="{{ route('structures.destroy', $structure) }}" method="POST" class="inline" onsubmit="return confirm('Confirmer suppression?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs p-1 hover:bg-red-100 rounded">Suppr</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/structures.js'])
</x-app-layout>
