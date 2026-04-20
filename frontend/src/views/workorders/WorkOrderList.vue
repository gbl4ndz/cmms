<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import workOrdersService from '@/services/workOrders'
import PageHeader from '@/components/common/PageHeader.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import PriorityBadge from '@/components/common/PriorityBadge.vue'
import BasePagination from '@/components/common/BasePagination.vue'

const router = useRouter()
const route  = useRoute()

const workOrders = ref([])
const meta       = ref(null)
const loading    = ref(true)

const filters = reactive({
  search: '',
  status: route.query.status || '',
  priority: '',
  page: 1,
})

const statusOptions = [
  { value: 'open', label: 'Open' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'on_hold', label: 'On Hold' },
  { value: 'closed', label: 'Closed' },
]

const priorityOptions = [
  { value: 'low', label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high', label: 'High' },
  { value: 'critical', label: 'Critical' },
]

onMounted(loadWorkOrders)

watch(filters, () => {
  filters.page = 1
  loadWorkOrders()
}, { deep: true })

async function loadWorkOrders() {
  loading.value = true
  try {
    const { data } = await workOrdersService.list(filters)
    workOrders.value = data.data
    meta.value       = data.meta
  } finally {
    loading.value = false
  }
}

function formatDate(d) { return d ? new Date(d).toLocaleDateString() : '—' }
function onPage(p) { filters.page = p; loadWorkOrders() }
</script>

<template>
  <div>
    <PageHeader title="Work Orders" subtitle="Track and manage maintenance tasks">
      <template #actions>
        <RouterLink to="/work-orders/create">
          <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Work Order
          </button>
        </RouterLink>
      </template>
    </PageHeader>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex flex-wrap gap-3">
      <input
        v-model="filters.search"
        placeholder="Search title or WO number…"
        class="flex-1 min-w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <select
        v-model="filters.status"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="">All Statuses</option>
        <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
      </select>
      <select
        v-model="filters.priority"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="">All Priorities</option>
        <option v-for="p in priorityOptions" :key="p.value" :value="p.value">{{ p.label }}</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400">Loading…</div>
      <template v-else>
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-5 py-3 font-medium text-gray-600">WO #</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Title</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Asset</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Assigned To</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Due Date</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Priority</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!workOrders.length">
              <td colspan="7" class="px-5 py-8 text-center text-gray-400">No work orders found.</td>
            </tr>
            <tr
              v-for="wo in workOrders"
              :key="wo.id"
              class="hover:bg-gray-50 cursor-pointer"
              @click="router.push(`/work-orders/${wo.id}`)"
            >
              <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ wo.wo_number }}</td>
              <td class="px-5 py-3 font-medium text-gray-900 max-w-xs truncate">{{ wo.title }}</td>
              <td class="px-5 py-3 text-gray-600">{{ wo.asset?.name || '—' }}</td>
              <td class="px-5 py-3 text-gray-600">{{ wo.assigned_to?.name || 'Unassigned' }}</td>
              <td class="px-5 py-3 text-gray-600">
                <span
                  :class="wo.due_date && new Date(wo.due_date) < new Date() && wo.status !== 'closed' ? 'text-red-600 font-medium' : ''"
                >
                  {{ formatDate(wo.due_date) }}
                </span>
              </td>
              <td class="px-5 py-3"><PriorityBadge :priority="wo.priority" /></td>
              <td class="px-5 py-3"><StatusBadge :status="wo.status" /></td>
            </tr>
          </tbody>
        </table>
        <div v-if="meta" class="px-5 py-4 border-t border-gray-100">
          <BasePagination :meta="meta" @page="onPage" />
        </div>
      </template>
    </div>
  </div>
</template>
