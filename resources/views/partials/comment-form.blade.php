{{-- Formulaire de commentaire AJAX --}}
@props(['task'])

<form method="POST" action="{{ route('comments.store') }}" {{ $attributes->merge(['class' => 'space-y-3 ajax-form']) }}>
    @csrf
    <input type="hidden" name="task_id" value="{{ $task->id }}">
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">Ajouter un commentaire</label>
        <div class="flex gap-2">
            <input
                type="text"
                name="content"
                placeholder="Votre commentaire..."
                class="flex-1 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition-all"
                required
                maxlength="2000"
            >
            <button type="submit" class="btn-primary whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                Envoyer
            </button>
        </div>
    </div>
</form>
