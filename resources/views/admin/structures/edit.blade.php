@extends('layouts.app')

@section('title', 'Modifier Structure - Admin')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center mb-8">
        <a href="{{ route('admin.structures.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900 ml-4">Modifier {{ $structure->name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.structures.update', $structure) }}" class="bg-white shadow-xl rounded-2xl p-8">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="name" :value="'Nom *'" />
                <x-text-input id="name" name="name" value="{{ old('name', $structure->name) }}" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="code" :value="'Code'" />
                <x-text-input id="code" name="code" value="{{ old('code', $structure->code) }}" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="type" :value="'Type *'" />
                <select name="type" id="type" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Sélectionner type</option>
                    <option value="dg" {{ old('type', $structure->type) === 'dg' ? 'selected' : '' }}>Direction Générale</option>
                    <option value="pole" {{ old('type', $structure->type) === 'pole' ? 'selected' : '' }}>Pôle</option>
                    <option value="division" {{ old('type', $structure->type) === 'division' ? 'selected' : '' }}>Division</option>
                    <option value="direction" {{ old('type', $structure->type) === 'direction' ? 'selected' : '' }}>Direction</option>
                    <option value="autre" {{ old('type', $structure->type) === 'autre' ? 'selected' : '' }}>Autre</option>
                </select>
                <x-input-error :messages="$errors->get('type')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="parent_id" :value="'Parent'" />
                <select name="parent_id" id="parent_id" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Racine</option>
                    @foreach($structures ?? [] as $s)
                        @if($s->id != $structure->id && !$structure->isDescendantOf($s))
                            <option value="{{ $s->id }}" {{ old('parent_id', $structure->parent_id) == $s->id ? 'selected' : '' }}>
                                {{ $s->hierarchy_path }}
                            </option>
                        @endif
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="description" :value="'Description'" />
                <textarea id="description" name="description" rows="4" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">{{ old('description', $structure->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>

        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.structures.index') }}" class="px-6 py-3 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Annuler
            </a>
            <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-lg shadow hover:bg-green-700 transition">
                Mettre à jour
            </button>
        </div>
    </form>
</div>
@endsection

