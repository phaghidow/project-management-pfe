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
    </head>
    <body class="font-sans antialiased">
        <div class="flex h-screen bg-[#F8F9FC]">
            <!-- Fixed Sidebar -->
            <div id="sidebar" class="fixed z-40 w-64 inset-y-0 left-0 bg-white shadow-2xl transform -translate-x-full lg:translate-x-0 lg:fixed transition-transform duration-300 ease-in-out overflow-y-auto border-r border-[#E2E8F0]">
                @include('layouts.navigation')
            </div>

            <!-- Overlay for mobile -->
            <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/50 lg:hidden hidden" onclick="toggleSidebar()"></div>

            <!-- Main Area -->
            <div class="flex-1 flex flex-col overflow-hidden lg:ml-64">
                <!-- Top Header -->
                <header class="bg-[#FFFFFF] border-b border-[#E2E8F0] shadow-sm z-50 sticky top-0">
                    <div class="max-w-7xl mx-auto px-4 py-3 sm:px-6 lg:px-8">
                        <div class="flex justify-end items-center">
                            <!-- User Actions -->
                            <div class="flex items-center space-x-4">
                                <!-- Notifications -->
                                <a href="/notifications" class="relative p-2 hover:bg-gray-100 rounded-xl transition z-50 inline-block">
                                    <span id="notif-count-header" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold min-w-5 h-5 flex items-center justify-center text-[10px]">
                                        0
                                    </span>
                                    <svg class="w-6 h-6 text-[#1A202C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                </a>

                                <!-- Logout (visible directement) -->
                                <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline">
                                    @csrf
                                    <button type="submit" class="flex items-center space-x-2 p-2 hover:bg-red-50 rounded-xl transition text-red-600" title="Déconnexion">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span class="text-sm font-medium">Déconnexion</span>
                                    </button>
                                </form>

                                <!-- User Dropdown -->
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button @click="open = !open" class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-xl transition">
                                        <img class="w-8 h-8 rounded-full bg-primary-500/20" src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&color=fff&background=2E3192" alt="{{ Auth::user()->name }}">
                                        <div class="hidden sm:block text-left">
                                            <div class="font-semibold text-[#1A202C] text-sm">{{ Str::limit(Auth::user()->name, 20) }}</div>
                                            <div class="text-xs text-gray-500">{{ Auth::user()->role_label }}</div>
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
                                         class="absolute right-0 mt-2 w-48 rounded-md shadow-lg origin-top-right z-50"
                                         style="display: none;"
                                         @click="open = false">
                                        <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                            <a href="{{ route('profile') }}" class="block w-full px-4 py-2 text-left text-sm text-[#1A202C] hover:bg-gray-100 transition">
                                                Mon Profil
                                            </a>
                                            <a href="{{ route('profile.edit') }}" class="block w-full px-4 py-2 text-left text-sm text-[#1A202C] hover:bg-gray-100 transition">
                                                Paramètres
                                            </a>
                                            <form method="POST" action="{{ route('logout') }}" class="sm:hidden">
                                                @csrf
                                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 transition">
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
                    <main class="flex-1 overflow-y-auto p-6 pt-6 pb-12 bg-[#F8F9FC]">
                        @include('partials.toasts')
                        {{ $slot ?? '' }}
                        @yield('content')
                    </main>
                </div>
            </div>
        </div>

        @stack('scripts')

        <script>
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
                    .then(res => res.json())
                    .then(count => {
                        const badge = document.getElementById('notif-count');
                        if(badge) badge.innerText = count;
                        const headerBadge = document.getElementById('notif-count-header');
                        if(headerBadge) headerBadge.innerText = count;
                    });

                // LIST
                fetch('/notifications')
                    .then(res => res.json())
                    .then(data => {
                        let html = '';
                        data.forEach(n => {
                            html += `
                                <div class="p-3 border-b hover:bg-gray-100 cursor-pointer"
                                     onclick="markAsRead(${n.id})">
                                    <div class="font-semibold text-sm text-[#1A202C]">${n.title}</div>
                                    <div class="text-xs text-slate-700">${n.message ?? ''}</div>
                                </div>
                            `;
                        });
                        const list = document.getElementById('notif-list');
                        if(list) list.innerHTML = html;
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
                window.Echo.private(`private-user.${{{ Auth::id() }}}`)
                    .listen('NewNotification', (e) => {
                        const dropdown = document.getElementById('notif-dropdown');
                        if (dropdown && dropdown.classList.contains('hidden') === false) {
                            loadNotifications();
                        }
                        const badge = document.getElementById('notif-count');
                        if (badge) badge.textContent = parseInt(badge.textContent) + 1;
                        const headerBadge = document.getElementById('notif-count-header');
                        if(headerBadge) headerBadge.textContent = parseInt(headerBadge.textContent) + 1;
                    })
                    .listen('NotificationRead', (e) => {
                        loadNotifications();
                    });
            }

            // Fallback polling
            setInterval(loadNotifications, 5000);
            // Initial load
            document.addEventListener('DOMContentLoaded', loadNotifications);
        </script>
    </body>
</html>

