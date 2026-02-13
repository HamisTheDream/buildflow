<script setup lang="ts">
defineProps<{
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
  size?: 'sm' | 'md' | 'lg'
}>()
</script>

<template>
  <button
    :type="type ?? 'button'"
    :disabled="disabled"
    :class="[
      'inline-flex items-center justify-center gap-2 rounded-lg font-semibold shadow-sm transition-all duration-150',
      'bg-brand-600 text-white hover:bg-brand-700 active:bg-brand-800',
      'focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600',
      'disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-brand-600',
      size === 'sm' ? 'px-3 py-1.5 text-xs' : size === 'lg' ? 'px-5 py-3 text-base' : 'px-4 py-2.5 text-sm'
    ]"
  >
    <slot />
  </button>
</template>
