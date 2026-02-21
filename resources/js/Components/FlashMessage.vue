<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { computed, watch, ref } from 'vue'

const page = usePage<any>()
const show = ref(false)
const type = ref<'success' | 'error'>('success')
const message = ref('')

const flash = computed(() => page.props.flash)

watch(flash, (newFlash) => {
  if (newFlash.success) {
    type.value = 'success'
    message.value = newFlash.success
    show.value = true
    setTimeout(() => { show.value = false }, 5000)
  } else if (newFlash.error) {
    type.value = 'error'
    message.value = newFlash.error
    show.value = true
    // Errors stay longer or until dismissed
  }
}, { deep: true, immediate: true })
</script>

<template>
  <div
    v-if="show"
    class="fixed bottom-6 right-6 z-50 flex max-w-sm items-center gap-3 rounded-xl p-4 shadow-xl transition-all duration-300"
    :class="[
      type === 'success' ? 'bg-emerald-50 text-emerald-900 border border-emerald-100' : 'bg-red-50 text-red-900 border border-red-100'
    ]"
  >
    <div 
        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
        :class="type === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'"
    >
      <svg v-if="type === 'success'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
      </svg>
      <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
    </div>
    
    <div class="flex-1 text-sm font-medium">
      {{ message }}
    </div>

    <button type="button" @click="show = false" class="shrink-0 rounded-lg p-1 hover:bg-black/5 transition">
      <svg class="h-4 w-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
</template>
