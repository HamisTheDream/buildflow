<script setup lang="ts">
defineProps<{ label: string; value?: string | number | null }>()
</script>

<template>
  <div class="flex items-start justify-between gap-4 py-2">
    <div class="text-sm text-gray-600">{{ label }}</div>
    <div class="text-sm font-semibold text-gray-900 text-right">{{ value ?? '—' }}</div>
  </div>
</template>
