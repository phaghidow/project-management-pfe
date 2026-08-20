<template>
  <div class="organization-chart">
    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <p>Chargement de l'organigramme...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <p><strong>Erreur :</strong> {{ error }}</p>
    </div>

    <!-- Chart State -->
    <div v-else-if="ready" class="chart-container">
      <VueFlow
        :nodes="flowData.nodes"
        :edges="flowData.edges"
        :node-types="nodeTypes"
        fit-view
      >
        <Background />
        <Controls />
        <MiniMap />
      </VueFlow>

      <NodeDetailsModal
        :node="selectedNode"
        :is-open="isDetailsModalOpen"
        :loading="selectedNodeLoading"
        :error="selectedNodeError"
        @close="closeDetailsModal"
      />
    </div>
  </div>
</template>

<script setup>
import '@vue-flow/core/dist/style.css'
import '@vue-flow/controls/dist/style.css'
import '@vue-flow/minimap/dist/style.css'

import { computed, defineComponent, h, markRaw, ref, onMounted } from 'vue'
import { Background } from '@vue-flow/background'
import { Controls } from '@vue-flow/controls'
import { MiniMap } from '@vue-flow/minimap'
import { MarkerType, Position, useVueFlow, VueFlow } from '@vue-flow/core'
import dagre from 'dagre'
import OrganizationNode from '../../Components/Organization/OrganizationNode.vue'
import NodeDetailsModal from '../../Components/Organization/NodeDetailsModal.vue'

const organizationData = ref([])
const loading = ref(true)
const error = ref(null)
const ready = ref(false)

const organizationEdges = computed(() =>
  organizationData.value
    .filter((member) => member.parent_id)
    .map((member) => ({
      id: `${member.parent_id}-${member.id}`,
      source: member.parent_id,
      target: member.id,
    }))
)

onMounted(async () => {
  try {
    loading.value = true
    error.value = null
    ready.value = false

    const response = await fetch('/api/organigramme-v2')

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`)
    }

    const apiData = await response.json()

    // Transform flat API data to internal format
    organizationData.value = apiData.map((item) => {
      // Defensive check: warn about orphaned parents
      if (item.parent_id !== null) {
        const parentExists = apiData.some((p) => p.id === item.parent_id)
        if (!parentExists) {
          console.warn(
            `Orphaned parent reference: structure ${item.id} (${item.name}) points to non-existent parent_id=${item.parent_id}`,
          )
        }
      }

      return {
        id: String(item.id),
        data: {
          name: item.name,
          title: item.name,
          type: item.type,
          assignedUsers: [],
        },
        parent_id: item.parent_id ? String(item.parent_id) : null,
      }
    })

    ready.value = true
  } catch (err) {
    error.value = err.message || "Erreur lors du chargement de l'organigramme"
    console.error('OrganizationChart fetch error:', err)
  } finally {
    loading.value = false
  }
})

const collapsedIds = ref(new Set())

const selectedNode = ref(null)
const isDetailsModalOpen = ref(false)
const selectedNodeLoading = ref(false)
const selectedNodeError = ref(null)
const selectedNodeRequest = ref(0)

const nodeTypes = {
  orgNode: markRaw(
    defineComponent({
      name: 'OrganizationNodeType',
      inheritAttrs: false,
      setup(_, { attrs }) {
        return () =>
          h(OrganizationNode, {
            ...attrs,
            onToggleCollapse: (nodeId) => toggleCollapse(nodeId),
          })
      },
    }),
  ),
}

function getNodeChildren(nodeId, allNodes) {
  return allNodes
    .filter((node) => node.parent_id === nodeId)
    .map((node) => node.id)
}

function getVisibleTree(allNodes, allEdges, collapsedSet) {
  const childrenMap = new Map()

  allNodes.forEach((node) => {
    const childIds = getNodeChildren(node.id, allNodes)
    if (childIds.length > 0) {
      childrenMap.set(node.id, childIds)
    }
  })

  const excludedIds = new Set()

  collapsedSet.forEach((collapsedId) => {
    const visitChildren = (currentId) => {
      const children = childrenMap.get(currentId) ?? []

      children.forEach((childId) => {
        if (excludedIds.has(childId)) {
          return
        }

        excludedIds.add(childId)
        visitChildren(childId)
      })
    }

    visitChildren(collapsedId)
  })

  const visibleNodes = allNodes.filter((node) => !excludedIds.has(node.id))
  const visibleNodeIds = new Set(visibleNodes.map((node) => node.id))

  const visibleEdges = allEdges.filter(
    (edge) => visibleNodeIds.has(edge.source) && visibleNodeIds.has(edge.target),
  )

  return { visibleNodes, visibleEdges }
}

function layoutElements(nodes, edges, collapsedState) {
  const graph = new dagre.graphlib.Graph()

  graph.setDefaultEdgeLabel(() => ({}))
  graph.setGraph({
    rankdir: 'TB',
    nodesep: 90,
    ranksep: 140,
    marginx: 30,
    marginy: 30,
  })

  nodes.forEach((node) => {
    graph.setNode(node.id, { width: 180, height: 64 })
  })

  edges.forEach((edge) => {
    graph.setEdge(edge.source, edge.target)
  })

  dagre.layout(graph)

  const layoutedNodes = nodes.map((node) => {
    const dagreNode = graph.node(node.id)
    const hasChildren = organizationData.value.some((entry) => entry.parent_id === node.id)

    return {
      id: node.id,
      type: 'orgNode',
      position: {
        x: dagreNode.x - dagreNode.width / 2,
        y: dagreNode.y - dagreNode.height / 2,
      },
      data: {
        ...(node.data ?? {}),
        id: node.id,
        name: node.data?.name ?? node.id,
        type: node.data?.type ?? 'role',
        hasChildren,
        collapsed: collapsedState.has(node.id),
      },
      draggable: false,
    }
  })

  const layoutedEdges = edges.map((edge) => ({
    ...edge,
    type: 'smoothstep',
    animated: false,
    sourcePosition: Position.Bottom,
    targetPosition: Position.Top,
    markerEnd: {
      type: MarkerType.ArrowClosed,
      color: '#64748b',
    },
    style: {
      stroke: '#64748b',
      strokeWidth: 2,
    },
  }))

  return {
    nodes: layoutedNodes,
    edges: layoutedEdges,
  }
}

const visibleTree = computed(() => getVisibleTree(organizationData.value, organizationEdges.value, collapsedIds.value))

const flowData = computed(() => {
  const { visibleNodes, visibleEdges } = visibleTree.value

  return layoutElements(visibleNodes, visibleEdges, collapsedIds.value)
})

function toggleCollapse(nodeId) {
  const nextCollapsed = new Set(collapsedIds.value)

  if (nextCollapsed.has(nodeId)) {
    nextCollapsed.delete(nodeId)
  } else {
    nextCollapsed.add(nodeId)
  }

  collapsedIds.value = nextCollapsed
}

const { onNodeClick } = useVueFlow()

async function loadNodeDetails(node) {
  const requestId = selectedNodeRequest.value + 1
  selectedNodeRequest.value = requestId
  selectedNodeLoading.value = true
  selectedNodeError.value = null

  try {
    const response = await fetch(`/api/structures/${node.id}/details`, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })

    if (!response.ok) {
      throw new Error('Impossible de charger les détails de la structure.')
    }

    const details = await response.json()
    if (requestId === selectedNodeRequest.value) {
      selectedNode.value = {
        ...node.data,
        ...details,
      }
    }
  } catch (err) {
    if (requestId === selectedNodeRequest.value) {
      selectedNodeError.value = err?.message || 'Une erreur est survenue lors du chargement.'
    }
  } finally {
    if (requestId === selectedNodeRequest.value) {
      selectedNodeLoading.value = false
    }
  }
}

function closeDetailsModal() {
  isDetailsModalOpen.value = false
  selectedNodeLoading.value = false
  selectedNodeError.value = null
}

onNodeClick(async (event) => {
  const node = event?.node ?? event
  const target = event?.event?.target ?? null

  if (target && typeof target.closest === 'function' && target.closest('.node-collapse-arrow')) {
    return
  }

  selectedNode.value = node.data
  selectedNodeLoading.value = true
  selectedNodeError.value = null
  isDetailsModalOpen.value = true
  await loadNodeDetails(node)
})
</script>

<style scoped>
.organization-chart {
  width: 100%;
  height: 680px;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  overflow: hidden;
  background: linear-gradient(180deg, #f8fafc 0%, #eef4ff 100%);
}

.loading-state,
.error-state {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  font-size: 14px;
  color: #64748b;
}

.error-state {
  color: #dc2626;
  background-color: #fee2e2;
}

.chart-container {
  width: 100%;
  height: 100%;
}

:deep(.vue-flow__renderer) {
  background: transparent;
}

:deep(.vue-flow__controls) {
  margin: 12px;
}

:deep(.vue-flow__minimap) {
  border: 1px solid #dbeafe;
  border-radius: 12px;
  overflow: hidden;
}
</style>
