<x-app-layout>
<div class="page-mobile max-w-5xl mx-auto">
    @php $user = auth()->user(); @endphp

    {{-- Header minimaliste --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-[#1A202C]">Mon Espace</h1>
            <p class="mt-1 text-base text-slate-700">Mes tâches assignées</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <a href="{{ route('gantt') }}" class="btn-primary text-center">
                🎯 Gantt
            </a>
            <a href="{{ route('calendar.index') }}" class="btn-primary text-center">
                📅 Calendrier
            </a>
        </div>
    </div>

    {{-- Stats rapides --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-4 shadow-lg rounded-2xl border border-gray-100 text-center">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total</p>
            <p class="text-2xl font-bold text-[#1A202C] mt-1">{{ $tasks->count() }}</p>
        </div>
        <div class="bg-white p-4 shadow-lg rounded-2xl border border-gray-100 text-center">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">En cours</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $tasks->where('status', 'in_progress')->count() }}</p>
        </div>
        <div class="bg-white p-4 shadow-lg rounded-2xl border border-gray-100 text-center">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Validées</p>
            <p class="text-2xl font-bold text-[#397B44] mt-1">{{ $tasks->where('status', 'validated')->count() }}</p>
        </div>
        <div class="bg-white p-4 shadow-lg rounded-2xl border border-gray-100 text-center">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Urgentes</p>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ $tasks->where('due_date', '<=', now()->addDays(3))->where('status', '!=', 'validated')->count() }}</p>
        </div>
    </div>

    {{-- Dashboard Widgets: Focus, Progress, Timeline, Notifications --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        {{-- LEFT COLUMN: Focus du Jour + Timeline --}}
        <div class="md:col-span-2 space-y-6">
            
            {{-- 1. Widget "Focus du Jour" (Top 3 Priorité) --}}
            <div class="bg-white shadow-lg rounded-2xl border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-[#1A202C] mb-4 flex items-center gap-2">
                    📅 Focus du Jour
                </h2>
                
                @php
                    $focusTasks = $tasks
                        ->where('status', '!=', 'validated')
                        ->filter(function($task) {
                            return $task->due_date && ($task->due_date->isToday() || $task->due_date->isTomorrow());
                        })
                        ->sortBy('due_date')
                        ->take(3);
                @endphp
                
                @if($focusTasks->count() > 0)
                    <div class="space-y-3">
                        @foreach($focusTasks as $task)
                            @php
                                $isToday = $task->due_date->isToday();
                                $isPast = $task->due_date->isPast();
                                $urgency = $isPast || $isToday ? 'red' : 'amber';
                                $borderColor = $urgency === 'red' ? 'border-l-red-500' : 'border-l-amber-500';
                                $badgeText = $isToday ? 'Aujourd\'hui' : 'Demain';
                                $badgeClass = $urgency === 'red' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700';
                            @endphp
                            <div class="border-l-4 {{ $borderColor }} bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-[#1A202C] truncate">{{ $task->name }}</p>
                                        <p class="text-xs text-slate-600 mt-1 truncate">{{ $task->milestone?->project?->name ?? 'Projet' }}</p>
                                    </div>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $badgeClass }} flex-shrink-0 whitespace-nowrap">
                                        {{ $badgeText }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-slate-500 py-6">Aucune tâche prévue aujourd'hui ou demain 🎉</p>
                @endif
            </div>
            
            {{-- 3. Flux "Dernières Activités" (Timeline) --}}
            <div class="bg-white shadow-lg rounded-2xl border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-[#1A202C] mb-4 flex items-center gap-2">
                    ✨ Dernières Activités
                </h2>
                
                @php
                    $activities = $tasks
                        ->sortByDesc('updated_at')
                        ->take(5);
                @endphp
                
                @if($activities->count() > 0)
                    <div class="relative">
                        <div class="absolute left-4 top-6 bottom-0 w-0.5 bg-gray-200"></div>
                        
                        <div class="space-y-4 relative z-10">
                            @foreach($activities as $activity)
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center mt-1">
                                        <div class="w-8 h-8 rounded-full border-2 border-gray-200 bg-white flex items-center justify-center text-lg flex-shrink-0">
                                            @if($activity->status === 'validated')
                                                ✅
                                            @elseif($activity->status === 'in_progress')
                                                🚀
                                            @else
                                                📌
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-1 pb-3 min-w-0">
                                        <p class="text-sm font-semibold text-[#1A202C] truncate">{{ $activity->name }}</p>
                                        <p class="text-xs text-slate-500 mt-1">
                                            @if($activity->status === 'validated')
                                                Validée {{ $activity->validated_at->diffForHumans() }}
                                            @else
                                                Mise à jour {{ $activity->updated_at->diffForHumans() }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-center text-slate-500 py-6">Aucune activité récente</p>
                @endif
            </div>
        </div>
        
        {{-- RIGHT COLUMN: Progress + Notifications --}}
        <div class="space-y-6">
            
            {{-- 2. Barre de Progression Personnelle (Radial Progress) --}}
            <div class="bg-white shadow-lg rounded-2xl border border-gray-100 p-6 flex flex-col items-center justify-center h-fit">
                <h2 class="text-lg font-bold text-[#1A202C] mb-6 text-center">
                    📊 Objectif Hebdo
                </h2>
                
                @php
                    $startOfWeek = now()->startOfWeek();
                    $endOfWeek = now()->endOfWeek();
                    $weekTasks = $tasks->filter(function($task) use ($startOfWeek, $endOfWeek) {
                        return $task->created_at >= $startOfWeek && $task->created_at <= $endOfWeek;
                    });
                    $completedWeek = $weekTasks->where('status', 'validated')->count();
                    $totalWeek = $weekTasks->count();
                    $progressPercent = $totalWeek > 0 ? round(($completedWeek / $totalWeek) * 100) : 0;
                    $circumference = 70 * 2 * 3.14159;
                    $offset = $circumference * (1 - $progressPercent / 100);
                @endphp
                
                <div class="relative w-40 h-40 flex items-center justify-center">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 160 160">
                        <circle cx="80" cy="80" r="70" stroke="#e5e7eb" stroke-width="8" fill="none" />
                        <circle 
                            cx="80" cy="80" r="70" 
                            stroke="#3b82f6" 
                            stroke-width="8" 
                            fill="none"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $offset }}"
                            stroke-linecap="round"
                            style="transition: stroke-dashoffset 0.6s ease"
                        />
                    </svg>
                    <div class="absolute text-center">
                        <p class="text-3xl font-bold text-blue-500">{{ $progressPercent }}%</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $completedWeek }}/{{ $totalWeek }}</p>
                    </div>
                </div>
                
                <p class="text-xs text-slate-600 mt-4 text-center">
                    {{ $totalWeek }} tâche{{ $totalWeek !== 1 ? 's' : '' }} cette semaine
                </p>
            </div>
            
            <!-- Notification preferences removed -->

            {{-- 4. Cartes de Notifications Critiques --}}
            <div class="bg-white shadow-lg rounded-2xl border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-[#1A202C] mb-4 flex items-center gap-2">
                    🔔 Alertes
                </h2>

                
                @php
                    $urgentTasks = $tasks
                        ->where('status', '!=', 'validated')
                        ->filter(function($task) {
                            return $task->due_date && $task->due_date->isPast();
                        })
                        ->sortBy('due_date')
                        ->take(3);
                @endphp
                
                @if($unreadNotifications->count() > 0)
                    <div class="space-y-3">
                        @foreach($unreadNotifications as $notification)
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <span class="text-lg flex-shrink-0">🔔</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-amber-900 truncate">{{ $notification->title }}</p>
                                        <p class="text-xs text-amber-700 mt-1">{{ $notification->message }}</p>
                                        <p class="text-[11px] text-amber-700/80 mt-2">{{ $notification->created_at?->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif($urgentTasks->count() > 0)
                    <div class="space-y-3">
                        @foreach($urgentTasks as $task)
                            <a href="#task-card-{{ $task->id }}" class="block">
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4 hover:bg-red-100 transition-colors">
                                    <div class="flex items-start gap-3">
                                        <span class="text-lg flex-shrink-0">⚠️</span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-red-900 truncate">{{ $task->name }}</p>
                                            <p class="text-xs text-red-700 mt-1">
                                                ⏰ En retard depuis {{ $task->due_date->diffInDays(now()) }} jour(s)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-slate-500 py-6 text-sm">Aucune tâche en retard 🎉</p>
                @endif
            </div>
        </div>
    </div>

    <div class="mb-8">
        <div class="bg-white shadow-lg rounded-2xl border border-gray-100 p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-[#1A202C]">Mes Projets</h2>
                    <p class="text-sm text-slate-600">Projets où vous êtes affecté personnellement</p>
                </div>
                <a href="{{ route('projects.my-projects') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700 whitespace-nowrap">Voir tout</a>
            </div>

            @php
                $personalProjects = $projects->take(6);
            @endphp

            @forelse($personalProjects as $project)
                <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 mb-3 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-[#1A202C] truncate">{{ $project->name }}</p>
                            <p class="text-xs text-slate-600 mt-1 truncate">Responsable : {{ $project->user?->name ?? 'Non défini' }}</p>
                        </div>
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full whitespace-nowrap {{ $project->status === 'completed' ? 'bg-green-100 text-green-700' : ($project->status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-200 text-gray-700') }}">
                            {{ $project->status === 'completed' ? 'Terminé' : ($project->status === 'in_progress' ? 'En cours' : 'Brouillon') }}
                        </span>
                    </div>
                    <div class="mt-2 flex items-center justify-between gap-2 text-xs text-slate-500">
                        <span>📅 {{ $project->start_date?->format('d/m/Y') ?? 'Début libre' }}</span>
                        <span>{{ $project->progressPercentage() }}% avancé</span>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-center text-sm text-slate-500">
                    Aucun projet personnel pour le moment.
                </div>
            @endforelse
        </div>
    </div>

    <div class="mb-8 space-y-6">
        @include('partials.recent-attachments', ['attachments' => $recentAttachments ?? collect(), 'title' => 'Mes fichiers récents'])

        @include('partials.recent-comments', ['comments' => $recentComments ?? collect(), 'title' => 'Mes commentaires récents'])
    </div>

    <div class="mb-8">
        <div class="bg-white shadow-lg rounded-2xl border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-[#1A202C]">Mes Tâches</h2>
                    <p class="text-sm text-slate-600">La liste détaillée a été déplacée dans la section dédiée de la sidebar.</p>
                </div>
                <a href="{{ route('tasks.my-tasks') }}" class="btn-primary text-center whitespace-nowrap">
                    Ouvrir Mes Tâches
                </a>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

