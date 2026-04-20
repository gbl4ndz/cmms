<script setup>
defineProps({ status: String })

const map = {
  open:             'bg-blue-100 text-blue-700',
  in_progress:      'bg-yellow-100 text-yellow-700',
  on_hold:          'bg-orange-100 text-orange-700',
  closed:           'bg-green-100 text-green-700',
  active:           'bg-green-100 text-green-700',
  inactive:         'bg-gray-100 text-gray-600',
  under_maintenance:'bg-yellow-100 text-yellow-700',
  retired:          'bg-red-100 text-red-700',
}

const label = {
  open:             'Open',
  in_progress:      'In Progress',
  on_hold:          'On Hold',
  closed:           'Closed',
  active:           'Active',
  inactive:         'Inactive',
  under_maintenance:'Under Maint.',
  retired:          'Retired',
}
</script>

<template>
  <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium', map[status] || 'bg-gray-100 text-gray-600']">
    {{ label[status] || status }}
  </span>
</template>
