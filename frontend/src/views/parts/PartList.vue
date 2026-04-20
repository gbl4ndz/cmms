<script setup>
import { ref, reactive, onMounted } from 'vue'
import partsService from '@/services/parts'
import categoriesService from '@/services/categories'
import PageHeader from '@/components/common/PageHeader.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseSelect from '@/components/common/BaseSelect.vue'
import BaseTextarea from '@/components/common/BaseTextarea.vue'
import BasePagination from '@/components/common/BasePagination.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const parts      = ref([])
const categories = ref([])
const meta       = ref(null)
const loading    = ref(true)
const saving     = ref(false)
const deleting   = ref(null)
const confirmId  = ref(null)
const showModal  = ref(false)
const editItem   = ref(null)
const errors     = ref({})
const lowStock   = ref(false)

const form = reactive({
  name: '', part_number: '', description: '', unit: 'pcs',
  unit_cost: '', quantity_on_hand: 0, minimum_quantity: 0, category_id: '',
})

const unitOptions     = ['pcs', 'kg', 'liters', 'meters', 'pairs', 'sets'].map(u => ({ value: u, label: u }))
const categoryOptions = () => categories.value.map(c => ({ value: String(c.id), label: c.name }))

onMounted(async () => {
  const [, catsRes] = await Promise.all([loadParts(), categoriesService.list()])
  categories.value = catsRes.data
})

async function loadParts(page = 1) {
  loading.value = true
  const { data } = await partsService.list({ page, low_stock: lowStock.value ? 1 : undefined })
  parts.value = data.data
  meta.value  = data.meta
  loading.value = false
}

function openCreate() {
  editItem.value = null
  Object.assign(form, { name: '', part_number: '', description: '', unit: 'pcs', unit_cost: '', quantity_on_hand: 0, minimum_quantity: 0, category_id: '' })
  errors.value = {}
  showModal.value = true
}

function openEdit(item) {
  editItem.value = item
  Object.assign(form, { ...item, category_id: item.category_id ? String(item.category_id) : '' })
  errors.value = {}
  showModal.value = true
}

async function save() {
  saving.value = true
  errors.value = {}
  try {
    editItem.value ? await partsService.update(editItem.value.id, form) : await partsService.create(form)
    showModal.value = false
    loadParts()
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
  } finally {
    saving.value = false
  }
}

async function deleteItem() {
  deleting.value = confirmId.value
  try { await partsService.remove(confirmId.value); confirmId.value = null; loadParts() }
  finally { deleting.value = null }
}
</script>

<template>
  <div>
    <PageHeader title="Parts & Inventory" subtitle="Spare parts and materials">
      <template #actions>
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
          <input type="checkbox" v-model="lowStock" @change="loadParts()" class="rounded" />
          Low stock only
        </label>
        <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700" @click="openCreate">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          New Part
        </button>
      </template>
    </PageHeader>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400">Loading…</div>
      <template v-else>
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Part</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Category</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Unit Cost</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">In Stock</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Min</th>
              <th class="px-5 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!parts.length"><td colspan="6" class="px-5 py-8 text-center text-gray-400">No parts found.</td></tr>
            <tr v-for="p in parts" :key="p.id" :class="['hover:bg-gray-50', p.quantity_on_hand <= p.minimum_quantity ? 'bg-orange-50' : '']">
              <td class="px-5 py-3">
                <p class="font-medium text-gray-900">{{ p.name }}</p>
                <p class="text-xs text-gray-400 font-mono">{{ p.part_number || '—' }}</p>
              </td>
              <td class="px-5 py-3">
                <span v-if="p.category" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white" :style="{ backgroundColor: p.category.color }">{{ p.category.name }}</span>
                <span v-else class="text-gray-400">—</span>
              </td>
              <td class="px-5 py-3 text-gray-600">${{ p.unit_cost }}</td>
              <td class="px-5 py-3">
                <span :class="['font-medium', p.quantity_on_hand <= p.minimum_quantity ? 'text-orange-600' : 'text-gray-900']">
                  {{ p.quantity_on_hand }} {{ p.unit }}
                </span>
              </td>
              <td class="px-5 py-3 text-gray-500">{{ p.minimum_quantity }}</td>
              <td class="px-5 py-3 text-right">
                <button class="text-blue-600 hover:underline text-xs mr-3" @click="openEdit(p)">Edit</button>
                <button class="text-red-500 hover:underline text-xs" @click="confirmId = p.id">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="meta" class="px-5 py-4 border-t border-gray-100"><BasePagination :meta="meta" @page="loadParts" /></div>
      </template>
    </div>

    <BaseModal v-if="showModal" :title="editItem ? 'Edit Part' : 'New Part'" @close="showModal = false">
      <form class="space-y-4" @submit.prevent="save">
        <div class="grid grid-cols-2 gap-3">
          <BaseInput v-model="form.name" label="Name" required :error="errors.name?.[0]" />
          <BaseInput v-model="form.part_number" label="Part Number" :error="errors.part_number?.[0]" />
        </div>
        <BaseTextarea v-model="form.description" label="Description" :rows="2" />
        <div class="grid grid-cols-2 gap-3">
          <BaseSelect v-model="form.unit" label="Unit" :options="unitOptions" :error="errors.unit?.[0]" />
          <BaseInput v-model="form.unit_cost" type="number" label="Unit Cost ($)" :error="errors.unit_cost?.[0]" />
          <BaseInput v-model.number="form.quantity_on_hand" type="number" label="Qty on Hand" :error="errors.quantity_on_hand?.[0]" />
          <BaseInput v-model.number="form.minimum_quantity" type="number" label="Minimum Qty" :error="errors.minimum_quantity?.[0]" />
        </div>
        <BaseSelect v-model="form.category_id" label="Category" :options="categoryOptions()" />
      </form>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showModal = false">Cancel</button>
          <button :disabled="saving" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" @click="save">{{ saving ? 'Saving…' : 'Save' }}</button>
        </div>
      </template>
    </BaseModal>

    <ConfirmDialog v-if="confirmId" title="Delete Part" message="This will delete the part." :loading="!!deleting" @confirm="deleteItem" @cancel="confirmId = null" />
  </div>
</template>
