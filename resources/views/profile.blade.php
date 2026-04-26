@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#1A202C]">Mon Profil</h1>
        <p class="text-sm text-gray-500 mt-1">Consultez vos informations personnelles</p>
    </div>

    <!-- Profile Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Banner & Avatar -->
        <div class="relative h-32 bg-linear-to-r from-[#2E3192] to-[#1A202C]">
            <div class="absolute -bottom-12 left-6">
                <img class="w-24 h-24 rounded-2xl border-4 border-white shadow-lg bg-white"
                     src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=128&color=fff&background=2E3192"
                     alt="{{ $user->name }}">
            </div>
        </div>

        <div class="pt-16 pb-6 px-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-bold text-[#1A202C]">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}"
                   class="inline-flex items-center px-4 py-2 bg-[#2E3192] text-white text-sm font-medium rounded-xl hover:bg-[#1A202C] transition shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Modifier le profil
                </a>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="border-t border-gray-100 px-6 py-6">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Informations du compte</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Rôle</p>
                        <p class="text-sm font-semibold text-[#1A202C]">{{ $user->role_label }}</p>
                    </div>
                </div>

                <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Structure</p>
                        <p class="text-sm font-semibold text-[#1A202C]">{{ $user->structure?->name ?? 'Non assignée' }}</p>
                    </div>
                </div>

                <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Statut</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $user->status_color }}-100 text-{{ $user->status_color }}-800">
                            {{ $user->status_label }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                    <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Dernière connexion</p>
                        <p class="text-sm font-semibold text-[#1A202C]">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="border-t border-gray-100 px-6 py-6">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Détails</h3>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-500">Nom d'utilisateur</span>
                    <span class="text-sm font-medium text-[#1A202C]">{{ $user->username ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-500">Email vérifié</span>
                    <span class="text-sm font-medium text-[#1A202C]">
                        {{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : 'Non vérifié' }}
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-500">Membre depuis</span>
                    <span class="text-sm font-medium text-[#1A202C]">{{ $user->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

