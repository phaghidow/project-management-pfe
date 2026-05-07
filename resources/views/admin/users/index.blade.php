@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestion des Utilisateurs</h1>
            <p class="text-gray-600 mt-1">{{ $users->total() }} utilisateurs</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn-primary">
            + Nouvel utilisateur
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white p-6 rounded-xl shadow border mb-8 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Nom, username, email..." class="w-full min-w-0 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                <select id="role" name="role" class="w-full min-w-0 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les rôles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="structure_id" class="block text-sm font-medium text-gray-700 mb-2">Structure</label>
                <select
                    id="structure_id"
                    name="structure_id"
                    class="w-full min-w-0 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"
                    data-tree-select="structures"
                    data-placeholder="Rechercher une structure..."
                >
                    <option value="">Toutes structures</option>
                    @foreach($structures as $structure)
                        <option
                            value="{{ $structure->id }}"
                            data-level="{{ (int) ($structure->level ?? 0) }}"
                            data-name="{{ $structure->name }}"
                            data-path="{{ str_replace(' > ', ' / ', $structure->hierarchy_path) }}"
                            {{ request('structure_id') == $structure->id ? 'selected' : '' }}
                        >
                            {{ $structure->hierarchy_path }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select id="status" name="status" class="w-full min-w-0 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous statuts</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="en_attente" {{ request('status') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Désactivé</option>
                </select>
            </div>

            <div class="md:col-span-4 flex flex-col sm:flex-row gap-2 sm:justify-end">
                <button type="submit" class="inline-flex items-center justify-center bg-primary-500 text-white px-6 py-2 rounded-lg hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition w-full sm:w-auto !text-white">
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'role', 'structure_id', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="btn-light text-center w-full sm:w-auto">
                        Effacer
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Users Table --}}
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fonction</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Structure</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            @if($user->function)
                                <div class="text-xs text-gray-500 mt-0.5">{{ $user->function }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $user->function ?? 'Non renseignée' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->username }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ str_replace('_', ' ', ucfirst($user->role)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->structure?->hierarchy_path ?? 'Aucune' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-status-badge :status="$user->status" size="sm" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-900">Voir</a>
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="inline" onsubmit="return confirm('Voulez-vous {{ $user->isActive() ? 'désactiver' : 'activer' }} cet utilisateur ?');">
                                @csrf
                                <button type="submit" class="text-{{ $user->isActive() ? 'red' : 'green' }}-600 hover:text-{{ $user->isActive() ? 'red' : 'green' }}-900 font-medium">
                                    {{ $user->isActive() ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900">Modifier</a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Confirmer suppression?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                            Aucun utilisateur trouvé. <a href="{{ route('admin.users.create') }}" class="text-blue-600 hover:underline">Créer le premier</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    <div class="px-6 py-4 bg-gray-50">
        {{ $users->appends(request()->query())->links() }}
    </div>
</div>
@endsection
