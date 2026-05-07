import { createApp } from 'vue'
import Toast from './components/Toast.vue'

// Mount FlashToast globally
window.addEventListener('load', function() {
  const container = document.getElementById('flash-toast-app')
  if (container) {
    const app = createApp(Toast)
    app.mount(container)
  }
})
