<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

defineProps<{ links: any[] }>()
</script>

<template>
  <div v-if="links && links.length" class="mt-4 flex flex-wrap gap-2">
    <Link
      v-for="lnk in links"
      :key="lnk.label"
      :href="lnk.url || ''"
      v-html="lnk.label"
      class="rounded-lg border px-3 py-1.5 text-sm"
      :class="[
        lnk.active ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-white text-gray-700',
        !lnk.url ? 'opacity-50 pointer-events-none' : 'hover:bg-gray-50'
      ]"
    />
  </div>
</template>
