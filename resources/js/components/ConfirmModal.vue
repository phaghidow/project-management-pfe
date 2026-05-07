<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div @click="close" class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

    <!-- Modal -->
    <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full animate-fade-in">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
      </div>

      <!-- Body -->
      <div class="px-6 py-4">
        <p class="text-sm text-gray-600">{{ message }}</p>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 border-t border-gray-200 flex gap-3 justify-end">
        <button
          @click="close"
          :disabled="isLoading"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 transition-colors"
        >
          Annuler
        </button>
        <button
          @click="confirm"
          :disabled="isLoading"
          :class="[
            'px-4 py-2 text-sm font-medium text-white rounded-lg disabled:opacity-50 transition-colors',
            confirmButtonClass || 'bg-red-600 hover:bg-red-700'
          ]"
        >
          {{ isLoading ? 'Traitement...' : confirmButtonText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: 'Confirmation'
  },
  message: {
    type: String,
    required: true
  },
  confirmButtonText: {
    type: String,
    default: 'Confirmer'
  },
  confirmButtonClass: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['confirm', 'close'])

const isOpen = ref(false)
const isLoading = ref(false)
let resolveConfirm = null

const open = (options = {}) => {
  if (options.title) props.title = options.title
  if (options.message) props.message = options.message
  if (options.confirmButtonText) props.confirmButtonText = options.confirmButtonText
  if (options.confirmButtonClass) props.confirmButtonClass = options.confirmButtonClass
  isOpen.value = true
  
  return new Promise((resolve) => {
    resolveConfirm = resolve
  })
}

const confirm = async () => {
  isLoading.value = true
  emit('confirm')
  if (resolveConfirm) {
    resolveConfirm(true)
    resolveConfirm = null
  }
}

const close = () => {
  isOpen.value = false
  isLoading.value = false
  emit('close')
  if (resolveConfirm) {
    resolveConfirm(false)
    resolveConfirm = null
  }
}

defineExpose({ open, close })
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.animate-fade-in {
  animation: fadeIn 0.2s ease-out forwards;
}
</style>
