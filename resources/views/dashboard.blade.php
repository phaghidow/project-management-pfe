<x-app-layout>
<div class="page-mobile">
    @php $user = auth()->user(); @endphp

    {{-- Header : titre + actions selon le rôle --}}
    <div class="flex flex-col gap-4 xl:flex-row xl:justify-between xl:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#1A202C]">Dashboard</h1>
            <p class="mt-1 text-lg text-slate-700">
                @if($user->isAdmin())
                    Vue globale du système
                @elseif($user->isChefDepartement())
                    Résumé département {{ $user->structure?->name ?? '' }}
                @elseif($user->isChefProjet())
                    Vos projets en cours
                @else
                    Vos tâches et activités assignées
                @endif
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
            @if($user->isAdmin() || $user->isChefProjet())
                <a href="{{ route('projects.create') }}" class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-xl font-medium shadow-lg transition-all">
                    + Nouveau projet
                </a>
            @endif
            <a href="/notifications" class="bg-at-orange-500/10 hover:bg-at-orange-500/20 text-at-orange-500 px-6 py-3 rounded-xl font-medium shadow-lg transition-all flex items-center border border-at-orange-500/30">
                🔔 Notifications
                <span id="dash-notif-badge" class="ml-2 bg-at-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">0</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @if($user->isMembre())
            {{-- Membre : cartes simplifiées centrées sur ses tâches --}}
            <div class="bg-white p-6 shadow-lg rounded-2xl hover:shadow-2xl transition-all border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-linear-to-r from-primary-500 to-primary-500/80 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-slate-700 uppercase tracking-wide">Tâches assignées</p>
                        <p class="text-3xl font-bold text-[#1A202C] mt-1">{{ $tasks->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 shadow-lg rounded-2xl hover:shadow-2xl transition-all border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-linear-to-r from-emerald-500 to-emerald-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-slate-700 uppercase tracking-wide">Validées</p>
                        <p class="text-3xl font-bold text-[#1A202C] mt-1">{{ $tasks->where('status','validated')->count() }}</p>
                    </div>
                </div>
                <div class="mt-4 text-sm text-emerald-600 font-semibold">
                    {{ $tasks->whereIn('status', ['pending', 'started', 'in_progress'])->count() }} en cours
                </div>
            </div>

            <div class="bg-white p-6 shadow-lg rounded-2xl hover:shadow-2xl transition-all border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-linear-to-r from-amber-500 to-amber-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-slate-700 uppercase tracking-wide">Échéances proches</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">
                            {{ $tasks->where('end_date', '<=', now()->addDays(7))->where('status', '!=', 'validated')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 shadow-lg rounded-2xl hover:shadow-2xl transition-all border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-linear-to-r from-secondary-500 to-secondary-500/80 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-slate-700 uppercase tracking-wide">Projets liés</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $projects->count() }}</p>
                    </div>
                </div>
            </div>
        @else
            {{-- Admin / Chef Dept / Chef Projet : cartes complètes --}}
            <div class="bg-white p-6 shadow-lg rounded-2xl hover:shadow-2xl transition-all border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-linear-to-r from-primary-500 to-primary-500/80 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-slate-700 uppercase tracking-wide">
                            @if($user->isChefDepartement()) Projets département @else Projets visibles @endif
                        </p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $projects->count() }}</p>
                    </div>
                </div>
                @if($user->isChefDepartement())
                    <p class="mt-2 text-sm text-primary-500 font-semibold">Dans votre département</p>
                @endif
            </div>

            <div class="bg-white p-6 shadow-lg rounded-2xl hover:shadow-2xl transition-all border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-linear-to-r from-emerald-500 to-emerald-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-slate-700 uppercase tracking-wide">
                            @if($user->isChefDepartement()) Tâches département @else Tâches totales @endif
                        </p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $tasks->count() }}</p>
                    </div>
                </div>
                <div class="mt-4 text-sm text-emerald-600 font-semibold">
                    {{ $tasks->where('status','validated')->count() }} validées
                </div>
            </div>

            <div class="bg-white p-6 shadow-lg rounded-2xl hover:shadow-2xl transition-all border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-linear-to-r from-secondary-500 to-secondary-500/80 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-slate-700 uppercase tracking-wide">Progression globale</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ round($projects->avg('progress') ?? 0, 1) }}%</p>
                    </div>
                </div>
                <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-linear-to-r from-primary-500 to-secondary-500 h-2 rounded-full" style="width: {{ round($projects->avg('progress') ?? 0, 1) }}%"></div>
                </div>
            </div>

            <div class="bg-white p-6 shadow-lg rounded-2xl hover:shadow-2xl transition-all border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-linear-to-r from-amber-500 to-amber-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-slate-700 uppercase tracking-wide">Échéances proches</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">
                            {{ $tasks->where('end_date', '<=', now()->addDays(7))->count() }}
                        </p>
                    </div>
                </div>
                @if($user->isChefDepartement())
                    <p class="mt-2 text-sm text-amber-600 font-semibold">Priorité département</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Charts : uniquement pour non-membres --}}
    @if(!$user->isMembre())
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
    @endif

    {{-- Quick Sections --}}
    <div class="grid grid-cols-1 {{ $user->isMembre() ? 'lg:grid-cols-2' : 'xl:grid-cols-3' }} gap-6">
        {{-- Projets (masqué pour membre si peu de contenu) --}}
        @if(!$user->isMembre())
        <div class="bg-white shadow-xl rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">
                    @if($user->isChefDepartement())
                        Projets département
                    @elseif($user->isChefProjet())
                        Vos projets
                    @else
                        Tous les projets
                    @endif
                </h2>
                <a href="{{ route('projects.index') }}" class="text-primary-500 hover:text-primary-600 font-medium">Voir tout →</a>
            </div>
            <div class="space-y-4">
                @forelse($projects->take(4) as $project)
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                    <div>
                        <div class="font-semibold text-gray-900">{{ $project->name }}</div>
                        <div class="text-sm text-slate-700">{{ $project->user->name ?? 'Non assigné' }} @if($user->isChefDepartement() && $project->user?->structure_id !== $user->structure_id)(sub-struct)@endif</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-900">{{ $project->progress ?? 0 }}%</div>
                        <div class="w-20 bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-linear-to-r from-primary-500 to-primary-500/60 h-2 rounded-full" style="width: {{ $project->progress ?? 0 }}%"></div>
                        </div>
                    </div>
                    <a href="{{ route('projects.show', $project) }}" class="ml-4 text-primary-500 hover:text-primary-600 text-sm font-medium">Détails</a>
                </div>
                @empty
                <div class="text-center py-12 text-slate-700">
                    Aucun projet visible.
                    @if($user->isChefProjet() || $user->isAdmin())
                    <a href="{{ route('projects.create') }}" class="font-semibold hover:text-primary-500">Créer le premier !</a>
                    @endif
                </div>
                @endforelse
            </div>
        </div>
        @endif

        {{-- Tâches récentes --}}
        <div class="bg-white shadow-xl rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">
                    @if($user->isChefDepartement())
                        Tâches département
                    @elseif($user->isMembre())
                        Mes tâches assignées
                    @else
                        Tâches assignées
                    @endif
                </h2>
                <a href="{{ route('tasks.index') }}" class="text-primary-500 hover:text-primary-600 font-medium">Voir tout →</a>
            </div>
            <div class="space-y-3">
                @forelse($tasks->take(5) as $task)
                <div class="flex items-center p-3 bg-gray-50 rounded-xl hover:bg-gray-100">
                    <div class="w-2 h-2 rounded-full {{ $task->status === 'validated' ? 'bg-[#397B44]' : ($task->status === 'in_progress' ? 'bg-amber-500' : 'bg-gray-500') }} mr-3"></div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate">{{ $task->name }}</div>
                        <div class="text-xs text-slate-700">
                            {{ $task->milestone?->project?->name ?? 'Non assigné' }} •
                            {{ \Carbon\Carbon::parse($task->end_date)->diffForHumans() }}
                        </div>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full
                        {{ $task->status === 'validated' ? 'bg-[#E8F5E9] text-[#397B44]' : ($task->status === 'in_progress' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                </div>
                @empty
                <div class="text-center py-12 text-slate-700">
                    Aucune tâche. <a href="{{ route('tasks.index') }}" class="font-semibold hover:text-primary-500">Explorer</a>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Calendrier compact --}}
        <div class="bg-white shadow-xl rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">📅 Calendrier</h2>
                <a href="{{ route('calendar.index') }}" class="text-primary-500 hover:text-primary-600 font-medium">Complet →</a>
            </div>
            <div class="min-h-75 border border-gray-200 rounded-xl overflow-hidden">
                <div id="calendar-app" data-compact="true"></div>
            </div>
        </div>
    </div>

    {{-- Section Chef Département : Équipe & Structures --}}
    @if($user->isChefDepartement())
    <div class="mt-8 p-6 bg-linear-to-r from-primary-500/5 to-secondary-500/5 rounded-2xl border border-primary-500/20">
        <h3 class="text-xl font-bold mb-4 text-primary-500">👥 Équipe Département</h3>
        <p class="text-slate-700 mb-4">Gérez votre équipe et structures enfants.</p>
        <div class="flex gap-3">
            <a href="{{ route('admin.users.index') }}" class="bg-primary-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-primary-600 transition">Utilisateurs</a>
            <a href="{{ route('admin.structures.index') }}" class="bg-secondary-500/20 text-secondary-500 hover:bg-secondary-500/30 px-6 py-2 rounded-xl font-medium transition border border-secondary-500/30">Structures</a>
        </div>
    </div>
    @endif

    {{-- Section Chef Projet : Jalons récents --}}
    @if($user->isChefProjet())
    <div class="mt-8 p-6 bg-linear-to-r from-primary-500/5 to-secondary-500/5 rounded-2xl border border-primary-500/20">
        <h3 class="text-xl font-bold mb-4 text-primary-500">📌 Jalons récents</h3>
        <div class="space-y-3">
            @php
                $milestones = \App\Models\Milestone::whereHas('project', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->with('project')->orderBy('due_date')->take(5)->get();
            @endphp
            @forelse($milestones as $milestone)
            <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-gray-100">
                <div>
                    <div class="font-medium text-gray-900">{{ $milestone->name }}</div>
                    <div class="text-xs text-slate-700">{{ $milestone->project->name }} • {{ \Carbon\Carbon::parse($milestone->due_date)->diffForHumans() }}</div>
                </div>
                <a href="{{ route('milestones.show', $milestone) }}" class="text-primary-500 hover:text-primary-600 text-sm font-medium">Détails</a>
            </div>
            @empty
            <p class="text-slate-700 text-sm">Aucun jalon récent.</p>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Section Membre : Actions rapides --}}
    @if($user->isMembre())
    <div class="mt-8 p-6 bg-linear-to-r from-primary-500/5 to-secondary-500/5 rounded-2xl border border-primary-500/20">
        <h3 class="text-xl font-bold mb-4 text-primary-500">⚡ Actions rapides</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('tasks.my-tasks') }}" class="bg-primary-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-primary-600 transition">Mes tâches</a>
            <a href="{{ route('calendar.index') }}" class="bg-secondary-500/20 text-secondary-500 hover:bg-secondary-500/30 px-6 py-2 rounded-xl font-medium transition border border-secondary-500/30">Calendrier</a>
        </div>
    </div>
    @endif
</div>

@vite(['resources/js/calendar-mount.js'])
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update notification badge
    fetch('/notifications/count')
        .then(res => res.json())
        .then(count => {
            document.getElementById('dash-notif-badge').textContent = count;
        });

    @if(!$user->isMembre())
    // Status chart - AT colors
    const statusCtx = document.getElementById('statusChart')?.getContext('2d');
    if (statusCtx) {
        const statusData = {
            labels: ['À faire', 'En cours', 'Validées'],
            datasets: [{
                data: [
                    {{ $tasks->where('status', 'pending')->count() }},
                    {{ $tasks->whereIn('status', ['started', 'in_progress'])->count() }},
                    {{ $tasks->where('status', 'validated')->count() }}
                ],
                backgroundColor: ['#ef4444', '#f37021', '#397B44'],
                borderWidth: 0,
                cutout: '60%'
            }]
        };
        new Chart(statusCtx, { type: 'doughnut', data: statusData, options: { responsive: true, maintainAspectRatio: false } });
    }

    // Progress chart - AT primary
    const progressCtx = document.getElementById('progressChart')?.getContext('2d');
    if (progressCtx) {
        const projectsProgress = @json($projects->pluck('progress', 'name')->filter());
        new Chart(progressCtx, {
            type: 'bar',
            data: {
                labels: projectsProgress.keys().toArray(),
                datasets: [{ label: 'Progression %', data: projectsProgress.values().toArray(), backgroundColor: 'rgba(243, 112, 33, 0.8)' }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100 } } }
        });
    }
    @endif
});
</script>
@endpush
</x-app-layout>

