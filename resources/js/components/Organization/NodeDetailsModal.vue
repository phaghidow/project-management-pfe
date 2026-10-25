<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center" @click="handleOverlayClick">
        <!-- Overlay semi-transparent -->
        <div
          class="absolute inset-0 bg-black/50 backdrop-blur-sm"
        ></div>

        <!-- Modal centré -->
        <div class="relative z-50 w-full max-w-md rounded-2xl bg-white shadow-2xl" @click.stop>
          <!-- Bouton croix -->
          <button
            type="button"
            class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
            @click="handleClose"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Contenu -->
          <div class="p-6">
            <!-- Titre du poste -->
            <div class="mb-4">
              <h2 class="text-2xl font-bold text-slate-900">{{ node?.name || 'Poste sans nom' }}</h2>
              <p class="mt-1 text-sm font-medium text-slate-500">{{ node?.type?.toUpperCase() }}</p>
            </div>

            <div v-if="loading" class="mb-6 rounded-lg bg-slate-50 p-3 text-sm text-slate-500">
              Chargement...
            </div>

            <div v-else-if="error" class="mb-6 rounded-lg bg-red-50 p-3 text-sm text-red-700" role="alert">
              {{ error }}
            </div>

            <!-- Titre détaillé -->
            <div v-else class="mb-6 rounded-lg bg-slate-50 p-3">
              <p class="text-sm text-slate-700">{{ node?.description || node?.title || 'Aucun titre détaillé' }}</p>
            </div>

            <!-- Utilisateurs assignés -->
            <div v-if="!loading && !error">
              <h3 class="mb-3 text-sm font-semibold text-slate-700">Utilisateurs assignés</h3>
              <div v-if="users.length > 0" class="space-y-2">
                <div
                  v-for="user in users"
                  :key="user.id"
                  class="flex items-center rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-700"
                >
                  <div class="mr-2 h-2 w-2 rounded-full bg-slate-400"></div>
                  <div>
                    <div>{{ user.name }}</div>
                    <div v-if="user.function || user.email" class="text-xs text-slate-500">
                      {{ user.function || 'Fonction non renseignée' }}<span v-if="user.email"> · {{ user.email }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500 italic">
                Aucun utilisateur assigné
              </div>
            </div>

            <!-- Bouton de fermeture (optionnel) -->
            <button
              type="button"
              class="mt-6 w-full rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-200"
              @click="handleClose"
            >
              Fermer
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  node: {
    type: Object,
    default: null,
  },
  isOpen: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: null,
  },
})

const users = computed(() => {
  if (Array.isArray(props.node?.users)) {
    return props.node.users
  }

  return Array.isArray(props.node?.assignedUsers)
    ? props.node.assignedUsers.map((user, index) =>
        typeof user === 'string' ? { id: `legacy-${index}`, name: user } : user,
      )
    : []
})

const emit = defineEmits(['close'])

function handleClose() {
  emit('close')
}

function handleOverlayClick() {
  emit('close')
}

function handleKeyDown(event) {
  if (event.key === 'Escape') {
    emit('close')
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyDown)
})
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-to,
.modal-leave-from {
  opacity: 1;
}
</style>
