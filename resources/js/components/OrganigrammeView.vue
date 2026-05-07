<template>
  <section v-if="isRoot" class="org-root" :class="{ 'is-fullscreen': isFullscreen }">
    <header class="org-header">
      <div>
        <h3 class="org-title">Organigramme interactif</h3>
        <p class="org-subtitle">Cliquez une structure pour voir les utilisateurs ou ajouter une structure enfant.</p>
      </div>
      <div class="org-header-actions">
        <button class="org-btn org-btn-ghost" :disabled="loading" @click="fetchStructures">
          {{ loading ? 'Chargement...' : 'Actualiser' }}
        </button>
        <button class="org-btn org-btn-primary" @click="toggleFullscreen">
          {{ isFullscreen ? 'Quitter plein écran' : 'Aperçu plein écran' }}
        </button>
      </div>
    </header>

    <div class="org-toolbar" :class="{ 'is-active': isFullscreen }">
      <span class="org-toolbar-label">Navigation</span>
      <div class="org-zoom-controls">
        <button class="org-mini-btn" type="button" @click="zoomOut">-</button>
        <span class="org-zoom-value">{{ Math.round(zoom * 100) }}%</span>
        <button class="org-mini-btn" type="button" @click="zoomIn">+</button>
        <button class="org-mini-btn" type="button" @click="resetView">Recentrer</button>
      </div>
    </div>

    <div v-if="error" class="org-error">
      {{ error }}
    </div>

    <div class="org-layout" :class="{ 'is-readonly-view': isFullscreen }">
      <div
        class="org-tree"
        :class="{ 'is-panning': isPanning }"
        role="tree"
        aria-label="Organigramme des structures"
        @mousedown="startPan"
        @mousemove="onPanMove"
        @mouseup="stopPan"
        @mouseleave="stopPan"
      >
        <p v-if="!loading && rootNodes.length === 0" class="org-empty">Aucune structure disponible.</p>

        <div v-else class="org-canvas" :style="canvasStyle">
          <ul class="org-list org-list-root">
            <li v-for="item in rootNodes" :key="item.id" class="org-list-item">
              <OrganigrammeView
                :node="item"
                :selected-id="selectedId"
                :readonly="isFullscreen"
                @select-structure="openDetails"
                @open-add-child="openAddChild"
              />
            </li>
          </ul>
        </div>
      </div>

      <aside v-if="!isFullscreen" class="org-side-panel">
        <div v-if="activePanel === 'details' && selectedStructure" class="org-panel">
          <h4 class="org-panel-title">{{ selectedStructure.nom || selectedStructure.name }}</h4>
          <p class="org-panel-meta">ID: {{ selectedStructure.id }}</p>

          <h5 class="org-section-title">Utilisateurs rattachés</h5>
          <ul v-if="selectedUsers.length" class="org-users">
            <li v-for="user in selectedUsers" :key="user.id ?? user.email" class="org-user-item">
              <strong>{{ user.name || user.nom || user.username || 'Utilisateur' }}</strong>
              <span>{{ user.function || user.fonction || user.email || user.role || 'Aucune information complémentaire' }}</span>
            </li>
          </ul>
          <p v-else class="org-muted">Aucun utilisateur rattaché sur cette structure.</p>
        </div>

        <div v-else-if="activePanel === 'add-child'" class="org-panel">
          <h4 class="org-panel-title">Ajouter une structure enfant</h4>
          <p class="org-panel-meta">Parent: {{ selectedStructureName }}</p>

          <form class="org-form" @submit.prevent="submitChild">
            <label>
              Nom de la structure
              <input v-model="childForm.name" type="text" required />
            </label>

            <label>
              Type
              <select v-model="childForm.type" required>
                <option value="dg">DG</option>
                <option value="pole">Pole</option>
                <option value="division">Division</option>
                <option value="direction">Direction</option>
                <option value="autre">Autre</option>
              </select>
            </label>

            <label>
              Description (optionnel)
              <textarea v-model="childForm.description" rows="3"></textarea>
            </label>

            <div class="org-form-actions">
              <button type="button" class="org-btn org-btn-ghost" @click="closePanel">Annuler</button>
              <button type="submit" class="org-btn org-btn-primary" :disabled="submitting">
                {{ submitting ? 'Création...' : 'Créer la structure enfant' }}
              </button>
            </div>
          </form>

          <p v-if="formMessage" class="org-message">{{ formMessage }}</p>
        </div>

        <div v-else class="org-panel org-panel-placeholder">
          <p>Sélectionnez un noeud de l'arbre pour voir les détails.</p>
        </div>
      </aside>
    </div>
  </section>

  <div
    v-else
    class="org-node-wrapper"
    :class="{ 'is-child': depth > 0 }"
  >
    <div class="org-node-connector" v-if="depth > 0" aria-hidden="true"></div>

    <article
      class="org-node"
      :class="{ 'is-selected': selectedId === node.id, 'is-readonly': readonly }"
      role="treeitem"
      tabindex="0"
      @click="emitSelect"
      @keydown.enter.prevent="emitSelect"
      @keydown.space.prevent="emitSelect"
    >
      <div class="org-node-name">{{ node.nom || node.name }}</div>
      <div class="org-node-actions">
        <button class="org-mini-btn" :disabled="readonly" type="button" @click.stop="emitSelect">Details</button>
        <button class="org-mini-btn" :disabled="readonly" type="button" @click.stop="emitAdd">+ Enfant</button>
      </div>
    </article>

    <ul v-if="childNodes.length" class="org-list org-list-children" role="group">
      <li v-for="child in childNodes" :key="child.id" class="org-list-item">
        <OrganigrammeView
          :node="child"
          :depth="depth + 1"
          :selected-id="selectedId"
          :readonly="readonly"
          @select-structure="forwardSelect"
          @open-add-child="forwardAdd"
        />
      </li>
    </ul>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'

defineOptions({
  name: 'OrganigrammeView'
})

const props = defineProps({
  node: {
    type: Object,
    default: null
  },
  depth: {
    type: Number,
    default: 0
  },
  selectedId: {
    type: [Number, String, null],
    default: null
  },
  apiUrl: {
    type: String,
    default: '/api/structures'
  },
  readonly: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['select-structure', 'open-add-child'])

const isRoot = computed(() => props.node === null)
const childNodes = computed(() => {
  if (!props.node) return []
  return props.node.enfants || props.node.children || []
})

const loading = ref(false)
const submitting = ref(false)
const error = ref('')
const formMessage = ref('')
const activePanel = ref('')
const selectedId = ref(null)
const structures = ref([])
const isFullscreen = ref(false)
const zoom = ref(1)
const panX = ref(0)
const panY = ref(0)
const isPanning = ref(false)
const panStartX = ref(0)
const panStartY = ref(0)

const childForm = reactive({
  parent_id: null,
  name: '',
  type: 'direction',
  description: ''
})

const rootNodes = computed(() => structures.value || [])
const selectedStructure = computed(() => findById(structures.value, selectedId.value))
const selectedStructureName = computed(() => {
  return selectedStructure.value ? (selectedStructure.value.nom || selectedStructure.value.name) : 'N/A'
})
const selectedUsers = computed(() => {
  const item = selectedStructure.value
  if (!item) return []

  return item.users || item.utilisateurs || item.members || item.attached_users || []
})

const canvasStyle = computed(() => {
  return {
    transform: `translate(${panX.value}px, ${panY.value}px) scale(${zoom.value})`
  }
})

function emitSelect() {
  if (props.readonly) return
  emit('select-structure', props.node)
}

function emitAdd() {
  if (props.readonly) return
  emit('open-add-child', props.node)
}

function forwardSelect(node) {
  emit('select-structure', node)
}

function forwardAdd(node) {
  emit('open-add-child', node)
}

function findById(nodes, id) {
  if (!Array.isArray(nodes) || id === null) return null

  for (const item of nodes) {
    if (String(item.id) === String(id)) return item
    const children = item.enfants || item.children || []
    const found = findById(children, id)
    if (found) return found
  }

  return null
}

async function fetchStructures() {
  if (!isRoot.value) return

  loading.value = true
  error.value = ''

  try {
    const response = await fetch(props.apiUrl, {
      headers: {
        Accept: 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error('Impossible de charger les structures.')
    }

    structures.value = await response.json()
  } catch (err) {
    error.value = err?.message || 'Erreur inattendue lors du chargement.'
  } finally {
    loading.value = false
  }
}

function openDetails(node) {
  if (!isRoot.value) {
    emit('select-structure', node)
    return
  }

  if (isFullscreen.value) {
    return
  }

  selectedId.value = node.id
  activePanel.value = 'details'
  formMessage.value = ''
}

function openAddChild(node) {
  if (!isRoot.value) {
    emit('open-add-child', node)
    return
  }

  if (isFullscreen.value) {
    return
  }

  selectedId.value = node.id
  activePanel.value = 'add-child'
  formMessage.value = ''

  childForm.parent_id = node.id
  childForm.name = ''
  childForm.type = 'direction'
  childForm.description = ''
}

function closePanel() {
  activePanel.value = ''
  formMessage.value = ''
}

function toggleFullscreen() {
  isFullscreen.value = !isFullscreen.value
  if (!isFullscreen.value) {
    isPanning.value = false
  }
  resetView()
}

function zoomIn() {
  zoom.value = Math.min(zoom.value + 0.1, 2.5)
}

function zoomOut() {
  zoom.value = Math.max(zoom.value - 0.1, 0.5)
}

function resetView() {
  zoom.value = 1
  panX.value = 0
  panY.value = 0
}

function startPan(event) {
  if (!isRoot.value) return
  if (!isFullscreen.value) return
  if (event.button !== 0) return
  if (event.target.closest('.org-node') || event.target.closest('button') || event.target.closest('input') || event.target.closest('select') || event.target.closest('textarea')) {
    return
  }

  isPanning.value = true
  panStartX.value = event.clientX - panX.value
  panStartY.value = event.clientY - panY.value
}

function onPanMove(event) {
  if (!isRoot.value) return
  if (!isPanning.value) return

  panX.value = event.clientX - panStartX.value
  panY.value = event.clientY - panStartY.value
}

function stopPan() {
  if (!isRoot.value) return
  isPanning.value = false
}

function handleEscape(event) {
  if (event.key === 'Escape' && isFullscreen.value) {
    toggleFullscreen()
  }
}

async function submitChild() {
  submitting.value = true
  formMessage.value = ''

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

  try {
    const payload = new FormData()
    payload.append('name', childForm.name)
    payload.append('type', childForm.type)
    payload.append('parent_id', String(childForm.parent_id))
    payload.append('description', childForm.description || '')

    const response = await fetch('/admin/structures', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken || '',
        Accept: 'application/json'
      },
      body: payload
    })

    if (!response.ok) {
      throw new Error('La creation a echoue. Verifiez vos droits et les champs saisis.')
    }

    formMessage.value = 'Structure enfant creee avec succes.'
    await fetchStructures()
    activePanel.value = 'details'
  } catch (err) {
    formMessage.value = err?.message || 'Erreur lors de la creation.'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  if (isRoot.value) {
    fetchStructures()
    window.addEventListener('keydown', handleEscape)
  }
})

onBeforeUnmount(() => {
  if (isRoot.value) {
    window.removeEventListener('keydown', handleEscape)
  }
})
</script>

<style scoped>
.org-root {
  background: #ffffff;
  border: 1px solid #e7eaf0;
  border-radius: 18px;
  padding: 20px;
  width: 100%;
}

.org-header {
  align-items: center;
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  gap: 12px;
  justify-content: space-between;
  margin-bottom: 18px;
}

.org-header-actions {
  display: flex;
  flex-shrink: 0;
  gap: 8px;
}

.org-toolbar {
  align-items: center;
  display: flex;
  gap: 12px;
  justify-content: space-between;
  margin-bottom: 12px;
}

.org-toolbar-label {
  color: #6b7280;
  font-size: 0.82rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.org-zoom-controls {
  align-items: center;
  display: flex;
  gap: 8px;
}

.org-zoom-value {
  color: #374151;
  font-size: 0.82rem;
  font-weight: 700;
  min-width: 48px;
  text-align: center;
}

.org-header > div {
  min-width: 0;
}

.org-title {
  color: #1f2937;
  font-size: 1.125rem;
  font-weight: 700;
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.org-subtitle {
  color: #6b7280;
  font-size: 0.9rem;
  margin: 4px 0 0;
}

.org-layout {
  display: grid;
  gap: 18px;
  grid-template-columns: minmax(0, 2fr) minmax(260px, 1fr);
}

.org-layout.is-readonly-view {
  grid-template-columns: 1fr;
}

.org-tree {
  background: #ffffff;
  border: 1px solid #edf0f5;
  border-radius: 14px;
  min-height: 420px;
  overflow: auto;
  padding: 14px;
}

.org-tree.is-panning {
  cursor: grabbing;
  user-select: none;
}

.org-canvas {
  min-width: max-content;
  transform-origin: left top;
  width: 100%;
}

.org-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.org-list-children {
  margin-left: 34px;
  margin-top: 14px;
  padding-left: 22px;
  position: relative;
}

.org-list-children::before {
  border-left: 2px solid #e0e4ea;
  content: '';
  height: calc(100% - 8px);
  left: 2px;
  position: absolute;
  top: 0;
}

.org-list-item {
  margin-bottom: 16px;
}

.org-node-wrapper {
  position: relative;
}

.org-node-connector {
  border-top: 2px solid #e0e4ea;
  left: -14px;
  position: absolute;
  top: 20px;
  width: 14px;
}

.org-node {
  align-items: center;
  background: #ffffff;
  border: 1px solid #d9e1ec;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  padding: 12px 14px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}

.org-node.is-readonly {
  cursor: default;
}

.org-node:hover,
.org-node:focus-visible,
.org-node.is-selected {
  border-color: #2e3192;
  box-shadow: 0 8px 20px rgba(46, 49, 146, 0.16);
  outline: none;
  transform: translateY(-1px);
}

.org-node-name {
  color: #111827;
  font-size: 0.95rem;
  font-weight: 600;
}

.org-node-actions {
  display: flex;
  gap: 8px;
}

.org-mini-btn {
  background: #f4f6fa;
  border: 1px solid #dce3ed;
  border-radius: 8px;
  color: #374151;
  cursor: pointer;
  font-size: 0.78rem;
  font-weight: 600;
  padding: 4px 9px;
}

.org-mini-btn:hover {
  border-color: #2e3192;
  color: #2e3192;
}

.org-mini-btn:disabled {
  cursor: not-allowed;
  opacity: 0.45;
}

.org-side-panel {
  align-self: start;
}

.org-panel {
  background: #ffffff;
  border: 1px solid #e7eaf0;
  border-radius: 14px;
  min-height: 200px;
  padding: 16px;
}

.org-panel-title {
  color: #1f2937;
  font-size: 1rem;
  font-weight: 700;
  margin: 0;
}

.org-panel-meta {
  color: #6b7280;
  font-size: 0.85rem;
  margin: 6px 0 14px;
}

.org-section-title {
  color: #374151;
  font-size: 0.9rem;
  font-weight: 700;
  margin: 0 0 8px;
}

.org-users {
  display: grid;
  gap: 8px;
  list-style: none;
  margin: 0;
  padding: 0;
}

.org-user-item {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 10px;
}

.org-user-item strong {
  color: #1f2937;
  font-size: 0.9rem;
}

.org-user-item span,
.org-muted,
.org-empty {
  color: #6b7280;
  font-size: 0.85rem;
}

.org-form {
  display: grid;
  gap: 12px;
}

.org-form label {
  color: #374151;
  display: grid;
  font-size: 0.84rem;
  font-weight: 600;
  gap: 6px;
}

.org-form input,
.org-form select,
.org-form textarea {
  background: #ffffff;
  border: 1px solid #d1d9e6;
  border-radius: 8px;
  color: #111827;
  font-size: 0.9rem;
  padding: 8px 10px;
}

.org-form input:focus,
.org-form select:focus,
.org-form textarea:focus {
  border-color: #2e3192;
  box-shadow: 0 0 0 3px rgba(46, 49, 146, 0.15);
  outline: none;
}

.org-form-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.org-btn {
  border-radius: 10px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 700;
  padding: 8px 12px;
}

.org-btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.org-btn-primary {
  background: #2e3192;
  border: 1px solid #2e3192;
  color: #ffffff;
}

.org-btn-primary:hover {
  background: #262975;
}

.org-btn-ghost {
  background: #ffffff;
  border: 1px solid #d6dce6;
  color: #374151;
}

.org-btn-ghost:hover {
  border-color: #2e3192;
  color: #2e3192;
}

.org-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 10px;
  color: #b91c1c;
  font-size: 0.85rem;
  margin-bottom: 12px;
  padding: 10px 12px;
}

.org-message {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  color: #1d4ed8;
  font-size: 0.82rem;
  margin-top: 12px;
  padding: 8px 10px;
}

.org-panel-placeholder {
  align-items: center;
  color: #6b7280;
  display: flex;
  justify-content: center;
  text-align: center;
}

.org-root.is-fullscreen {
  border-radius: 0;
  height: 100vh;
  inset: 0;
  overflow: auto;
  padding: 18px;
  position: fixed;
  width: 100vw;
  z-index: 2147483647;
}

@media (max-width: 1024px) {
  .org-layout {
    grid-template-columns: 1fr;
  }

  .org-side-panel {
    position: static;
  }
}
</style>