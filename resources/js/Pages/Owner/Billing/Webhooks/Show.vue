<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import { formatDateTime } from '@/utils/format'

defineProps<{ event:any }>()
</script>

<template>
  <OwnerLayout>
    <Head :title="`Webhook — ${event.event}`" />

    <div class="space-y-6">
      <SectionCard title="Webhook event" subtitle="Raw data received from Paystack.">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Event</div>
            <div class="mt-2 text-sm font-bold text-gray-900">{{ event.event }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Reference</div>
            <div class="mt-2 text-sm font-bold text-gray-900">{{ event.reference || '—' }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Received</div>
            <div class="mt-2 text-sm font-bold text-gray-900">{{ formatDateTime(event.received_at) }}</div>
          </div>
        </div>
      </SectionCard>

      <SectionCard title="Payload" subtitle="For debugging and reconciliation.">
        <pre class="whitespace-pre-wrap text-xs bg-gray-50 border rounded-xl p-4 overflow-x-auto">{{ JSON.stringify(event.payload, null, 2) }}</pre>
      </SectionCard>
    </div>
  </OwnerLayout>
</template>
