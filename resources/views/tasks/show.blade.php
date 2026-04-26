<x-app-layout>
<div class="p-6 max-w-4xl mx-auto">
    <div class="flex justify-between items-start mb-8">
        <div class="flex items-center gap-3 {{ $task->status === 'validated' ? 'opacity-60 grayscale' : '' }}">
            <svg class="w-8 h-8 text-[#397B44]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h1 class="text-3xl font-bold mb-1">{{ $task->name }}</h1>
                <div class="flex items-center gap-2">
                    <x-status-badge :status="$task->status" size="lg" />
                    @if($task->validated_at)
                        <span class="text-sm text-gray-600">Validée le {{ $task->validated_at->format('d/m/Y H:i') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            @if($task->status !== 'validated')
            <a href="{{ route('tasks.edit', $task) }}" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                Modifier
            </a>
            @endif
            <a href="{{ route('tasks.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                Liste
            </a>
        </div>
    </div>

    @if($task->status === 'validated')
        <div class="bg-[#E8F5E9] border border-[#397B44]/30 rounded-lg p-4 mb-6 mb-opacity-60">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-[#397B44]" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-[#397B44]">Tâche validée</h3>
                    <p class="mt-1 text-sm text-[#2d6236]">Les actions de démarrage et validation ne sont plus disponibles.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-8 mb-8">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Détails</h2>
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Jalon</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('milestones.show', $task->milestone) }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                            {{ $task->milestone->project->name }} / {{ $task->milestone->name }}
                        </a>
                    </dd>
                </div>
                @if($task->start_date)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Début</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $task->start_date->format('d/m/Y') }}</dd>
                    </div>
                @endif
                @if($task->end_date)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fin</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $task->end_date->format('d/m/Y') }}</dd>
                    </div>
                @endif
                @if($task->due_date)
                    <div>
                    <dt class="text-sm font-medium text-gray-500">Échéance</dt>
                        <dd class="mt-1">
                            <x-due-date :date="$task->due_date" />
                        </dd>

                    </div>
                @endif
            </dl>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Intervenants ({{ $task->users->count() }})</h2>
            <div class="space-y-2">
                @forelse($task->users as $user)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
                        @if($user->id === auth()->id())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#E8F5E9] text-[#397B44]">
                                Vous
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Aucun intervenant assigné</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Actions</h2>
        </div>

        <div class="flex gap-4">
            @if($task->status === 'pending' || $task->status === 'in_progress')
                <form method="POST" action="{{ route('tasks.start', $task) }}" class="inline-flex items-center space-x-2">
                    @csrf
                    <button type="submit" onclick="return confirm('Démarrer cette tâche ? Toutes les dépendances doivent être terminées.')" 
                            class="bg-slate-700 hover:bg-slate-800 text-white px-6 py-2 rounded-md font-medium transition-colors">
                        🚀 Démarrer
                    </button>
                </form>

                @if($task->canBeValidated())
                    <form method="POST" action="{{ route('tasks.validate', $task) }}" class="inline-flex items-center space-x-2">
                        @csrf
                        <button type="submit" onclick="return confirm('Valider définitivement cette tâche ? Cette action est irréversible.')" 
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                            ✅ Valider
                        </button>
                    </form>
                @else
                    <div class="text-sm text-gray-500 bg-gray-100 px-4 py-2 rounded-md">
                        ⏳ En attente de dépendances
                    </div>
                @endif
            @else
                <div class="text-lg font-semibold text-emerald-700 bg-emerald-50 px-6 py-3 rounded-md border-2 border-emerald-200">
                    ✅ Tâche validée définitivement
                </div>
            @endif
        </div>
    </div>

@if($task->dependencies->count() > 0)
        <div class="mt-8">
            <h3 class="text-lg font-bold mb-4">Dépendances ({{ $task->dependencies->count() }})</h3>
            <div class="grid gap-4">
                @foreach($task->dependencies as $dep)
                    <form method="POST" action="{{ route('tasks.dependencies.remove', [$task, $dep]) }}" class="flex items-center p-4 bg-gray-50 rounded-lg border">
                        @csrf
                        @method('DELETE')
                        <div class="flex-1">
                            <span class="font-medium">{{ $dep->name }}</span>
                            <span class="ml-2 px-2 py-1 text-xs rounded-full {{ $dep->status === 'validated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($dep->status) }}
                            </span>
                            <a href="{{ route('tasks.show', $dep) }}" class="text-indigo-600 hover:text-indigo-900 text-sm ml-2">Voir</a>
                        </div>
                        <button type="submit" onclick="return confirm('Supprimer cette dépendance ? Cette action affectera les validations.') " class="text-red-600 hover:text-red-800 font-medium">
                            Supprimer
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    @if($task->dependents->count() > 0)
        <div class="mt-8">
            <h3 class="text-lg font-bold mb-4">Tâches dépendantes ({{ $task->dependents->count() }})</h3>
            <div class="grid gap-4">
                @foreach($task->dependents as $dependent)
                    <div class="flex items-center p-4 bg-blue-50 rounded-lg">
                        <div class="flex-1">
                            <span class="font-medium">{{ $dependent->name }}</span>
                            <span class="ml-2 px-2 py-1 text-xs rounded-full {{ $dependent->status === 'validated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($dependent->status) }}
                            </span>
                            <a href="{{ route('tasks.show', $dependent) }}" class="text-indigo-600 hover:text-indigo-900 text-sm ml-2">Voir</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @include('partials.audit-timeline', ['audits' => $task->auditLogs()->latest()->take(20)->get()])

    @include('partials.attachments', ['attachable' => $task, 'type' => 'task'])

</div>
</x-app-layout>

