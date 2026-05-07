import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import flash messages middleware
import './middleware/flashMessages';

// Laravel Echo setup
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const rawPusherAppKey = (import.meta.env.VITE_PUSHER_APP_KEY || '').trim();
const isPlaceholderKey = /^\$\{[^}]+\}$/.test(rawPusherAppKey);
const pusherAppKey = !isPlaceholderKey && rawPusherAppKey !== '' ? rawPusherAppKey : null;

if (pusherAppKey) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        logToConsole: false,
        key: pusherAppKey,
        wsHost: import.meta.env.VITE_PUSHER_HOST || window.location.hostname,
        wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
        wssPort: import.meta.env.VITE_PUSHER_PORT || 6001,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME || 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
} else {
    window.Echo = null;
}

