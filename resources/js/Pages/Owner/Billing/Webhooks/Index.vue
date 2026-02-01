<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import Pagination from '@/Components/Pagination.vue'
import { formatDateTime } from '@/utils/format'

const props = defineProps<{
  filters: { q:string; event:string }
  events: { data:any[]; links:any[] }
}>()

const form = useForm({
  q: props.filters.q || '',
  event: props.filters.event || '',
})

function apply() {
  form.get('/owner/billing/webhooks', { preserveScroll:true, preserveState:true })
}
</script>

<template>
  <OwnerLayout>
    <Head title="Billing - Webhooks" />

    <SectionCard title="Webhook events" subtitle="Raw Paystack webhook log for observability.">
      <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
        <div class="md:col-span-8">
          <input v-model="form.q" class="w-full rounded-lg border p-2 text-sm" placeholder="Search by reference..." />
        </div>
        <div class="md:col-span-3">
          <input v-model="form.event" class="w-full rounded-lg border p-2 text-sm" placeholder="Event (e.g. charge.success)" />
        </div>
        <div class="md:col-span-1">
          <button @click="apply" class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            Go
          </button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto">
        <table class="min-w-full border rounded-lg overflow-hidden">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Event</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Reference</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Received</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600"></th>
            </tr>
          </thead>

          <tbody class="divide-y">
            <tr v-for="e in events.data" :key="e.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ e.event }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ e.reference || '—' }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ formatDateTime(e.received_at) }}</td>
              <td class="px-4 py-3 text-right text-sm">
                <Link :href="`/owner/billing/webhooks/${e.id}`" class="text-indigo-600 hover:underline">View</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="events.links" />
    </SectionCard>
  </OwnerLayout>
</template>
