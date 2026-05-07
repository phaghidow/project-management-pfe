@extends('layouts.app')

@section('title', 'Créer Utilisateur')

@section('content')
<div class="p-4 max-w-3xl mx-auto">
    <div class="flex items-center mb-8">
        <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900 ml-3">Nouveau Utilisateur</h1>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="bg-white shadow-xl rounded-2xl border border-slate-100 p-6">
        @csrf
        <div class="mb-4 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-700">
            Remplissez les informations essentielles puis choisissez le role et le statut d'activation.
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="'Nom complet'" />
                <x-text-input id="name" name="name" :value="old('name')" required autofocus class="mt-1 block w-full" placeholder="Ex: Ahmed Benali" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="function" :value="'Fonction'" />
                <x-text-input id="function" name="function" :value="old('function')" class="mt-1 block w-full" placeholder="Ex: Ingénieur, Développeur, Expert IT" />
                <x-input-error :messages="$errors->get('function')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="username" :value="'Username'" />
                <x-text-input id="username" name="username" :value="old('username')" required class="mt-1 block w-full" placeholder="Ex: a.benali" />
                <x-input-error :messages="$errors->get('username')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="email" :value="'Email professionnel'" />
                <x-text-input id="email" name="email" type="email" :value="old('email')" required class="mt-1 block w-full" placeholder="prenom.nom@at.dz" />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
                <p class="text-xs text-gray-500 mt-1">Format: username@at.dz ou username@algerietelecom.dz</p>
            </div>

            <div>
                <x-input-label for="password" :value="'Mot de passe'" />
                <x-text-input id="password" name="password" type="password" required class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="'Confirmer mot de passe'" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            </div>

            <div>
                <x-input-label for="role" :value="'Rôle'" />
                <select name="role" id="role" class="mt-1 w-full border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Sélectionner rôle</option>
                    <option value="{{ \App\Models\User::ROLE_ADMIN }}" {{ old('role') === \App\Models\User::ROLE_ADMIN ? 'selected' : '' }}>Administrateur</option>
                    <option value="{{ \App\Models\User::ROLE_CHEF_DEPARTEMENT }}" {{ old('role') === \App\Models\User::ROLE_CHEF_DEPARTEMENT ? 'selected' : '' }}>Chef de Département</option>
                    <option value="{{ \App\Models\User::ROLE_CHEF_PROJET }}" {{ old('role') === \App\Models\User::ROLE_CHEF_PROJET ? 'selected' : '' }}>Chef Projet</option>
                    <option value="{{ \App\Models\User::ROLE_MEMBRE }}" {{ old('role') === \App\Models\User::ROLE_MEMBRE ? 'selected' : '' }}>Membre</option>
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="structure_id" :value="'Structure'" />
                <select
                    name="structure_id"
                    id="structure_id"
                    class="mt-1 w-full border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    data-tree-select="structures"
                    data-placeholder="Rechercher une structure..."
                >
                    <option value="">Aucune structure</option>
                    @foreach($structures as $structure)
                    <option
                        value="{{ $structure->id }}"
                        data-level="{{ (int) ($structure->level ?? 0) }}"
                        data-name="{{ $structure->name }}"
                        data-path="{{ str_replace(' > ', ' / ', $structure->hierarchy_path) }}"
                        {{ old('structure_id') == $structure->id ? 'selected' : '' }}
                    >
                        {{ $structure->hierarchy_path }}
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Recherche rapide avec affichage hiérarchique des directions.</p>
                <x-input-error :messages="$errors->get('structure_id')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label :value="'Statut du compte'" />
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <label for="status_en_attente" class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 cursor-pointer hover:border-primary-300 transition">
                        <input type="radio" name="status" value="en_attente" id="status_en_attente" {{ old('status', 'en_attente') === 'en_attente' ? 'checked' : '' }} class="text-primary-600 focus:ring-primary-500">
                        <span class="text-sm font-medium text-gray-700">En attente</span>
                    </label>
                    <label for="status_active" class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 cursor-pointer hover:border-primary-300 transition">
                        <input type="radio" name="status" value="active" id="status_active" {{ old('status') === 'active' ? 'checked' : '' }} class="text-primary-600 focus:ring-primary-500">
                        <span class="text-sm font-medium text-gray-700">Actif</span>
                    </label>
                    <label for="status_disabled" class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 cursor-pointer hover:border-primary-300 transition">
                        <input type="radio" name="status" value="disabled" id="status_disabled" {{ old('status') === 'disabled' ? 'checked' : '' }} class="text-primary-600 focus:ring-primary-500">
                        <span class="text-sm font-medium text-gray-700">Désactivé</span>
                    </label>
                </div>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 mt-6 pt-4 border-t border-[#E2E8F0] sm:flex-row sm:justify-end">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-[#E2E8F0] bg-white text-gray-900 font-medium transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                Annuler
            </a>
            <x-primary-button class="px-7 py-3 rounded-lg shadow hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700">
                Créer utilisateur
            </x-primary-button>
        </div>
    </form>
</div>
@endsection
