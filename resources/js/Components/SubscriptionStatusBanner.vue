<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { formatDate } from '@/utils/format'

const page = usePage<any>()
const org = computed(() => page.props.auth?.organization ?? null)
const sub = computed(() => org.value?.subscription ?? {})

const isPastDue = computed(() => sub.value.status === 'past_due')

// Check if grace period is active (past_due but not yet locked)
// Simplistic check: if past_due and we have a grace_ends_at date that is in the future
// In reality, the backend might handle exact "is locked" logic, but we can infer from dates for UI.
const graceEnds = computed(() => sub.value.grace_ends_at ? new Date(sub.value.grace_ends_at) : null)
const now = new Date()

const showBanner = computed(() => {
    if (!isPastDue.value) return false
    // If grace ends exists and is in future, show warning.
    // If grace ends is past, we might show lock screen instead (handled by layout), 
    // but showing a banner is harmless if layout doesn't block.
    if (!graceEnds.value) return true // default to showing if no date
    return graceEnds.value > now
})
</script>

<template>
  <div v-if="showBanner" class="bg-red-600 px-4 py-3 text-white sm:px-6 lg:px-8">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-6 gap-y-2">
      <div class="flex items-center gap-x-2 text-sm font-medium leading-6">
        <svg class="h-5 w-5 flex-none" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
        </svg>
        <strong class="font-semibold">Payment Failed</strong>
        <span class="hidden sm:inline">&middot;</span>
        <span>
            Your subscription is past due. 
            <span v-if="graceEnds">Access will be suspended on {{ formatDate(graceEnds) }}.</span>
        </span>
      </div>
      <div class="flex flex-none justify-end">
        <Link href="/app/billing" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-red-600 shadow-sm hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
          Renew Now <span aria-hidden="true">&rarr;</span>
        </Link>
      </div>
    </div>
  </div>
</template>
