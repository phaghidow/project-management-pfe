import { createApp } from 'vue';
import MyTasks from './components/MyTasks.vue';

document.addEventListener('DOMContentLoaded', function() {
  console.log('%c[MyTasks] DOMContentLoaded fired', 'color: blue; font-weight: bold');
  const appEl = document.getElementById('my-tasks-app');
  if (!appEl) {
    console.error('%c[MyTasks] ERROR: Element #my-tasks-app not found', 'color: red; font-weight: bold');
    return;
  }
  console.log('%c[MyTasks] Found app element', 'color: green');

  const apiUrl = appEl.dataset.apiUrl;
  console.log('%c[MyTasks] apiUrl:', 'color: green', apiUrl);

  if (!apiUrl) {
    console.error('%c[MyTasks] ERROR: apiUrl not found in data attribute', 'color: red; font-weight: bold');
    return;
  }

  const app = createApp({
    template: '<MyTasks :api-url="apiUrl" />',
    components: { MyTasks },
    data() {
      return { apiUrl };
    }
  });

  app.mount(appEl);
  console.log('%c[MyTasks] Component mounted successfully!', 'color: green; font-weight: bold');
});
