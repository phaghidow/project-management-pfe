<div class="flex flex-col h-full p-4 space-y-2 bg-white text-[#1A202C]">
    <!-- Logo -->
    <div class="flex items-center pb-4 mb-4 border-b border-gray-200">
        <a href="{{ route('dashboard') }}">
            <x-application-logo class="block h-10 w-auto fill-current text-primary-500" />
        </a>
        <span class="ml-3 text-lg font-bold" style="color: #2E3192;">Gestion Projets</span>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1">
        <ul class="space-y-1">
            @php
                $user = Auth::user();
                $canManageExecution = $user && ($user->isAdmin() || $user->isChefDepartement() || $user->isChefProjet());
            @endphp

            {{-- Dashboard -- accessible to all roles --}}
            <li>
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </x-nav-link>
            </li>

            {{-- Projets -- accessible to all roles --}}
            <li>
                <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Projets
                </x-nav-link>
            </li>

            {{-- Mes Projets -- accessible to all roles --}}
            <li>
                <x-nav-link :href="route('projects.my-projects')" :active="request()->routeIs('projects.my-projects')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656 .126-1.283 .356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Mes Projets
                </x-nav-link>
            </li>

            {{-- Tâches (all tasks) -- admin, chef_dept, chef_projet only --}}
            @if($canManageExecution)
            <li>
                <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*') && !request()->routeIs('tasks.my-tasks')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Tâches
                </x-nav-link>
            </li>
            @endif

            {{-- Mes Tâches -- accessible to all roles --}}
            <li>
                <x-nav-link :href="route('tasks.my-tasks')" :active="request()->routeIs('tasks.my-tasks')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656 .126-1.283 .356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Mes Tâches
                </x-nav-link>
            </li>

            {{-- Calendrier -- accessible to all roles --}}
            <li>
                <x-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Calendrier
                </x-nav-link>
            </li>

            {{-- Jalons -- admin, chef_dept, chef_projet only --}}
            @if($canManageExecution)
            <li>
                <x-nav-link :href="route('milestones.index')" :active="request()->routeIs('milestones.*')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2-2 2 2 0 00-2 2v5a2 2 0 01-2 2z"></path>
                    </svg>
                    Jalons
                </x-nav-link>
            </li>
            @endif

            {{-- Admin only menu items --}}
            @if($user && $user->isAdmin())
            <li class="pt-4 mt-2 border-t border-gray-100">
                <span class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administration</span>
            </li>

            <li>
                <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Utilisateurs
                </x-nav-link>
            </li>

            <li>
                <x-nav-link :href="route('admin.structures.index')" :active="request()->routeIs('admin.structures.*')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Structures
                </x-nav-link>
            </li>
            @endif

            {{-- Paramètres -- accessible to all roles --}}
            <li class="pt-4 mt-2 border-t border-gray-100">
                <span class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Compte</span>
            </li>

            <li>
                <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Mon Profil
                </x-nav-link>
            </li>

            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="group flex items-center w-full px-3 py-3 rounded-xl text-sm font-medium text-gray-600 hover:text-[#2E3192] hover:bg-blue-50 transition-colors">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Se déconnecter
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</div>

