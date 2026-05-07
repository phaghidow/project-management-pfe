@extends('layouts.app')

@section('title', 'Détails - {{ $user->name }}')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="flex items-center mb-8">
        <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900 ml-4">Détails de {{ $user->name }}</h1>
    </div>

    <div class="bg-white shadow-xl rounded-2xl p-8 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="text-center mb-8">
                    <div class="w-24 h-24 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full mx-auto flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6.47-2.47a5.47 5.47 0 11-7.94 0 5.47 5.47 0 017.94 0zM10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-500">{{ $user->username }}</p>
                    <x-status-badge :status="$user->status" size="lg" class="mt-2 mx-auto block" />
                </div>
                
                <div class="space-y-4">
                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="text-center" onsubmit="return confirm('Voulez-vous {{ $user->isActive() ? 'désactiver' : 'activer' }} cet utilisateur ?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center w-full bg-{{ $user->isActive() ? 'red' : 'green' }}-600 text-white py-3 px-6 rounded-xl font-semibold hover:bg-{{ $user->isActive() ? 'red' : 'green' }}-700 focus:bg-{{ $user->isActive() ? 'red' : 'green' }}-700 active:bg-{{ $user->isActive() ? 'red' : 'green' }}-800 transition shadow-lg !text-white">
                            {{ $user->isActive() ? 'Désactiver l\'utilisateur' : 'Activer l\'utilisateur' }}
                        </button>
                    </form>
                        <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center justify-center w-full bg-primary-500 text-white py-3 px-6 rounded-xl font-semibold hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition shadow-lg text-center !text-white">
                        Modifier les détails
                    </a>
                </div>

            <div class="lg:col-span-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Contact</h3>
                        <div class="space-y-2 text-sm">
                            <p><span class="font-medium">Email:</span> <a href="mailto:{{ $user->email }}" class="text-blue-600 hover:underline">{{ $user->email }}</a></p>
                            <p><span class="font-medium">Rôle:</span> <span class="capitalize">{{ str_replace('_', ' ', $user->role) }}</span></p>
                            <p><span class="font-medium">Structure:</span> {{ $user->structure?->hierarchy_path ?? 'Aucune' }}</p>
                            <p><span class="font-medium">Créé le:</span> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                            <p><span class="font-medium">Dernière connexion:</span> {{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Jamais' }}</p>
                        </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Statut</h3>
                        <div class="space-y-2 text-sm">
                            <p><span class="font-medium">Statut actuel:</span> {{ $user->status_label }}</p>
                            <p><span class="font-medium">Actif:</span> {{ $user->isActive() ? 'Oui' : 'Non' }}</p>
                            <p><span class="font-medium">Nombre de changements:</span> {{ $user->statusHistory->count() }}</p>
                        </div>
                </div>
        </div>

    @if($user->statusHistory->count() > 0)
        <div class="bg-white shadow-xl rounded-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <svg class="w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Historique des statuts ({{ $user->statusHistory->count() }})
            </h2>
            
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @foreach($user->statusHistory()->latest()->take(10)->get() as $history)
                    <div class="flex items-start space-x-4 p-4 border-l-4 border-blue-400 bg-blue-50 rounded-r-lg">
                        <div class="shrink-0">
                            <div class="w-12 h-12 bg-white border-2 border-blue-200 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">{{ $history->actor->name ?? 'Système' }}</span>
                                <span class="text-xs text-gray-500">{{ $history->changed_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-900">
                                Changement de statut: <span class="font-semibold text-red-600 bg-red-100 px-2 py-1 rounded text-xs">{{ $history->old_status }}</span> 
                                → <span class="font-semibold text-green-600 bg-green-100 px-2 py-1 rounded text-xs">{{ $history->new_status }}</span>
                                <br><span class="text-xs text-gray-500">Raison: {{ $history->reason ?? 'Non spécifiée' }}</span>
                            </p>
                        </div>
                @endforeach
            </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tailwind toast handling if needed
});
</script>
@endsection
