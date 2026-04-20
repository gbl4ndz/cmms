<script setup>
defineProps({
  modelValue: { default: '' },
  label:      { type: String, default: '' },
  error:      { type: String, default: '' },
  rows:       { type: Number, default: 3 },
  placeholder: { type: String, default: '' },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" class="text-sm font-medium text-gray-700">{{ label }}</label>
    <textarea
      :value="modelValue"
      :rows="rows"
      :placeholder="placeholder"
      :class="[
        'block w-full rounded-lg border px-3 py-2 text-sm text-gray-900 placeholder-gray-400 resize-none',
        'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition',
        error ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:border-gray-400',
      ]"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
  </div>
</template>
