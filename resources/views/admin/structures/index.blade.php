@extends('layouts.app')

@section('title', 'Gestion des Structures - Admin')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestion des Structures</h1>
            <p class="text-gray-600 mt-1">{{ $structures->total() }} structures</p>
        </div>
        <a href="{{ route('admin.structures.create') }}" class="inline-flex items-center justify-center bg-primary-500 text-white px-6 py-2.5 rounded-lg shadow hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition !text-white">
            + Nouvelle structure
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white p-6 rounded-xl shadow border mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, code..." class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select name="type" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous types</option>
                    <option value="dg" {{ request('type') === 'dg' ? 'selected' : '' }}>DG</option>
                    <option value="pole" {{ request('type') === 'pole' ? 'selected' : '' }}>Pôle</option>
                    <option value="division" {{ request('type') === 'division' ? 'selected' : '' }}>Division</option>
                    <option value="direction" {{ request('type') === 'direction' ? 'selected' : '' }}>Direction</option>
                    <option value="autre" {{ request('type') === 'autre' ? 'selected' : '' }}>Autre</option>
                </select>
            </div>
            <div class="flex gap-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Niveau</label>
                <select name="level" class="flex-1 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous niveaux</option>
                    @for($i=1; $i<=10; $i++)
                        <option value="{{ $i }}" {{ request('level') == $i ? 'selected' : '' }} >Niv. {{ $i }}</option>
                    @endfor
                </select>
                <button type="submit" class="inline-flex items-center justify-center bg-primary-500 text-white px-6 py-2 rounded-lg hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition !text-white">Filtrer</button>
                @if(request()->hasAny(['search', 'type', 'level']))
                    <a href="{{ route('admin.structures.index') }}" class="inline-flex items-center justify-center bg-gray-50 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">Effacer</a>
                @endif
            </div>
        </div>
    </form>

    {{-- Structures Table --}}
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Niveau</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($structures as $structure)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $structure->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $structure->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $structure->code ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                {{ ucfirst($structure->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Niv. {{ $structure->level }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $structure->parent?->name ?? 'Racine' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $structure->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.structures.show', $structure) }}" class="text-indigo-600 hover:text-indigo-900">Voir</a>
                            <a href="{{ route('admin.structures.edit', $structure) }}" class="text-blue-600 hover:text-blue-900">Édit</a>
                            <form method="POST" action="{{ route('admin.structures.destroy', $structure) }}" class="inline" onsubmit="return confirm('Confirmer suppression?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Suppr</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            Aucune structure. <a href="{{ route('admin.structures.create') }}" class="text-blue-600 hover:underline font-semibold">Créer la première</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gray-50">
            {{ $structures->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

