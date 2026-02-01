<script setup lang="ts">
defineProps<{
  title: string
  subtitle?: string
}>()
</script>

<template>
  <div class="flex items-end justify-between gap-4">
    <div>
      <div class="text-sm font-semibold text-gray-900">{{ title }}</div>
      <div v-if="subtitle" class="mt-1 text-sm text-gray-600">{{ subtitle }}</div>
    </div>
    <slot />
  </div>
</template>
