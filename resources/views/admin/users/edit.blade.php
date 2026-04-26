@extends('layouts.app')

@section('title', 'Modifier ' . $user->name)

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center mb-8">
        <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900 ml-4">Modifier {{ $user->name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white shadow-xl rounded-2xl p-8">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="name" :value="'Nom complet'" />
                <x-text-input id="name" name="name" :value="old('name', $user->name)" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="username" :value="'Username'" />
                <x-text-input id="username" name="username" :value="old('username', $user->username)" required />
                <x-input-error :messages="$errors->get('username')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="email" :value="'Email'" />
                <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="role" :value="'Rôle'" />
                <select name="role" id="role" class="w-full border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500">
                    <option value="{{ \App\Models\User::ROLE_ADMIN }}" {{ old('role', $user->role) === \App\Models\User::ROLE_ADMIN ? 'selected' : '' }}>Administrateur</option>
                    <option value="{{ \App\Models\User::ROLE_CHEF_DEPT }}" {{ old('role', $user->role) === \App\Models\User::ROLE_CHEF_DEPT ? 'selected' : '' }}>Chef de Dept</option>
                    <option value="{{ \App\Models\User::ROLE_CHEF_DEPARTEMENT }}" {{ old('role', $user->role) === \App\Models\User::ROLE_CHEF_DEPARTEMENT ? 'selected' : '' }}>Chef Département</option>
                    <option value="{{ \App\Models\User::ROLE_CHEF_PROJET }}" {{ old('role', $user->role) === \App\Models\User::ROLE_CHEF_PROJET ? 'selected' : '' }}>Chef Projet</option>
                    <option value="{{ \App\Models\User::ROLE_MEMBRE }}" {{ old('role', $user->role) === \App\Models\User::ROLE_MEMBRE ? 'selected' : '' }}>Membre</option>
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="structure_id" :value="'Structure'" />
                <select name="structure_id" id="structure_id" class="w-full border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500">
                    <option value="">Aucune</option>
                    @foreach($structures as $structure)
                    <option value="{{ $structure->id }}" {{ old('structure_id', $user->structure_id) == $structure->id ? 'selected' : '' }}>
                        {{ $structure->hierarchy_path }}
                    </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('structure_id')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <fieldset>
                    <legend class="text-sm font-medium text-gray-700 mb-3">Statut</legend>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="flex items-center p-3 border rounded-lg hover:border-blue-300 cursor-pointer {{ old('status', $user->status) === 'en_attente' ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                            <input type="radio" name="status" value="en_attente" class="mr-3" {{ old('status', $user->status) === 'en_attente' ? 'checked' : '' }}>
                            <div>
                                <div class="font-medium text-gray-900">En attente</div>
                                <div class="text-xs text-gray-500">Nécessite activation</div>
                        </label>
                        <label class="flex items-center p-3 border rounded-lg hover:border-green-300 cursor-pointer {{ old('status', $user->status) === 'active' ? 'border-green-500 bg-green-50' : 'border-gray-300' }}">
                            <input type="radio" name="status" value="active" class="mr-3" {{ old('status', $user->status) === 'active' ? 'checked' : '' }}>
                            <div>
                                <div class="font-medium text-gray-900">Actif</div>
                                <div class="text-xs text-gray-500">Peut se connecter</div>
                        </label>
                        <label class="flex items-center p-3 border rounded-lg hover:border-red-300 cursor-pointer {{ old('status', $user->status) === 'disabled' ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                            <input type="radio" name="status" value="disabled" class="mr-3" {{ old('status', $user->status) === 'disabled' ? 'checked' : '' }}>
                            <div>
                                <div class="font-medium text-gray-900">Désactivé</div>
                                <div class="text-xs text-gray-500">Bloqué</div>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </fieldset>
            </div>

            @if(auth()->user()->isAdmin())
            <div class="md:col-span-2">
                <x-input-label for="password" :value="'Nouveau mot de passe (optionnel)'" />
                <x-text-input id="password" name="password" type="password" />
                <x-input-label for="password_confirmation" class="mt-2" :value="'Confirmer nouveau mot de passe'" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" />
                <p class="text-xs text-gray-500 mt-1">Laisser vide pour conserver l'ancien mot de passe</p>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            @endif
        </div>

        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.users.index') }}" class="px-6 py-3 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Annuler
            </a>
            <button type="submit" class="bg-emerald-600 text-white px-8 py-3 rounded-lg shadow hover:bg-emerald-700 transition">
                {{ __('Mettre à jour') }}
            </button>
        </div>
    </form>
</div>
@endsection
