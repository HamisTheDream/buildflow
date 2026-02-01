<script setup lang="ts">
defineProps<{ title?: string; subtitle?: string }>()
</script>

<template>
  <div class="rounded-xl bg-white p-6 shadow">
    <div v-if="title || subtitle" class="mb-4">
      <div v-if="title" class="text-lg font-semibold text-gray-900">{{ title }}</div>
      <div v-if="subtitle" class="mt-1 text-sm text-gray-600">{{ subtitle }}</div>
    </div>
    <slot />
  </div>
</template>
