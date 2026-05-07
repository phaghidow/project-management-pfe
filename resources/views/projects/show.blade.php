<x-app-layout>
<div class="page-mobile max-w-6xl mx-auto">
    <div class="flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-start mb-8">
        <div class="flex items-center gap-3">
            <svg class="w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <div>
                <h1 class="text-3xl font-bold mb-1">{{ $project->name }}</h1>
                <x-status-badge :status="$project->status ?? 'draft'" size="lg" />
            </div>
        </div>


        <div class="flex flex-wrap gap-2">
            <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center justify-center bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-800 transition !text-white">
                Modifier projet
            </a>
            @can('closeProject', $project)
            @if($project->tasks()->where('status', '!=', 'validated')->count() === 0 && $project->status !== 'completed')
                <form method="POST" action="{{ route('projects.close', $project) }}" class="inline">
                    @csrf
                    <button type="submit"
                        onclick="return confirm('Clôturer définitivement ce projet ?')"
                        class="inline-flex items-center justify-center bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:bg-green-700 active:bg-green-800 transition !text-white">
                        ✅ Clôturer projet
                    </button>
                </form>
            @endif
            @endcan
            <form method="POST" action="{{ route('projects.destroy', $project) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center justify-center bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:bg-red-700 active:bg-red-800 transition !text-white" onclick="return confirm('Supprimer ce projet ?')">
                    Supprimer (soft)
                </button>
            </form>
            @can('restore', $project)
            <form method="POST" action="{{ route('projects.restore', $project) }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center bg-success-500 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:bg-green-700 active:bg-green-800 transition !text-white" onclick="return confirm('Restaurer ?')">
                    Restaurer
                </button>
            </form>
            @endcan
            @can('forceDelete', $project)
            <form method="POST" action="{{ route('projects.force-delete', $project) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center justify-center bg-red-800 text-white px-4 py-2 rounded-md hover:bg-red-900 focus:bg-red-900 active:bg-red-950 transition !text-white" onclick="return confirm('Supprimer définitivement ?')">
                    Force Delete
                </button>
            </form>
            @endcan
        </div>
    </div>

    <!-- Détails projet -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">Détails</h2>
        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div>
                <strong>Responsable:</strong> {{ $project->user->name }}
            </div>
            @if($project->start_date)
                <div><strong>Début:</strong> {{ $project->start_date->format('d/m/Y') }}</div>
            @endif
            @if($project->end_date)
                <div><strong>Fin prévue:</strong> {{ $project->end_date->format('d/m/Y') }}</div>
            @endif
            @if($project->description)
                <div class="md:col-span-2"><strong>Description:</strong> {{ $project->description }}</div>
            @endif
        </div>
    </div>

    <!-- Tâches du projet -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Tâches du projet ({{ $project->tasks->count() }})</h2>
            <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="inline-flex items-center justify-center bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:bg-green-700 active:bg-green-800 text-sm gap-2 transition !text-white">
                + Nouvelle tâche
            </a>
        </div>

        @if($project->tasks->count() > 0)
            <!-- Progression -->
            <div class="mb-8">
                <x-progress-bar :percent="$project->progressPercentage() ?? 0" label="Progression: {{ $project->completedTasks->count() }} / {{ $project->tasks->count() }} tâches validées" color="green" />

            </div>

            <!-- Stats par statut -->
            <div class="grid md:grid-cols-4 gap-4 mb-8">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-800">{{ $project->tasks->where('status', 'pending')->count() }}</div>
                    <div class="text-sm text-gray-500 uppercase tracking-wide">À faire</div>
                </div>
                <div class="text-center p-4 bg-yellow-50 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-800">{{ $project->tasks->where('status', 'in_progress')->count() }}</div>
                    <div class="text-sm text-yellow-700 uppercase tracking-wide">En cours</div>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-800">{{ $project->completedTasks->count() }}</div>
                    <div class="text-sm text-green-700 uppercase tracking-wide">Validées</div>
                </div>
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-800">{{ $project->tasks->whereNotIn('status', ['pending', 'in_progress', 'validated'])->count() }}</div>
                    <div class="text-sm text-blue-700 uppercase tracking-wide">Autres</div>
                </div>
            </div>

            <!-- Liste des tâches -->
            <div class="table-responsive overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="w-full min-w-full responsive-table divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tâche</th>
                            <th class="w-24 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="w-32 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Échéance</th>
                            <th class="max-w-xs px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignés</th>
                            <th class="w-32 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validée</th>
                            <th class="w-20 px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($project->tasks as $task)
                            <tr class="{{ $task->status === 'validated' ? 'opacity-75 bg-green-50' : '' }}">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 break-words">{{ $task->name }}</div>
                                    @if($task->milestone)
                                        <div class="text-xs text-gray-500">Jalon: {{ $task->milestone->name }}</div>
                                    @endif
                                </td>
                                <td class="w-24 px-6 py-4 whitespace-nowrap">
                                    <x-status-badge :status="$task->status" />

                                </td>
                                <td class="w-32 px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($task->due_date)
                                        <x-due-date :date="$task->due_date" format="d/m" />

                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="max-w-xs px-6 py-4">
                                    @if($task->users->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($task->users as $user)
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full whitespace-normal">
                                                    {{ $user->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-500 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="w-32 px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($task->validated_at)
                                        {{ $task->validated_at->format('d/m/Y') }}
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="w-20 px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('tasks.show', $task) }}" class="text-indigo-600 hover:text-indigo-900">Voir</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12 text-gray-500 bg-gray-50 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <h3 class="text-lg font-medium mb-2">Aucune tâche</h3>
                <p class="mb-4">Commencez par ajouter votre première tâche.</p>
                <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="inline-flex items-center justify-center bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 focus:bg-green-700 active:bg-green-800 transition !text-white">
                    + Créer la première tâche
                </a>
            </div>
        @endif
    </div>

    <!-- Jalons -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Jalons ({{ $project->milestones->count() }})</h2>
            <a href="{{ route('milestones.create') }}?project_id={{ $project->id }}" class="inline-flex items-center justify-center bg-primary-500 text-white px-4 py-2 rounded-md hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 text-sm transition !text-white">
                + Ajouter jalon
            </a>
        </div>

        @if($project->milestones->count() > 0)
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($project->milestones as $milestone)
                    <div class="border rounded-lg p-4 hover:shadow-md">
                        <h3 class="font-bold mb-1">{{ $milestone->name }}</h3>
                        @if($milestone->due_date)
                            <p class="text-xs text-gray-500 mb-2">{{ $milestone->due_date->format('d/m/Y') }}</p>
                        @endif
                        <p class="text-sm text-gray-600 mb-3">{{ $milestone->tasks->count() }} tâches</p>
                        <div class="flex gap-2">
                            <a href="{{ route('milestones.show', $milestone) }}" class="text-blue-600 text-xs hover:underline">Voir</a>
                            <a href="{{ route('tasks.create') }}?milestone_id={{ $milestone->id }}" class="text-green-600 text-xs hover:underline">+ Tâche</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                Aucun jalon. <a href="{{ route('milestones.create') }}?project_id={{ $project->id }}" class="text-primary-600 hover:text-primary-700 hover:underline">Créer le premier</a>
            </div>
        @endif
    </div>

@include('partials.audit-timeline', ['audits' => $project->auditLogs()->latest()->take(20)->get()])

    @include('partials.attachments', ['attachable' => $project, 'type' => 'project'])



    <!-- Navigation -->
    <div class="flex gap-4 text-sm">
        <a href="{{ route('projects.index') }}" class="text-gray-600 hover:text-gray-900">
            ← Tous les projets
        </a>
        <a href="{{ route('milestones.index') }}" class="text-primary-600 hover:text-primary-700 hover:underline">
            Voir tous les jalons
        </a>
        <a href="{{ route('tasks.index') }}" class="text-success-500 hover:underline">
            Voir toutes les tâches
        </a>
    </div>
</div>
</x-app-layout>

