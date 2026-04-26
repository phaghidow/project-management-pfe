<x-app-layout>
<div class="page-mobile max-w-4xl mx-auto">
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start mb-8">
        <div class="flex items-center gap-3">
            <svg class="w-8 h-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <div>
                <h1 class="text-3xl font-bold">{{ $milestone->name }}</h1>
                <div class="flex items-center gap-2 mt-2">
                    <x-due-date :date="$milestone->due_date" />
                </div>
                <p class="text-sm text-gray-500 mt-1">Responsable: {{ $milestone->project->user->name }}</p>
            </div>
        </div>


        <div class="flex flex-wrap gap-2">
            <a href="{{ route('milestones.edit', $milestone) }}" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                Modifier
            </a>
            <form method="POST" action="{{ route('milestones.destroy', $milestone) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" onclick="return confirm('Supprimer ce jalon et ses tâches associées ?')">
                    Supprimer
                </button>
            </form>
        </div>
    </div>

    <!-- Tâches du jalon -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Tâches ({{ $milestone->tasks->count() }})</h2>

        @if($milestone->tasks->count() > 0)
            <div class="table-responsive">
                <table class="responsive-table divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignés</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Échéance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($milestone->tasks as $task)
                            <tr class="{{ $task->status === 'validated' ? 'opacity-60' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:underline font-medium">
                                        {{ $task->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                <x-status-badge :status="$task->status" />

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @foreach($task->users as $user)
                                        {{ $user->name }}<br>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $task->due_date?->format('d/m/Y') ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                Aucune tâche pour ce jalon.
                <a href="{{ route('tasks.create') }}" class="block mt-4 text-blue-600 hover:underline">
                    Créer la première tâche
                </a>
            </div>
        @endif
    </div>

    @include('partials.audit-timeline', ['audits' => $milestone->auditLogs()->latest()->take(20)->get()])

    <div class="mt-6 flex gap-2">
        <a href="{{ route('milestones.index') }}" class="text-gray-600 hover:text-gray-900">
            ← Tous les jalons
        </a>
        <a href="{{ route('projects.show', $milestone->project) }}" class="text-blue-600 hover:underline">
            Projet {{ $milestone->project->name }}
        </a>
    </div>
</div>
</x-app-layout>

