<x-app-layout>
<div class="p-6 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Toutes les Notifications</h1>
        <p class="text-gray-500 mt-2">Gérez vos notifications et préférences de communication</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-blue-600">{{ $unreadCount ?? 0 }}</div>
                    <div class="text-sm text-gray-600 mt-1">Non lues</div>
                </div>
                <div class="text-3xl">📬</div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-green-600">{{ $totalCount ?? 0 }}</div>
                    <div class="text-sm text-gray-600 mt-1">Total</div>
                </div>
                <div class="text-3xl">📊</div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-lg border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-semibold text-gray-900">{{ auth()->user()->role_label }}</div>
                    <div class="text-sm text-gray-600 mt-1">Votre rôle</div>
                </div>
                <div class="text-3xl">👤</div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-lg border border-orange-200">
            <button onclick="markAllAsRead()" 
                    class="w-full bg-orange-600 text-white px-4 py-3 rounded-lg hover:bg-orange-700 transition font-medium text-sm">
                ✓ Tout marquer comme lu
            </button>
        </div>
    </div>

    {{-- Notifications List --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <span class="mr-3">📬</span>
                Liste des notifications
            </h2>
        </div>
        
        <div id="notifications-list" class="divide-y divide-gray-200">
            {{-- Loaded via JS --}}
        </div>

        @if($notifications->count() > 20)
        <div class="p-6 bg-gray-50 text-center border-t">
            <button onclick="loadMoreNotifications()" 
                    class="text-blue-600 hover:text-blue-700 font-medium text-sm hover:underline">
                ↓ Charger plus de notifications
            </button>
        </div>
        @endif
    </div>

    {{-- Empty State --}}
    <div id="empty-state" class="hidden text-center py-12">
        <div class="text-4xl mb-3">🎉</div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Vous êtes à jour !</h3>
        <p class="text-gray-500">Vous avez consultées toutes vos notifications</p>
    </div>
</div>

<script>
let page = 1;
const perPage = 20;

function loadNotifications() {
    fetch(`/notifications?ajax=1&page=${page}&per_page=${perPage}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(res => res.json())
        .then(data => {
            const notifications = data.data || data;
            displayNotifications(notifications);
            
            // Show empty state if no notifications
            if (!notifications || notifications.length === 0) {
                document.getElementById('empty-state').classList.remove('hidden');
            }
        })
        .catch(err => {
            console.error('Error loading notifications:', err);
            document.getElementById('notifications-list').innerHTML = 
                '<div class="p-6 text-red-600 text-center">❌ Erreur lors du chargement des notifications</div>';
        });
}

function displayNotifications(notifications) {
    const container = document.getElementById('notifications-list');
    
    if (!notifications || notifications.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    notifications.forEach(notification => {
        const div = document.createElement('div');
        const readClass = notification.read_at ? '' : 'bg-blue-50 border-blue-200 border-l-4';
        const relatedLink = notification.related_type && notification.related_id && notification.can_access 
            ? `/${notification.related_type}s/${notification.related_id}` 
            : null;
        
        // Determine icon based on notification type
        let icon = 'ℹ️';
        if (notification.type?.includes('task')) icon = '📋';
        else if (notification.type?.includes('milestone')) icon = '📌';
        else if (notification.type?.includes('project')) icon = '👥';
        else if (notification.type?.includes('status')) icon = '🔄';
        
        div.className = `p-6 hover:bg-gray-50 cursor-pointer transition ${readClass}`;
        
        let html = `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-sm">
                        ${icon}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">${notification.title}</p>
                    <p class="mt-1 text-sm text-gray-500">${notification.message}</p>
        `;
        
        // Show link only if user has access
        if (relatedLink) {
            html += `<a href="${relatedLink}" class="text-blue-500 hover:underline text-sm block mt-2">→ Voir l'élément</a>`;
        } else if (notification.related_type && !notification.can_access) {
            // Show access denied message
            html += `
                <div class="bg-red-50 border border-red-200 rounded p-2 mt-2">
                    <p class="text-xs text-red-700 font-semibold">⛔ Accès refusé</p>
                    <p class="text-xs text-red-600">${notification.access_reason || 'Vous n\'avez pas les permissions pour accéder à cette ressource.'}</p>
                </div>
            `;
        }
        
        html += `
                    <p class="mt-3 text-xs text-gray-400">${new Date(notification.created_at).toLocaleString('fr-FR')}</p>
                </div>
                ${!notification.read_at ? 
                    `<button onclick="markAsRead(${notification.id})" class="ml-4 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full hover:bg-green-200 whitespace-nowrap transition">
                        ✓ Marquer comme lu
                    </button>` : 
                    `<span class="ml-4 text-xs text-gray-400">✓ Lu</span>`
                }
            </div>
        `;
        
        div.innerHTML = html;
        div.onclick = () => markAsRead(notification.id);
        container.appendChild(div);
    });
}

function loadMoreNotifications() {
    page++;
    fetch(`/notifications?ajax=1&page=${page}&per_page=${perPage}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(res => res.json())
        .then(data => {
            const notifications = data.data || data;
            displayNotifications(notifications);
        });
}

function markAsRead(id) {
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
        .then(() => {
            loadNotifications();
        });
}

function markAllAsRead() {
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
        .then(() => {
            loadNotifications();
        })
        .catch(() => {
            alert('Impossible de marquer toutes les notifications comme lues.');
        });
}

loadNotifications();
</script>

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
</x-app-layout>
