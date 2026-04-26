@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestion des Utilisateurs</h1>
            <p class="text-gray-600 mt-1">{{ $users->total() }} utilisateurs</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg shadow hover:bg-blue-700 transition">
            + Nouvel utilisateur
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white p-6 rounded-xl shadow border mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, username, email..." class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                <select name="role" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les rôles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Structure</label>
                <select name="structure_id" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Toutes structures</option>
                    @foreach($structures as $structure)
                        <option value="{{ $structure->id }}" {{ request('structure_id') == $structure->id ? 'selected' : '' }}>
                            {{ $structure->hierarchy_path }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select name="status" class="flex-1 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous statuts</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="en_attente" {{ request('status') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Désactivé</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Filtrer</button>
                @if(request()->hasAny(['search', 'role', 'structure_id', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">Effacer</a>
                @endif
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
                        </td>
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
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
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
