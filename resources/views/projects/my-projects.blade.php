<x-app-layout>
<div class="page-mobile">

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

    <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-end mb-4">
        <div>
            <h1 class="text-xl font-bold text-[#1A202C]">Mes Projets</h1>
            <p class="text-sm text-slate-600">Liste simplifiée des projets qui vous sont attribués personnellement</p>
        </div>
        <div class="text-sm text-slate-500">
            {{ $projects->total() }} projet{{ $projects->total() > 1 ? 's' : '' }}
        </div>
    </div>

    <div class="grid gap-4" style="min-height: 400px;">
        @forelse($projects as $project)
            @php
                $statusMeta = [
                    'draft' => ['border' => '#9CA3AF', 'bg' => '#F3F4F6', 'text' => '#4B5563', 'label' => 'Brouillon'],
                    'in_progress' => ['border' => '#3B82F6', 'bg' => '#DBEAFE', 'text' => '#1D4ED8', 'label' => 'En cours'],
                    'completed' => ['border' => '#10B981', 'bg' => '#D1FAE5', 'text' => '#047857', 'label' => 'Clôturé'],
                ][$project->status] ?? ['border' => '#9CA3AF', 'bg' => '#F3F4F6', 'text' => '#4B5563', 'label' => ucfirst($project->status)];
            @endphp

            <div class="bg-white p-5 shadow rounded-xl border-l-4" style="border-left-color: {{ $statusMeta['border'] }}">
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-start">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="font-semibold text-base text-[#1A202C] truncate">{{ $project->name }}</h2>
                            <span class="px-2 py-1 text-[11px] font-semibold rounded-full whitespace-nowrap" style="background-color: {{ $statusMeta['bg'] }}; color: {{ $statusMeta['text'] }}">
                                {{ $statusMeta['label'] }}
                            </span>
                        </div>

                        <p class="text-sm text-slate-600">
                            {{ $project->description ? Str::limit($project->description, 120) : 'Aucune description disponible.' }}
                        </p>
                    </div>

                    @if($project->pivot)
                        <div class="text-xs text-slate-500 sm:text-right space-y-1 flex-shrink-0">
                            <p>📌 Affecté le {{ optional($project->pivot->assigned_at)->format('d/m/Y') ?? 'N/A' }}</p>
                            @if($project->pivot->role_in_project)
                                <p>👤 Rôle : {{ $project->pivot->role_in_project }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                    <div class="rounded-lg bg-gray-50 px-3 py-2">
                        <span class="block text-xs uppercase tracking-wide text-gray-500">Période</span>
                        <span class="font-medium text-[#1A202C]">{{ $project->start_date?->format('d/m/Y') ?? '—' }} → {{ $project->end_date?->format('d/m/Y') ?? '—' }}</span>
                    </div>

                    <div class="rounded-lg bg-gray-50 px-3 py-2">
                        <span class="block text-xs uppercase tracking-wide text-gray-500">Responsable</span>
                        <span class="font-medium text-[#1A202C] truncate">{{ $project->user?->name ?? 'Non défini' }}</span>
                    </div>

                    <div class="rounded-lg bg-gray-50 px-3 py-2">
                        <span class="block text-xs uppercase tracking-wide text-gray-500">Progression</span>
                        <div class="mt-1 flex items-center gap-2">
                            <div class="h-2 flex-1 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" style="width: {{ $project->progressPercentage() }}%; background-color: {{ $statusMeta['border'] }}"></div>
                            </div>
                            <span class="text-xs font-semibold text-[#1A202C] whitespace-nowrap">{{ $project->progressPercentage() }}%</span>
                        </div>
                    </div>
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
