@php
    $attachableType = $type ?? ($attachable instanceof \App\Models\Project ? 'project' : 'task');
@endphp

<div class="bg-white shadow rounded-lg p-6 mb-8">
    <h2 class="text-xl font-bold mb-4">Pièces jointes</h2>

    <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="mb-6">
        @csrf
        <input type="hidden" name="attachable_type" value="{{ $attachableType }}">
        <input type="hidden" name="attachable_id" value="{{ $attachable->id }}">

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="attachment-name-{{ $attachableType }}-{{ $attachable->id }}" class="block text-sm font-medium text-gray-700 mb-2">Nom du fichier</label>
                <input type="text" name="name" id="attachment-name-{{ $attachableType }}-{{ $attachable->id }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nom personnalisé (optionnel)">
            </div>

            <div>
                <label for="attachment-file-{{ $attachableType }}-{{ $attachable->id }}" class="block text-sm font-medium text-gray-700 mb-2">Fichier</label>
                <input type="file" name="file" id="attachment-file-{{ $attachableType }}-{{ $attachable->id }}" class="w-full text-sm border border-gray-300 rounded-md shadow-sm bg-white">
                @error('file')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-3 flex justify-end">
            <button type="submit" class="inline-flex items-center justify-center bg-primary-500 text-white px-4 py-2 rounded-md hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition !text-white">
                Uploader
            </button>
        </div>
    </form>

    <div class="space-y-3">
        @forelse($attachable->attachments as $attachment)
            <div class="flex flex-col gap-3 rounded-lg border bg-gray-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="font-medium text-gray-900">{{ $attachment->name }}</div>
                    <div class="text-xs text-gray-500">
                        {{ $attachment->mime_type ?? 'Fichier' }}
                        @if($attachment->size)
                            • {{ number_format($attachment->size / 1024, 1) }} KB
                        @endif
                        @if($attachment->user)
                            • Ajoute par {{ $attachment->user->name }}
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('attachments.download', $attachment) }}" class="inline-flex items-center justify-center text-sm bg-white border border-gray-300 px-3 py-2 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">Télécharger</a>
                    @can('update', $attachment)
                        <details class="relative">
                            <summary class="cursor-pointer list-none inline-flex items-center justify-center text-sm bg-white border border-gray-300 px-3 py-2 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">Modifier</summary>
                            <form method="POST" action="{{ route('attachments.update', $attachment) }}" enctype="multipart/form-data" class="mt-3 w-80 max-w-sm rounded-lg border border-gray-200 bg-white p-4 shadow-lg">
                                @csrf
                                @method('PATCH')

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Nom du fichier</label>
                                        <input type="text" name="name" value="{{ $attachment->name }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Remplacer le fichier</label>
                                        <input type="file" name="file" class="w-full text-xs border border-gray-300 rounded-md shadow-sm bg-white">
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        <button type="submit" class="inline-flex items-center justify-center text-sm bg-primary-500 text-white px-3 py-2 rounded-md hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition !text-white">Enregistrer</button>
                                    </div>
                                </div>
                            </form>
                        </details>
                    @endcan
                    @can('delete', $attachment)
                        <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" onsubmit="return confirm('Supprimer ce fichier ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center text-sm bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 focus:bg-red-700 active:bg-red-800 transition !text-white">Supprimer</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">Aucune pièce jointe pour le moment.</p>
        @endforelse
    </div>
</div>
