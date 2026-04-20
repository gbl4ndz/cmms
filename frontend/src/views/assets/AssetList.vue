<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import assetsService from '@/services/assets'
import locationsService from '@/services/locations'
import PageHeader from '@/components/common/PageHeader.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import BasePagination from '@/components/common/BasePagination.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const router = useRouter()

const assets    = ref([])
const locations = ref([])
const meta      = ref(null)
const loading   = ref(true)
const deleting  = ref(null)
const confirmId = ref(null)

const filters = reactive({ search: '', location_id: '', status: '', page: 1 })

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'under_maintenance', label: 'Under Maintenance' },
  { value: 'retired', label: 'Retired' },
]

onMounted(async () => {
  const [, res] = await Promise.all([
    loadAssets(),
    locationsService.list({ per_page: 100 }),
  ])
  locations.value = res.data.data
})

async function loadAssets() {
  loading.value = true
  try {
    const { data } = await assetsService.list(filters)
    assets.value = data.data
    meta.value   = data.meta
  } finally {
    loading.value = false
  }
}

watch(filters, () => {
  filters.page = 1
  loadAssets()
}, { deep: true })

async function confirmDelete(id) { confirmId.value = id }

async function deleteAsset() {
  deleting.value = confirmId.value
  try {
    await assetsService.remove(confirmId.value)
    confirmId.value = null
    loadAssets()
  } finally {
    deleting.value = null
  }
}

function onPage(p) {
  filters.page = p
  loadAssets()
}
</script>

<template>
  <div>
    <PageHeader title="Assets" subtitle="All equipment and installations">
      <template #actions>
        <RouterLink to="/assets/create">
          <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Asset
          </button>
        </RouterLink>
      </template>
    </PageHeader>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex flex-wrap gap-3">
      <input
        v-model="filters.search"
        placeholder="Search assets…"
        class="flex-1 min-w-40 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <select
        v-model="filters.location_id"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="">All Locations</option>
        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
      </select>
      <select
        v-model="filters.status"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="">All Statuses</option>
        <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400">Loading…</div>
      <template v-else>
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Name</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Location / Area</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Category</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Status</th>
              <th class="px-5 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!assets.length">
              <td colspan="5" class="px-5 py-8 text-center text-gray-400">No assets found.</td>
            </tr>
            <tr
              v-for="asset in assets"
              :key="asset.id"
              class="hover:bg-gray-50 cursor-pointer"
              @click="router.push(`/assets/${asset.id}`)"
            >
              <td class="px-5 py-3">
                <p class="font-medium text-gray-900">{{ asset.name }}</p>
                <p class="text-xs text-gray-400">{{ asset.serial_number || '—' }}</p>
              </td>
              <td class="px-5 py-3 text-gray-600">
                {{ asset.location?.name || '—' }}
                <span v-if="asset.area"> · {{ asset.area.name }}</span>
              </td>
              <td class="px-5 py-3">
                <span
                  v-if="asset.category"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white"
                  :style="{ backgroundColor: asset.category.color }"
                >
                  {{ asset.category.name }}
                </span>
                <span v-else class="text-gray-400">—</span>
              </td>
              <td class="px-5 py-3"><StatusBadge :status="asset.status" /></td>
              <td class="px-5 py-3 text-right" @click.stop>
                <button
                  class="text-blue-600 hover:underline text-xs mr-3"
                  @click="router.push(`/assets/${asset.id}/edit`)"
                >Edit</button>
                <button
                  class="text-red-500 hover:underline text-xs"
                  @click="confirmDelete(asset.id)"
                >Delete</button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="meta" class="px-5 py-4 border-t border-gray-100">
          <BasePagination :meta="meta" @page="onPage" />
        </div>
      </template>
    </div>

    <ConfirmDialog
      v-if="confirmId"
      title="Delete Asset"
      message="This will permanently delete the asset and all related data."
      :loading="!!deleting"
      @confirm="deleteAsset"
      @cancel="confirmId = null"
    />
  </div>
</template>
