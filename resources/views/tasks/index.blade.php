<x-app-layout>
<div class="page-mobile">

    {{-- Filters Form Component --}}
    <x-search-filter-bar
        route="tasks.index"
        searchPlaceholder="Nom de tâche ou projet..."
        :filterOptions="[
            'status' => [
                'label' => 'Statut',
                'type' => 'select',
                'values' => [
                    'pending' => 'En attente',
                    'in_progress' => 'En cours',
                    'validated' => 'Validée',
                ]
            ],
        ]"
    />

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center mb-4 mt-6">
        <h1 class="text-xl font-bold">Tâches</h1>

        <a href="{{ route('tasks.create') }}"
           class="bg-primary-500 text-white px-4 py-2 rounded">
            + Nouvelle tâche
        </a>
    </div>

    <div class="grid gap-4">

        @foreach($tasks as $task)

            <div class="bg-white p-4 shadow rounded {{ $task->status === 'validated' ? 'opacity-60 grayscale' : '' }}">

                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-[#397B44]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h2 class="font-bold">{{ $task->name }}</h2>
                </div>

                <div class="flex flex-wrap gap-2 mb-2">
                    <x-status-badge :status="$task->status" />
                    <x-due-date :date="$task->due_date" />
                </div>

                <p class="text-sm text-gray-500">
                    Projet: {{ $task->milestone->project->name ?? '-' }}
                </p>


                <div class="mt-2 text-xs text-gray-500">
                    Assignés:
                    @foreach($task->users as $user)
                        {{ $user->name }},
                    @endforeach
                </div>

                <div class="mt-3 flex gap-2">
                    @if($task->status === 'pending' || $task->status === 'in_progress')
                        <form method="POST" action="{{ route('tasks.start', $task) }}" class="inline-block">
                            @csrf
                            <button type="submit" onclick="return confirm('Démarrer cette tâche ? (Dépendances doivent être terminées)');"
                                    class="px-3 py-1 rounded bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium transition-colors">🚀 Démarrer</button>
                        </form>

                        <form method="POST" action="{{ route('tasks.validate', $task) }}" class="inline-block">
                            @csrf
                            <button type="submit" onclick="return confirm('Valider définitivement cette tâche ? Action irréversible.');"
                                    class="px-3 py-1 rounded bg-[#397B44] hover:bg-[#2d6236] text-white text-xs font-medium transition-colors">✅ Valider</button>
                        </form>
                    @else
                        <span class="text-xs font-semibold text-[#397B44]">Validation définitive</span>
                    @endif
                </div>

            </div>

        @endforeach

    </div>

</div>
</x-app-layout>