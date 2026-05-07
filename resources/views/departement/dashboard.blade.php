<x-app-layout>
<div class="page-mobile">
    @php $user = auth()->user(); @endphp

    {{-- Header --}}
    <div class="flex flex-col gap-4 xl:flex-row xl:justify-between xl:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#1A202C]">Dashboard Département</h1>
            <p class="mt-1 text-lg text-slate-700">
                {{ $user->structure?->name ?? 'Votre département' }}
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
            <a href="{{ route('projects.create') }}" class="btn-primary">
                + Nouveau projet
            </a>
            <a href="{{ route('gantt') }}" class="btn-primary">
                🎯 Gantt
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 shadow-lg rounded-2xl hover:shadow-2xl transition-all border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #2E3192 0%, #1E216D 100%);">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5V4.5A1.5 1.5 0 015.5 3h13A1.5 1.5 0 0120 4.5v15a.5.5 0 01-.5.5H4.5a.5.5 0 01-.5-.5zM8 7h8M8 11h8M8 15h5"></path>
                    </svg>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-slate-700 uppercase tracking-wide">Projets</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['projects_count'] }}</p>
                </div>
            </div>
            <p class="mt-2 text-sm text-primary-500 font-semibold">Dans votre périmètre</p>
        </div>

        <div class="bg-white p-6 shadow-lg rounded-2xl hover:shadow-2xl transition-all border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 bg-linear-to-r from-emerald-500 to-emerald-600 rounded-xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-slate-700 uppercase tracking-wide">Membres</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['members_count'] }}</p>
                </div>
            </div>
            <p class="mt-2 text-sm text-emerald-600 font-semibold">Rattachés à votre structure</p>
        </div>

        <div class="bg-white p-6 shadow-lg rounded-2xl hover:shadow-2xl transition-all border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 bg-linear-to-r from-emerald-500 to-emerald-600 rounded-xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-slate-700 uppercase tracking-wide">Tâches</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['tasks_count'] }}</p>
                </div>
            </div>
            <p class="mt-2 text-sm text-secondary-500 font-semibold">{{ $stats['tasks_validated'] }} validées</p>
        </div>

        <div class="bg-white p-6 shadow-lg rounded-2xl hover:shadow-2xl transition-all border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 bg-linear-to-r from-amber-500 to-amber-600 rounded-xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-slate-700 uppercase tracking-wide">Progression</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ round($stats['progress_avg'], 1) }}%</p>
                </div>
            </div>
            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                <div class="bg-linear-to-r from-primary-500 to-secondary-500 h-2 rounded-full" style="width: {{ round($stats['progress_avg'], 1) }}%"></div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-8 shadow-xl rounded-2xl">
            <h3 class="text-xl font-bold mb-6">Distribution des tâches par statut</h3>
            <div class="chart-wrap">
                <canvas id="statusChart" class="responsive-chart"></canvas>
            </div>
        </div>
        <div class="bg-white p-8 shadow-xl rounded-2xl">
            <h3 class="text-xl font-bold mb-6">Progression des projets</h3>
            <div class="chart-wrap">
                <canvas id="progressChart" class="responsive-chart"></canvas>
            </div>
        </div>
    </div>

    {{-- Projects Table + Members List --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        {{-- Projects Table --}}
        <div class="xl:col-span-2 bg-white shadow-xl rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Projets du département</h2>
                <a href="{{ route('projects.index') }}" class="text-primary-500 hover:text-primary-600 font-medium">Voir tout →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projet</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsable</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Structure</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progression</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($projects as $project)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $project->name }}</div>
                                <div class="text-xs text-slate-700">{{ Str::limit($project->description, 40) }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $project->user?->name ?? 'Non assigné' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $project->user?->structure?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $project->progress ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium">{{ number_format($project->progress ?? 0, 2) }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <x-status-badge :status="$project->status" />
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('projects.show', $project) }}" class="text-primary-500 hover:text-primary-600 text-sm font-medium">Détails</a>
                                    @can('update', $project)
                                    <a href="{{ route('projects.edit', $project) }}" class="text-secondary-500 hover:text-secondary-600 text-sm font-medium">Modifier</a>
                                    @endcan
                                    {{-- PAS DE BOUTON SUPPRIMER conformément au cahier des charges --}}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-700">
                                Aucun projet dans votre département.
                                <a href="{{ route('projects.create') }}" class="font-semibold text-[#2E3192] hover:text-[#1E216D]">Créer le premier !</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Members List (Read-only) --}}
        <div class="bg-white shadow-xl rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Membres du département</h2>
                <span class="text-sm text-slate-500">Lecture seule</span>
            </div>
            <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                @forelse($members as $member)
                <div class="flex items-center p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                    <div class="w-10 h-10 rounded-full bg-primary-500/20 flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-bold text-primary-600">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate">{{ $member->name }}</div>
                        <div class="text-xs text-slate-700 truncate">{{ $member->email }}</div>
                        <div class="text-xs text-gray-400">{{ $member->getRoleLabelAttribute() }} • {{ $member->structure?->name ?? 'Aucune structure' }}</div>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $member->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $member->getStatusLabelAttribute() }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-slate-700">
                    Aucun membre rattaché à ce département.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Tasks + Milestones --}}
    <!-- Notification preferences removed -->

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Tasks --}}
        <div class="bg-white shadow-xl rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Tâches du département</h2>

                <a href="{{ route('tasks.index') }}" class="text-primary-500 hover:text-primary-600 font-medium">Voir tout →</a>
            </div>
            <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                @forelse($tasks->take(10) as $task)
                <div class="flex items-center p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                    <div class="w-2 h-2 rounded-full {{ $task->status === 'validated' ? 'bg-[#397B44]' : ($task->status === 'in_progress' ? 'bg-amber-500' : 'bg-gray-500') }} mr-3"></div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate">{{ $task->name }}</div>
                        <div class="text-xs text-slate-700">
                            {{ $task->milestone?->project?->name ?? 'Non assigné' }} •
                            {{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->diffForHumans() : 'Pas d\'échéance' }}
                        </div>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full
                        {{ $task->status === 'validated' ? 'bg-[#E8F5E9] text-[#397B44]' : ($task->status === 'in_progress' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                </div>
                @empty
                <div class="text-center py-12 text-slate-700">
                    Aucune tâche dans ce département.
                </div>
                @endforelse
            </div>
        </div>

        {{-- Milestones --}}
        <div class="bg-white shadow-xl rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Jalons à venir</h2>
                <a href="{{ route('milestones.index') }}" class="text-primary-500 hover:text-primary-600 font-medium">Voir tout →</a>
            </div>
            <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                @forelse($milestones->take(10) as $milestone)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate">{{ $milestone->name }}</div>
                        <div class="text-xs text-slate-700">
                            {{ $milestone->project?->name ?? 'Projet non défini' }}
                            @if($milestone->due_date)
                            • Échéance : {{ $milestone->due_date->format('d/m/Y') }}
                            @endif
                        </div>
                    </div>
                    @if($milestone->due_date && $milestone->due_date->isPast())
                        <span class="text-xs font-bold text-red-500 bg-red-50 px-2 py-1 rounded-full flex-shrink-0">EN RETARD</span>
                    @elseif($milestone->due_date && $milestone->due_date->diffInDays(now()) <= 7)
                        <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-full flex-shrink-0">URGENT</span>
                    @endif
                </div>
                @empty
                <div class="text-center py-12 text-slate-700">
                    Aucun jalon dans ce département.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-8">
        @include('partials.recent-attachments', ['attachments' => $recentAttachments ?? collect(), 'title' => 'Fichiers du département'])

        @include('partials.recent-comments', ['comments' => $recentComments ?? collect(), 'title' => 'Commentaires du département'])
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update notification badge
    fetch('/notifications/count')
        .then(res => res.json())
        .then(count => {
            const badge = document.getElementById('dash-notif-badge');
            if(badge) badge.textContent = count;
        });

    // Status chart
    const statusCtx = document.getElementById('statusChart')?.getContext('2d');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['En cours', 'Validées'],
                datasets: [{
                    data: [
                        {{ $stats['tasks_pending'] }},
                        {{ $stats['tasks_validated'] }}
                    ],
                    backgroundColor: ['#f37021', '#397B44'],
                    borderWidth: 0,
                    cutout: '60%'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // Progress chart
    const progressCtx = document.getElementById('progressChart')?.getContext('2d');
    if (progressCtx) {
        const projectsProgressRaw = @json($projects->pluck('progress', 'name')->filter()->toArray());
        const projectsProgress = (projectsProgressRaw && typeof projectsProgressRaw === 'object' && !Array.isArray(projectsProgressRaw))
            ? projectsProgressRaw
            : {};
        new Chart(progressCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(projectsProgress),
                datasets: [{ label: 'Progression %', data: Object.values(projectsProgress), backgroundColor: 'rgba(243, 112, 33, 0.8)' }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100 } } }
        });
    }
});
</script>
@endpush
</x-app-layout>

