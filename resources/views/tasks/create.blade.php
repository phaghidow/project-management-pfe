<x-app-layout>
<div class="page-mobile max-w-2xl mx-auto">
    <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold">Nouvelle tâche</h1>
            <p class="text-gray-600 mt-1">Créer une nouvelle tâche</p>
        </div>
        <a href="{{ route('tasks.index') }}" class="text-gray-600 hover:text-gray-900">
            ← Retour
        </a>
    </div>

    <form method="POST" action="{{ route('tasks.store') }}" class="responsive-form bg-white shadow rounded-lg p-4 sm:p-6">
        @csrf

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-4">Projet et Jalon *</label>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Projet</label>
                    <select name="project_id" id="project_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('project_id') border-red-500 @enderror">
                        <option value="">Sélectionnez un projet...</option>
                        @foreach($projects ?? [] as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $selectedProjectId ?? '') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="milestone_id" class="block text-sm font-medium text-gray-700 mb-2">Jalon</label>
                    <select name="milestone_id" id="milestone_id" required disabled class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('milestone_id') border-red-500 @enderror">
                        <option value="">Sélectionnez un projet d'abord</option>
                    </select>
                    @error('milestone_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom de la tâche *</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Échéance</label>
                <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Intervenants</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                @foreach($users ?? [] as $user)
                    <label class="flex items-center p-2 border rounded-md cursor-pointer hover:bg-gray-50 @if(old('users.' . $user->id)) bg-indigo-50 border-indigo-200 @endif">
                        <input type="checkbox" name="users[]" value="{{ $user->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('users.' . $user->id) ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700">{{ $user->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('users.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="responsive-form-actions">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Créer la tâche
            </button>
            <a href="{{ route('tasks.index') }}" class="border border-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-50">
                Annuler
            </a>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const projectSelect = document.getElementById('project_id');
            const milestoneSelect = document.getElementById('milestone_id');
            const preselectedMilestoneId = {{ $selectedMilestone->id ?? 'null' }};
            const preselectedProjectId = {{ $selectedProjectId ?? 'null' }};

            // Function to load milestones for a project
            function loadMilestones(projectId, selectedMilestoneId = null) {
                if (!projectId) {
                    milestoneSelect.innerHTML = '<option value="">Sélectionnez un projet d\'abord</option>';
                    milestoneSelect.disabled = true;
                    return;
                }

                milestoneSelect.disabled = true;
                milestoneSelect.innerHTML = '<option value="">Chargement...</option>';

                fetch(`/api/milestones/by-project/${projectId}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(milestones => {
                    milestoneSelect.innerHTML = '<option value="">Sélectionnez un jalon</option>';
                    
                    if (milestones.length === 0) {
                        milestoneSelect.innerHTML = '<option value="" disabled>Aucun jalon disponible</option>';
                        milestoneSelect.disabled = true;
                    } else {
                        milestones.forEach(milestone => {
                            const option = document.createElement('option');
                            option.value = milestone.id;
                            option.textContent = milestone.name;
                            
                            // Pre-select the milestone if it matches
                            if (selectedMilestoneId && milestone.id == selectedMilestoneId) {
                                option.selected = true;
                            }
                            
                            milestoneSelect.appendChild(option);
                        });
                        milestoneSelect.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    milestoneSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                    milestoneSelect.disabled = true;
                });
            }

            // Load milestones on page load if a project is preselected
            if (preselectedProjectId) {
                loadMilestones(preselectedProjectId, preselectedMilestoneId);
            }

            // Handle project change
            projectSelect.addEventListener('change', function() {
                const projectId = this.value;
                loadMilestones(projectId);
            });
        });
    </script>
</div>
</x-app-layout>

