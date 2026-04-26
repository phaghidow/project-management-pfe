import { createApp, h } from 'vue';
import CalendarView from './components/CalendarView.vue';

document.addEventListener('DOMContentLoaded', () => {
    const appEl = document.getElementById('calendar-app');
    if (!appEl) return;

    const compact = appEl.dataset.compact === 'true';

    const app = createApp({
        render() {
            return h(CalendarView, { compact });
        }
    });

    app.mount(appEl);
});

