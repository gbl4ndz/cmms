<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import assetsService from '@/services/assets'
import StatusBadge from '@/components/common/StatusBadge.vue'
import FileUpload from '@/components/common/FileUpload.vue'

const router = useRouter()
const route  = useRoute()
const asset  = ref(null)
const loading = ref(true)
const uploading = ref(false)

onMounted(async () => {
  const { data } = await assetsService.get(route.params.id)
  asset.value   = data
  loading.value = false
})

function formatDate(d) { return d ? new Date(d).toLocaleDateString() : '—' }

async function deleteMedia(id) {
  await assetsService.deleteMedia(id)
  asset.value.media = asset.value.media.filter(m => m.id !== id)
}

async function uploadMedia({ file, collection }) {
  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('collection', collection)
    const { data } = await assetsService.uploadMedia(asset.value.id, formData)
    asset.value.media.push(data)
  } catch (e) {
    alert(e.response?.data?.message || 'Upload failed.')
  } finally {
    uploading.value = false
  }
}
</script>

<template>
  <div>
    <div class="flex items-center gap-3 mb-6">
      <button class="text-gray-400 hover:text-gray-600" @click="router.back()">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <h1 class="text-2xl font-bold text-gray-900">Asset Details</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-400">Loading…</div>
    <template v-else-if="asset">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main info -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h2 class="text-xl font-bold text-gray-900">{{ asset.name }}</h2>
                <p v-if="asset.description" class="text-gray-500 text-sm mt-1">{{ asset.description }}</p>
              </div>
              <div class="flex items-center gap-2">
                <StatusBadge :status="asset.status" />
                <RouterLink :to="`/assets/${asset.id}/edit`">
                  <button class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Edit</button>
                </RouterLink>
              </div>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm">
              <div><dt class="text-gray-500">Serial Number</dt><dd class="font-medium text-gray-900">{{ asset.serial_number || '—' }}</dd></div>
              <div><dt class="text-gray-500">Model</dt><dd class="font-medium text-gray-900">{{ asset.model || '—' }}</dd></div>
              <div><dt class="text-gray-500">Manufacturer</dt><dd class="font-medium text-gray-900">{{ asset.manufacturer || '—' }}</dd></div>
              <div><dt class="text-gray-500">Category</dt><dd class="font-medium text-gray-900">{{ asset.category?.name || '—' }}</dd></div>
              <div><dt class="text-gray-500">Purchase Date</dt><dd class="font-medium text-gray-900">{{ formatDate(asset.purchase_date) }}</dd></div>
              <div><dt class="text-gray-500">Warranty Expiry</dt><dd class="font-medium text-gray-900">{{ formatDate(asset.warranty_expiry) }}</dd></div>
              <div><dt class="text-gray-500">Location</dt><dd class="font-medium text-gray-900">{{ asset.location?.name || '—' }}</dd></div>
              <div><dt class="text-gray-500">Area</dt><dd class="font-medium text-gray-900">{{ asset.area?.name || '—' }}</dd></div>
              <div><dt class="text-gray-500">Contractor</dt><dd class="font-medium text-gray-900">{{ asset.contractor?.name || '—' }}</dd></div>
            </dl>
          </div>

          <!-- Media -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Files & Images</h3>
            <div v-if="asset.media?.length" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div
                v-for="m in asset.media"
                :key="m.id"
                class="border border-gray-200 rounded-lg overflow-hidden group relative"
              >
                <img v-if="m.is_image" :src="m.url" class="w-full h-28 object-cover" />
                <div v-else class="h-28 bg-gray-50 flex items-center justify-center">
                  <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                </div>
                <div class="p-2">
                  <p class="text-xs text-gray-600 truncate">{{ m.filename }}</p>
                </div>
                <button
                  class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs hidden group-hover:flex items-center justify-center"
                  @click="deleteMedia(m.id)"
                >×</button>
              </div>
            </div>
            <p v-else class="text-sm text-gray-400 mb-4">No files uploaded.</p>
            <div class="mt-4">
              <FileUpload
                label="Upload image, manual, or document"
                :loading="uploading"
                collection="documents"
                @upload="uploadMedia"
              />
            </div>
          </div>
        </div>

        <!-- Sidebar: meters + work orders -->
        <div class="space-y-6">
          <!-- Meters -->
          <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Meters</h3>
            <div v-if="asset.meters?.length" class="space-y-3">
              <div v-for="m in asset.meters" :key="m.id" class="text-sm">
                <p class="font-medium text-gray-800">{{ m.name }}</p>
                <p class="text-gray-500">{{ m.current_reading }} {{ m.unit }} · every {{ m.frequency }} {{ m.unit }}</p>
              </div>
            </div>
            <p v-else class="text-sm text-gray-400">No meters.</p>
          </div>

          <!-- Work Orders -->
          <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Work Orders</h3>
            <div v-if="asset.work_orders?.length" class="space-y-2">
              <RouterLink
                v-for="wo in asset.work_orders.slice(0, 5)"
                :key="wo.id"
                :to="`/work-orders/${wo.id}`"
                class="block text-sm text-blue-600 hover:underline"
              >
                {{ wo.wo_number }} — {{ wo.title }}
              </RouterLink>
            </div>
            <p v-else class="text-sm text-gray-400">No work orders.</p>
            <RouterLink :to="`/work-orders/create?asset_id=${asset.id}`" class="mt-3 block">
              <button class="w-full text-sm text-blue-600 border border-blue-200 rounded-lg py-2 hover:bg-blue-50">
                + New Work Order
              </button>
            </RouterLink>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
