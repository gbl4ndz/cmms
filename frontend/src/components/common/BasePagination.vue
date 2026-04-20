<script setup>
defineProps({
  meta: { type: Object, required: true }, // Laravel paginator meta
})
defineEmits(['page'])
</script>

<template>
  <div v-if="meta.last_page > 1" class="flex items-center justify-between text-sm text-gray-600">
    <span>
      Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }}
    </span>
    <div class="flex items-center gap-1">
      <button
        :disabled="meta.current_page === 1"
        class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
        @click="$emit('page', meta.current_page - 1)"
      >
        Prev
      </button>
      <template v-for="p in meta.last_page" :key="p">
        <button
          :class="[
            'px-3 py-1.5 rounded-md border text-sm',
            p === meta.current_page
              ? 'bg-blue-600 text-white border-blue-600'
              : 'border-gray-300 hover:bg-gray-50'
          ]"
          @click="$emit('page', p)"
        >
          {{ p }}
        </button>
      </template>
      <button
        :disabled="meta.current_page === meta.last_page"
        class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
        @click="$emit('page', meta.current_page + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>
