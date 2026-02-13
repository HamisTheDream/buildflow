<script setup lang="ts">
defineProps<{
  message?: string
  role?: string
}>()
</script>

<template>
  <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-3 text-sm">
    <div class="flex items-center gap-2">
      <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
      </svg>
      <span class="text-gray-600">
        <span class="font-medium text-gray-900">View only</span>
        <span v-if="message"> — {{ message }}</span>
        <span v-else-if="role"> — Your role ({{ role }}) doesn't allow editing.</span>
        <span v-else> — You don't have permission to make changes.</span>
      </span>
    </div>
  </div>
</template>
