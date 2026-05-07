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
            @if($user->isAdmin() || $user->isChefDepartement())
                <a href="{{ route('projects.create') }}" class="btn-primary">
                    + Nouveau projet
                </a>
            @endif
            <a href="{{ route('gantt') }}" class="btn-primary">
                🎯 Gantt
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
                    {{ $tasks->where('status', 'in_progress')->count() }} en cours
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
                    <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #E8ECFF 0%, #DCE4FF 100%);">
                        <svg class="w-8 h-8 text-[#2E3192]" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 7a2 2 0 012-2h4l2 2h6a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
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
                    <div class="p-3 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #FFF2DF 0%, #FFE7C2 100%);">
                        <svg class="w-8 h-8 text-[#D97706]" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 4h2v16H4V4zm7 5h2v11h-2V9zm7-3h2v14h-2V6z" />
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
    <div class="grid grid-cols-1 {{ $user->isMembre() ? 'lg:grid-cols-2' : ($user->isChefProjet() ? 'xl:grid-cols-2' : 'xl:grid-cols-3') }} gap-6 mb-8">
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
                        <div class="text-sm font-medium text-gray-900">{{ number_format($project->progress ?? 0, 2) }}%</div>
                        <div class="w-20 bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-linear-to-r from-primary-500 to-primary-500/60 h-2 rounded-full" style="width: {{ $project->progress ?? 0 }}%"></div>
                        </div>
                    </div>
                    <a href="{{ route('projects.show', $project) }}" class="ml-4 text-primary-500 hover:text-primary-600 text-sm font-medium">Détails</a>
                </div>
                @empty
                <div class="text-center py-12 text-slate-700">
                    Aucun projet visible.
                    @if($user->isAdmin())
                    <a href="{{ route('projects.create') }}" class="font-semibold text-[#2E3192] hover:text-[#1E216D]">Créer le premier !</a>
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
        <div class="bg-white shadow-xl rounded-2xl p-6 {{ $user->isChefProjet() ? 'xl:col-span-2' : '' }}">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">📅 Calendrier</h2>
                <a href="{{ route('calendar.index') }}" class="text-primary-500 hover:text-primary-600 font-medium">Complet →</a>
            </div>
            <div class="min-h-75 border border-gray-200 rounded-xl overflow-hidden">
                <div id="calendar-app" data-compact="true"></div>
            </div>
        </div>
    </div>

    @include('partials.recent-attachments', ['attachments' => $recentAttachments ?? collect(), 'title' => 'Pièces jointes du tableau de bord'])

    @include('partials.recent-comments', ['comments' => $recentComments ?? collect(), 'title' => 'Commentaires récents'])

    {{-- Section Chef Département : Équipe & Structures --}}
    @if($user->isChefDepartement())
    <div class="mt-8 p-6 bg-linear-to-r from-primary-500/5 to-secondary-500/5 rounded-2xl border border-primary-500/20">
        <h3 class="text-xl font-bold mb-4 text-primary-500">👥 Équipe Département</h3>
        <p class="text-slate-700 mb-4">Gérez votre équipe et structures enfants.</p>
        <div class="flex gap-3">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center bg-primary-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition !text-white">Utilisateurs</a>
            <a href="{{ route('admin.structures.index') }}" class="btn-secondary text-center">Structures</a>
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

    {{-- Section Chef Projet : Affectation des membres aux projets --}}
    @if($user->isChefProjet())
    <div class="mt-8 p-6 bg-linear-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl border border-blue-500/20">
        <h3 class="text-xl font-bold mb-4 text-blue-600">👥 Affecter des membres aux projets</h3>
        <p class="text-slate-700 mb-4 text-sm">Sélectionnez un projet et assignez des membres avec leur rôle.</p>

        @php
            $chefProjects = \App\Models\Project::where('user_id', $user->id)
                ->with(['members'])
                ->orderBy('name')
                ->get();
        @endphp

        @forelse($chefProjects as $project)
        <div class="mb-6 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <div class="font-semibold text-gray-900">{{ $project->name }}</div>
                    <div class="text-xs text-slate-500">{{ $project->members->count() }} membre(s) assigné(s)</div>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-full
                    {{ $project->status === 'draft' ? 'bg-gray-100 text-gray-800' : ($project->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : ($project->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                    {{ ucfirst($project->status) }}
                </span>
            </div>

            {{-- Membres actuels --}}
            @if($project->members->count() > 0)
            <div class="p-4 border-b border-gray-100">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Membres assignés :</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($project->members as $member)
                    <div class="inline-flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-lg px-3 py-1.5 text-sm">
                        <span class="font-medium text-gray-800">{{ $member->name }}</span>
                        @if($member->pivot->role_in_project)
                            <span class="text-xs text-blue-600 bg-blue-100 px-1.5 py-0.5 rounded">{{ $member->pivot->role_in_project }}</span>
                        @endif
                        <form method="POST" action="{{ route('projects.remove-member', [$project, $member]) }}" class="inline ajax-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm ml-1" title="Retirer" data-confirm-delete="Retirer ce membre du projet ?">×</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Formulaire d'affectation --}}
            <div class="p-4">
                <form method="POST" action="{{ route('projects.assign-members', $project) }}" class="space-y-3 ajax-form">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-3 items-end">
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Membre à affecter</label>
                            <select name="users[]" id="member-select-{{ $project->id }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required onchange="showMemberFunction(this, {{ $project->id }})">
                                <option value="" data-function="">-- Choisir un membre --</option>
                                @foreach($availableMembers as $member)
                                    @if(!$project->members->contains('id', $member->id))
                                    <option value="{{ $member->id }}" data-function="{{ $member->function ?? 'Non spécifiée' }}">{{ $member->name }} — {{ $member->email }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Fonction</label>
                            <div id="function-display-{{ $project->id }}" class="px-3 py-2 bg-gray-100 rounded-lg text-sm text-gray-700 font-medium min-w-[140px] text-center">
                                —
                            </div>
                        </div>
                        <button type="submit" class="btn-primary btn-sm whitespace-nowrap w-full sm:w-auto">
                            + Affecter
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-slate-500 bg-white rounded-xl border border-gray-200">
            Vous n'avez aucun projet pour le moment.
        </div>
        @endforelse
    </div>
    @endif

    {{-- Section Membre : Actions rapides --}}
    @if($user->isMembre())
    <div class="mt-8 p-6 bg-linear-to-r from-primary-500/5 to-secondary-500/5 rounded-2xl border border-primary-500/20">
        <h3 class="text-xl font-bold mb-4 text-primary-500">⚡ Actions rapides</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('tasks.my-tasks') }}" class="inline-flex items-center justify-center bg-primary-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition !text-white">Mes tâches</a>
            <a href="{{ route('calendar.index') }}" class="btn-primary btn-block text-center">Calendrier</a>
        </div>
    </div>
    @endif

    <!-- Notification preferences removed -->
</div>


@vite(['resources/js/calendar-mount.js'])
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Member function display for project assignment
    window.showMemberFunction = function(select, projectId) {
        const selectedOption = select.options[select.selectedIndex];
        const func = selectedOption.getAttribute('data-function') || '—';
        const display = document.getElementById('function-display-' + projectId);
        if (display) {
            display.textContent = func;
            display.className = func !== '—' && func !== 'Non spécifiée'
                ? 'px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700 font-medium min-w-[140px] text-center'
                : 'px-3 py-2 bg-gray-100 rounded-lg text-sm text-gray-500 font-medium min-w-[140px] text-center';
        }
    };

    @if(!$user->isMembre())
    // Status chart - AT colors
    const statusCtx = document.getElementById('statusChart')?.getContext('2d');
    if (statusCtx) {
        const statusData = {
            labels: ['En cours', 'Validées'],
            datasets: [{
                data: [
                    {{ $tasks->where('status', 'in_progress')->count() }},
                    {{ $tasks->where('status', 'validated')->count() }}
                ],
                backgroundColor: ['#f37021', '#397B44'],
                borderWidth: 0,
                cutout: '60%'
            }]
        };
        new Chart(statusCtx, { type: 'doughnut', data: statusData, options: { responsive: true, maintainAspectRatio: false } });
    }

    // Progress chart - AT primary
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
    @endif
});
</script>
@endpush
</x-app-layout>

