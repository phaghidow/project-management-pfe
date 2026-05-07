<template>
  <div>
    <!-- Backdrop with blur -->
    <div 
      v-if="isOpen"
      @click="closeDrawer"
      class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity duration-300 z-40"
      style="opacity: 1;"
    ></div>

    <!-- Drawer Panel -->
    <div 
      class="fixed right-0 top-16 bottom-0 w-full md:w-96 bg-white shadow-2xl z-50 transition-transform duration-300 overflow-y-auto"
      :style="{ transform: isOpen ? 'translateX(0)' : 'translateX(100%)' }"
    >
      <!-- Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 p-6 flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
          <h2 class="text-xl font-bold text-gray-900 break-words">{{ task?.name }}</h2>
          <p class="text-sm text-gray-500 mt-2">{{ task?.milestone?.project?.name }}</p>
        </div>
        <button
          @click="closeDrawer"
          class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors"
          title="Fermer"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-6">
        <div v-if="actionError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {{ actionError }}
        </div>

        <!-- Task Info -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">Statut</span>
            <span
              class="inline-flex px-3 py-1 text-xs font-semibold rounded-full"
              :class="statusBadgeClass(task?.status)"
            >
              {{ formatStatus(task?.status) }}
            </span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">Échéance</span>
            <span class="text-sm text-gray-900">{{ formatDate(task?.due_date) }}</span>
          </div>
        </div>

        <!-- Validate Button -->
        <div v-if="task?.status === 'in_progress'">
          <button
            @click="confirmValidateTask"
            :disabled="validating"
            class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-400 text-white px-4 py-2 rounded font-medium transition-colors flex items-center justify-center gap-2"
          >
            <span v-if="validating">⋯ Validation en cours...</span>
            <span v-else>✅ Valider cette tâche</span>
          </button>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200"></div>

        <!-- Comments Section -->
        <div>
          <h3 class="text-sm font-bold text-gray-900 mb-3">Commentaires ({{ comments?.length || 0 }})</h3>
          
          <!-- Comments List -->
          <div class="space-y-3 max-h-48 overflow-y-auto mb-4">
            <div
              v-for="comment in comments"
              :key="comment.id"
              class="flex gap-3 p-3 bg-gray-50 rounded border border-gray-100"
            >
              <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600">
                  {{ getInitials(comment.user?.name) }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-900">{{ comment.user?.name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ formatTimeAgo(comment.created_at) }}</p>
                <p class="text-sm text-gray-700 mt-2 break-words">{{ comment.content }}</p>
              </div>
            </div>

            <div v-if="!comments || comments.length === 0" class="text-center py-4 text-gray-400 text-sm">
              Aucun commentaire
            </div>
          </div>

          <!-- Add Comment Form -->
          <form @submit.prevent="addComment" class="border border-gray-200 rounded-lg p-4 bg-gray-50">
            <input
              v-model="newComment"
              type="text"
              placeholder="Ajouter un commentaire..."
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-medium"
              :disabled="addingComment"
            >
            <p v-if="errors.content" class="text-xs text-red-600 mt-2">{{ errors.content[0] }}</p>
            <button
              type="submit"
              :disabled="!newComment.trim() || addingComment"
              class="mt-2 w-full px-3 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white text-sm font-medium rounded transition-colors flex items-center justify-center gap-2"
            >
              <span v-if="addingComment">⋯ Envoi...</span>
              <span v-else>↤ Envoyer</span>
            </button>
          </form>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200"></div>

        <!-- Attachments Section -->
        <div>
          <h3 class="text-sm font-bold text-gray-900 mb-3">Fichiers ({{ attachments?.length || 0 }})</h3>

          <!-- Attachments List -->
          <div class="space-y-2 max-h-32 overflow-y-auto mb-4">
            <div
              v-for="attachment in attachments"
              :key="attachment.id"
              class="flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-100 text-xs"
            >
              <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900 truncate">{{ attachment.name }}</p>
                <p class="text-gray-500 text-xs">{{ formatFileSize(attachment.size) }}</p>
              </div>
              <a
                :href="`/attachments/${attachment.id}/download`"
                class="ml-2 text-indigo-600 hover:text-indigo-800 font-medium"
                title="Télécharger"
              >
                ⬇
              </a>
            </div>

            <div v-if="!attachments || attachments.length === 0" class="text-center py-4 text-gray-400 text-sm">
              Aucun fichier
            </div>
          </div>

          <!-- Upload File Form -->
          <form @submit.prevent="uploadFile" class="border-t pt-4">
            <input
              ref="fileInput"
              type="file"
              class="hidden"
              @change="handleFileSelect"
              :disabled="uploading"
            >
            <p v-if="errors.file" class="text-xs text-red-600 mt-2">{{ errors.file[0] }}</p>
            <button
              type="button"
              @click="$refs.fileInput?.click()"
              :disabled="uploading"
              class="w-full px-3 py-2 border-2 border-dashed border-gray-300 hover:border-indigo-500 rounded text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
            >
              <span v-if="uploading">⋯ Envoi...</span>
              <span v-else>📎 Joindre un fichier</span>
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <ConfirmModal
      ref="confirmModal"
      title="Valider la tâche"
      message="Êtes-vous sûr de vouloir valider cette tâche ? Cette action est irréversible."
      confirmButtonText="Valider"
      confirmButtonClass="bg-emerald-600 hover:bg-emerald-700"
      @confirm="validateTask"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import ConfirmModal from './ConfirmModal.vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  task: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'updated', 'task-validated'])

const comments = ref([])
const attachments = ref([])
const newComment = ref('')
const addingComment = ref(false)
const validating = ref(false)
const uploading = ref(false)
const selectedFile = ref(null)
const fileInput = ref(null)
const confirmModal = ref(null)
const errors = ref({})
const actionError = ref('')

// Fetch comments and attachments when task changes
watch(
  () => props.task?.id,
  async (taskId) => {
    if (taskId && props.isOpen) {
      await fetchComments(taskId)
      await fetchAttachments(taskId)
    }
  },
  { immediate: true }
)

const fetchComments = async (taskId) => {
  try {
    const response = await axios.get(`/api/tasks/${taskId}/comments`)
    comments.value = response.data.data || []
  } catch (error) {
    console.error('Erreur lors du chargement des commentaires:', error)
    comments.value = []
  }
}

const fetchAttachments = async (taskId) => {
  try {
    const response = await axios.get(`/api/tasks/${taskId}/attachments`)
    attachments.value = response.data.data || []
  } catch (error) {
    console.error('Erreur lors du chargement des fichiers:', error)
    attachments.value = []
  }
}

const addComment = async () => {
  if (!newComment.value.trim() || !props.task?.id) return

  addingComment.value = true
  actionError.value = ''
  try {
    await axios.post('/comments', {
      task_id: props.task.id,
      content: newComment.value,
      _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    })
    newComment.value = ''
    errors.value = {}
    await fetchComments(props.task.id)
  } catch (error) {
    console.error('Erreur lors de l\'ajout du commentaire:', error)
    // Let global middleware dispatch validation-errors event; show generic fallback
    if (!error.response || error.response.status !== 422) {
      window.dispatchEvent(new CustomEvent('flash-message', {
        detail: { error: 'Erreur lors de l\'ajout du commentaire' }
      }))
    }
    actionError.value = error.response?.data?.message || 'Impossible d\'ajouter le commentaire.'
  } finally {
    addingComment.value = false
  }
}

const handleFileSelect = (event) => {
  selectedFile.value = event.target.files?.[0]
  if (selectedFile.value) {
    uploadFile()
  }
}

const uploadFile = async () => {
  if (!selectedFile.value || !props.task?.id) return

  uploading.value = true
  actionError.value = ''
  const formData = new FormData()
  formData.append('file', selectedFile.value)
  formData.append('attachable_type', 'task')
  formData.append('attachable_id', props.task.id)
  formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))

  try {
    await axios.post('/attachments', formData)
    selectedFile.value = null
    errors.value = {}
    await fetchAttachments(props.task.id)
  } catch (error) {
    console.error('Erreur lors de l\'upload:', error)
    if (!error.response || error.response.status !== 422) {
      window.dispatchEvent(new CustomEvent('flash-message', {
        detail: { error: 'Erreur lors de l\'upload du fichier' }
      }))
    }
    actionError.value = error.response?.data?.message || 'Impossible d\'envoyer le fichier.'
  } finally {
    uploading.value = false
  }
}

// Validation errors listener
const onValidationErrors = (e) => {
  // Laravel returns errors object: { field: [messages...] }
  errors.value = e.detail || {}
}

onMounted(() => {
  window.addEventListener('validation-errors', onValidationErrors)
})

onBeforeUnmount(() => {
  window.removeEventListener('validation-errors', onValidationErrors)
})

const confirmValidateTask = async () => {
  await confirmModal.value?.open()
}

const validateTask = async () => {
  if (!props.task?.id) return

  validating.value = true
  actionError.value = ''
  try {
    const response = await axios.post(`/tasks/${props.task.id}/validate`, {
      _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    })
    // Update local task status to 'validated'
    if (props.task) {
      props.task.status = 'validated'
    }
    // Emit event with validated task
    emit('task-validated', { task: props.task, message: 'Tâche validée avec succès !' })
  } catch (error) {
    console.error('Erreur lors de la validation:', error)
    actionError.value = error.response?.data?.message || 'Impossible de valider la tâche.'
    emit('task-validated', { task: props.task, message: 'Erreur lors de la validation', type: 'error' })
  } finally {
    validating.value = false
  }
}

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
  if (!status) return 'Inconnu'
  return String(status).replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
}

const formatTimeAgo = (date) => {
  if (!date) return ''
  const seconds = Math.floor((new Date() - new Date(date)) / 1000)
  if (seconds < 60) return 'à l\'instant'
  const minutes = Math.floor(seconds / 60)
  if (minutes < 60) return `il y a ${minutes}m`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `il y a ${hours}h`
  const days = Math.floor(hours / 24)
  return `il y a ${days}j`
}

const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

const getInitials = (name) => {
  if (!name) return '?'
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const closeDrawer = () => {
  emit('close')
}
</script>
