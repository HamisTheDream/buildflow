<script setup lang="ts">
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps<{
  payments: any
  filters: any
}>()

const form = useForm({
  search: props.filters.search || '',
})

watch(() => form.search, () => {
  form.get(route('owner.payments.index'), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
})

function formatCurrency(c: number) {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(c / 100)
}

function statusColor(status: string) {
    switch (status) {
        case 'success': return 'text-green-400'
        case 'pending': return 'text-yellow-400'
        case 'failed': return 'text-red-400'
        default: return 'text-gray-400'
    }
}
</script>

<template>
  <OwnerLayout>
    <Head title="Payments" />

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-white">Transactions</h1>
            <input 
                v-model="form.search"
                type="text" 
                placeholder="Search reference or org..." 
                class="rounded-lg border-gray-700 bg-gray-800 text-sm text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500"
            />
        </div>

        <div class="mt-8 overflow-hidden rounded-lg bg-gray-800 shadow ring-1 ring-white/5">
          <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-700/50">
              <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-white sm:pl-6">Reference</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Organization</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Plan</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Amount</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Status</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-700 bg-gray-800">
              <tr v-for="payment in payments.data" :key="payment.id">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-mono text-gray-300 sm:pl-6">{{ payment.reference }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-white">{{ payment.org_name }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-300">{{ payment.plan_name || '—' }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-white">{{ formatCurrency(payment.amount) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium capitalize" :class="statusColor(payment.status)">
                    {{ payment.status }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ new Date(payment.created_at).toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        
      </div>
    </div>
  </OwnerLayout>
</template>
