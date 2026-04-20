<script setup>
defineProps({
  modelValue: { default: '' },
  label:      { type: String, default: '' },
  error:      { type: String, default: '' },
  options:    { type: Array, default: () => [] }, // [{ value, label }]
  placeholder: { type: String, default: 'Select…' },
  required:   { type: Boolean, default: false },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" class="text-sm font-medium text-gray-700">
      {{ label }}<span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>
    <select
      :value="modelValue"
      :class="[
        'block w-full rounded-lg border px-3 py-2 text-sm text-gray-900',
        'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition',
        error ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:border-gray-400',
      ]"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <option value="">{{ placeholder }}</option>
      <option v-for="opt in options" :key="opt.value" :value="opt.value">
        {{ opt.label }}
      </option>
    </select>
    <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
  </div>
</template>
