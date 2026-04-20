<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import workOrdersService from '@/services/workOrders'
import assetsService from '@/services/assets'
import usersService from '@/services/users'
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
  title: '', description: '', priority: 'medium', type: 'corrective',
  asset_id: route.query.asset_id || '',
  assigned_to: '', due_date: '', estimated_hours: '',
})

const assets = ref([])
const users  = ref([])

onMounted(async () => {
  loading.value = true
  const [assetsRes, usersRes] = await Promise.all([
    assetsService.list({ per_page: 100 }),
    usersService.list({ per_page: 100 }),
  ])
  assets.value = assetsRes.data.data
  users.value  = usersRes.data.data

  if (isEdit.value) {
    const { data } = await workOrdersService.get(route.params.id)
    Object.assign(form, {
      title: data.title, description: data.description,
      priority: data.priority, type: data.type,
      asset_id:   data.asset_id   ? String(data.asset_id)   : '',
      assigned_to: data.assigned_to ? String(data.assigned_to) : '',
      due_date:    data.due_date   ? data.due_date.substring(0, 10) : '',
      estimated_hours: data.estimated_hours || '',
    })
  }
  loading.value = false
})

async function submit() {
  saving.value = true
  errors.value = {}
  try {
    const payload = { ...form }
    if (!payload.asset_id)    delete payload.asset_id
    if (!payload.assigned_to) delete payload.assigned_to
    if (!payload.due_date)    delete payload.due_date
    if (!payload.estimated_hours) delete payload.estimated_hours

    if (isEdit.value) {
      await workOrdersService.update(route.params.id, payload)
    } else {
      await workOrdersService.create(payload)
    }
    router.push('/work-orders')
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
  } finally {
    saving.value = false
  }
}

const priorityOptions = [
  { value: 'low', label: 'Low' }, { value: 'medium', label: 'Medium' },
  { value: 'high', label: 'High' }, { value: 'critical', label: 'Critical' },
]
const typeOptions = [
  { value: 'corrective', label: 'Corrective' }, { value: 'preventive', label: 'Preventive' },
  { value: 'inspection', label: 'Inspection' }, { value: 'emergency', label: 'Emergency' },
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
      <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Edit Work Order' : 'New Work Order' }}</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-400">Loading…</div>
    <form v-else class="bg-white rounded-xl border border-gray-200 p-6 space-y-5" @submit.prevent="submit">
      <BaseInput v-model="form.title" label="Title" required :error="errors.title?.[0]" />
      <BaseTextarea v-model="form.description" label="Description" :rows="4" :error="errors.description?.[0]" />

      <div class="grid grid-cols-2 gap-4">
        <BaseSelect v-model="form.priority" label="Priority" required :options="priorityOptions" :error="errors.priority?.[0]" />
        <BaseSelect v-model="form.type" label="Type" required :options="typeOptions" :error="errors.type?.[0]" />
        <BaseSelect v-model="form.asset_id" label="Asset" :options="toOptions(assets)" :error="errors.asset_id?.[0]" />
        <BaseSelect v-model="form.assigned_to" label="Assign To" :options="toOptions(users)" :error="errors.assigned_to?.[0]" />
        <BaseInput v-model="form.due_date" type="date" label="Due Date" :error="errors.due_date?.[0]" />
        <BaseInput v-model="form.estimated_hours" type="number" label="Est. Hours" placeholder="0" :error="errors.estimated_hours?.[0]" />
      </div>

      <div class="flex gap-3 pt-2">
        <button type="submit" :disabled="saving" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
          {{ saving ? 'Saving…' : (isEdit ? 'Save Changes' : 'Create Work Order') }}
        </button>
        <button type="button" class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50" @click="router.back()">
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>
