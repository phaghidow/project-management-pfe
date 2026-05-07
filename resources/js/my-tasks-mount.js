import { createApp } from 'vue';
import MyTasks from './components/MyTasks.vue';

window.addEventListener('load', function() {
  console.log('%c[MyTasks] LOAD EVENT FIRED', 'color: blue; font-weight: bold');
  const appEl = document.getElementById('my-tasks-app');
  console.log('%c[MyTasks] appEl found:', 'color: green', !!appEl, appEl);
  
  if (!appEl) {
    console.error('%c[MyTasks] ERROR: Element #my-tasks-app NOT FOUND in DOM', 'color: red; font-weight: bold');
    console.log('%c[MyTasks] DOM content:', 'color: orange', document.body.innerHTML.substring(0, 500));
    return;
  }

  const apiUrl = appEl.dataset?.apiUrl;
  const userRole = appEl.dataset?.userRole;
  console.log('%c[MyTasks] API URL from data attribute:', 'color: cyan', apiUrl);
  console.log('%c[MyTasks] User role from data attribute:', 'color: cyan', userRole);

  if (!apiUrl) {
    console.error('%c[MyTasks] ERROR: apiUrl missing or empty', 'color: red; font-weight: bold');
    console.log('%c[MyTasks] Dataset:', 'color: orange', appEl.dataset);
    return;
  }

  try {
    console.log('%c[MyTasks] About to mount component', 'color: blue');
    const app = createApp(MyTasks, { apiUrl, userRole });
    app.mount(appEl);
    console.log('%c[MyTasks] ✓ COMPONENT MOUNTED SUCCESSFULLY!', 'color: green; font-weight: bold');
  } catch (error) {
    console.error('%c[MyTasks] MOUNT ERROR:', 'color: red; font-weight: bold', error);
  }
});

console.log('%c[MyTasks] Script loaded, waiting for window load event', 'color: blue');
