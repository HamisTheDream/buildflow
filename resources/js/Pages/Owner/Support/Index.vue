<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import Pagination from '@/Components/Pagination.vue'
import Badge from '@/Components/Badge.vue'
import { formatDateTime, formatEnum } from '@/utils/format'

const props = defineProps<{
  filters: { q:string; status:string; priority:string }
  tickets: { data:any[]; links:any[] }
  statuses: string[]
  priorities: string[]
}>()

const form = useForm({
  q: props.filters.q || '',
  status: props.filters.status || '',
  priority: props.filters.priority || '',
})

function apply() {
  form.get('/owner/support', { preserveScroll:true, preserveState:true })
}

function statusTone(s:string) {
  if (s === 'open') return 'red'
  if (s === 'pending') return 'amber'
  if (s === 'resolved') return 'green'
  return 'gray'
}

function priorityTone(p:string) {
  if (p === 'urgent') return 'red'
  if (p === 'high') return 'amber'
  if (p === 'normal') return 'blue'
  return 'gray'
}
</script>

<template>
  <OwnerLayout>
    <Head title="Support" />

    <SectionCard title="Support tickets" subtitle="Track user requests and bugs.">
      <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
        <div class="md:col-span-6">
          <input v-model="form.q" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Search ticket / org..." />
        </div>
        <div class="md:col-span-3">
          <select v-model="form.status" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">All statuses</option>
            <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <select v-model="form.priority" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">All priorities</option>
            <option v-for="p in priorities" :key="p" :value="p">{{ p }}</option>
          </select>
        </div>
        <div class="md:col-span-1">
          <button type="button" @click="apply" class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            Go
          </button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto">
        <table class="min-w-full border rounded-lg overflow-hidden">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Ticket</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Org</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Priority</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Created</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600"></th>
            </tr>
          </thead>

          <tbody class="divide-y">
            <tr v-for="t in tickets.data" :key="t.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <div class="text-sm font-semibold text-gray-900">{{ t.subject }}</div>
                <div class="text-xs text-gray-500">{{ formatEnum(t.category || 'other') }}</div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ t.organization?.name || '—' }}</td>
              <td class="px-4 py-3 text-sm">
                <Badge :text="t.status" :tone="statusTone(t.status) as any" />
              </td>
              <td class="px-4 py-3 text-sm">
                <Badge :text="t.priority" :tone="priorityTone(t.priority) as any" />
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ formatDateTime(t.created_at) }}</td>
              <td class="px-4 py-3 text-right text-sm">
                <Link :href="`/owner/support/${t.id}`" class="text-indigo-600 hover:underline">View</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="tickets.links" />
    </SectionCard>
  </OwnerLayout>
</template>
