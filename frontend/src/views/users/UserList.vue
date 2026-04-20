<script setup>
import { ref, reactive, onMounted } from 'vue'
import usersService from '@/services/users'
import { useAuthStore } from '@/stores/auth'
import PageHeader from '@/components/common/PageHeader.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseSelect from '@/components/common/BaseSelect.vue'
import BasePagination from '@/components/common/BasePagination.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const auth      = useAuthStore()
const users     = ref([])
const meta      = ref(null)
const loading   = ref(true)
const saving    = ref(false)
const deleting  = ref(null)
const confirmId = ref(null)
const showModal = ref(false)
const editItem  = ref(null)
const errors    = ref({})

const form = reactive({ name: '', email: '', password: '', password_confirmation: '', role: 'user', is_active: true })

const roleOptions = [{ value: 'admin', label: 'Admin' }, { value: 'user', label: 'User' }]

onMounted(loadUsers)

async function loadUsers(page = 1) {
  loading.value = true
  const { data } = await usersService.list({ page })
  users.value = data.data
  meta.value  = data.meta
  loading.value = false
}

function openCreate() {
  editItem.value = null
  Object.assign(form, { name: '', email: '', password: '', password_confirmation: '', role: 'user', is_active: true })
  errors.value   = {}
  showModal.value = true
}

function openEdit(item) {
  editItem.value = item
  Object.assign(form, { name: item.name, email: item.email, password: '', password_confirmation: '', role: item.role, is_active: item.is_active })
  errors.value   = {}
  showModal.value = true
}

async function save() {
  saving.value = true
  errors.value = {}
  try {
    const payload = { ...form }
    if (editItem.value && !payload.password) {
      delete payload.password
      delete payload.password_confirmation
    }
    if (editItem.value) {
      await usersService.update(editItem.value.id, payload)
    } else {
      await usersService.create(payload)
    }
    showModal.value = false
    loadUsers()
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
  } finally {
    saving.value = false
  }
}

async function deleteUser() {
  deleting.value = confirmId.value
  try {
    await usersService.remove(confirmId.value)
    confirmId.value = null
    loadUsers()
  } finally {
    deleting.value = null
  }
}
</script>

<template>
  <div>
    <PageHeader title="Users" subtitle="Manage system access and roles">
      <template #actions>
        <button
          v-if="auth.isAdmin"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
          @click="openCreate"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New User
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
              <th class="text-left px-5 py-3 font-medium text-gray-600">Email</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Role</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Status</th>
              <th class="px-5 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!users.length">
              <td colspan="5" class="px-5 py-8 text-center text-gray-400">No users found.</td>
            </tr>
            <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50">
              <td class="px-5 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-medium text-sm">
                    {{ u.name.charAt(0).toUpperCase() }}
                  </div>
                  <span class="font-medium text-gray-900">{{ u.name }}</span>
                  <span v-if="u.id === auth.user?.id" class="text-xs text-gray-400">(you)</span>
                </div>
              </td>
              <td class="px-5 py-3 text-gray-600">{{ u.email }}</td>
              <td class="px-5 py-3">
                <span
                  :class="[
                    'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                    u.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'
                  ]"
                >
                  {{ u.role }}
                </span>
              </td>
              <td class="px-5 py-3">
                <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium', u.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600']">
                  {{ u.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-5 py-3 text-right" v-if="auth.isAdmin">
                <button class="text-blue-600 hover:underline text-xs mr-3" @click="openEdit(u)">Edit</button>
                <button
                  v-if="u.id !== auth.user?.id"
                  class="text-red-500 hover:underline text-xs"
                  @click="confirmId = u.id"
                >Delete</button>
              </td>
              <td v-else class="px-5 py-3" />
            </tr>
          </tbody>
        </table>
        <div v-if="meta" class="px-5 py-4 border-t border-gray-100">
          <BasePagination :meta="meta" @page="loadUsers" />
        </div>
      </template>
    </div>

    <BaseModal v-if="showModal" :title="editItem ? 'Edit User' : 'New User'" @close="showModal = false">
      <form class="space-y-4" @submit.prevent="save">
        <BaseInput v-model="form.name" label="Full Name" required :error="errors.name?.[0]" />
        <BaseInput v-model="form.email" type="email" label="Email" required :error="errors.email?.[0]" />
        <BaseSelect v-model="form.role" label="Role" required :options="roleOptions" :error="errors.role?.[0]" />
        <div class="border-t border-gray-100 pt-4">
          <p class="text-xs text-gray-400 mb-3">{{ editItem ? 'Leave blank to keep current password.' : 'Minimum 8 characters.' }}</p>
          <div class="grid grid-cols-2 gap-3">
            <BaseInput v-model="form.password" type="password" :label="editItem ? 'New Password' : 'Password'" :required="!editItem" :error="errors.password?.[0]" />
            <BaseInput v-model="form.password_confirmation" type="password" label="Confirm Password" :required="!editItem" />
          </div>
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
          <input type="checkbox" v-model="form.is_active" class="rounded" />
          Active account
        </label>
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

    <ConfirmDialog
      v-if="confirmId"
      title="Delete User"
      message="This will permanently remove the user account."
      :loading="!!deleting"
      @confirm="deleteUser"
      @cancel="confirmId = null"
    />
  </div>
</template>
