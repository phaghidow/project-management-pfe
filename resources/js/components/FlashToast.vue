<template>
  <div class="fixed bottom-6 right-6 z-[60] pointer-events-none space-y-3">
    <!-- Auto flash messages from Laravel -->
    <transition-group name="toast-fade" tag="div" class="space-y-3">
      <div
        v-for="(message, key) in flashMessages"
        :key="`flash-${key}`"
        class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium animate-slide-in"
        :class="getToastClass(key)"
      >
        <span v-if="key === 'success'" class="text-lg">✓</span>
        <span v-else-if="key === 'error'" class="text-lg">✕</span>
        <span v-else class="text-lg">ℹ</span>
        <span>{{ message }}</span>
      </div>
    </transition-group>

    <!-- Manual toasts -->
    <transition-group name="toast-fade" tag="div" class="space-y-3">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium animate-slide-in"
        :class="getToastClass(toast.type)"
      >
        <span v-if="toast.type === 'success'" class="text-lg">✓</span>
        <span v-else-if="toast.type === 'error'" class="text-lg">✕</span>
        <span v-else class="text-lg">ℹ</span>
        <span>{{ toast.message }}</span>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const toasts = ref([])
const flashMessages = ref({})
let toastId = 0
let flashTimeout = null

const getToastClass = (type) => {
  const classes = {
    success: 'bg-emerald-500 text-white',
    error: 'bg-red-500 text-white',
    warning: 'bg-amber-500 text-white',
    info: 'bg-blue-500 text-white'
  }
  return classes[type] || classes.info
}

const showToast = (message, type = 'info', duration = 3000) => {
  const id = toastId++
  const toast = { id, message, type }
  toasts.value.push(toast)

  setTimeout(() => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }, duration)

  return id
}

// Listen for flash messages from axios middleware
window.addEventListener('flash-message', (event) => {
  const { success, error } = event.detail
  
  if (success) {
    flashMessages.value.success = success
    clearTimeout(flashTimeout)
    flashTimeout = setTimeout(() => {
      flashMessages.value = {}
    }, 3000)
  }
  
  if (error) {
    flashMessages.value.error = error
    clearTimeout(flashTimeout)
    flashTimeout = setTimeout(() => {
      flashMessages.value = {}
    }, 3000)
  }
})

// Expose methods for parent component
defineExpose({ showToast })
</script>

<style scoped>
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideOut {
  from {
    opacity: 1;
    transform: translateX(0);
  }
  to {
    opacity: 0;
    transform: translateX(100%);
  }
}

.animate-slide-in {
  animation: slideIn 0.3s ease-out forwards;
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: all 0.3s ease;
}

.toast-fade-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-fade-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>

