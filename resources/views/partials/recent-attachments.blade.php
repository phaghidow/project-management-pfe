@php
    $attachments = collect($attachments ?? []);
    $title = $title ?? 'Pièces jointes récentes';
    $totalSize = (int) $attachments->sum('size');
    $projectAttachments = $attachments->filter(fn ($attachment) => ($attachment->attachable_type ?? '') === 'project')->count();
    $taskAttachments = $attachments->filter(fn ($attachment) => ($attachment->attachable_type ?? '') === 'task')->count();
@endphp

<div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-100">
    <button
        type="button"
        @click="open = !open"
        class="w-full flex items-center justify-between gap-3 p-5 text-left rounded-xl hover:bg-gray-50 transition-colors"
    >
        <div class="min-w-0">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary-500">Attachments</p>
            <h2 class="text-xl font-bold text-[#1E216D]">{{ $title }}</h2>
            <p class="text-sm text-slate-600">Derniers fichiers ajoutés dans votre périmètre, avec actions rapides.</p>
        </div>
        <div class="flex items-center gap-3 flex-shrink-0">
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ $attachments->count() }} fichiers</span>
            <svg class="w-5 h-5 text-primary-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </button>

    <div x-show="open" x-collapse x-cloak class="px-5 pb-5">
        <div class="mb-4 flex flex-wrap gap-2 text-xs font-semibold">
            <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">{{ $projectAttachments }} projet(s)</span>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">{{ $taskAttachments }} tâche(s)</span>
            <span class="rounded-full bg-amber-50 px-3 py-1 text-amber-700">{{ number_format($totalSize / 1024, 1) }} KB</span>
        </div>

        <div class="space-y-3">
        @forelse($attachments as $attachment)
            <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-4 lg:flex-row lg:items-center lg:justify-between" data-item-id="attachment-{{ $attachment->id }}">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center rounded-full bg-primary-500/10 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-primary-500">
                            {{ class_basename($attachment->attachable_type) }}
                        </span>
                        @if($attachment->mime_type)
                            <span class="text-[11px] font-medium text-slate-500">{{ $attachment->mime_type }}</span>
                        @endif
                    </div>
                    <div class="font-semibold text-gray-900 truncate">{{ $attachment->name }}</div>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                        @if($attachment->user)
                            <span class="rounded-full bg-white px-2 py-0.5 border border-gray-200">Ajouté par {{ $attachment->user->name }}</span>
                        @endif
                        @if($attachment->size)
                            <span class="rounded-full bg-white px-2 py-0.5 border border-gray-200">{{ number_format($attachment->size / 1024, 1) }} KB</span>
                        @endif
                        <span class="rounded-full bg-white px-2 py-0.5 border border-gray-200">{{ $attachment->created_at?->diffForHumans() ?? 'Récemment' }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                    <a href="{{ route('attachments.download', $attachment) }}" class="inline-flex items-center btn-light btn-sm">Télécharger</a>
                    @can('update', $attachment)
                        <details>
                            <summary class="cursor-pointer list-none inline-flex items-center text-sm btn-light btn-sm">Modifier</summary>
                            <form method="POST" action="{{ route('attachments.update', $attachment) }}" enctype="multipart/form-data" class="mt-3 w-[22rem] max-w-[90vw] rounded-2xl border border-gray-200 bg-white p-4 shadow-xl ajax-form">
                                @csrf
                                @method('PATCH')
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Nom du fichier</label>
                                        <input type="text" name="name" value="{{ $attachment->name }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Remplacer le fichier</label>
                                        <input type="file" name="file" class="w-full text-xs border border-gray-300 rounded-lg shadow-sm bg-white">
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="btn-primary btn-sm">Enregistrer</button>
                                    </div>
                                </div>
                            </form>
                        </details>
                    @endcan
                    @can('delete', $attachment)
                        <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" class="ajax-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm" data-confirm-delete="Supprimer ce fichier ?">Supprimer</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-6 text-center text-sm text-slate-500">
                Aucune pièce jointe récente.
            </div>
        @endforelse
        </div>
    </div>
</div>