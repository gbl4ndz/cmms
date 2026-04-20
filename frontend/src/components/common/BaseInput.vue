<script setup>
defineProps({
  modelValue: { default: '' },
  label:      { type: String, default: '' },
  error:      { type: String, default: '' },
  type:       { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  required:   { type: Boolean, default: false },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" class="text-sm font-medium text-gray-700">
      {{ label }}<span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>
    <input
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :class="[
        'block w-full rounded-lg border px-3 py-2 text-sm text-gray-900 placeholder-gray-400',
        'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition',
        error ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:border-gray-400',
      ]"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
  </div>
</template>
