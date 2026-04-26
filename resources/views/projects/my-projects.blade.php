<x-app-layout>
<div class="page-mobile">

    {{-- Filters Form Component --}}
    <x-search-filter-bar
        route="projects.my-projects"
        searchPlaceholder="Nom ou description..."
        :filterOptions="[
            'status' => [
                'label' => 'Statut',
                'type' => 'select',
                'values' => [
                    'draft' => 'Brouillon',
                    'in_progress' => 'En cours',
                    'completed' => 'Terminé',
                    'closed' => 'Clôturé',
                ]
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

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center mb-4">
        <h1 class="text-xl font-bold">Mes Projets</h1>
    </div>

    <div class="grid gap-4" style="min-height: 400px;">
        @forelse($projects as $project)
            <div class="bg-white p-6 shadow rounded border-l-4" style="border-left-color: {{ $project->status == 'draft' ? '#9CA3AF' : ($project->status == 'in_progress' ? '#3B82F6' : ($project->status == 'completed' ? '#10B981' : '#6B7280')) }}">

                <div class="flex justify-between items-start mb-2">
                    <h2 class="font-bold text-lg">{{ $project->name }}</h2>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-opacity-20"
                          style="background-color: {{ $project->status == 'draft' ? '#9CA3AF' : ($project->status == 'in_progress' ? '#3B82F6' : ($project->status == 'completed' ? '#10B981' : '#6B7280')) }}; color: {{ $project->status == 'draft' ? '#6B7280' : ($project->status == 'in_progress' ? '#2563EB' : ($project->status == 'completed' ? '#059669' : '#4B5563')) }}">
                        {{ $project->status == 'draft' ? 'Brouillon' : ($project->status == 'in_progress' ? 'En cours' : ($project->status == 'completed' ? 'Terminé' : 'Clôturé')) }}
                    </span>
                </div>

                @if($project->description)
                    <p class="text-sm text-gray-600 mb-3">{{ Str::limit($project->description, 100) }}</p>
                @endif

                <div class="text-xs text-gray-500 mb-3">
                    <span>📅 {{ $project->start_date }} au {{ $project->end_date }}</span>
                    @if($project->user)
                        <span class="ml-4">👤 {{ $project->user->name }}</span>
                    @endif
                </div>

                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span>Progression</span>
                        <span>{{ $project->progressPercentage() }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-linear-to-r from-indigo-500 to-blue-600 h-2 rounded-full" style="width: {{ $project->progressPercentage() }}%"></div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('projects.show', $project) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium">
                        Voir le projet
                    </a>
                </div>

            </div>
        @empty
            <div class="bg-white p-8 shadow rounded text-center text-gray-500 col-span-full">
                Aucun projet trouvé @if(isset($filters) && !empty(array_filter($filters))) avec les filtres actuels @endif.
            </div>
        @endforelse

        <div class="mt-8">
            {{ $projects->appends(request()->query())->links() }}
        </div>
    </div>

</div>
</x-app-layout>
