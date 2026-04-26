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
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
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
                    <a href="{{ route('attachments.download', $attachment) }}" class="text-sm bg-white border border-gray-300 px-3 py-2 rounded hover:bg-gray-50">Télécharger</a>
                    @can('delete', $attachment)
                        <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" onsubmit="return confirm('Supprimer ce fichier ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700">Supprimer</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">Aucune pièce jointe pour le moment.</p>
        @endforelse
    </div>
</div>
