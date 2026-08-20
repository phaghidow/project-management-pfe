<template>
  <div class="min-w-[180px] rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm ring-1 ring-slate-100 transition-all duration-200 hover:shadow-md">
    <Handle type="target" :position="Position.Top" class="!bg-slate-300" />
    <div class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">
      {{ normalizedType }}
    </div>
    <div class="mt-1 text-sm font-semibold text-slate-800 leading-5">
      {{ data?.name || 'Sans nom' }}
    </div>
    <button
      v-if="data?.hasChildren"
      type="button"
      class="node-collapse-arrow mt-2 flex w-full cursor-pointer items-center justify-center rounded-md text-[10px] font-bold text-slate-500 transition-colors hover:text-slate-700"
      @click.stop="handleCollapseClick"
    >
      {{ data?.collapsed ? '▶' : '▼' }}
    </button>
    <Handle type="source" :position="Position.Bottom" class="!bg-slate-300" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Handle, Position } from '@vue-flow/core'

const emit = defineEmits(['toggle-collapse'])

const props = defineProps({
  id: {
    type: String,
    default: '',
  },
  data: {
    type: Object,
    default: () => ({ id: '', name: '', type: '', hasChildren: false, collapsed: false }),
  },
})

const normalizedType = computed(() => String(props.data?.type || 'ROLE').toUpperCase())

function handleCollapseClick(event) {
  event.stopPropagation()
  emit('toggle-collapse', props.data?.id ?? props.id)
}
</script>
