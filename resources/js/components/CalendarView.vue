<template>
  <FullCalendar 
    ref="calendarRef"
    :options="calendarOptions"
    :height="compact ? 'auto' : '700px'"
    class="fc-theme-standard"
  />
  
  <!-- Event Modal (inchangé) -->
  <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-900">
            {{ selectedEvent?.title }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <div class="p-6 space-y-4">
        <div>
          <span class="text-sm font-medium text-gray-500">Type:</span>
          <span class="ml-2 px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800 capitalize">
            {{ selectedEvent?.extendedProps?.type }}
          </span>
        </div>

        <div v-if="selectedEvent?.extendedProps?.project">
          <span class="text-sm font-medium text-gray-500">Projet:</span>
          <span class="ml-2 text-gray-600">{{ selectedEvent.extendedProps.project }}</span>
        </div>

        <div v-if="selectedEvent?.extendedProps?.status">
          <span class="text-sm font-medium text-gray-500">Statut:</span>
          <span :class="getStatusClass(selectedEvent.extendedProps.status)" class="ml-2 px-3 py-1 text-xs font-medium rounded-full">
            {{ formatStatus(selectedEvent.extendedProps.status) }}
          </span>
        </div>

        <div v-if="selectedEvent?.extendedProps?.progress !== undefined">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-500">Progression:</span>
            <span class="text-sm text-gray-600">{{ selectedEvent.extendedProps.progress }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
            <div class="bg-indigo-600 h-2 rounded-full" :style="{ width: selectedEvent.extendedProps.progress + '%' }"></div>
          </div>
        </div>

        <div v-if="selectedEvent?.extendedProps?.assignedTo">
          <span class="text-sm font-medium text-gray-500">Assigné à:</span>
          <span class="ml-2 text-gray-600">{{ selectedEvent.extendedProps.assignedTo }}</span>
        </div>

        <div v-if="selectedEvent?.extendedProps?.due">
          <span class="text-sm font-medium text-gray-500">Échéance:</span>
          <span class="ml-2 text-gray-600">{{ selectedEvent.extendedProps.due }}</span>
        </div>

        <div v-if="selectedEvent?.extendedProps?.description">
          <hr class="my-2">
          <p class="text-sm text-gray-600">{{ selectedEvent.extendedProps.description }}</p>
        </div>
      </div>

      <div class="p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
        <div class="flex gap-3 justify-end">
          <button @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">
            Fermer
          </button>
          <button @click="viewDetails" :disabled="!selectedEvent?.url" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-xl hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg">
            Voir détails
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import listPlugin from '@fullcalendar/list'  // ✅ Remplacer timeGrid par list
import interactionPlugin from '@fullcalendar/interaction'
import frLocale from '@fullcalendar/core/locales/fr'

const props = defineProps({
  compact: {
    type: Boolean,
    default: false
  },
  height: {
    type: [String, Number],
    default: '600px'
  }
})

const calendarRef = ref()
const currentTooltip = ref(null)

// ✅ Nouvelle configuration avec vues Mois + Liste Hebdo + Liste Jour
const calendarOptions = ref({
  plugins: [dayGridPlugin, listPlugin, interactionPlugin],  // timeGridPlugin retiré
  initialView: props.compact ? 'dayGridMonth' : 'dayGridMonth',
  locale: frLocale,
  weekends: true,
  editable: false,
  selectable: true,
  selectMirror: true,
  
  // Pour la vue mois
  dayMaxEvents: true,  // Affiche "+ X en plus"
  
  // Pour les vues liste - personnalisation
  listDayFormat: { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' },
  listDaySideFormat: false,
  listWeekFormat: { week: 'numeric', year: 'numeric' },
  
  events: '/api/calendar/events',
  eventClick: handleEventClick,
  eventMouseEnter: handleEventMouseEnter,
  eventMouseLeave: handleEventMouseLeave,
  
  headerToolbar: props.compact 
    ? false 
    : {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,listWeek,listDay'  // ✅ Mois, Liste Semaine, Liste Jour
      },
  buttonText: {
    today: 'Aujourd\'hui',
    month: '📅 Mois',
    listWeek: '📋 Liste Semaine',
    listDay: '📄 Liste Jour'
  }
})

const showModal = ref(false)
const selectedEvent = ref(null)

function handleEventClick(clickInfo) {
  hideTooltip()
  selectedEvent.value = clickInfo.event
  showModal.value = true
  clickInfo.jsEvent.preventDefault()
}

function closeModal() {
  showModal.value = false
  selectedEvent.value = null
}

function viewDetails() {
  if (selectedEvent.value?.url) {
    window.location.href = selectedEvent.value.url
  }
}

// ✅ Synchronisation manuelle des événements (si nécessaire)
async function handleEventDrop(dropInfo) {
  await syncManualEvent(dropInfo.event, dropInfo.revert)
}

async function handleEventResize(resizeInfo) {
  await syncManualEvent(resizeInfo.event, resizeInfo.revert)
}

function isManualEvent(event) {
  return event.extendedProps?.type === 'calendar_event'
}

function extractManualEventId(event) {
  const dbId = event.extendedProps?.dbId
  if (dbId) return dbId
  const match = String(event.id || '').match(/^calendar-event-(\d+)$/)
  return match ? match[1] : null
}

async function syncManualEvent(event, revert) {
  if (!isManualEvent(event)) {
    revert()
    return
  }

  const calendarEventId = extractManualEventId(event)
  if (!calendarEventId) {
    revert()
    return
  }

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

  const payload = new FormData()
  payload.append('title', event.title.replace(/^🗓\s*/, ''))
  payload.append('description', event.extendedProps?.description ?? '')
  payload.append('start_date', event.start ? event.start.toISOString().slice(0, 16) : '')
  payload.append('end_date', event.end ? event.end.toISOString().slice(0, 16) : '')
  payload.append('all_day', event.allDay ? '1' : '0')
  payload.append('color', event.backgroundColor || '#2563eb')
  payload.append('_method', 'PUT')

  try {
    const response = await fetch(`/calendar/events/${calendarEventId}`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: payload
    })

    if (!response.ok) {
      revert()
      return
    }
  } catch (error) {
    console.error('Unable to sync calendar event', error)
    revert()
  }
}

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;')
}

function hideTooltip() {
  if (currentTooltip.value && currentTooltip.value.parentNode) {
    currentTooltip.value.parentNode.removeChild(currentTooltip.value)
  }
  const existingTooltip = document.querySelector('.fc-tooltip')
  if (existingTooltip && existingTooltip.parentNode) {
    existingTooltip.parentNode.removeChild(existingTooltip)
  }
  currentTooltip.value = null
}

function handleWindowClick(event) {
  const tooltipElement = currentTooltip.value
  if (!tooltipElement) return
  if (tooltipElement.contains(event.target)) return
  hideTooltip()
}

function handleEventMouseEnter(mouseEnterInfo) {
  try {
    hideTooltip()

    const tooltip = document.createElement('div')
    tooltip.className = 'fc-tooltip pointer-events-none absolute z-[9999] bg-gray-900 text-white text-xs p-2 rounded shadow-lg whitespace-pre-wrap max-w-xs'
    tooltip.style.visibility = 'hidden'

    const title = escapeHtml(mouseEnterInfo?.event?.title || 'Événement')
    const type = escapeHtml(mouseEnterInfo?.event?.extendedProps?.type || 'N/A')
    const project = mouseEnterInfo?.event?.extendedProps?.project
      ? `<br>📁 Projet: ${escapeHtml(mouseEnterInfo.event.extendedProps.project)}`
      : ''
    const status = mouseEnterInfo?.event?.extendedProps?.status
      ? `<br>📊 Statut: ${escapeHtml(mouseEnterInfo.event.extendedProps.status)}`
      : ''
    const progress = mouseEnterInfo?.event?.extendedProps?.progress
      ? `<br>📈 Progression: ${escapeHtml(mouseEnterInfo.event.extendedProps.progress)}%`
      : ''

    tooltip.innerHTML = `
      <strong>${title}</strong><br>
      🏷️ Type: ${type}${project}${status}${progress}
    `

    document.body.appendChild(tooltip)
    currentTooltip.value = tooltip

    const pageX = Number(mouseEnterInfo?.jsEvent?.pageX ?? 0)
    const pageY = Number(mouseEnterInfo?.jsEvent?.pageY ?? 0)
    const offset = 10

    requestAnimationFrame(() => {
      if (!currentTooltip.value || !document.body.contains(tooltip)) return

      const tooltipWidth = tooltip.offsetWidth || 220
      const tooltipHeight = tooltip.offsetHeight || 80
      const viewportRight = window.scrollX + window.innerWidth
      const viewportBottom = window.scrollY + window.innerHeight

      let left = pageX + offset
      let top = pageY + offset

      if (left + tooltipWidth > viewportRight) {
        left = pageX - tooltipWidth - offset
      }
      if (top + tooltipHeight > viewportBottom) {
        top = pageY - tooltipHeight - offset
      }

      tooltip.style.left = `${Math.max(window.scrollX + 8, left)}px`
      tooltip.style.top = `${Math.max(window.scrollY + 8, top)}px`
      tooltip.style.visibility = 'visible'
    })
  } catch (error) {
    console.warn('Unable to create calendar tooltip', error)
    hideTooltip()
  }
}

function handleEventMouseLeave() {
  hideTooltip()
}

function getStatusClass(status) {
  const classes = {
    pending: 'bg-orange-100 text-orange-800',
    in_progress: 'bg-blue-100 text-blue-800',
    'in_progress': 'bg-blue-100 text-blue-800',
    validated: 'bg-green-100 text-green-800',
    completed: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800',
    milestone: 'bg-purple-100 text-purple-800',
    planifie: 'bg-yellow-100 text-yellow-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function formatStatus(status) {
  const labels = {
    pending: 'En attente',
    in_progress: 'En cours',
    validated: 'Validée',
    completed: 'Terminé',
    planifie: 'Planifié',
    milestone: 'Jalon'
  }
  return labels[status] || status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

onMounted(() => {
  window.addEventListener('click', handleWindowClick)
})

onUnmounted(() => {
  window.removeEventListener('click', handleWindowClick)
  hideTooltip()
})
</script>

<style scoped>
:deep(.fc) {
  background: #ffffff;
  border-radius: 1rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  width: 100%;
  overflow-x: auto;
}

:deep(.fc-daygrid-day-frame) {
  border-color: #e5e7eb;
}

:deep(.fc-event) {
  border: 0 !important;
  border-radius: 0.375rem !important;
  cursor: pointer;
  transition: all 0.2s ease;
}

:deep(.fc-event:hover) {
  filter: brightness(0.95);
  transform: scale(1.01);
}

/* ============================================
   STYLES POUR LES BOUTONS DE LA BARRE D'OUTILS
   ============================================ */

:deep(.fc-toolbar) {
  flex-wrap: wrap;
  gap: 10px;
}

:deep(.fc-toolbar-title) {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
}

:deep(.fc-button) {
  background-color: #ffffff !important;
  border: 1px solid #d1d5db !important;
  color: #374151 !important;
  font-weight: 500 !important;
  text-transform: capitalize !important;
  transition: all 0.2s ease !important;
  border-radius: 0.5rem !important;
  padding: 0.5rem 1rem !important;
  cursor: pointer !important;
  box-shadow: none !important;
}

:deep(.fc-button:hover) {
  background-color: #f3f4f6 !important;
  border-color: #9ca3af !important;
  color: #111827 !important;
  transform: translateY(-1px);
}

/* Bouton actif */
:deep(.fc-button-primary.fc-button-active) {
  background-color: #4f46e5 !important;
  border-color: #4f46e5 !important;
  color: #ffffff !important;
}

:deep(.fc-button-primary.fc-button-active:hover) {
  background-color: #4338ca !important;
  color: #ffffff !important;
}

/* Bouton "Aujourd'hui" */
:deep(.fc-today-button) {
  background-color: #f8fafc !important;
  border-color: #cbd5e1 !important;
  color: #1e293b !important;
}

:deep(.fc-today-button:hover) {
  background-color: #e2e8f0 !important;
  color: #0f172a !important;
}

/* ============================================
   STYLES POUR LA VUE LISTE - VERSION FINALE
   ============================================ */

/* Conteneur principal - prend toute la largeur */
:deep(.fc-list) {
  background: transparent !important;
  width: 100% !important;
}

:deep(.fc-list-table) {
  width: 100% !important;
  display: block !important;
}

/* En-tête des jours */
:deep(.fc-list-day-cushion) {
  background-color: #f3f4f6 !important;
  color: #1f2937 !important;
  font-weight: 600 !important;
  font-size: 0.9rem !important;
  padding: 0.5rem 1rem !important;
  border-radius: 0.5rem !important;
  margin: 0.5rem 0 !important;
}

/* Supprimer les bordures inutiles */
:deep(.fc-list-table td),
:deep(.fc-list-table th) {
  border: none !important;
  padding: 0 !important;
}

/* Cacher le point coloré */
:deep(.fc-list-event-graphic) {
  display: none !important;
}

/* ============================================ */
/* CARTE INDIVIDUELLE - PLEINE LARGEUR */
/* ============================================ */

:deep(.fc-list-event) {
  display: flex !important;
  align-items: center !important;
  gap: 16px !important;
  background: white !important;
  border-radius: 12px !important;
  margin-bottom: 8px !important;
  padding: 12px 20px !important;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
  border: 1px solid #e9e9ef !important;
  transition: all 0.2s ease;
  cursor: pointer;
  width: 100% !important;
  box-sizing: border-box !important;
}

:deep(.fc-list-event:hover) {
  background-color: #fafaff !important;
  border-color: #c7d2fe !important;
  transform: translateX(2px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
}

/* Bloc Heure */
:deep(.fc-list-event-time) {
  font-size: 0.8rem !important;
  font-weight: 500 !important;
  background: #eef2ff !important;
  color: #1e40af !important;
  padding: 4px 12px !important;
  border-radius: 20px !important;
  white-space: nowrap !important;
  flex-shrink: 0 !important;
  width: auto !important;
  min-width: 90px;
  text-align: center;
}

/* Bloc Titre - prend tout l'espace disponible */
:deep(.fc-list-event-title) {
  font-size: 0.95rem !important;
  font-weight: 500 !important;
  color: #1f2937 !important;
  flex: 1 !important;
  text-align: left !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  padding: 0 !important;
}

/* Responsive : sur petits écrans, le titre peut passer à la ligne */
@media (max-width: 640px) {
  :deep(.fc-list-event) {
    flex-wrap: wrap !important;
    gap: 8px !important;
    padding: 10px 12px !important;
  }
  
  :deep(.fc-list-event-time) {
    font-size: 0.7rem !important;
    padding: 2px 8px !important;
    min-width: auto;
  }
  
  :deep(.fc-list-event-title) {
    white-space: normal !important;
    width: 100% !important;
    font-size: 0.85rem !important;
  }
}

/* ============================================
   STYLES POUR LA VUE MOIS
   ============================================ */

:deep(.fc-daygrid-day) {
  min-height: 100px;
}

:deep(.fc-daygrid-day-number) {
  font-size: 0.8rem;
  font-weight: 500;
  padding: 4px 6px;
}

:deep(.fc-daygrid-event) {
  font-size: 0.7rem;
  padding: 2px 4px;
  margin: 1px 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  border-radius: 4px;
}

:deep(.fc-daygrid-event:hover) {
  white-space: normal;
  z-index: 2;
}

/* ============================================
   AUTRES STYLES
   ============================================ */

:deep(.fc-header-toolbar) {
  margin-bottom: 1.5em;
  padding: 0 0.5rem;
}

:deep(.fc-view-harness) {
  width: 100% !important;
  background: transparent;
}

:deep(.fc-scroller) {
  overflow-x: visible !important;
}
</style>