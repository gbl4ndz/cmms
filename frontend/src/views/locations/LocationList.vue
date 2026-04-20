<script setup>
import { ref, reactive, onMounted } from 'vue'
import locationsService from '@/services/locations'
import PageHeader from '@/components/common/PageHeader.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseTextarea from '@/components/common/BaseTextarea.vue'
import BasePagination from '@/components/common/BasePagination.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const locations = ref([])
const meta      = ref(null)
const loading   = ref(true)
const saving    = ref(false)
const deleting  = ref(null)
const confirmId = ref(null)
const showModal = ref(false)
const editItem  = ref(null)
const errors    = ref({})
const search    = ref('')

const form = reactive({ name: '', address: '', phone: '', email: '' })

onMounted(loadLocations)

async function loadLocations(page = 1) {
  loading.value = true
  try {
    const { data } = await locationsService.list({ page, search: search.value })
    locations.value = data.data
    meta.value      = data.meta
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editItem.value = null
  Object.assign(form, { name: '', address: '', phone: '', email: '' })
  errors.value   = {}
  showModal.value = true
}

function openEdit(item) {
  editItem.value = item
  Object.assign(form, item)
  errors.value   = {}
  showModal.value = true
}

async function save() {
  saving.value = true
  errors.value = {}
  try {
    if (editItem.value) {
      await locationsService.update(editItem.value.id, form)
    } else {
      await locationsService.create(form)
    }
    showModal.value = false
    loadLocations()
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
  } finally {
    saving.value = false
  }
}

async function deleteItem() {
  deleting.value = confirmId.value
  try {
    await locationsService.remove(confirmId.value)
    confirmId.value = null
    loadLocations()
  } finally {
    deleting.value = null
  }
}
</script>

<template>
  <div>
    <PageHeader title="Locations" subtitle="Physical sites and facilities">
      <template #actions>
        <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700" @click="openCreate">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          New Location
        </button>
      </template>
    </PageHeader>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div class="p-4 border-b border-gray-100">
        <input v-model="search" @input="loadLocations()" placeholder="Search locations…"
          class="w-full max-w-xs border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div v-if="loading" class="p-8 text-center text-gray-400">Loading…</div>
      <template v-else>
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Name</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Address</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Phone</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Email</th>
              <th class="px-5 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!locations.length"><td colspan="5" class="px-5 py-8 text-center text-gray-400">No locations found.</td></tr>
            <tr v-for="loc in locations" :key="loc.id" class="hover:bg-gray-50">
              <td class="px-5 py-3 font-medium text-gray-900">{{ loc.name }}</td>
              <td class="px-5 py-3 text-gray-500 max-w-xs truncate">{{ loc.address || '—' }}</td>
              <td class="px-5 py-3 text-gray-500">{{ loc.phone || '—' }}</td>
              <td class="px-5 py-3 text-gray-500">{{ loc.email || '—' }}</td>
              <td class="px-5 py-3 text-right">
                <button class="text-blue-600 hover:underline text-xs mr-3" @click="openEdit(loc)">Edit</button>
                <button class="text-red-500 hover:underline text-xs" @click="confirmId = loc.id">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="meta" class="px-5 py-4 border-t border-gray-100">
          <BasePagination :meta="meta" @page="loadLocations" />
        </div>
      </template>
    </div>

    <BaseModal v-if="showModal" :title="editItem ? 'Edit Location' : 'New Location'" @close="showModal = false">
      <form class="space-y-4" @submit.prevent="save">
        <BaseInput v-model="form.name" label="Name" required :error="errors.name?.[0]" />
        <BaseTextarea v-model="form.address" label="Address" :rows="2" :error="errors.address?.[0]" />
        <BaseInput v-model="form.phone" label="Phone" :error="errors.phone?.[0]" />
        <BaseInput v-model="form.email" type="email" label="Email" :error="errors.email?.[0]" />
      </form>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showModal = false">Cancel</button>
          <button :disabled="saving" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" @click="save">
            {{ saving ? 'Saving…' : 'Save' }}
          </button>
        </div>
      </template>
    </BaseModal>

    <ConfirmDialog v-if="confirmId" title="Delete Location" message="This will delete the location." :loading="!!deleting" @confirm="deleteItem" @cancel="confirmId = null" />
  </div>
</template>
