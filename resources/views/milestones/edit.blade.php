<x-app-layout>
<div class="page-mobile max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Modifier jalon</h1>

    <form method="POST" action="{{ route('milestones.update', $milestone) }}" class="responsive-form">
        @csrf
        @method('PUT')

        <!-- Projet -->
        <div class="mb-6">
            <x-input-label for="project_id" value="Projet *" />
            <select name="project_id" id="project_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                <option value="">Sélectionner un projet</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ $milestone->project_id == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error for="project_id" />
        </div>

        <!-- Nom -->
        <div class="mb-6">
            <x-input-label for="name" value="Nom *" />
            <x-text-input name="name" id="name" value="{{ old('name', $milestone->name) }}" required />
            <x-input-error for="name" />
        </div>

        <!-- Date d'échéance -->
        <div class="mb-6">
            <x-input-label for="due_date" value="Date d'échéance" />
            <x-text-input type="date" name="due_date" id="due_date" value="{{ old('due_date', $milestone->due_date) }}" />
            <x-input-error for="due_date" />
        </div>

        <div class="responsive-form-actions">
            <a href="{{ route('milestones.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                Annuler
            </a>
            <x-primary-button type="submit">Mettre à jour</x-primary-button>
        </div>
    </form>
</div>
</x-app-layout>

