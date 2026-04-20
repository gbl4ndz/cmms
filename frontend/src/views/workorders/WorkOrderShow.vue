<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import workOrdersService from '@/services/workOrders'
import partsService from '@/services/parts'
import StatusBadge from '@/components/common/StatusBadge.vue'
import PriorityBadge from '@/components/common/PriorityBadge.vue'
import FileUpload from '@/components/common/FileUpload.vue'

const router    = useRouter()
const route     = useRoute()
const wo        = ref(null)
const loading   = ref(true)
const uploading = ref(false)

// Comment form
const comment = ref('')
const sendingComment = ref(false)

// Status change
const statusForm = ref({ status: '', comment: '' })
const changingStatus = ref(false)
const showStatusModal = ref(false)

// Add part form
const partSearch  = ref('')
const allParts    = ref([])
const partQty     = ref(1)
const selectedPart = ref(null)
const addingPart  = ref(false)
const showPartModal = ref(false)
const partError   = ref('')

onMounted(async () => {
  await loadWo()
  const { data } = await partsService.list({ per_page: 100 })
  allParts.value = data.data
})

async function loadWo() {
  loading.value = true
  const { data } = await workOrdersService.get(route.params.id)
  wo.value = data
  loading.value = false
}

async function submitComment() {
  if (!comment.value.trim()) return
  sendingComment.value = true
  try {
    const { data } = await workOrdersService.addComment(wo.value.id, { comment: comment.value })
    wo.value.updates.unshift(data)
    comment.value = ''
  } finally {
    sendingComment.value = false
  }
}

const allowedTransitions = computed(() => {
  const map = { open: ['in_progress'], in_progress: ['on_hold', 'closed'], on_hold: ['in_progress', 'closed'], closed: [] }
  return map[wo.value?.status] || []
})

async function changeStatus() {
  changingStatus.value = true
  try {
    const res = await workOrdersService.updateStatus(wo.value.id, statusForm.value)
    wo.value.status  = statusForm.value.status
    wo.value.updates = res.data.updates
    showStatusModal.value = false
    statusForm.value = { status: '', comment: '' }
  } catch (e) {
    alert(e.response?.data?.errors?.status?.[0] || 'Failed to update status.')
  } finally {
    changingStatus.value = false
  }
}

const filteredParts = computed(() =>
  partSearch.value
    ? allParts.value.filter(p => p.name.toLowerCase().includes(partSearch.value.toLowerCase()))
    : allParts.value
)

async function addPart() {
  if (!selectedPart.value) return
  partError.value = ''
  addingPart.value = true
  try {
    const { data } = await workOrdersService.addPart(wo.value.id, {
      part_id: selectedPart.value.id,
      quantity: partQty.value,
    })
    wo.value.parts.push(data)
    showPartModal.value = false
    selectedPart.value = null
    partQty.value = 1
  } catch (e) {
    partError.value = e.response?.data?.errors?.quantity?.[0] || 'Failed to add part.'
  } finally {
    addingPart.value = false
  }
}

async function removePart(partId) {
  await workOrdersService.removePart(wo.value.id, partId)
  wo.value.parts = wo.value.parts.filter(p => p.id !== partId)
}

const totalPartsCost = computed(() =>
  wo.value?.parts?.reduce((sum, p) => sum + parseFloat(p.total_cost || 0), 0).toFixed(2)
)

async function uploadMedia({ file, collection }) {
  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('collection', collection)
    const { data } = await workOrdersService.uploadMedia(wo.value.id, formData)
    wo.value.media = wo.value.media || []
    wo.value.media.push(data)
  } catch (e) {
    alert(e.response?.data?.message || 'Upload failed.')
  } finally {
    uploading.value = false
  }
}

function formatDate(d) { return d ? new Date(d).toLocaleString() : '—' }
function formatShort(d) { return d ? new Date(d).toLocaleDateString() : '—' }
</script>

<template>
  <div>
    <div class="flex items-center gap-3 mb-6">
      <button class="text-gray-400 hover:text-gray-600" @click="router.back()">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <h1 class="text-2xl font-bold text-gray-900">Work Order Detail</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-400">Loading…</div>
    <template v-else-if="wo">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main column -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Header card -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
              <div>
                <p class="text-xs font-mono text-gray-400 mb-1">{{ wo.wo_number }}</p>
                <h2 class="text-xl font-bold text-gray-900">{{ wo.title }}</h2>
                <p v-if="wo.description" class="text-sm text-gray-500 mt-2">{{ wo.description }}</p>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <PriorityBadge :priority="wo.priority" />
                <StatusBadge :status="wo.status" />
              </div>
            </div>

            <dl class="grid grid-cols-2 gap-3 text-sm">
              <div><dt class="text-gray-500">Asset</dt><dd class="font-medium">{{ wo.asset?.name || '—' }}</dd></div>
              <div><dt class="text-gray-500">Type</dt><dd class="font-medium capitalize">{{ wo.type }}</dd></div>
              <div><dt class="text-gray-500">Assigned To</dt><dd class="font-medium">{{ wo.assigned_to?.name || 'Unassigned' }}</dd></div>
              <div><dt class="text-gray-500">Created By</dt><dd class="font-medium">{{ wo.created_by?.name }}</dd></div>
              <div><dt class="text-gray-500">Due Date</dt>
                <dd :class="['font-medium', wo.due_date && new Date(wo.due_date) < new Date() && wo.status !== 'closed' ? 'text-red-600' : '']">
                  {{ formatShort(wo.due_date) }}
                </dd>
              </div>
              <div><dt class="text-gray-500">Est. Hours</dt><dd class="font-medium">{{ wo.estimated_hours || '—' }}</dd></div>
              <div><dt class="text-gray-500">Started</dt><dd class="font-medium">{{ formatShort(wo.started_at) }}</dd></div>
              <div><dt class="text-gray-500">Completed</dt><dd class="font-medium">{{ formatShort(wo.completed_at) }}</dd></div>
            </dl>

            <!-- Status actions -->
            <div v-if="allowedTransitions.length" class="mt-4 pt-4 border-t border-gray-100 flex gap-2">
              <button
                v-for="s in allowedTransitions"
                :key="s"
                class="px-3 py-1.5 text-sm font-medium border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50"
                @click="statusForm.status = s; showStatusModal = true"
              >
                Move to {{ s.replace('_', ' ') }}
              </button>
            </div>
          </div>

          <!-- Parts used -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-900">Parts Used</h3>
              <button
                v-if="wo.status !== 'closed'"
                class="text-sm text-blue-600 border border-blue-200 rounded-lg px-3 py-1 hover:bg-blue-50"
                @click="showPartModal = true"
              >
                + Add Part
              </button>
            </div>
            <table v-if="wo.parts?.length" class="min-w-full text-sm">
              <thead class="text-left text-gray-500 border-b border-gray-100">
                <tr>
                  <th class="pb-2 font-medium">Part</th>
                  <th class="pb-2 font-medium">Qty</th>
                  <th class="pb-2 font-medium">Unit Cost</th>
                  <th class="pb-2 font-medium">Total</th>
                  <th class="pb-2" />
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="p in wo.parts" :key="p.id">
                  <td class="py-2">{{ p.part?.name }}</td>
                  <td class="py-2">{{ p.quantity }} {{ p.part?.unit }}</td>
                  <td class="py-2">${{ p.unit_cost }}</td>
                  <td class="py-2 font-medium">${{ p.total_cost }}</td>
                  <td class="py-2 text-right">
                    <button v-if="wo.status !== 'closed'" class="text-red-400 hover:text-red-600 text-xs" @click="removePart(p.id)">Remove</button>
                  </td>
                </tr>
              </tbody>
              <tfoot class="border-t border-gray-200">
                <tr>
                  <td colspan="3" class="pt-2 text-sm text-gray-500 font-medium">Total Parts Cost</td>
                  <td class="pt-2 font-bold text-gray-900">${{ totalPartsCost }}</td>
                  <td />
                </tr>
              </tfoot>
            </table>
            <p v-else class="text-sm text-gray-400">No parts added yet.</p>
          </div>

          <!-- Activity / Comments -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Activity</h3>
            <div class="space-y-4 mb-5">
              <div v-for="u in wo.updates" :key="u.id" class="flex gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 text-sm font-medium flex-shrink-0">
                  {{ u.user?.name?.charAt(0) }}
                </div>
                <div class="flex-1 bg-gray-50 rounded-lg p-3">
                  <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                    <span class="font-medium text-gray-700">{{ u.user?.name }}</span>
                    <span>{{ formatDate(u.created_at) }}</span>
                  </div>
                  <p class="text-sm text-gray-700">{{ u.comment }}</p>
                  <div v-if="u.type === 'status_change'" class="mt-1 text-xs text-gray-400">
                    Status: <span class="text-gray-600 font-medium">{{ u.status_from }}</span>
                    → <span class="text-gray-600 font-medium">{{ u.status_to }}</span>
                  </div>
                </div>
              </div>
              <p v-if="!wo.updates?.length" class="text-sm text-gray-400">No activity yet.</p>
            </div>

            <!-- Comment box -->
            <div class="border-t border-gray-100 pt-4">
              <textarea
                v-model="comment"
                rows="3"
                placeholder="Add a comment…"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <button
                :disabled="!comment.trim() || sendingComment"
                class="mt-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50"
                @click="submitComment"
              >
                {{ sendingComment ? 'Sending…' : 'Add Comment' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Details</h3>
            <dl class="space-y-2 text-sm">
              <div><dt class="text-gray-500">Location</dt><dd class="font-medium">{{ wo.asset?.location?.name || '—' }}</dd></div>
              <div><dt class="text-gray-500">Area</dt><dd class="font-medium">{{ wo.asset?.area?.name || '—' }}</dd></div>
            </dl>
            <div class="mt-4 pt-4 border-t border-gray-100 space-y-3">
              <RouterLink :to="`/work-orders/${wo.id}/edit`">
                <button class="w-full text-sm text-gray-700 border border-gray-300 rounded-lg py-2 hover:bg-gray-50">Edit Work Order</button>
              </RouterLink>

              <!-- Attachments -->
              <div>
                <p class="text-xs font-medium text-gray-500 mb-2">Attachments ({{ wo.media?.length || 0 }})</p>
                <div v-if="wo.media?.length" class="space-y-1 mb-2">
                  <a
                    v-for="m in wo.media"
                    :key="m.id"
                    :href="m.url"
                    target="_blank"
                    class="flex items-center gap-2 text-xs text-blue-600 hover:underline"
                  >
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    {{ m.filename }}
                  </a>
                </div>
                <FileUpload
                  label="Attach file"
                  :loading="uploading"
                  collection="other"
                  @upload="uploadMedia"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Status Change Modal -->
    <div v-if="showStatusModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="showStatusModal = false" />
      <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold mb-4">
          Change status to <span class="text-blue-600 capitalize">{{ statusForm.status?.replace('_', ' ') }}</span>
        </h3>
        <textarea
          v-model="statusForm.comment"
          rows="3"
          placeholder="Add a comment (optional)…"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <div class="flex gap-3 mt-4">
          <button :disabled="changingStatus" class="flex-1 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50" @click="changeStatus">
            {{ changingStatus ? 'Updating…' : 'Confirm' }}
          </button>
          <button class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50" @click="showStatusModal = false">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Add Part Modal -->
    <div v-if="showPartModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="showPartModal = false" />
      <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold mb-4">Add Part</h3>
        <input
          v-model="partSearch"
          placeholder="Search parts…"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <div class="max-h-40 overflow-y-auto border border-gray-200 rounded-lg mb-4">
          <button
            v-for="p in filteredParts"
            :key="p.id"
            :class="[
              'w-full text-left px-3 py-2 text-sm hover:bg-blue-50 transition-colors',
              selectedPart?.id === p.id ? 'bg-blue-50 text-blue-700' : 'text-gray-700'
            ]"
            @click="selectedPart = p"
          >
            <span class="font-medium">{{ p.name }}</span>
            <span class="text-gray-400 ml-2 text-xs">${{ p.unit_cost }} · {{ p.quantity_on_hand }} in stock</span>
          </button>
        </div>
        <div class="flex gap-3 items-end">
          <div class="flex-1">
            <label class="text-sm font-medium text-gray-700 block mb-1">Quantity</label>
            <input
              v-model.number="partQty"
              type="number"
              min="1"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
            />
          </div>
          <button
            :disabled="!selectedPart || addingPart"
            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50"
            @click="addPart"
          >
            {{ addingPart ? 'Adding…' : 'Add' }}
          </button>
        </div>
        <p v-if="partError" class="mt-2 text-sm text-red-600">{{ partError }}</p>
      </div>
    </div>
  </div>
</template>
