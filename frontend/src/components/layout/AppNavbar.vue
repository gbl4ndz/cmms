<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineEmits(['toggle-sidebar'])

const auth   = useAuthStore()
const router = useRouter()
const menuOpen = ref(false)

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <header class="h-16 bg-white border-b border-gray-200 flex items-center px-4 gap-4 flex-shrink-0">
    <!-- Hamburger -->
    <button
      class="p-2 rounded-md text-gray-500 hover:bg-gray-100 lg:hidden"
      @click="$emit('toggle-sidebar')"
    >
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>

    <div class="flex-1" />

    <!-- User menu -->
    <div class="relative">
      <button
        class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors"
        @click="menuOpen = !menuOpen"
      >
        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
          {{ auth.user?.name?.charAt(0).toUpperCase() }}
        </div>
        <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ auth.user?.name }}</span>
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <div
        v-if="menuOpen"
        class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
        @click.stop
      >
        <div class="px-4 py-2 border-b border-gray-100">
          <p class="text-sm font-medium text-gray-900">{{ auth.user?.name }}</p>
          <p class="text-xs text-gray-500">{{ auth.user?.email }}</p>
        </div>
        <button
          class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
          @click="logout"
        >
          Sign out
        </button>
      </div>
    </div>

    <!-- Close dropdown when clicking outside -->
    <div v-if="menuOpen" class="fixed inset-0 z-40" @click="menuOpen = false" />
  </header>
</template>
