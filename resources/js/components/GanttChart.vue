<template>
  <section class="gantt-chart-shell">
    <header class="gantt-chart-header">
      <div>
        <p class="gantt-chart-eyebrow">Diagramme de Gantt</p>
        <h2 class="gantt-chart-title">Planification des tâches</h2>
      </div>

      <div class="gantt-chart-meta">
        <span v-if="loading">Chargement...</span>
        <span v-else>{{ tasks.length }} tâche(s)</span>
      </div>
    </header>

    <p v-if="error" class="gantt-chart-error">
      {{ error }}
    </p>

    <div class="gantt-chart-frame">
      <div class="gantt-chart-toolbar px-4 py-3 flex items-center gap-3 border-b border-gray-100 bg-white">
        <div class="flex items-center gap-2">
          <button @click="zoomOut" title="Zoom out" class="btn-ghost">−</button>
          <button @click="zoomIn" title="Zoom in" class="btn-ghost">＋</button>
          <span class="text-sm text-gray-600 ml-2">Vue: <strong>{{ currentView }}</strong></span>
        </div>
        <div class="ml-auto">
          <button @click="exportSvg" class="btn-primary btn-sm">Exporter (SVG)</button>
        </div>
      </div>

      <div id="gantt" ref="ganttRef" class="gantt-chart-canvas"></div>

      <div v-if="!loading && tasks.length === 0" class="p-12 text-center text-gray-500">
        <div class="text-xl font-semibold">Aucune tâche disponible</div>
        <div class="text-sm mt-1">Les tâches du projet seront affichées ici une fois ajoutées.</div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, shallowRef, watch } from 'vue';
import Gantt from 'frappe-gantt';

const props = defineProps({
  apiUrl: {
    type: String,
    default: '/api/tasks-gantt'
  },
  viewMode: {
    type: String,
    default: 'Day'
  },
  language: {
    type: String,
    default: 'en'
  }
});

const ganttRef = ref(null);
const tasks = shallowRef([]);
const loading = ref(false);
const error = ref('');
const isReady = ref(false);

let ganttInstance = null;
let requestSequence = 0;
const viewModes = ['Hour','Day', 'Week', 'Month'];
const currentViewIndex = ref(Math.max(0, viewModes.indexOf(props.viewMode) || 1));
const currentView = ref(viewModes[currentViewIndex.value] ?? props.viewMode);

async function fetchTasks() {
  loading.value = true;
  error.value = '';

  const requestId = ++requestSequence;

  try {
    const response = await fetch(props.apiUrl, {
      headers: {
        Accept: 'application/json'
      },
      credentials: 'same-origin'
    });

    if (!response.ok) {
      throw new Error(`Impossible de charger le Gantt (${response.status})`);
    }

    const payload = await response.json();

    if (requestId !== requestSequence) {
      return;
    }

    const rawTasks = Array.isArray(payload) ? payload : (payload.data ?? []);
    tasks.value = normalizeTasks(rawTasks);
  } catch (exception) {
    if (requestId === requestSequence) {
      error.value = 'Le diagramme de Gantt est indisponible pour le moment.';
      tasks.value = [];
      console.error('Unable to load gantt tasks', exception);
    }
  } finally {
    if (requestId === requestSequence) {
      loading.value = false;
    }
  }
}

function destroyGantt() {
  if (ganttInstance && typeof ganttInstance.destroy === 'function') {
    ganttInstance.destroy();
  }

  ganttInstance = null;

  if (ganttRef.value) {
    ganttRef.value.innerHTML = '';
  }
}

function normalizeTasks(rawTasks) {
  return (Array.isArray(rawTasks) ? rawTasks : [])
    .map((task, index) => ({
      id: String(task.id ?? index + 1),
      name: String(task.name ?? task.text ?? 'Tâche sans nom'),
      start: task.start ?? task.start_date ?? task.startDate,
      end: task.end ?? task.end_date ?? task.endDate ?? task.start ?? task.start_date,
      progress: Number.isFinite(Number(task.progress)) ? Number(task.progress) : 0,
      dependencies: task.dependencies ?? '',
      custom_class: task.custom_class ?? (task.project_color ? `project-color-${task.project_id}` : ''),
      // Provide popup HTML for richer tooltips
      popup_html: `<div class="popup-wrapper"><div class="title">${escapeHtml(String(task.name ?? 'Tâche'))}</div><div class="subtitle">Projet: ${escapeHtml(String(task.project_name ?? '—'))}</div><div class="details">Début: ${escapeHtml(String(task.start))} • Fin: ${escapeHtml(String(task.end))}</div><div class="details">Progress: ${Number.isFinite(Number(task.progress)) ? Number(task.progress) : 0}%</div></div>`
    }))
    .filter((task) => task.start && task.end);
}

function escapeHtml(unsafe) {
  return unsafe
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function renderGantt() {
  if (!isReady.value || !ganttRef.value || tasks.value.length === 0) {
    destroyGantt();
    return;
  }

  if (ganttInstance) {
    destroyGantt();
  }

  const ganttHeight = Math.max(400, tasks.value.length * 56 + 160);
  ganttRef.value.style.minHeight = `${ganttHeight}px`;
  destroyGantt();
  ganttInstance = new Gantt(ganttRef.value, tasks.value, {
    view_mode: currentView.value,
    language: props.language,
    on_click: (task) => {
      // default popup handled by frappe-gantt when popup_html exists
      // Force refresh to ensure visibility
      // no-op here — popup_html provides tooltip
      return;
    }
  });
}

function zoomIn() {
  if (currentViewIndex.value < viewModes.length - 1) {
    currentViewIndex.value++;
    currentView.value = viewModes[currentViewIndex.value];
    renderGantt();
  }
}

function zoomOut() {
  if (currentViewIndex.value > 0) {
    currentViewIndex.value--;
    currentView.value = viewModes[currentViewIndex.value];
    renderGantt();
  }
}

function exportSvg() {
  // Find the first SVG inside ganttRef
  const svg = ganttRef.value.querySelector('svg');
  if (!svg) {
    console.warn('No SVG found to export');
    return;
  }

  const serializer = new XMLSerializer();
  let source = serializer.serializeToString(svg);

  // Add name spaces.
  if(!source.match(/^<svg[^>]+xmlns="http\:\/\/www\.w3\.org\/2000\/svg"/)){
    source = source.replace(/^<svg/, '<svg xmlns="http://www.w3.org/2000/svg"');
  }
  if(!source.match(/^<svg[^>]+xmlns:xlink="http\:\/\/www\.w3\.org\/1999\/xlink"/)){
    source = source.replace(/^<svg/, '<svg xmlns:xlink="http://www.w3.org/1999/xlink"');
  }

  const blob = new Blob([source], {type: 'image/svg+xml;charset=utf-8'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `gantt-export-${new Date().toISOString().slice(0,19)}.svg`;
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}

onMounted(async () => {
  await fetchTasks();
  await nextTick();
  isReady.value = true;
  renderGantt();
});

watch(() => props.apiUrl, async () => {
  await fetchTasks();
  await nextTick();
  renderGantt();
});

watch(tasks, async () => {
  await nextTick();
  renderGantt();
});

watch(() => [props.viewMode, props.language], async () => {
  await nextTick();
  renderGantt();
});

onBeforeUnmount(() => {
  destroyGantt();
});
</script>

<style scoped>
.gantt-chart-shell {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.gantt-chart-header {
  display: flex;
  flex-wrap: wrap;
  align-items: end;
  justify-content: space-between;
  gap: 1rem;
}

.gantt-chart-eyebrow {
  margin: 0;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #2e3192;
}

.gantt-chart-title {
  margin: 0.25rem 0 0;
  font-size: 1.25rem;
  font-weight: 800;
  color: #1a202c;
}

.gantt-chart-meta {
  font-size: 0.875rem;
  font-weight: 600;
  color: #475569;
}

.gantt-chart-error {
  margin: 0;
  border-radius: 0.75rem;
  border: 1px solid rgba(220, 38, 38, 0.2);
  background: rgba(254, 242, 242, 0.95);
  padding: 0.875rem 1rem;
  font-size: 0.875rem;
  color: #b91c1c;
}

.gantt-chart-frame {
  overflow: hidden;
  border-radius: 1.25rem;
  border: 1px solid #e2e8f0;
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  box-shadow: 0 20px 45px -28px rgba(15, 23, 42, 0.45);
}

.gantt-chart-canvas {
  min-height: 400px;
  position: relative;
}

.gantt-chart-canvas :deep(.gantt-container) {
  position: relative;
  overflow: auto;
  width: 100%;
  height: var(--gv-grid-height, 32rem);
  line-height: 14.5px;
  font-size: 12px;
  border-radius: 0.75rem;
  isolation: isolate;
  background: #ffffff;
}

.gantt-chart-canvas :deep(.gantt) {
  position: absolute;
  user-select: none;
  -webkit-user-select: none;
}

.gantt-chart-canvas :deep(.grid-header) {
  position: sticky;
  top: 0;
  left: 0;
  z-index: 5;
  height: auto;
  background-color: #ffffff;
  border-bottom: 1px solid #e2e8f0;
}

.gantt-chart-canvas :deep(.grid-header),
.gantt-chart-canvas :deep(.side-header) {
  background-color: #ffffff;
}

.gantt-chart-canvas :deep(.upper-text),
.gantt-chart-canvas :deep(.lower-text) {
  fill: #475569;
  color: #475569;
}

.gantt-chart-canvas :deep(.grid-row) {
  fill: #fdfdfd;
}

.gantt-chart-canvas :deep(.row-line) {
  stroke: #e2e8f0;
}

.gantt-chart-canvas :deep(.tick) {
  stroke: #e5e7eb;
  stroke-width: 0.4;
}

.gantt-chart-canvas :deep(.tick.thick) {
  stroke: #cbd5e1;
  stroke-width: 0.7;
}

.gantt-chart-canvas :deep(.bar-wrapper .bar) {
  fill: #2e3192;
  stroke: #2e3192;
  stroke-width: 0;
  border-radius: 0.25rem;
}

.gantt-chart-canvas :deep(.bar-progress) {
  fill: #f37021;
}

.gantt-chart-canvas :deep(.bar-expected-progress) {
  fill: rgba(243, 112, 33, 0.25);
}

.gantt-chart-canvas :deep(.bar-label) {
  fill: #ffffff;
  dominant-baseline: central;
  font-size: 13px;
  font-weight: 500;
}

.gantt-chart-canvas :deep(.bar-label.big) {
  fill: #1a202c;
  text-anchor: start;
}

.gantt-chart-canvas :deep(.handle) {
  fill: #1a202c;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.gantt-chart-canvas :deep(.handle.active),
.gantt-chart-canvas :deep(.handle.visible) {
  opacity: 1;
  cursor: ew-resize;
}

.gantt-chart-canvas :deep(.grid-column) {
  fill: transparent;
  pointer-events: all;
}

.gantt-chart-canvas :deep(.grid-column:hover) {
  fill: rgba(243, 244, 246, 0.8);
}

.gantt-chart-canvas :deep(.bar-label),
.gantt-chart-canvas :deep(.bar-label.big) {
  font-family: inherit;
}

.gantt-chart-canvas :deep(.popup-wrapper) {
  border: 1px solid #e2e8f0;
  background: #ffffff;
  box-shadow: 0 10px 24px -3px rgba(0, 0, 0, 0.16);
  padding: 0.75rem;
  border-radius: 0.5rem;
}

.gantt-chart-canvas :deep(.popup-wrapper .title) {
  margin-bottom: 0.125rem;
  color: #1a202c;
  font-size: 0.85rem;
  font-weight: 700;
}

.gantt-chart-canvas :deep(.popup-wrapper .subtitle) {
  color: #1a202c;
  font-size: 0.8rem;
  margin-bottom: 0.375rem;
}

.gantt-chart-canvas :deep(.popup-wrapper .details) {
  color: #64748b;
  font-size: 0.7rem;
}
</style>