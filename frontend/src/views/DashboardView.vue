<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import dashboardService from '@/services/dashboard'
import StatusBadge from '@/components/common/StatusBadge.vue'
import PriorityBadge from '@/components/common/PriorityBadge.vue'

const router = useRouter()
const data   = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await dashboardService.get()
    data.value = res.data
  } finally {
    loading.value = false
  }
})

const statusCards = computed(() => {
  if (!data.value) return []
  const s = data.value.work_orders.by_status
  return [
    { label: 'Open',        value: s.open || 0,        color: 'bg-blue-500',   text: 'text-blue-600',   bg: 'bg-blue-50' },
    { label: 'In Progress', value: s.in_progress || 0, color: 'bg-yellow-500', text: 'text-yellow-600', bg: 'bg-yellow-50' },
    { label: 'On Hold',     value: s.on_hold || 0,     color: 'bg-orange-500', text: 'text-orange-600', bg: 'bg-orange-50' },
    { label: 'Closed',      value: s.closed || 0,      color: 'bg-green-500',  text: 'text-green-600',  bg: 'bg-green-50' },
  ]
})

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString() : '—'
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
      <p class="text-sm text-gray-500 mt-1">Overview of your maintenance operations</p>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div v-for="i in 4" :key="i" class="h-24 bg-gray-100 rounded-xl animate-pulse" />
    </div>

    <template v-else-if="data">
      <!-- Status cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div
          v-for="card in statusCards"
          :key="card.label"
          :class="['rounded-xl p-4 cursor-pointer hover:shadow-md transition-shadow', card.bg]"
          @click="router.push(`/work-orders?status=${card.label.toLowerCase().replace(' ', '_')}`)"
        >
          <div :class="['text-3xl font-bold', card.text]">{{ card.value }}</div>
          <div class="text-sm font-medium text-gray-600 mt-1">{{ card.label }}</div>
        </div>
      </div>

      <!-- Summary row -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
          <p class="text-sm text-gray-500">Total Assets</p>
          <p class="text-2xl font-bold text-gray-900">{{ data.assets.total }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
          <p class="text-sm text-gray-500">Overdue Work Orders</p>
          <p class="text-2xl font-bold" :class="data.work_orders.overdue > 0 ? 'text-red-600' : 'text-gray-900'">
            {{ data.work_orders.overdue }}
          </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
          <p class="text-sm text-gray-500">Low Stock Parts</p>
          <p class="text-2xl font-bold" :class="data.parts.low_stock.length > 0 ? 'text-orange-600' : 'text-gray-900'">
            {{ data.parts.low_stock.length }}
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Work Orders -->
        <div class="bg-white rounded-xl border border-gray-200">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900">Recent Work Orders</h2>
            <button class="text-sm text-blue-600 hover:underline" @click="router.push('/work-orders')">
              View all
            </button>
          </div>
          <div class="divide-y divide-gray-50">
            <div
              v-for="wo in data.recent_work_orders"
              :key="wo.id"
              class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 cursor-pointer"
              @click="router.push(`/work-orders/${wo.id}`)"
            >
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ wo.title }}</p>
                <p class="text-xs text-gray-500">{{ wo.wo_number }} · {{ wo.asset?.name || 'No asset' }}</p>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                <PriorityBadge :priority="wo.priority" />
                <StatusBadge :status="wo.status" />
              </div>
            </div>
            <p v-if="!data.recent_work_orders.length" class="px-5 py-4 text-sm text-gray-400">No work orders yet.</p>
          </div>
        </div>

        <!-- Meters Due + Low Stock -->
        <div class="space-y-6">
          <!-- Meters Due -->
          <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
              <h2 class="font-semibold text-gray-900">Meters Due for Maintenance</h2>
              <button class="text-sm text-blue-600 hover:underline" @click="router.push('/meters')">View all</button>
            </div>
            <div class="divide-y divide-gray-50">
              <div v-for="m in data.meters.due" :key="m.id" class="px-5 py-3">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ m.asset?.name }}</p>
                    <p class="text-xs text-gray-500">{{ m.name }} · {{ m.units_since_maintenance }} / {{ m.frequency }} {{ m.unit }}</p>
                  </div>
                  <div class="w-20">
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                      <div class="h-full bg-red-500 rounded-full" :style="{ width: Math.min(100, m.maintenance_progress) + '%' }" />
                    </div>
                    <p class="text-right text-xs text-red-600 mt-0.5">{{ m.maintenance_progress }}%</p>
                  </div>
                </div>
              </div>
              <p v-if="!data.meters.due.length" class="px-5 py-4 text-sm text-gray-400">No meters due.</p>
            </div>
          </div>

          <!-- Low Stock -->
          <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
              <h2 class="font-semibold text-gray-900">Low Stock Parts</h2>
              <button class="text-sm text-blue-600 hover:underline" @click="router.push('/parts')">View all</button>
            </div>
            <div class="divide-y divide-gray-50">
              <div v-for="p in data.parts.low_stock" :key="p.id" class="px-5 py-3 flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ p.name }}</p>
                  <p class="text-xs text-gray-500">{{ p.part_number }}</p>
                </div>
                <span class="text-sm font-semibold text-orange-600">
                  {{ p.quantity_on_hand }} / {{ p.minimum_quantity }} {{ p.unit }}
                </span>
              </div>
              <p v-if="!data.parts.low_stock.length" class="px-5 py-4 text-sm text-gray-400">All parts sufficiently stocked.</p>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
