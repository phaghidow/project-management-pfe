<template>
  <FullCalendar 
    ref="calendarRef"
    :options="calendarOptions"
    :height="compact ? 'auto' : '600px'"
    class="fc-theme-standard"
  />
  <!-- Event Modal -->
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

        <div v-if="selectedEvent?.extendedProps?.status">
          <span class="text-sm font-medium text-gray-500">Statut:</span>
          <span :class="getStatusClass(selectedEvent.extendedProps.status)" class="ml-2 px-3 py-1 text-xs font-medium rounded-full">
            {{ formatStatus(selectedEvent.extendedProps.status) }}
          </span>
        </div>

        <div v-if="selectedEvent?.extendedProps?.project" class="text-sm">
          <span class="font-medium text-gray-900">Projet:</span>
          <span class="ml-2 text-gray-600">{{ selectedEvent.extendedProps.project }}</span>
        </div>

        <div v-if="selectedEvent?.extendedProps?.progress !== undefined" class="text-sm">
          <span class="font-medium text-gray-900">Progression:</span>
          <span class="ml-2 text-gray-600">{{ selectedEvent.extendedProps.progress }}%</span>
        </div>

        <div v-if="selectedEvent?.extendedProps?.taskCount" class="text-sm">
          <span class="font-medium text-gray-900">Tâches:</span>
          <span class="ml-2 text-gray-600">{{ selectedEvent.extendedProps.taskCount }}</span>
        </div>

        <div v-if="selectedEvent?.extendedProps?.due" class="text-sm">
          <span class="font-medium text-gray-900">Échéance:</span>
          <span class="ml-2 text-gray-600">{{ selectedEvent.extendedProps.due }}</span>
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
import { ref, onMounted } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
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

const calendarOptions = ref({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: props.compact ? 'dayGridMonth' : 'timeGridWeek',
  locale: frLocale,
  weekends: true,
  editable: false,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: props.compact ? 3 : true,
  events: '/api/calendar/events',
  eventClick: handleEventClick,
  eventDrop: handleEventDrop,
  eventResize: handleEventResize,
  eventMouseEnter: handleEventMouseEnter,
  eventMouseLeave: handleEventMouseLeave,
  headerToolbar: props.compact 
    ? false 
    : {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
  buttonText: {
    today: 'Aujourd’hui',
    month: 'Mois',
    week: 'Semaine',
    day: 'Jour',
    list: 'Liste'
  }
})

const showModal = ref(false)
const selectedEvent = ref(null)

function handleEventClick(clickInfo) {
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
  if (dbId) {
    return dbId
  }

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

function handleEventMouseEnter(mouseEnterInfo) {
  const tooltip = document.createElement('div')
  tooltip.className = 'fc-tooltip absolute z-50 bg-gray-900 text-white text-xs p-2 rounded shadow-lg whitespace-pre-wrap max-w-xs'
  tooltip.innerHTML = `
    <strong>${mouseEnterInfo.event.title}</strong><br>
    Type: ${mouseEnterInfo.event.extendedProps.type}<br>
    ${mouseEnterInfo.event.extendedProps.project ? `Projet: ${mouseEnterInfo.event.extendedProps.project}` : ''}
    ${mouseEnterInfo.event.extendedProps.status ? `<br>Statut: ${mouseEnterInfo.event.extendedProps.status}` : ''}
  `
  document.body.appendChild(tooltip)
  
  const rect = mouseEnterInfo.jsEvent.target.getBoundingClientRect()
  tooltip.style.left = (rect.left + window.scrollX) + 'px'
  tooltip.style.top = (rect.bottom + window.scrollY + 5) + 'px'
  
  mouseEnterInfo.jsEvent.target._tooltip = tooltip
}

function handleEventMouseLeave(mouseLeaveInfo) {
  if (mouseLeaveInfo.jsEvent.target._tooltip) {
    document.body.removeChild(mouseLeaveInfo.jsEvent.target._tooltip)
    delete mouseLeaveInfo.jsEvent.target._tooltip
  }
}

function getStatusClass(status) {
  const classes = {
    pending: 'bg-orange-100 text-orange-800',
    'in_progress': 'bg-blue-100 text-blue-800',
    validated: 'bg-green-100 text-green-800',
    completed: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800',
    milestone: 'bg-purple-100 text-purple-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function formatStatus(status) {
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

// Add mouseleave to options
onMounted(() => {
  const api = calendarRef.value?.getApi()
  if (api) {
    api.setOption('eventMouseLeave', handleEventMouseLeave)
  }
})

</script>

<style scoped>
:deep(.fc) {
  background: #ffffff;
  border-radius: 1rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
}
:deep(.fc-daygrid-day-frame) {
  border-color: #e5e7eb;
}
:deep(.fc-event) {
  border: 0 !important;
  border-radius: 0.375rem !important;
}
</style>

