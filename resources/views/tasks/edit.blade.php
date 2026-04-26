<x-app-layout>
<div class="page-mobile max-w-2xl mx-auto">
    <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-start mb-6">
        <div class="opacity-60 {{ $task->status === 'validated' ? 'grayscale' : '' }}">
            <h1 class="text-2xl font-bold">Modifier tâche</h1>
            <p class="text-gray-600 mt-1">
                Statut: <span class="font-semibold {{ $task->status === 'validated' ? 'text-green-600' : 'text-yellow-600' }}">{{ ucfirst($task->status) }}</span>
                @if($task->validated_at)
                    <br>Validée le {{ $task->validated_at->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>
        <a href="{{ route('tasks.index') }}" class="text-gray-600 hover:text-gray-900">
            ← Retour
        </a>
    </div>

    @if($task->status === 'validated')
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 opacity-60 grayscale">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Tâche validée</h3>
                    <p class="mt-1 text-sm text-green-700">Cette tâche est validée et ne peut plus être modifiée.</p>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('tasks.update', $task) }}" class="responsive-form bg-white shadow rounded-lg p-4 sm:p-6 {{ $task->status === 'validated' ? 'opacity-60 grayscale pointer-events-none' : '' }}">
        @csrf
        @method('PUT')

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-4">Projet et Jalon *</label>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 {{ $task->status === 'validated' ? 'opacity-60 pointer-events-none' : '' }}">
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Projet</label>
                    <select name="project_id" id="project_id" {{ $task->status === 'validated' ? 'disabled' : '' }} class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('project_id') border-red-500 @enderror">
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
                    <select name="milestone_id" id="milestone_id" {{ $task->status === 'validated' ? 'disabled' : '' }} required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('milestone_id') border-red-500 @enderror">
                        <option value="">Sélectionnez un projet d'abord</option>
                    </select>
                    @error('milestone_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $task->name) }}" {{ $task->status === 'validated' ? 'readonly disabled' : '' }} required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $task->start_date?->format('Y-m-d')) }}" {{ $task->status === 'validated' ? 'disabled' : '' }} class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $task->end_date?->format('Y-m-d')) }}" {{ $task->status === 'validated' ? 'disabled' : '' }} class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Échéance</label>
                <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" {{ $task->status === 'validated' ? 'disabled' : '' }} class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Intervenants</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                @foreach($users ?? [] as $user)
                    <label class="flex items-center p-2 border rounded-md cursor-pointer hover:bg-gray-50 {{ in_array($user->id, old('users', $task->users->pluck('id')->toArray())) ? 'bg-indigo-50 border-indigo-200' : '' }} {{ $task->status === 'validated' ? 'opacity-50 pointer-events-none' : '' }}">
                        <input type="checkbox" name="users[]" value="{{ $user->id }}" {{ in_array($user->id, old('users', $task->users->pluck('id')->toArray())) ? 'checked' : '' }} {{ $task->status === 'validated' ? 'disabled' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">{{ $user->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('users.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Section Dépendances --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-4">Dépendances</label>
            <form method="POST" action="{{ route('tasks.dependencies.add', $task) }}" class="mb-4">
                @csrf
                <div class="flex flex-col gap-2 sm:flex-row">
                    <select name="dependency_id" class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($task->milestone->tasks->where('id', '!=', $task->id) as $otherTask)
                            <option value="{{ $otherTask->id }}">{{ $otherTask->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        Ajouter
                    </button>
                </div>
            </form>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                @foreach($task->dependencies as $dep)
                    <form method="POST" action="{{ route('tasks.dependencies.remove', [$task, $dep]) }}" class="inline-flex items-center bg-red-50 border border-red-200 rounded p-2">
                        @csrf
                        @method('DELETE')
                        <span class="text-sm">{{ $dep->name }}</span>
                        <button type="submit" onclick="return confirm('Supprimer cette dépendance ?')" class="ml-2 text-red-600 hover:text-red-800">
                            ×
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        <div class="responsive-form-actions">
            <button type="submit" {{ $task->status === 'validated' ? 'disabled' : '' }} class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $task->status === 'validated' ? 'opacity-50 cursor-not-allowed' : '' }}">
                {{ $task->status === 'validated' ? 'Validée (lecture seule)' : 'Mettre à jour' }}
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
            const currentMilestoneId = {{ $task->milestone_id ?? 'null' }};
            const currentProjectId = {{ $selectedProjectId ?? 'null' }};

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
                            
                            // Pre-select the current milestone if it matches
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

            // Load milestones on page load if a project is selected
            if (currentProjectId) {
                loadMilestones(currentProjectId, currentMilestoneId);
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

