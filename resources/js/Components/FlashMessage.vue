<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage<any>()
const flash = computed(() => page.props.flash || {})

function tone() {
  if (flash.value.error) return 'bg-red-50 text-red-800 border-red-200'
  if (flash.value.success) return 'bg-green-50 text-green-800 border-green-200'
  if (flash.value.warning) return 'bg-amber-50 text-amber-800 border-amber-200'
  return 'bg-indigo-50 text-indigo-800 border-indigo-200'
}
</script>

<template>
  <div v-if="flash.success || flash.error || flash.warning || flash.info"
       class="mb-4 rounded-xl border p-4 text-sm"
       :class="tone()">
    {{ flash.error || flash.success || flash.warning || flash.info }}
  </div>
</template>
