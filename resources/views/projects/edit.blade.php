<x-app-layout>
<div class="page-mobile">
    <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center mb-6">
        <h1 class="text-2xl font-bold">Modifier le Projet</h1>
        <a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-gray-700">
            ← Retour
        </a>
    </div>

    <form method="POST" action="{{ route('projects.update', $project) }}" class="responsive-form">
        @csrf
        @method('PUT')
        
        <div class="bg-white p-6 shadow rounded-lg">
            <div class="responsive-form-grid md:grid-cols-2">
                
                <div>
                    <x-input-label for="name" :value="__('Nom du projet')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $project->name) }}" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="user_id" :value="'Responsable'" />
                    <select name="user_id" id="user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Sélectionner un utilisateur</option>
                        @foreach(\App\Models\User::where('active', true)->get() as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $project->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm resize-vertical">{{ old('description', $project->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="start_date" :value="'Date de début'" />
                    <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" value="{{ old('start_date', $project->start_date) }}" />
                    <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="end_date" :value="'Date de fin'" />
                    <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" value="{{ old('end_date', $project->end_date) }}" />
                    <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                </div>

                @if($project->progress)
                <div class="md:col-span-2">
                    <x-input-label for="progress" :value="'Progression (%)'" />
                    <x-text-input id="progress" name="progress" type="number" min="0" max="100" class="mt-1 block w-full" value="{{ old('progress', $project->progress) }}" step="0.1" />
                    <x-input-error :messages="$errors->get('progress')" class="mt-2" />
                </div>
                @endif
            </div>
        </div>

        <div class="responsive-form-actions mt-6">
            <a href="{{ route('projects.show', $project) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Annuler
            </a>
            <x-primary-button>
                {{ __('Mettre à jour') }}
            </x-primary-button>
        </div>
    </form>
</div>
</x-app-layout>
