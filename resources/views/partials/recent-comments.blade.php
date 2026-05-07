@php
    $comments = collect($comments ?? []);
    $title = $title ?? 'Commentaires récents';
@endphp

<div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-100">
    <button
        type="button"
        @click="open = !open"
        class="w-full flex items-center justify-between gap-3 p-5 text-left rounded-xl hover:bg-gray-50 transition-colors"
    >
        <div class="min-w-0">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary-500">Commentaires</p>
            <h2 class="text-xl font-bold text-[#1E216D]">{{ $title }}</h2>
            <p class="text-sm text-slate-600">Derniers commentaires ajoutés dans votre périmètre, avec actions rapides.</p>
        </div>
        <div class="flex items-center gap-3 flex-shrink-0">
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ $comments->count() }} commentaires</span>
            <svg class="w-5 h-5 text-primary-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </button>

    <div x-show="open" x-collapse x-cloak class="px-5 pb-5">
        <div class="space-y-3">
        @forelse($comments as $comment)
            <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-4 lg:flex-row lg:items-start lg:justify-between" data-item-id="comment-{{ $comment->id }}">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center rounded-full bg-primary-500/10 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-primary-500">
                            Tâche
                        </span>
                        @if($comment->task)
                            <span class="text-[11px] font-medium text-slate-500">{{ Str::limit($comment->task->name, 50) }}</span>
                        @endif
                    </div>
                    <div class="text-gray-900 line-clamp-2 text-sm">{{ $comment->content }}</div>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                        @if($comment->user)
                            <span class="rounded-full bg-white px-2 py-0.5 border border-gray-200">{{ $comment->user->name }}</span>
                        @endif
                        <span class="rounded-full bg-white px-2 py-0.5 border border-gray-200">{{ $comment->created_at?->diffForHumans() ?? 'Récemment' }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 lg:justify-end flex-shrink-0">
                    @can('update', $comment)
                        <details>
                            <summary class="cursor-pointer list-none inline-flex items-center text-sm btn-light btn-sm">Modifier</summary>
                            <form method="POST" action="{{ route('comments.update', $comment) }}" class="mt-3 w-[22rem] max-w-[90vw] rounded-2xl border border-gray-200 bg-white p-4 shadow-xl ajax-form">
                                @csrf
                                @method('PATCH')
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Commentaire</label>
                                        <textarea name="content" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ $comment->content }}</textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="btn-primary btn-sm">Enregistrer</button>
                                    </div>
                                </div>
                            </form>
                        </details>
                    @endcan
                    @can('delete', $comment)
                        <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="inline ajax-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm" data-confirm-delete="Supprimer ce commentaire ?">Supprimer</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-6 text-center text-sm text-slate-500">
                Aucun commentaire récent.
            </div>
        @endforelse
        </div>
    </div>
</div>
