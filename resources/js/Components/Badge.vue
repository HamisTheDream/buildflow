<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  text: string
  tone?: 'gray' | 'blue' | 'green' | 'amber' | 'red' | 'indigo' | 'brand'
  size?: 'sm' | 'md'
  dot?: boolean
}>()

const cls = computed(() => {
  const t = props.tone || 'gray'
  const map: Record<string, string> = {
    gray: 'bg-gray-100 text-gray-700',
    blue: 'bg-blue-50 text-blue-700',
    green: 'bg-emerald-50 text-emerald-700',
    amber: 'bg-amber-50 text-amber-700',
    red: 'bg-red-50 text-red-700',
    indigo: 'bg-indigo-50 text-indigo-700',
    brand: 'bg-brand-50 text-brand-700',
  }
  return map[t]
})

const dotCls = computed(() => {
  const t = props.tone || 'gray'
  const map: Record<string, string> = {
    gray: 'bg-gray-500',
    blue: 'bg-blue-500',
    green: 'bg-emerald-500',
    amber: 'bg-amber-500',
    red: 'bg-red-500',
    indigo: 'bg-indigo-500',
    brand: 'bg-brand-500',
  }
  return map[t]
})
</script>

<template>
  <span 
    :class="[
      'inline-flex items-center gap-1.5 rounded-full font-medium',
      cls,
      size === 'sm' ? 'px-2 py-0.5 text-[10px]' : 'px-2.5 py-1 text-xs'
    ]"
  >
    <span v-if="dot" :class="['h-1.5 w-1.5 rounded-full', dotCls]" />
    {{ text }}
  </span>
</template>
