<script setup>
import { ref, reactive, onMounted } from 'vue'
import metersService from '@/services/meters'
import assetsService from '@/services/assets'
import PageHeader from '@/components/common/PageHeader.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseSelect from '@/components/common/BaseSelect.vue'
import BasePagination from '@/components/common/BasePagination.vue'

const meters  = ref([])
const assets  = ref([])
const meta    = ref(null)
const loading = ref(true)
const saving  = ref(false)
const showCreateModal  = ref(false)
const showReadingModal = ref(false)
const selectedMeter    = ref(null)
const errors           = ref({})
const dueOnly          = ref(false)
const pmNotification   = ref(null)  // auto-created preventive WO alert

const form        = reactive({ asset_id: '', name: '', unit: '', frequency: '' })
const readingForm = reactive({ reading_value: '', notes: '' })

const unitOptions = ['hours', 'km', 'miles', 'cycles', 'days'].map(u => ({ value: u, label: u }))
const assetOptions = () => assets.value.map(a => ({ value: String(a.id), label: a.name }))

onMounted(async () => {
  const [, assetsRes] = await Promise.all([loadMeters(), assetsService.list({ per_page: 100 })])
  assets.value = assetsRes.data.data
})

async function loadMeters(page = 1) {
  loading.value = true
  const { data } = await metersService.list({ page, due: dueOnly.value ? 1 : undefined })
  meters.value = data.data
  meta.value   = data.meta
  loading.value = false
}

async function createMeter() {
  saving.value = true
  errors.value = {}
  try {
    await metersService.create(form)
    showCreateModal.value = false
    loadMeters()
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
  } finally {
    saving.value = false
  }
}

function openReading(meter) {
  selectedMeter.value = meter
  readingForm.reading_value = ''
  readingForm.notes = ''
  showReadingModal.value = true
}

async function submitReading() {
  saving.value = true
  try {
    const { data } = await metersService.addReading(selectedMeter.value.id, readingForm)
    showReadingModal.value = false
    if (data.preventive_wo_created) {
      pmNotification.value = data.preventive_wo_created
    }
    loadMeters()
  } catch (e) {
    alert(e.response?.data?.errors?.reading_value?.[0] || 'Failed to save reading.')
  } finally {
    saving.value = false
  }
}

async function resetBaseline(meter) {
  if (!confirm(`Reset maintenance baseline for "${meter.name}"?`)) return
  await metersService.resetBaseline(meter.id)
  loadMeters()
}
</script>

<template>
  <div>
    <!-- Preventive WO auto-created notification -->
    <div
      v-if="pmNotification"
      class="mb-4 flex items-center justify-between bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3 text-sm"
    >
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Preventive work order <strong>{{ pmNotification.wo_number }}</strong> auto-created: {{ pmNotification.title }}</span>
      </div>
      <div class="flex items-center gap-3">
        <RouterLink :to="`/work-orders/${pmNotification.id}`" class="font-medium underline">View WO</RouterLink>
        <button class="text-green-600 hover:text-green-800 font-bold" @click="pmNotification = null">×</button>
      </div>
    </div>

    <PageHeader title="Meters" subtitle="Track usage for preventive maintenance">
      <template #actions>
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
          <input type="checkbox" v-model="dueOnly" @change="loadMeters()" class="rounded" />
          Due only
        </label>
        <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700" @click="showCreateModal = true">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          New Meter
        </button>
      </template>
    </PageHeader>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400">Loading…</div>
      <template v-else>
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Meter</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Asset</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Current</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Frequency</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Progress</th>
              <th class="px-5 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!meters.length"><td colspan="6" class="px-5 py-8 text-center text-gray-400">No meters found.</td></tr>
            <tr v-for="m in meters" :key="m.id" :class="m.maintenance_progress >= 100 ? 'bg-red-50' : 'hover:bg-gray-50'">
              <td class="px-5 py-3 font-medium text-gray-900">{{ m.name }}</td>
              <td class="px-5 py-3 text-gray-600">{{ m.asset?.name || '—' }}</td>
              <td class="px-5 py-3 text-gray-600">{{ m.current_reading }} {{ m.unit }}</td>
              <td class="px-5 py-3 text-gray-600">Every {{ m.frequency }} {{ m.unit }}</td>
              <td class="px-5 py-3 w-36">
                <div class="flex items-center gap-2">
                  <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all"
                      :class="m.maintenance_progress >= 100 ? 'bg-red-500' : m.maintenance_progress >= 75 ? 'bg-orange-400' : 'bg-green-400'"
                      :style="{ width: Math.min(100, m.maintenance_progress) + '%' }"
                    />
                  </div>
                  <span class="text-xs font-medium" :class="m.maintenance_progress >= 100 ? 'text-red-600' : 'text-gray-500'">
                    {{ m.maintenance_progress }}%
                  </span>
                </div>
              </td>
              <td class="px-5 py-3 text-right space-x-2">
                <button class="text-blue-600 hover:underline text-xs" @click="openReading(m)">Add Reading</button>
                <button
                  v-if="m.maintenance_progress >= 100"
                  class="text-green-600 hover:underline text-xs"
                  @click="resetBaseline(m)"
                >
                  Reset
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="meta" class="px-5 py-4 border-t border-gray-100"><BasePagination :meta="meta" @page="loadMeters" /></div>
      </template>
    </div>

    <!-- Create Meter Modal -->
    <BaseModal v-if="showCreateModal" title="New Meter" @close="showCreateModal = false">
      <form class="space-y-4" @submit.prevent="createMeter">
        <BaseSelect v-model="form.asset_id" label="Asset" required :options="assetOptions()" :error="errors.asset_id?.[0]" />
        <BaseInput v-model="form.name" label="Meter Name" required placeholder="e.g. Engine Hours" :error="errors.name?.[0]" />
        <div class="grid grid-cols-2 gap-3">
          <BaseSelect v-model="form.unit" label="Unit" required :options="unitOptions" :error="errors.unit?.[0]" />
          <BaseInput v-model.number="form.frequency" type="number" label="Maintenance Interval" required placeholder="e.g. 500" :error="errors.frequency?.[0]" />
        </div>
      </form>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showCreateModal = false">Cancel</button>
          <button :disabled="saving" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" @click="createMeter">{{ saving ? 'Saving…' : 'Create' }}</button>
        </div>
      </template>
    </BaseModal>

    <!-- Add Reading Modal -->
    <BaseModal v-if="showReadingModal" :title="`Add Reading — ${selectedMeter?.name}`" @close="showReadingModal = false">
      <form class="space-y-4" @submit.prevent="submitReading">
        <BaseInput v-model.number="readingForm.reading_value" type="number" label="Reading Value" required :placeholder="`Current: ${selectedMeter?.current_reading} ${selectedMeter?.unit}`" />
        <BaseInput v-model="readingForm.notes" label="Notes (optional)" />
      </form>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showReadingModal = false">Cancel</button>
          <button :disabled="saving" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" @click="submitReading">{{ saving ? 'Saving…' : 'Save Reading' }}</button>
        </div>
      </template>
    </BaseModal>
  </div>
</template>
