import { createApp, h } from 'vue'
import OrganigrammeView from './components/OrganigrammeView.vue'

document.addEventListener('DOMContentLoaded', () => {
  const appEl = document.getElementById('organigramme-root')
  if (!appEl) return

  const apiUrl = appEl.dataset.apiUrl || '/api/structures'

  const app = createApp({
    render() {
      return h(OrganigrammeView, { apiUrl })
    }
  })

  app.mount(appEl)
})
