<script setup lang="ts">
import { useToast } from '@/composables/useToast'
import { computed } from 'vue'

const { toasts, removeToast } = useToast()

const iconPaths: Record<string, string> = {
  success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
  warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
  info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
}

const toastStyles: Record<string, { bg: string; icon: string; border: string }> = {
  success: { bg: 'bg-emerald-50', icon: 'text-emerald-500', border: 'border-emerald-200' },
  error: { bg: 'bg-red-50', icon: 'text-red-500', border: 'border-red-200' },
  warning: { bg: 'bg-amber-50', icon: 'text-amber-500', border: 'border-amber-200' },
  info: { bg: 'bg-blue-50', icon: 'text-blue-500', border: 'border-blue-200' },
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-3 w-full max-w-sm pointer-events-none">
      <TransitionGroup
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-x-full opacity-0"
        enter-to-class="translate-x-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-x-0 opacity-100"
        leave-to-class="translate-x-full opacity-0"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            'pointer-events-auto flex items-start gap-3 rounded-xl border p-4 shadow-lg backdrop-blur-sm',
            toastStyles[toast.type].bg,
            toastStyles[toast.type].border,
          ]"
        >
          <!-- Icon -->
          <div :class="['shrink-0', toastStyles[toast.type].icon]">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths[toast.type]" />
            </svg>
          </div>

          <!-- Content -->
          <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-gray-900">{{ toast.title }}</p>
            <p v-if="toast.message" class="mt-1 text-sm text-gray-600">{{ toast.message }}</p>
          </div>

          <!-- Close button -->
          <button
            @click="removeToast(toast.id)"
            class="shrink-0 rounded-lg p-1 text-gray-400 hover:bg-gray-200/50 hover:text-gray-600 transition"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>
