<script setup>
import { ref, reactive, onMounted } from 'vue'
import contractorsService from '@/services/contractors'
import PageHeader from '@/components/common/PageHeader.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseTextarea from '@/components/common/BaseTextarea.vue'
import BasePagination from '@/components/common/BasePagination.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const contractors = ref([])
const meta        = ref(null)
const loading     = ref(true)
const saving      = ref(false)
const deleting    = ref(null)
const confirmId   = ref(null)
const showModal   = ref(false)
const editItem    = ref(null)
const errors      = ref({})

const form = reactive({ name: '', address: '', phone: '', email: '', point_of_contact: '' })

onMounted(loadContractors)

async function loadContractors(page = 1) {
  loading.value = true
  const { data } = await contractorsService.list({ page })
  contractors.value = data.data
  meta.value        = data.meta
  loading.value = false
}

function openCreate() {
  editItem.value = null
  Object.assign(form, { name: '', address: '', phone: '', email: '', point_of_contact: '' })
  errors.value = {}
  showModal.value = true
}

function openEdit(item) {
  editItem.value = item
  Object.assign(form, item)
  errors.value = {}
  showModal.value = true
}

async function save() {
  saving.value = true
  errors.value = {}
  try {
    editItem.value ? await contractorsService.update(editItem.value.id, form) : await contractorsService.create(form)
    showModal.value = false
    loadContractors()
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
  } finally {
    saving.value = false
  }
}

async function deleteItem() {
  deleting.value = confirmId.value
  try { await contractorsService.remove(confirmId.value); confirmId.value = null; loadContractors() }
  finally { deleting.value = null }
}
</script>

<template>
  <div>
    <PageHeader title="Contractors" subtitle="External service providers">
      <template #actions>
        <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700" @click="openCreate">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          New Contractor
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
              <th class="text-left px-5 py-3 font-medium text-gray-600">Contact Person</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Phone</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Email</th>
              <th class="px-5 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!contractors.length"><td colspan="5" class="px-5 py-8 text-center text-gray-400">No contractors found.</td></tr>
            <tr v-for="c in contractors" :key="c.id" class="hover:bg-gray-50">
              <td class="px-5 py-3 font-medium text-gray-900">{{ c.name }}</td>
              <td class="px-5 py-3 text-gray-500">{{ c.point_of_contact || '—' }}</td>
              <td class="px-5 py-3 text-gray-500">{{ c.phone || '—' }}</td>
              <td class="px-5 py-3 text-gray-500">{{ c.email || '—' }}</td>
              <td class="px-5 py-3 text-right">
                <button class="text-blue-600 hover:underline text-xs mr-3" @click="openEdit(c)">Edit</button>
                <button class="text-red-500 hover:underline text-xs" @click="confirmId = c.id">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="meta" class="px-5 py-4 border-t border-gray-100"><BasePagination :meta="meta" @page="loadContractors" /></div>
      </template>
    </div>

    <BaseModal v-if="showModal" :title="editItem ? 'Edit Contractor' : 'New Contractor'" @close="showModal = false">
      <form class="space-y-4" @submit.prevent="save">
        <BaseInput v-model="form.name" label="Name" required :error="errors.name?.[0]" />
        <BaseInput v-model="form.point_of_contact" label="Point of Contact" :error="errors.point_of_contact?.[0]" />
        <BaseTextarea v-model="form.address" label="Address" :rows="2" :error="errors.address?.[0]" />
        <div class="grid grid-cols-2 gap-3">
          <BaseInput v-model="form.phone" label="Phone" :error="errors.phone?.[0]" />
          <BaseInput v-model="form.email" type="email" label="Email" :error="errors.email?.[0]" />
        </div>
      </form>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showModal = false">Cancel</button>
          <button :disabled="saving" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" @click="save">{{ saving ? 'Saving…' : 'Save' }}</button>
        </div>
      </template>
    </BaseModal>

    <ConfirmDialog v-if="confirmId" title="Delete Contractor" message="This will delete the contractor." :loading="!!deleting" @confirm="deleteItem" @cancel="confirmId = null" />
  </div>
</template>
