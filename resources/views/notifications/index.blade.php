<x-app-layout>
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Notifications</h1>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white p-6 shadow rounded-lg text-center">
            <div class="text-3xl font-bold text-blue-600">{{ $unreadCount ?? 0 }}</div>
            <div class="text-gray-500">Non lues</div>
        </div>
        <div class="bg-white p-6 shadow rounded-lg text-center">
            <div class="text-3xl font-bold text-green-600">{{ $totalCount ?? 0 }}</div>
            <div class="text-gray-500">Total</div>
        </div>
        <div class="bg-white p-6 shadow rounded-lg text-center">
            <button onclick="markAllAsRead()" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                Tout marquer comme lu
            </button>
        </div>
    </div>

    {{-- Notifications List --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold">Liste des notifications</h2>
        </div>
        
        <div id="notifications-list" class="divide-y divide-gray-200">
            {{-- Loaded via JS --}}
        </div>

        @if($notifications->count() > 10)
        <div class="p-6 bg-gray-50 text-center">
            <button onclick="loadMoreNotifications()" class="text-blue-600 hover:underline">
                Charger plus
            </button>
        </div>
        @endif
    </div>
</div>

<script>
let page = 1;
const perPage = 20;

function loadNotifications() {
    fetch(`/notifications?page=${page}&per_page=${perPage}`)
        .then(res => res.json())
        .then(notifications => {
            displayNotifications(notifications.data || notifications);
        });
}

    function displayNotifications(notifications) {
        const container = document.getElementById('notifications-list');
        notifications.forEach(notification => {
            const div = document.createElement('div');
            const readClass = notification.read_at ? '' : 'bg-blue-50 border-blue-200 border-l-4';
            const relatedLink = notification.related_type && notification.related_id ? `/${notification.related_type}s/${notification.related_id}` : '#';
            div.className = `p-6 hover:bg-gray-50 cursor-pointer ${readClass}`;
            div.innerHTML = `
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-semibold text-sm">
                            ${notification.type === 'task_assigned' ? '👤' : notification.type === 'task_due' ? '⏰' : 'ℹ️'}
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">${notification.title}</p>
                        <p class="mt-1 text-sm text-gray-500">${notification.message}</p>
                        ${notification.related_type ? `<a href="${relatedLink}" class="text-blue-500 hover:underline block mt-1">Voir élément</a>` : ''}
                        <p class="mt-2 text-xs text-gray-400">${new Date(notification.created_at).toLocaleString('fr-FR')}</p>
                    </div>
                    ${!notification.read_at ? 
                        `<button onclick="markAsRead(${notification.id})" class="ml-4 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full hover:bg-green-200">
                            Marquer comme lu
                        </button>` : ''
                    }
                </div>
            `;
            div.onclick = () => markAsRead(notification.id);
            container.appendChild(div);
        });
    }

function markAsRead(id) {
    fetch(`/notifications/${id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
        .then(() => {
            location.reload();
        });
}

function markAllAsRead() {
    document.querySelectorAll('[onclick*="markAsRead"]').forEach(btn => btn.click());
}

loadNotifications();
</script>

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush>
</x-app-layout>
