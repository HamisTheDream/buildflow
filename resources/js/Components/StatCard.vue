<script setup lang="ts">
defineProps<{
  label: string
  value: string
  hint?: string
}>()
</script>

<template>
  <div class="rounded-2xl border bg-white p-5">
    <div class="text-xs font-semibold text-gray-600">{{ label }}</div>
    <div class="mt-2 text-xl font-bold text-gray-900">{{ value }}</div>
    <div v-if="hint" class="mt-1 text-xs text-gray-500">{{ hint }}</div>
  </div>
</template>
