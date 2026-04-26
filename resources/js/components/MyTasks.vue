<template>
  <div class="page-mobile">
    <!-- Header with title and filters -->
    <div class="flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Mes Tâches</h1>
      
      <!-- Filters -->
      <div class="flex flex-col sm:flex-row gap-4 sm:items-end">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
          <input
            v-model="filters.q"
            @keyup.enter="fetchTasks"
            type="text"
            placeholder="Nom de tâche ou projet..."
            class="w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          >
        </div>

        <!-- Status filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
          <select v-model="filters.status" @change="fetchTasks" class="w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="all">Toutes</option>
            <option value="pending">En attente</option>
            <option value="in_progress">En cours</option>
            <option value="validated">Validées</option>
          </select>
        </div>
        
        <!-- Project filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Projet</label>
          <select v-model="filters.project_id" @change="fetchTasks" class="w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Tous les projets</option>
            <option v-for="project in uniqueProjects" :key="project.id" :value="project.id">
              {{ project.name }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Sort links -->
    <div class="mb-6 flex flex-wrap gap-2 text-sm bg-white p-4 rounded-lg shadow">
      <a href="#" @click.prevent="updateSort('name')" 
         :class="['px-3 py-1 rounded hover:bg-indigo-100', { 'bg-indigo-100 text-indigo-800 font-semibold': sort.column === 'name' }]">
        Nom {{ sort.column === 'name' ? (sort.direction === 'asc' ? '↑' : '↓') : '' }}
      </a>
      <a href="#" @click.prevent="updateSort('due_date')" 
         :class="['px-3 py-1 rounded hover:bg-indigo-100 font-semibold', { 'bg-indigo-100 text-indigo-800': sort.column === 'due_date' }]">
        Échéance {{ sort.column === 'due_date' ? (sort.direction === 'asc' ? '↑' : '↓') : '' }}
      </a>
    </div>

    <!-- Tasks Table -->
    <div v-if="loading" class="text-center py-12 text-gray-500">Chargement...</div>
    
    <div v-else-if="tasks.data.length > 0" class="bg-white shadow overflow-hidden sm:rounded-md">
      <div class="table-responsive">
        <table class="min-w-full divide-y divide-gray-200 responsive-table">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projet</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Échéance</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignés</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="task in tasks.data" :key="task.id" :class="{ 'opacity-60': task.status === 'validated' }">
              <td class="px-6 py-4 whitespace-nowrap">
                <a :href="`/tasks/${task.id}`" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                  {{ task.name }}
                </a>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ task.milestone?.project?.name || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="statusBadgeClass(task.status)">
                  {{ formatStatus(task.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(task.due_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span v-for="(user, index) in task.users.slice(0,2)" :key="user.id">
                  {{ user.name }}{{ index < 1 && task.users.length > 1 ? ', ' : '' }}
                </span>
                <span v-if="task.users.length > 2"> +{{ task.users.length - 2 }} autres</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <form v-if="task.status === 'in_progress'" @submit.prevent="validateTask(task)" class="inline">
                  <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-1 rounded text-xs font-medium transition-colors">
                    ✅ Valider
                  </button>
                </form>
                <span v-else-if="task.status === 'pending'" class="text-yellow-600 text-xs">En attente</span>
                <span v-else class="text-green-600 text-xs font-medium">Validée</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6" v-if="tasks.last_page > 1">
        <div class="flex-1 flex justify-between sm:hidden">
          <button @click="changePage(tasks.current_page - 1)" :disabled="tasks.current_page === 1" 
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            Précédent
          </button>
          <button @click="changePage(tasks.current_page + 1)" :disabled="tasks.current_page === tasks.last_page" 
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            Suivant
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Affichage de <span class="font-medium">{{ (tasks.current_page - 1) * tasks.per_page + 1 }}</span> à 
              <span class="font-medium">{{ Math.min(tasks.current_page * tasks.per_page, tasks.total) }}</span> sur 
              <span class="font-medium">{{ tasks.total }}</span> résultats
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <button @click="changePage(1)" :disabled="tasks.current_page === 1"
                      class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                1
              </button>
              <span v-if="tasks.current_page > 2" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
              <button @click="changePage(tasks.current_page - 1)" :disabled="tasks.current_page === 1"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                <span v-if="tasks.current_page > 1">Précédent</span>
              </button>
              <button @click="changePage(tasks.current_page + 1)" :disabled="tasks.current_page === tasks.last_page"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                <span v-if="tasks.current_page < tasks.last_page">Suivant</span>
              </button>
              <span v-if="tasks.last_page > 3 && tasks.current_page < tasks.last_page - 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
              <button @click="changePage(tasks.last_page)" :disabled="tasks.current_page === tasks.last_page"
                      class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                {{ tasks.last_page }}
              </button>
            </nav>
          </div>
        </div>
      </nav>
    </div>

    <!-- Empty state -->
    <div v-else class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune tâche assignée</h3>
      <p class="mt-1 text-sm text-gray-500">Vous n'avez pas de tâches assignées pour le moment.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  apiUrl: {
    type: String,
    required: true
  }
})

const tasks = ref({ data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 })
const loading = ref(false)
const filters = ref({
  q: '',
  status: 'all',
  project_id: ''
})
const sort = ref({
  column: 'due_date',
  direction: 'desc'
})

const uniqueProjects = computed(() => {
  const projects = new Map()
  tasks.value.data.forEach(task => {
    if (task.milestone?.project) {
      projects.set(task.milestone.project.id, task.milestone.project)
    }
  })
  return Array.from(projects.values())
})

const statusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    validated: 'bg-green-100 text-green-800',
    default: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || classes.default
}

const formatStatus = (status) => {
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
}

const fetchTasks = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      ...filters.value,
      sort: sort.value.column,
      dir: sort.value.direction,
      page
    }
    const response = await axios.get(props.apiUrl, { params })
    tasks.value = response.data
  } catch (error) {
    console.error('Error fetching tasks:', error)
    alert('Erreur lors du chargement des tâches')
  } finally {
    loading.value = false
  }
}

const updateSort = (column) => {
  if (sort.value.column === column) {
    sort.value.direction = sort.value.direction === 'asc' ? 'desc' : 'asc'
  } else {
    sort.value.column = column
    sort.value.direction = 'asc'
  }
  fetchTasks()
}

const changePage = (page) => {
  if (page >= 1 && page <= tasks.value.last_page) {
    fetchTasks(page)
  }
}

const validateTask = async (task) => {
  if (!confirm('Valider cette tâche ?')) return
  
  try {
    await axios.post(`/tasks/${task.id}/validate`, {
      _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    })
    // Refresh tasks
    fetchTasks(tasks.value.current_page)
    alert('Tâche validée avec succès !')
  } catch (error) {
    console.error('Validation error:', error)
    alert('Erreur lors de la validation')
  }
}

onMounted(() => {
  fetchTasks()
})


watch([filters, sort], () => {
  fetchTasks(1)
}, { deep: true })
</script>
