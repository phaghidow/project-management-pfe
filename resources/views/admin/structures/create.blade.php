@extends('layouts.app')

@section('title', 'Créer Structure - Admin')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center mb-8">
        <a href="{{ route('admin.structures.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900 ml-4">Nouvelle Structure</h1>
    </div>

    <form method="POST" action="{{ route('admin.structures.store') }}" class="bg-white shadow-xl rounded-2xl p-8">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="name" :value="'Nom complet *'" />
                <x-text-input id="name" name="name" :value="old('name')" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="code" :value="'Code (optionnel)'" />
                <x-text-input id="code" name="code" :value="old('code')" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="type" :value="'Type *'" />
                <select name="type" id="type" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Sélectionner type</option>
                    <option value="dg" {{ old('type') === 'dg' ? 'selected' : '' }}>Direction Générale</option>
                    <option value="pole" {{ old('type') === 'pole' ? 'selected' : '' }}>Pôle</option>
                    <option value="division" {{ old('type') === 'division' ? 'selected' : '' }}>Division</option>
                    <option value="direction" {{ old('type') === 'direction' ? 'selected' : '' }}>Direction</option>
                    <option value="autre" {{ old('type') === 'autre' ? 'selected' : '' }}>Autre</option>
                </select>
                <x-input-error :messages="$errors->get('type')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="parent_id" :value="'Parent (optionnel)'" />
                <select name="parent_id" id="parent_id" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Racine</option>
                    @foreach($structures ?? [] as $structure)
                        <option value="{{ $structure->id }}" {{ old('parent_id') == $structure->id ? 'selected' : '' }}>
                            {{ $structure->hierarchy_path }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="description" :value="'Description'" />
                <textarea id="description" name="description" rows="4" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>

        <div class="flex flex-col-reverse gap-4 mt-8 pt-6 border-t border-[#E2E8F0] sm:flex-row sm:justify-end">
            <a href="{{ route('admin.structures.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-gray-300 bg-white text-gray-700 font-medium transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                Annuler
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-8 py-3 rounded-lg bg-primary-500 text-white shadow hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition !text-white">
                Créer Structure
            </button>
        </div>
    </form>
</div>
@endsection

