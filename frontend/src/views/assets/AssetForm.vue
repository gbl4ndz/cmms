<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import assetsService from '@/services/assets'
import locationsService from '@/services/locations'
import areasService from '@/services/areas'
import contractorsService from '@/services/contractors'
import categoriesService from '@/services/categories'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseSelect from '@/components/common/BaseSelect.vue'
import BaseTextarea from '@/components/common/BaseTextarea.vue'

const router = useRouter()
const route  = useRoute()

const isEdit  = computed(() => !!route.params.id)
const loading = ref(false)
const saving  = ref(false)
const errors  = ref({})

const form = reactive({
  name: '', description: '', serial_number: '', model: '', manufacturer: '',
  purchase_date: '', warranty_expiry: '', status: 'active',
  contractor_id: '', location_id: '', area_id: '', category_id: '',
})

const locations   = ref([])
const areas       = ref([])
const contractors = ref([])
const categories  = ref([])

onMounted(async () => {
  loading.value = true
  const [locsRes, contsRes, catsRes] = await Promise.all([
    locationsService.list({ per_page: 100 }),
    contractorsService.list({ per_page: 100 }),
    categoriesService.list(),
  ])
  locations.value   = locsRes.data.data
  contractors.value = contsRes.data.data
  categories.value  = catsRes.data

  if (isEdit.value) {
    const { data } = await assetsService.get(route.params.id)
    Object.assign(form, {
      ...data,
      purchase_date:   data.purchase_date   ? data.purchase_date.substring(0, 10) : '',
      warranty_expiry: data.warranty_expiry ? data.warranty_expiry.substring(0, 10) : '',
      contractor_id:   data.contractor_id   || '',
      location_id:     data.location_id     || '',
      area_id:         data.area_id         || '',
      category_id:     data.category_id     || '',
    })
    if (form.location_id) loadAreas(form.location_id)
  }
  loading.value = false
})

async function loadAreas(locationId) {
  if (!locationId) { areas.value = []; return }
  const { data } = await areasService.list({ location_id: locationId, per_page: 100 })
  areas.value = data.data
}

async function submit() {
  saving.value = true
  errors.value = {}
  try {
    const payload = { ...form }
    if (!payload.contractor_id) delete payload.contractor_id
    if (!payload.location_id)   delete payload.location_id
    if (!payload.area_id)       delete payload.area_id
    if (!payload.category_id)   delete payload.category_id
    if (!payload.purchase_date) delete payload.purchase_date
    if (!payload.warranty_expiry) delete payload.warranty_expiry

    if (isEdit.value) {
      await assetsService.update(route.params.id, payload)
    } else {
      await assetsService.create(payload)
    }
    router.push('/assets')
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
  } finally {
    saving.value = false
  }
}

const statusOptions  = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'under_maintenance', label: 'Under Maintenance' },
  { value: 'retired', label: 'Retired' },
]

const toOptions = (arr, labelKey = 'name') =>
  arr.map(i => ({ value: String(i.id), label: i[labelKey] }))
</script>

<template>
  <div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
      <button class="text-gray-400 hover:text-gray-600" @click="router.back()">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Asset' : 'New Asset' }}</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-400">Loading…</div>
    <form v-else class="bg-white rounded-xl border border-gray-200 p-6 space-y-5" @submit.prevent="submit">
      <BaseInput v-model="form.name" label="Name" required :error="errors.name?.[0]" />
      <BaseTextarea v-model="form.description" label="Description" :rows="3" :error="errors.description?.[0]" />

      <div class="grid grid-cols-2 gap-4">
        <BaseInput v-model="form.serial_number" label="Serial Number" :error="errors.serial_number?.[0]" />
        <BaseInput v-model="form.model" label="Model" :error="errors.model?.[0]" />
        <BaseInput v-model="form.manufacturer" label="Manufacturer" :error="errors.manufacturer?.[0]" />
        <BaseSelect v-model="form.status" label="Status" :options="statusOptions" :error="errors.status?.[0]" />
        <BaseInput v-model="form.purchase_date" type="date" label="Purchase Date" :error="errors.purchase_date?.[0]" />
        <BaseInput v-model="form.warranty_expiry" type="date" label="Warranty Expiry" :error="errors.warranty_expiry?.[0]" />
      </div>

      <div class="border-t border-gray-100 pt-5 space-y-4">
        <h3 class="text-sm font-semibold text-gray-700">Assignment</h3>
        <div class="grid grid-cols-2 gap-4">
          <BaseSelect
            v-model="form.location_id"
            label="Location"
            :options="toOptions(locations)"
            :error="errors.location_id?.[0]"
            @update:model-value="val => { form.area_id = ''; loadAreas(val) }"
          />
          <BaseSelect
            v-model="form.area_id"
            label="Area"
            :options="toOptions(areas)"
            :error="errors.area_id?.[0]"
            placeholder="Select area…"
          />
          <BaseSelect
            v-model="form.contractor_id"
            label="Contractor"
            :options="toOptions(contractors)"
            :error="errors.contractor_id?.[0]"
          />
          <BaseSelect
            v-model="form.category_id"
            label="Category"
            :options="toOptions(categories)"
            :error="errors.category_id?.[0]"
          />
        </div>
      </div>

      <div class="flex gap-3 pt-2">
        <button
          type="submit"
          :disabled="saving"
          class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50"
        >
          {{ saving ? 'Saving…' : (isEdit ? 'Save Changes' : 'Create Asset') }}
        </button>
        <button
          type="button"
          class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50"
          @click="router.back()"
        >
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>
