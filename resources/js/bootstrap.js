import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Laravel Echo setup
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: '{{ config("broadcasting.default") }}',
    logToConsole: true,
    key: process.env.MIX_PUSHER_APP_KEY,
});

