import { createApp } from 'vue'
import OrganizationChart from './Pages/Organization/OrganizationChart.vue'

const mountPoint = document.getElementById('org-chart-root')

if (mountPoint) {
  createApp(OrganizationChart).mount(mountPoint)
}
