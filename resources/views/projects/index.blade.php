<x-app-layout>
<div class="page-mobile">

    {{-- Filters Form Component --}}
    <x-search-filter-bar
        route="projects.index"
        searchPlaceholder="Nom, description, responsable..."
        :filterOptions="[
            'status' => [
                'label' => 'Statut',
                'type' => 'select',
                'values' => [
                    'draft' => 'Brouillon',
                    'in_progress' => 'En cours',
                    'completed' => 'Clôturé',
                ]
            ],
            'user_id' => [
                'label' => 'Responsable',
                'type' => 'select',
                'values' => \App\Models\User::where('status', \App\Models\User::STATUS_ACTIVE)->orderBy('name')->pluck('name', 'id')->toArray()
            ],
            'start_date' => [
                'label' => 'Date début (à partir du)',
                'type' => 'date',
            ],
            'end_date' => [
                'label' => 'Date fin (jusqu\'au)',
                'type' => 'date',
            ],
        ]"
    />

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center mb-4 mt-6">
        <h1 class="text-xl font-bold">Projets</h1>

                    <a href="{{ route('projects.create') }}"
                            class="btn-primary">
                        + Nouveau projet
                </a>
    </div>

    <div class="grid gap-4" style="min-height: 400px;">
        @forelse($projects as $project)
            <div class="bg-white p-4 shadow rounded">

                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h2 class="font-bold text-lg">{{ $project->name }}</h2>
                </div>

                <div class="flex flex-wrap gap-2 mb-2">
                    <x-status-badge :status="$project->status ?? 'draft'" />
                    <x-due-date :date="$project->end_date" />
                </div>

                <x-progress-bar :percent="$project->progressPercentage() ?? 0" label="Progression" color="indigo" />

                <p class="text-sm text-gray-500 mt-3">
                    {{ $project->description }}
                </p>

                <div class="mt-2 text-xs text-gray-400">
                    Owner: {{ $project->user->name ?? '-' }}
                </div>


                <div class="mt-3 flex gap-2">

                    <a href="{{ route('projects.show', $project->id) }}"
                       class="text-blue-600 text-sm">Voir</a>

                    <a href="{{ route('milestones.index') }}"
                       class="text-indigo-600 text-sm hover:underline">Jalons</a>

                    <a href="{{ route('projects.edit', $project->id) }}"
                       class="text-yellow-600 text-sm">Modifier</a>

                    <form method="POST"
                          action="{{ route('projects.destroy', $project->id) }}">
                        @csrf
                        @method('DELETE')

                        <button class="text-red-600 text-sm">
                            Supprimer
                        </button>
                    </form>

                </div>

            </div>
        @empty
            <div class="bg-white p-8 shadow rounded text-center text-gray-500">
                Aucun projet trouvé @if(isset($filters) && !empty(array_filter($filters))) avec les filtres actuels @endif.
            </div>
        @endforelse

        <div class="mt-8">
            {{ $projects->appends(request()->query())->links() }}
        </div>
    </div>

</div>

</x-app-layout>