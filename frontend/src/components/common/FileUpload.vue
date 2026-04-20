<script setup>
import { ref } from 'vue'

const props = defineProps({
  accept: { type: String, default: 'image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt' },
  label: { type: String, default: 'Upload File' },
  loading: { type: Boolean, default: false },
  collection: { type: String, default: 'other' },
})

const emit = defineEmits(['upload'])
const dragOver = ref(false)
const inputRef = ref(null)

function onFiles(files) {
  if (!files?.length) return
  Array.from(files).forEach(file => emit('upload', { file, collection: props.collection }))
}

function onDrop(e) {
  dragOver.value = false
  onFiles(e.dataTransfer.files)
}
</script>

<template>
  <div
    :class="[
      'border-2 border-dashed rounded-xl p-6 text-center transition-colors cursor-pointer',
      dragOver ? 'border-blue-400 bg-blue-50' : 'border-gray-300 hover:border-blue-300 hover:bg-gray-50',
      loading ? 'opacity-50 pointer-events-none' : '',
    ]"
    @dragover.prevent="dragOver = true"
    @dragleave="dragOver = false"
    @drop.prevent="onDrop"
    @click="inputRef?.click()"
  >
    <input
      ref="inputRef"
      type="file"
      :accept="accept"
      multiple
      class="hidden"
      @change="onFiles($event.target.files)"
    />

    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
    </svg>

    <p class="text-sm font-medium text-gray-700">{{ loading ? 'Uploading…' : label }}</p>
    <p class="text-xs text-gray-400 mt-1">Drag & drop or click to browse · Max 20MB</p>
  </div>
</template>
