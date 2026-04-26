<x-app-layout>
<div class="p-6 max-w-4xl mx-auto">
    <div class="flex justify-between items-start mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">{{ $structure->name }}</h1>
            <p class="text-xl text-gray-600 mt-2">{{ $structure->type === 'dg' ? 'Direction Générale' : ucfirst($structure->type) }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('structures.edit', $structure) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transition-all">
                Modifier
            </a>
            <a href="{{ route('structures.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transition-all">
                Liste
            </a>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        {{-- Main Info --}}
        <div class="lg:col-span-2">
            <div class="bg-white shadow-2xl rounded-3xl p-8">
                <div class="grid grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Détails</h3>
                        <div class="space-y-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Code</span>
                                <p class="text-2xl font-bold text-gray-900">{{ $structure->code ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Niveau hiérarchique</span>
                                <p class="text-2xl font-bold text-gray-900">Niveau {{ $structure->level }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Parent</span>
                                @if($structure->parent)
                                    <p class="text-xl font-semibold text-blue-600 hover:text-blue-800">
                                        <a href="{{ route('structures.show', $structure->parent) }}">{{ $structure->parent->name }}</a>
                                    </p>
                                    <p class="text-sm text-gray-500">Niv. {{ $structure->parent->level }}</p>
                                @else
                                    <p class="text-xl font-bold text-green-600">Structure racine</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Enfants directes</h3>
                        <div class="space-y-3">
                            @forelse($structure->children as $child)
                                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 hover:bg-blue-100 transition">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $child->name }}</div>
                                            <div class="text-sm text-blue-700">{{ $child->type }}</div>
                                        </div>
                                        <a href="{{ route('structures.show', $child) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Voir</a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500">
                                    Aucune structure enfant
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                @if($structure->description)
                    <div class="bg-gray-50 rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                        <p class="text-lg text-gray-700 leading-relaxed">{{ $structure->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Stats Sidebar --}}
        <div>
            <div class="bg-white shadow-xl rounded-3xl p-6 sticky top-6 h-fit space-y-6">
                <div>
                    <h4 class="text-lg font-bold text-gray-900 mb-2">Actions rapides</h4>
                    <div class="space-y-2">
                        <a href="{{ route('structures.create', ['parent_id' => $structure->id]) }}" class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-3 px-4 rounded-xl font-semibold transition-all">
                            + Ajouter enfant
                        </a>
                        <a href="{{ route('structures.edit', $structure) }}" class="block w-full bg-yellow-600 hover:bg-yellow-700 text-white text-center py-3 px-4 rounded-xl font-semibold transition-all">
                            Modifier
                        </a>
                    </div>
                </div>

                <div class="pt-6 border-t">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ $structure->descendants->count() }}</div>
                        <div class="text-sm text-gray-600">Sous-structures</div>
                    </div>
                </div>

                @if($structure->users->count())
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-3">Utilisateurs assignés</h4>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($structure->users->take(5) as $user)
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 text-sm">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->role }}</div>
                                    </div>
                                </div>
                            @endforeach
                            @if($structure->users->count() > 5)
                                <div class="text-center py-2 text-sm text-gray-500">+{{ $structure->users->count() - 5 }} autres</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</x-app-layout>
