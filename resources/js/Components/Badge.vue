<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  text: string
  tone?: 'gray'|'blue'|'green'|'amber'|'red'|'indigo'
}>()

const cls = computed(() => {
  const t = props.tone || 'gray'
  const map: Record<string, string> = {
    gray: 'bg-gray-100 text-gray-800 border-gray-200',
    blue: 'bg-blue-50 text-blue-800 border-blue-200',
    green: 'bg-green-50 text-green-800 border-green-200',
    amber: 'bg-amber-50 text-amber-800 border-amber-200',
    red: 'bg-red-50 text-red-800 border-red-200',
    indigo: 'bg-indigo-50 text-indigo-800 border-indigo-200',
  }
  return map[t]
})
</script>

<template>
  <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold" :class="cls">
    {{ text }}
  </span>
</template>
