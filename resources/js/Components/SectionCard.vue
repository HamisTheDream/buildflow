<script setup lang="ts">
defineProps<{ 
  title?: string
  subtitle?: string
  noPadding?: boolean
}>()
</script>

<template>
  <div :class="[
    'rounded-2xl border border-gray-200 bg-white shadow-card transition-shadow',
    noPadding ? '' : 'p-6'
  ]">
    <div v-if="title || subtitle" class="mb-5">
      <h2 v-if="title" class="text-lg font-semibold text-gray-900">{{ title }}</h2>
      <p v-if="subtitle" class="mt-1 text-sm text-gray-500">{{ subtitle }}</p>
    </div>
    <slot />
  </div>
</template>
