<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div class="flex min-h-screen bg-gray-50">
            <!-- Fixed Sidebar -->
            <div id="sidebar" class="fixed z-50 w-64 inset-y-0 left-0 bg-white shadow-2xl transform -translate-x-full lg:translate-x-0 lg:fixed transition-transform duration-300 ease-in-out overflow-y-auto border-r border-[#E2E8F0]">
                @include('layouts.navigation')
            </div>

            <!-- Overlay for mobile -->
            <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black/50 lg:hidden hidden" onclick="toggleSidebar()"></div>

            <!-- Main Area -->
            <div class="flex-1 flex flex-col lg:ml-64 min-w-0">
                <!-- Top Header -->
                <header class="bg-[#FFFFFF] border-b border-[#E2E8F0] shadow-sm z-[70] sticky top-0">
                    <div class="w-full px-4 py-3 sm:px-6 lg:px-8 xl:px-10">
                        <div class="flex justify-end items-center">
                            <!-- User Actions -->
                            <div class="flex items-center space-x-4">
                                <!-- Notifications -->
                                <div class="relative z-50" x-data="{ openNotif: false }" @click.outside="openNotif = false">
                                    <button @click="openNotif = !openNotif" class="inline-flex items-center justify-center relative p-2 rounded-xl transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                        <span id="notif-count-header" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold min-w-5 h-5 flex items-center justify-center text-[10px]">
                                            0
                                        </span>
                                        <svg class="w-6 h-6 text-[#1A202C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                    </button>

                                    <div x-show="openNotif" x-transition class="origin-top-right absolute right-0 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5" id="notif-dropdown" style="display: none;">
                                        <div class="p-3 border-b flex items-center justify-between">
                                            <div class="text-sm font-semibold">Notifications</div>
                                            <button onclick="markAllFromHeader()" class="inline-flex items-center justify-center text-xs text-blue-600 hover:underline focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">Tout marquer comme lu</button>
                                        </div>
                                        <div id="notif-list" class="max-h-64 overflow-y-auto">
                                            <div class="p-3 text-xs text-slate-500">Chargement...</div>
                                        </div>
                                        <div class="p-2 text-center border-t">
                                            <a href="/notifications" class="text-sm text-gray-600 hover:underline">Voir toutes les notifications</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Logout (visible directement) -->
                                <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center gap-2 p-2 rounded-xl transition text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" title="Déconnexion">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span class="text-sm font-medium">Déconnexion</span>
                                    </button>
                                </form>

                                <!-- User Dropdown -->
                                <div class="relative z-[90]" x-data="{ open: false }" @click.outside="open = false">
                                    <button @click="open = !open" class="inline-flex items-center justify-center gap-3 p-2 rounded-xl transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                        <img class="w-8 h-8 rounded-full bg-primary-500/20" src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&color=fff&background=2E3192" alt="{{ Auth::user()->name }}">
                                        <div class="hidden sm:block text-left">
                                            <div class="font-semibold text-[#1A202C] text-sm">{{ Str::limit(Auth::user()->name, 20) }}</div>
                                            <div class="text-xs text-gray-500">{{ Auth::user()->function ?: Auth::user()->role_label }}</div>
                                        </div>
                                        <svg class="w-4 h-4 text-[#1A202C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute right-0 mt-2 w-48 rounded-md shadow-lg origin-top-right z-[95]"
                                         style="display: none;"
                                         @click="open = false">
                                        <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                            <a href="{{ route('profile') }}" class="block w-full px-4 py-2 text-left text-sm text-[#1A202C] hover:bg-gray-100 focus:bg-gray-100 transition">
                                                Mon Profil
                                            </a>
                                            <a href="{{ route('profile.edit') }}" class="block w-full px-4 py-2 text-left text-sm text-[#1A202C] hover:bg-gray-100 focus:bg-gray-100 transition">
                                                Paramètres
                                            </a>
                                            <form method="POST" action="{{ route('logout') }}" class="sm:hidden">
                                                @csrf
                                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 focus:bg-red-50 transition">
                                                    Déconnexion
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Main Content -->
                <div id="app" class="contents">
                    <main id="app-main-content" class="flex-1 overflow-y-auto overflow-x-hidden bg-gray-50">
                        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 pt-6 pb-12">
                        {{ $slot ?? '' }}
                        @yield('content')
                        </div>
                    </main>
                </div>
            </div>
        </div>

@stack('scripts')

        <script>
            window.__SERVER_FLASH_MESSAGES__ = {!! json_encode([
                'success' => session('success'),
                'error' => session('error'),
                'warning' => session('warning'),
                'info' => session('info'),
            ]) !!};
        </script>

        <script>
            function updateNotificationBadge(count) {
                const normalizedCount = Number.isFinite(Number(count)) ? Number(count) : 0;
                const headerBadge = document.getElementById('notif-count-header');

                if (headerBadge) {
                    headerBadge.innerText = normalizedCount;
                    // Always display badge so users can see 0, 1, 2...
                    headerBadge.style.display = 'flex';
                    headerBadge.classList.toggle('bg-red-500', normalizedCount > 0);
                    headerBadge.classList.toggle('bg-gray-400', normalizedCount === 0);
                }
            }

            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                if (sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    overlay.classList.remove('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('translate-x-0');
                    overlay.classList.add('hidden');
                }
            }

            function loadNotifications() {
                // COUNT
                fetch('/notifications/count')
                    .then(async (res) => {
                        if (!res.ok) {
                            throw new Error('Count request failed');
                        }

                        const parsed = await res.json();
                        return Number(parsed?.count ?? 0);
                    })
                    .then(count => {
                        updateNotificationBadge(count);
                    })
                    .catch(() => {
                        updateNotificationBadge(0);
                    });

                // LIST
                fetch('/notifications?ajax=1', {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        const items = Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : []);
                        let html = '';
                        items.forEach(n => {
                            let icon = 'ℹ️';
                            if (n.type?.includes('task')) icon = '📋';
                            else if (n.type?.includes('milestone')) icon = '📌';
                            else if (n.type?.includes('project')) icon = '👥';
                            else if (n.type?.includes('status')) icon = '🔄';
                            
                            // Show access warning if user cannot access related resource
                            let accessInfo = '';
                            if (n.related_type && !n.can_access) {
                                accessInfo = `<div class="text-xs text-red-600 mt-1">⛔ ${n.access_reason || 'Accès refusé'}</div>`;
                            }
                            
                            html += `
                                <div class="p-3 border-b hover:bg-gray-100 cursor-pointer transition"
                                     onclick="markAsRead(${n.id})">
                                    <div class="flex items-start">
                                        <span class="mr-2">${icon}</span>
                                        <div class="flex-1">
                                            <div class="font-semibold text-sm text-[#1A202C]">${n.title}</div>
                                            <div class="text-xs text-slate-700 mt-1">${n.message ?? ''}</div>
                                            ${accessInfo}
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        if (items.length === 0) {
                            html = '<div class="p-3 text-xs text-slate-500">Aucune notification non lue</div>';
                        }
                        const list = document.getElementById('notif-list');
                        if(list) list.innerHTML = html;
                    })
                    .catch(() => {
                        const list = document.getElementById('notif-list');
                        if (list) {
                            list.innerHTML = '<div class="p-3 text-xs text-red-500">Impossible de charger les notifications</div>';
                        }
                    });
            }

            function markAsRead(id) {
                fetch('/notifications/' + id + '/read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(() => loadNotifications());
            }

            // Real-time listeners
            if (window.Echo) {
                window.Echo.private('private-user.{{ Auth::id() }}')
                    .listen('NewNotification', (e) => {
                        // Re-sync from server to avoid drift between local counter and DB state.
                        loadNotifications();
                    })
                    .listen('NotificationRead', (e) => {
                        loadNotifications();
                    });
            }

            // Fallback polling
            setInterval(loadNotifications, 5000);
            // Initial load
            document.addEventListener('DOMContentLoaded', loadNotifications);

            function markAllFromHeader() {
                fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(() => {
                    loadNotifications();
                }).catch(() => {
                    // noop
                });
            }
        </script>
           <!-- Flash Toast Container -->
           <div id="flash-toast-app"></div>

    </body>
</html>

