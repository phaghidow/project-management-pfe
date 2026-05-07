<x-app-layout>
<div class="page-mobile">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="text-sm text-slate-700">Gestion globale avec filtres rôle / période</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <a href="{{ route('projects.create') }}" class="btn-primary text-center">
                + Nouveau projet
            </a>
            <a href="{{ route('gantt') }}" class="btn-secondary text-center">
                🎯 Gantt
            </a>
        </div>
        {{-- Filters --}}
        <form id="dashboard-filters" method="GET" action="{{ route('admin.dashboard') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 bg-white p-4 rounded-xl shadow-sm border items-end w-full lg:w-auto">
            <div class="flex flex-col gap-2 min-w-0">
                <label class="text-sm font-medium text-gray-700">Rôle:</label>
                <select name="role" class="w-full border-gray-300 rounded-md px-3 h-10 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="all" {{ ($roleFilter ?? 'all') === 'all' ? 'selected' : '' }}>Tous</option>
                    <option value="admin" {{ ($roleFilter ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="chef_departement" {{ ($roleFilter ?? '') === 'chef_departement' ? 'selected' : '' }}>Chef Département</option>
                    <option value="chef_projet" {{ ($roleFilter ?? '') === 'chef_projet' ? 'selected' : '' }}>Chef Projet</option>
                    <option value="membre" {{ ($roleFilter ?? '') === 'membre' ? 'selected' : '' }}>Membre</option>
                </select>
            </div>

            <div class="flex flex-col gap-2 min-w-0">
                <label class="text-sm font-medium text-gray-700">Du</label>
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="w-full border-gray-300 rounded-md px-3 h-10 text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex flex-col gap-2 min-w-0">
                <label class="text-sm font-medium text-gray-700">Au</label>
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="w-full border-gray-300 rounded-md px-3 h-10 text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="md:justify-self-end">
                <button type="submit" class="btn-primary btn-sm w-full md:w-auto">Filtrer</button>
            </div>
        </form>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Utilisateurs --}}
        <div class="bg-white p-6 shadow-lg rounded-xl border border-gray-200 hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-xl">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-700">Utilisateurs</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $usersTotal }}</p>
                </div>
            </div>
            <p class="mt-2 text-sm text-slate-700">{{ $usersActive }} actifs</p>
        </div>

        {{-- Projets --}}
        <div class="bg-white p-6 shadow-lg rounded-xl border border-gray-200 hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-xl">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-700">Projets</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $projectsTotal }}</p>
                </div>
            </div>
            <p class="mt-2 text-sm text-slate-700">Progression moyenne : {{ round($projectsProgressAvg, 1) }}%</p>
        </div>

        {{-- Tâches --}}
        <div class="bg-white p-6 shadow-lg rounded-xl border border-gray-200 hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-emerald-100 rounded-xl">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-700">Tâches (période)</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalTasks }}</p>
                </div>
            </div>
            <p class="mt-2 text-sm text-red-500 font-semibold">{{ $overdueTasks }} en retard</p>
        </div>

        {{-- Structures --}}
        <div class="bg-white p-6 shadow-lg rounded-xl border border-gray-200 hover:shadow-xl transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-amber-100 rounded-xl">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-700">Structures</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $structuresTotal }}</p>
                </div>
            </div>
            <p class="mt-2 text-sm text-slate-700">{{ $notificationsTotal }} notifications non lues</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mb-8">
        <div class="bg-white shadow rounded-xl p-6">
            <h3 class="text-lg font-semibold mb-4">Actions rapides</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.structures.index') }}" class="block p-4 bg-white border border-slate-200 rounded-xl hover:border-blue-300 hover:bg-blue-50 hover:shadow-sm transition-all text-center">
                    <div class="text-2xl mb-2 text-blue-600">🏢</div>
                    <div class="font-medium text-slate-900">Gérer structures</div>
                </a>
                <a href="{{ route('admin.users.index') }}" class="block p-4 bg-white border border-slate-200 rounded-xl hover:border-green-300 hover:bg-green-50 hover:shadow-sm transition-all text-center">
                    <div class="text-2xl mb-2 text-green-600">👥</div>
                    <div class="font-medium text-green-900">Utilisateurs</div>
                </a>
                <a href="{{ route('projects.index') }}" class="block p-4 bg-white border border-slate-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 hover:shadow-sm transition-all text-center">
                    <div class="text-2xl mb-2 text-purple-600">📊</div>
                    <div class="font-medium text-purple-900">Projets</div>
                </a>
                <a href="{{ route('calendar.index') }}" class="block p-4 bg-white border border-slate-200 rounded-xl hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-sm transition-all text-center">
                    <div class="text-2xl mb-2 text-indigo-600">📅</div>
                    <div class="font-medium text-indigo-900">Calendrier</div>
                </a>
            </div>
        </div>
    </div>

    {{-- Notifications récentes --}}
    <div class="mb-8">
        <div class="bg-white shadow rounded-xl p-6">
            <h3 class="text-lg font-semibold mb-4">Notifications récentes</h3>
            <div id="admin-notifications" class="space-y-3 max-h-64 overflow-y-auto">
                @forelse($recentNotifications as $notification)
                    <div class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition group">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($notification->type ?? 'info') }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $notification->data['message'] ?? $notification->message ?? 'Nouvelle notification' }}</p>
                                <p class="text-xs text-slate-700 mt-0.5">
                                    Par {{ $notification->user?->name ?? 'Système' }} •
                                    <span title="{{ $notification->created_at }}">{{ $notification->created_at->diffForHumans() }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-700 text-sm">
                        Aucune notification récente
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Chart : Tâches par statut --}}
    <!-- Notification preferences removed -->

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white shadow-xl rounded-2xl p-8">
            <h3 class="text-xl font-bold mb-6">Tâches par statut (filtré)</h3>
            <div class="chart-wrap">
                <canvas id="statusChart" class="responsive-chart"></canvas>
            </div>
        </div>
        <div class="bg-white shadow-xl rounded-2xl p-8">
            <h3 class="text-xl font-bold mb-6">Résumé des métriques</h3>

            <div class="space-y-4">
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl">
                    <span class="text-slate-700">Projets totaux</span>
                    <span class="text-2xl font-bold text-gray-900">{{ $projectsTotal }}</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl">
                    <span class="text-slate-700">Structures</span>
                    <span class="text-2xl font-bold text-gray-900">{{ $structuresTotal }}</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl">
                    <span class="text-slate-700">Progression moyenne</span>
                    <span class="text-2xl font-bold text-primary-500">{{ round($projectsProgressAvg, 1) }}%</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl">
                    <span class="text-slate-700">Tâches en retard</span>
                    <span class="text-2xl font-bold text-red-500">{{ $overdueTasks }}</span>
                </div>
            </div>
        </div>
    </div>

    @include('partials.recent-attachments', ['attachments' => $recentAttachments ?? collect(), 'title' => 'Pièces jointes récentes'])

    @include('partials.recent-comments', ['comments' => $recentComments ?? collect(), 'title' => 'Commentaires récents'])
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx2 = document.getElementById('statusChart')?.getContext('2d');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['En cours', 'Validé'],
                datasets: [{
                    data: [{{ $tasksByStatus['in_progress'] ?? 0 }}, {{ $tasksByStatus['validated'] ?? 0 }}],
                    backgroundColor: ['#f59e0b', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>
@endpush
</x-app-layout>

