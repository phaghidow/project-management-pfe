<x-app-layout>
<div class="page-mobile max-w-5xl mx-auto">
    @php $user = auth()->user(); @endphp

    {{-- Header minimaliste --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-[#1A202C]">Mon Espace</h1>
            <p class="mt-1 text-base text-slate-700">Mes tâches assignées</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/notifications" class="bg-at-orange-500/10 hover:bg-at-orange-500/20 text-at-orange-500 px-4 py-2 rounded-xl font-medium shadow-lg transition-all flex items-center border border-at-orange-500/30 text-sm">
                🔔 Notifications
                <span id="dash-notif-badge" class="ml-2 bg-at-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">0</span>
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
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $tasks->whereIn('status', ['pending', 'in_progress'])->count() }}</p>
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

    {{-- Liste des tâches --}}
    <div class="space-y-6" id="tasks-list">
        @forelse($tasks as $task)
        <div
            id="task-card-{{ $task->id }}"
            class="task-card bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden transition-all duration-500 {{ $task->status === 'validated' ? 'opacity-60 grayscale bg-gray-50' : '' }}"
            data-task-id="{{ $task->id }}"
            data-status="{{ $task->status }}"
        >
            {{-- Entête de la tâche --}}
            <div class="p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <h3 class="text-lg font-bold text-[#1A202C]">{{ $task->name }}</h3>
                            <x-status-badge :status="$task->status" />
                        </div>
                        <div class="text-sm text-slate-600 space-y-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <span>{{ $task->milestone?->project?->name ?? 'Projet non défini' }} / {{ $task->milestone?->name ?? 'Jalon non défini' }}</span>
                            </div>
                            @if($task->due_date)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Échéance : <strong>{{ $task->due_date->format('d/m/Y') }}</strong></span>
                                @if($task->due_date->isPast() && $task->status !== 'validated')
                                    <span class="text-xs font-bold text-red-500 bg-red-50 px-2 py-0.5 rounded-full">EN RETARD</span>
                                @elseif($task->due_date->diffInDays(now()) <= 3 && $task->status !== 'validated')
                                    <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">URGENT</span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Bouton Valider --}}
                    @if($task->status !== 'validated')
                        @if($task->canBeValidated())
                        <form
                            method="POST"
                            action="{{ route('tasks.validate', $task) }}"
                            class="validate-form flex-shrink-0"
                            data-task-id="{{ $task->id }}"
                        >
                            @csrf
                            <button
                                type="submit"
                                class="validate-btn bg-[#397B44] hover:bg-[#2d6236] text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg transition-all flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Valider ma tâche
                            </button>
                        </form>
                        @else
                        <div class="flex-shrink-0 text-sm text-gray-500 bg-gray-100 px-4 py-2.5 rounded-xl flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Attente dépendances
                        </div>
                        @endif
                    @else
                        <div class="flex-shrink-0 text-sm text-[#397B44] bg-[#E8F5E9] px-4 py-2.5 rounded-xl flex items-center gap-2 font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Validée le {{ $task->validated_at?->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Accordion: Commentaires & Pièces jointes --}}
            <div class="border-t border-gray-100">
                <button
                    type="button"
                    class="w-full flex items-center justify-between px-5 sm:px-6 py-3 text-sm font-medium text-slate-600 hover:bg-gray-50 transition-colors"
                    onclick="toggleAccordion({{ $task->id }})"
                >
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Commentaires & Fichiers
                        <span class="text-xs text-slate-400">({{ $task->comments->count() }} commentaires, {{ $task->attachments->count() }} fichiers)</span>
                    </span>
                    <svg id="accordion-icon-{{ $task->id }}" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div id="accordion-body-{{ $task->id }}" class="hidden px-5 sm:px-6 pb-5 space-y-5">
                    {{-- Commentaires existants --}}
                    <div class="space-y-3">
                        @forelse($task->comments as $comment)
                        <div class="flex gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-primary-600">{{ strtoupper(substr($comment->user->name, 0, 2)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-semibold text-gray-900">{{ $comment->user->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $comment->content }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-400 italic">Aucun commentaire pour le moment.</p>
                        @endforelse
                    </div>

                    {{-- Ajouter un commentaire --}}
                    <form method="POST" action="{{ route('member.tasks.comment', $task) }}" class="flex gap-2">
                        @csrf
                        <input
                            type="text"
                            name="content"
                            placeholder="Ajouter un commentaire..."
                            class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            required
                            maxlength="2000"
                        >
                        <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Envoyer
                        </button>
                    </form>

                    {{-- Pièces jointes existantes --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Fichiers joints</h4>
                        <div class="space-y-2">
                            @forelse($task->attachments as $attachment)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    <span class="text-sm text-gray-700 truncate">{{ $attachment->name }}</span>
                                    <span class="text-xs text-gray-400 flex-shrink-0">{{ number_format($attachment->size / 1024, 1) }} KB</span>
                                </div>
                                <a href="{{ route('attachments.download', $attachment) }}" class="text-xs bg-white border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors flex-shrink-0">
                                    Télécharger
                                </a>
                            </div>
                            @empty
                            <p class="text-sm text-gray-400 italic">Aucun fichier joint.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Uploader un fichier --}}
                    <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="flex items-end gap-2">
                        @csrf
                        <input type="hidden" name="attachable_type" value="task">
                        <input type="hidden" name="attachable_id" value="{{ $task->id }}">

                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Nom du fichier</label>
                            <input type="text" name="name" placeholder="Nom (optionnel)" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Fichier</label>
                            <input type="file" name="file" required class="w-full text-sm border border-gray-200 rounded-xl px-3 py-1.5 bg-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary-50 file:text-primary-600 hover:file:bg-primary-100">
                        </div>
                        <button type="submit" class="bg-secondary-500 hover:bg-secondary-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Uploader
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white shadow-xl rounded-2xl p-12 text-center border border-gray-100">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Aucune tâche assignée</h3>
            <p class="text-slate-600">Vous n'avez pas encore de tâches assignées.</p>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
    // Notification badge
    fetch('/notifications/count')
        .then(res => res.json())
        .then(count => {
            const badge = document.getElementById('dash-notif-badge');
            if(badge) badge.textContent = count;
        });

    // Accordion toggle
    function toggleAccordion(taskId) {
        const body = document.getElementById('accordion-body-' + taskId);
        const icon = document.getElementById('accordion-icon-' + taskId);
        if (body.classList.contains('hidden')) {
            body.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            body.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    // Valider ma tâche: grisage immédiat + soumission fetch
    document.querySelectorAll('.validate-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const taskId = this.dataset.taskId;
            const card = document.getElementById('task-card-' + taskId);
            const btn = this.querySelector('.validate-btn');

            if (!confirm('Valider définitivement cette tâche ?')) return;

            // Grisage immédiat
            card.classList.add('opacity-60', 'grayscale', 'bg-gray-50');
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Validation...
            `;

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.redirected) {
                    window.location.reload();
                } else {
                    window.location.reload();
                }
            })
            .catch(() => {
                // En cas d'erreur, on recharge quand même pour refléter l'état serveur
                window.location.reload();
            });
        });
    });
</script>
@endpush
</x-app-layout>

