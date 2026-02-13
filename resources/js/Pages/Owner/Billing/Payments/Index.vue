<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import Pagination from '@/Components/Pagination.vue'
import { formatDateTime, formatEnum, formatMoneyKobo } from '@/utils/format'
import Badge from '@/Components/Badge.vue'

const props = defineProps<{
  filters: { q:string; status:string }
  payments: { data:any[]; links:any[] }
}>()

const form = useForm({
  q: props.filters.q || '',
  status: props.filters.status || '',
})

function apply() {
  form.get('/owner/billing/payments', { preserveScroll:true, preserveState:true })
}

function tone(s:string) {
  if (s === 'success') return 'green'
  if (s === 'failed') return 'red'
  if (s === 'pending') return 'amber'
  return 'gray'
}
</script>

<template>
  <OwnerLayout>
    <Head title="Billing - Payments" />

    <SectionCard title="Payments" subtitle="Search and reconcile payments.">
      <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
        <div class="md:col-span-7">
          <input v-model="form.q" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Search by reference..." />
        </div>
        <div class="md:col-span-4">
          <select v-model="form.status" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">All statuses</option>
            <option value="success">Success</option>
            <option value="pending">Pending</option>
            <option value="failed">Failed</option>
          </select>
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
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Reference</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Org</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Amount</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">At</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600"></th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="p in payments.data" :key="p.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ p.reference }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ p.organization?.name || '—' }}</td>
              <td class="px-4 py-3 text-sm">
                <Badge :text="formatEnum(p.status)" :tone="tone(p.status) as any" />
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ formatMoneyKobo(p.amount_kobo, p.currency || 'NGN') }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ formatDateTime(p.created_at) }}</td>
              <td class="px-4 py-3 text-right text-sm">
                <Link :href="`/owner/billing/payments/${p.id}`" class="text-indigo-600 hover:underline">View</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="payments.links" />
    </SectionCard>
  </OwnerLayout>
</template>
