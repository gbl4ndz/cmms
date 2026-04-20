<script setup>
import { ref, reactive, onMounted } from 'vue'
import areasService from '@/services/areas'
import locationsService from '@/services/locations'
import PageHeader from '@/components/common/PageHeader.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseSelect from '@/components/common/BaseSelect.vue'
import BasePagination from '@/components/common/BasePagination.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const areas     = ref([])
const locations = ref([])
const meta      = ref(null)
const loading   = ref(true)
const saving    = ref(false)
const deleting  = ref(null)
const confirmId = ref(null)
const showModal = ref(false)
const editItem  = ref(null)
const errors    = ref({})

const form = reactive({ location_id: '', name: '', area_code: '' })

onMounted(async () => {
  const [, locsRes] = await Promise.all([loadAreas(), locationsService.list({ per_page: 100 })])
  locations.value = locsRes.data.data
})

async function loadAreas(page = 1) {
  loading.value = true
  const { data } = await areasService.list({ page })
  areas.value = data.data
  meta.value  = data.meta
  loading.value = false
}

const locationOptions = () => locations.value.map(l => ({ value: String(l.id), label: l.name }))

function openCreate() {
  editItem.value = null
  Object.assign(form, { location_id: '', name: '', area_code: '' })
  errors.value = {}
  showModal.value = true
}

function openEdit(item) {
  editItem.value = item
  Object.assign(form, { location_id: String(item.location_id), name: item.name, area_code: item.area_code || '' })
  errors.value = {}
  showModal.value = true
}

async function save() {
  saving.value = true
  errors.value = {}
  try {
    if (editItem.value) {
      await areasService.update(editItem.value.id, form)
    } else {
      await areasService.create(form)
    }
    showModal.value = false
    loadAreas()
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
  } finally {
    saving.value = false
  }
}

async function deleteItem() {
  deleting.value = confirmId.value
  try { await areasService.remove(confirmId.value); confirmId.value = null; loadAreas() }
  finally { deleting.value = null }
}
</script>

<template>
  <div>
    <PageHeader title="Areas" subtitle="Zones within locations">
      <template #actions>
        <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700" @click="openCreate">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          New Area
        </button>
      </template>
    </PageHeader>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400">Loading…</div>
      <template v-else>
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Name</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Location</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Area Code</th>
              <th class="px-5 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!areas.length"><td colspan="4" class="px-5 py-8 text-center text-gray-400">No areas found.</td></tr>
            <tr v-for="area in areas" :key="area.id" class="hover:bg-gray-50">
              <td class="px-5 py-3 font-medium text-gray-900">{{ area.name }}</td>
              <td class="px-5 py-3 text-gray-500">{{ area.location?.name || '—' }}</td>
              <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ area.area_code || '—' }}</td>
              <td class="px-5 py-3 text-right">
                <button class="text-blue-600 hover:underline text-xs mr-3" @click="openEdit(area)">Edit</button>
                <button class="text-red-500 hover:underline text-xs" @click="confirmId = area.id">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="meta" class="px-5 py-4 border-t border-gray-100"><BasePagination :meta="meta" @page="loadAreas" /></div>
      </template>
    </div>

    <BaseModal v-if="showModal" :title="editItem ? 'Edit Area' : 'New Area'" @close="showModal = false">
      <form class="space-y-4" @submit.prevent="save">
        <BaseSelect v-model="form.location_id" label="Location" required :options="locationOptions()" :error="errors.location_id?.[0]" />
        <BaseInput v-model="form.name" label="Name" required :error="errors.name?.[0]" />
        <BaseInput v-model="form.area_code" label="Area Code" :error="errors.area_code?.[0]" />
      </form>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showModal = false">Cancel</button>
          <button :disabled="saving" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" @click="save">{{ saving ? 'Saving…' : 'Save' }}</button>
        </div>
      </template>
    </BaseModal>

    <ConfirmDialog v-if="confirmId" title="Delete Area" message="This will delete the area." :loading="!!deleting" @confirm="deleteItem" @cancel="confirmId = null" />
  </div>
</template>
