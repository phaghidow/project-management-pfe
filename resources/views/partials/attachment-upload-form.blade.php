{{-- Formulaire d'upload de fichier AJAX --}}
@props(['task'])

<form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" {{ $attributes->merge(['class' => 'space-y-3 p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200 ajax-form']) }}>
    @csrf
    <div class="flex items-center gap-2 mb-3">
        <svg class="w-5 h-5 text-at-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        <h4 class="text-sm font-semibold text-gray-700">Joindre un fichier</h4>
    </div>
    <input type="hidden" name="attachable_type" value="task">
    <input type="hidden" name="attachable_id" value="{{ $task->id }}">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Nom du fichier <span class="text-gray-400">(optionnel)</span></label>
            <input type="text" name="name" placeholder="Ex: rapport.pdf" class="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-at-orange-400 focus:border-transparent transition-all">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Fichier <span class="text-red-500">*</span></label>
            <input type="file" name="file" required class="w-full text-xs border-2 border-gray-200 rounded-xl px-3 py-2.5 bg-white file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-at-orange-50 file:text-at-orange-600 hover:file:bg-at-orange-100 cursor-pointer transition-all focus:outline-none focus:ring-2 focus:ring-at-orange-400 focus:border-transparent">
        </div>
    </div>
    <button type="submit" class="btn-secondary w-full md:w-auto">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
        </svg>
        Uploader le fichier
    </button>
</form>
