<div class="flex flex-col h-full p-4 space-y-2 bg-white text-[#1A202C]">
    <!-- Logo -->
    <div class="flex items-center justify-between gap-1 pb-2 mb-2 border-b border-gray-200">
        <a href="{{ route('dashboard') }}" class="flex items-center">
            <img src="{{ asset('images/Logo_Algérie_Télécom.svg.png') }}" alt="Algérie Télécom" class="h-8 w-auto object-contain" />
        </a>
        <span class="text-[14px] font-semibold text-[#2E3192]">Gestion de Projets</span>
    </div>

    @php
        $user = Auth::user();

        $dashboardRoute = match(true) {
            $user->isAdmin() => route('admin.dashboard'),
            $user->isChefDepartement() => route('departement.dashboard'),
            $user->isMembre() => route('member.dashboard'),
            default => route('dashboard'),
        };
        $dashboardActive = request()->routeIs(['dashboard', 'admin.dashboard', 'departement.dashboard', 'member.dashboard']);

        // Sert à ouvrir automatiquement le groupe "Projets" si on est sur une de ses sous-pages
        $projectsGroupActive = request()->routeIs(['projects.index', 'projects.cancelled', 'projects.my-projects', 'milestones.*']);
        $adminGroupActive = request()->routeIs(['admin.users.*', 'admin.structures.*']);
    @endphp

    <!-- Navigation Links -->
    <nav class="flex-1 overflow-y-auto">
        <ul class="space-y-1">

            {{-- ========================================
                SECTION: GÉNÉRAL — liens directs, toujours visibles
                ======================================== --}}
            <li class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Général</li>

            <li>
                <x-nav-link :href="$dashboardRoute" :active="$dashboardActive" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </x-nav-link>
            </li>

            <li>
                <x-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Calendrier
                </x-nav-link>
            </li>

            @if($user->isChefProjet() || $user->isChefDepartement() || $user->isAdmin())
            <li>
                <x-nav-link :href="route('gantt')" :active="request()->routeIs('gantt')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 6v2h8V6M4 12h16M4 12v2h12v-2M4 18h16M4 18v2h5v-2"></path>
                    </svg>
                    Gantt
                </x-nav-link>
            </li>
            @endif

            <li>
                <x-nav-link :href="route('organigramme.preview')" :active="request()->routeIs('organigramme.preview')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Organigramme
                </x-nav-link>
            </li>

            {{-- ========================================
                GROUPE ACCORDÉON: PROJETS
                Visible pour: Membre, Chef de Projet, Chef de Département, Admin
                ======================================== --}}
            @if($user->isMembre() || $user->isChefProjet() || $user->isChefDepartement() || $user->isAdmin())
            <li class="pt-4 mt-2 border-t border-gray-100"></li>

            <li x-data="{ open: {{ $projectsGroupActive ? 'true' : 'false' }} }">
                <button
                    @click="open = !open"
                    type="button"
                    class="group w-full flex items-center justify-between px-3 py-3 rounded-xl sidebar-link transition-colors"
                    :class="open ? 'text-[#2E3192] bg-indigo-50/60' : 'text-gray-700 hover:bg-gray-50'"
                >
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Projets
                    </span>
                    <svg
                        class="w-4 h-4 flex-shrink-0 transition-transform duration-300 ease-in-out"
                        :class="open ? 'rotate-90' : 'rotate-0'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                {{-- Astuce CSS pure pour une hauteur animée fluide (pas besoin du plugin Alpine collapse) --}}
                <div class="grid transition-[grid-template-rows] duration-300 ease-in-out" :style="open ? 'grid-template-rows: 1fr' : 'grid-template-rows: 0fr'">
                    <div class="overflow-hidden">
                        <ul class="ml-5 mt-1 pl-3 space-y-1 border-l-2 border-gray-100">

                            @if($user->isMembre() || $user->isChefProjet())
                            <li>
                                <x-nav-link :href="route('projects.my-projects')" :active="request()->routeIs('projects.my-projects')" class="group flex items-center px-3 py-2 rounded-lg text-sm sidebar-link">
                                    Mes Projets
                                </x-nav-link>
                            </li>
                            @endif

                            @if($user->isAdmin() || $user->isChefDepartement() || $user->isChefProjet())
                            <li>
                                <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.index')" class="group flex items-center px-3 py-2 rounded-lg text-sm sidebar-link">
                                    Tous les projets
                                </x-nav-link>
                            </li>

                            <li>
                                <x-nav-link :href="route('projects.cancelled')" :active="request()->routeIs('projects.cancelled')" class="group flex items-center px-3 py-2 rounded-lg text-sm sidebar-link">
                                    Projets annulés
                                </x-nav-link>
                            </li>

                            <li>
                                <x-nav-link :href="route('milestones.index')" :active="request()->routeIs('milestones.*')" class="group flex items-center px-3 py-2 rounded-lg text-sm sidebar-link">
                                    Jalons
                                </x-nav-link>
                            </li>
                            @endif

                        </ul>
                    </div>
                </div>
            </li>
            @endif

            {{-- ========================================
                SECTION: TÂCHES — lien direct (un seul enfant selon rôle)
                ======================================== --}}
            <li class="pt-4 mt-2 border-t border-gray-100"></li>

            @if($user->isMembre())
            <li>
                <x-nav-link :href="route('tasks.my-tasks')" :active="request()->routeIs('tasks.my-tasks')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Mes Tâches
                </x-nav-link>
            </li>
            @endif

            @if($user->isChefProjet() || $user->isChefDepartement() || $user->isAdmin())
            <li>
                <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.index')" class="group flex items-center px-3 py-3 rounded-xl sidebar-link">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Tâches
                </x-nav-link>
            </li>
            @endif

            {{-- ========================================
                GROUPE ACCORDÉON: ADMINISTRATION (Admin seulement)
                ======================================== --}}
            @if($user->isAdmin())
            <li class="pt-4 mt-2 border-t border-gray-100"></li>

            <li x-data="{ open: {{ $adminGroupActive ? 'true' : 'false' }} }">
                <button
                    @click="open = !open"
                    type="button"
                    class="group w-full flex items-center justify-between px-3 py-3 rounded-xl sidebar-link transition-colors"
                    :class="open ? 'text-[#2E3192] bg-indigo-50/60' : 'text-gray-700 hover:bg-gray-50'"
                >
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Administration
                    </span>
                    <svg
                        class="w-4 h-4 flex-shrink-0 transition-transform duration-300 ease-in-out"
                        :class="open ? 'rotate-90' : 'rotate-0'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <div class="grid transition-[grid-template-rows] duration-300 ease-in-out" :style="open ? 'grid-template-rows: 1fr' : 'grid-template-rows: 0fr'">
                    <div class="overflow-hidden">
                        <ul class="ml-5 mt-1 pl-3 space-y-1 border-l-2 border-gray-100">
                            <li>
                                <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" class="group flex items-center px-3 py-2 rounded-lg text-sm sidebar-link">
                                    Utilisateurs
                                </x-nav-link>
                            </li>
                            <li>
                                <x-nav-link :href="route('admin.structures.index')" :active="request()->routeIs('admin.structures.*')" class="group flex items-center px-3 py-2 rounded-lg text-sm sidebar-link">
                                    Structures
                                </x-nav-link>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
            @endif

        </ul>
    </nav>
</div>