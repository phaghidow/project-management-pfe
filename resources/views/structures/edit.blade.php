<x-app-layout>
<div class="p-6 max-w-2xl mx-auto">
    <div class="flex justify-between items-start mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $structure->name }}</h1>
            <p class="text-lg text-gray-600 mt-2">Modifier la structure</p>
        </div>
        <a href="{{ route('admin.structures.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transition-all">
            ← Retour
        </a>
    </div>

    <form method="POST" action="{{ route('admin.structures.update', $structure) }}" id="structure-form" class="bg-white shadow-2xl rounded-3xl p-8">
        @csrf @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div>
                <x-input-label for="name" value="Nom de la structure *" class="text-lg font-semibold" />
                <x-text-input id="name" name="name" type="text" class="mt-2" value="{{ old('name', $structure->name) }}" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="type" value="Type *" class="text-lg font-semibold" />
                <select name="type" id="type" class="mt-2 block w-full border-gray-300 rounded-xl shadow-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-500 py-3 px-4" required>
                    <option value="">Sélectionner type</option>
                    <option value="dg" {{ old('type', $structure->type) === 'dg' ? 'selected' : '' }}>Direction Générale (DG)</option>
                    <option value="pole" {{ old('type', $structure->type) === 'pole' ? 'selected' : '' }}>Pôle</option>
                    <option value="division" {{ old('type', $structure->type) === 'division' ? 'selected' : '' }}>Division</option>
                    <option value="direction" {{ old('type', $structure->type) === 'direction' ? 'selected' : '' }}>Direction</option>
                    <option value="autre" {{ old('type', $structure->type) === 'autre' ? 'selected' : '' }}>Autre</option>
                </select>
                <x-input-error :messages="$errors->get('type')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="code" value="Code (optionnel)" class="text-lg font-semibold" />
                <x-text-input id="code" name="code" type="text" class="mt-2" value="{{ old('code', $structure->code) }}" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                <p class="mt-1 text-sm text-gray-500">Ex: DG01, POLE-IT, DIV-FIN</p>
            </div>

            <div>
                <x-input-label for="parent_id" value="Structure parent (optionnel)" class="text-lg font-semibold" />
                <select name="parent_id" id="parent_id" class="mt-2 block w-full border-gray-300 rounded-xl shadow-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-500 py-3 px-4">
                    <option value="">-- Structure racine --</option>
                    @foreach($structures ?? [] as $s)
                        @if($s->id != $structure->id && !$s->isDescendantOf($structure))
                            <option value="{{ $s->id }}" {{ old('parent_id', $structure->parent_id) == $s->id ? 'selected' : '' }}>
                                {{ str_repeat('—', $s->level) }} {{ $s->name }} (Niv. {{ $s->level }})
                            </option>
                        @endif
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
                <div id="cycle-warning" class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-yellow-800 text-sm hidden">
                    ⚠️ Ce parent créerait un cycle hiérarchique!
                </div>
            </div>
        </div>

        <div class="mb-8">
            <x-input-label for="description" value="Description" class="text-lg font-semibold" />
            <textarea id="description" name="description" rows="4" class="mt-2 block w-full border-gray-300 rounded-xl shadow-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-500 resize-vertical p-4">{{ old('description', $structure->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="flex justify-end gap-4 pt-6 border-t border-[#E2E8F0]">
            <a href="{{ route('admin.structures.index') }}" class="px-8 py-3 text-[#1A202C] bg-white border border-[#E2E8F0] rounded-xl hover:bg-[#F8F9FC] font-semibold transition-all">
                Annuler
            </a>
            <x-primary-button id="submit-btn" class="px-12 py-3 bg-primary-500 hover:bg-primary-600 shadow-xl hover:shadow-2xl font-semibold text-lg transition-all">
                Mettre à jour
            </x-primary-button>
        </div>

        <div id="structure-level" class="hidden">{{ $structure->level }}</div>
    </form>
</div>

@vite(['resources/js/structures.js'])

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('structure-form');
    const parentSelect = document.getElementById('parent_id');
    const levelField = document.getElementById('structure-level');
    const cycleWarning = document.getElementById('cycle-warning');
    const structureId = '{{ $structure->id }}';

    parentSelect.addEventListener('change', function() {
        if (this.value) {
            fetch(`/admin/structures/${this.value}`)
                .then(res => res.json())
                .then(parent => {
                    levelField.value = parent.level + 1;
                })
                .catch(err => console.error('Error:', err));
        } else {
            levelField.value = 0;
        }

        // Check cycle
        if (this.value) {
            fetch('/admin/structures/check-parent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    structure_id: structureId,
                    parent_id: this.value
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.valid) {
                    cycleWarning.classList.remove('hidden');
                    parentSelect.setCustomValidity(data.error);
                } else {
                    cycleWarning.classList.add('hidden');
                    parentSelect.setCustomValidity('');
                }
                parentSelect.reportValidity();
            })
            .catch(err => console.error('Validation error:', err));
        } else {
            cycleWarning.classList.add('hidden');
            parentSelect.setCustomValidity('');
        }
    });
});
</script>
</x-app-layout>
