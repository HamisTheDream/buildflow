<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  disabled?: boolean
  title?: string
  class?: string
}>()

const tooltipText = computed(() => {
  if (!props.disabled) return ''
  return props.title || 'You don\'t have permission for this action'
})
</script>

<template>
  <button
    :disabled="disabled"
    :title="tooltipText"
    :class="[
      'transition',
      disabled && 'cursor-not-allowed opacity-50',
      $props.class
    ]"
  >
    <slot />
  </button>
</template>
