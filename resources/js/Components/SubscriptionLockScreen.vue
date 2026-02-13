<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

defineProps<{
    graceEndsAt?: string
}>()
</script>

<template>
  <div class="flex min-h-[60vh] flex-col items-center justify-center p-8 text-center">
    <div class="rounded-full bg-red-100 p-4">
      <svg class="h-10 w-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
      </svg>
    </div>
    <h2 class="mt-4 text-2xl font-bold tracking-tight text-gray-900">Access Suspended</h2>
    <p class="mt-2 text-base text-gray-600 max-w-md">
      Your subscription grace period has ended due to failed payment. 
      Please update your payment method to restore access to your workspace.
    </p>

    <div class="mt-8">
      <Link href="/app/billing" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
        Go to Billing
      </Link>
    </div>
    
    <div class="mt-6 text-xs text-gray-500">
      Need help? <a href="mailto:hello@buildflow.app" class="text-indigo-600 hover:underline">Contact Support</a>
    </div>
  </div>
</template>
