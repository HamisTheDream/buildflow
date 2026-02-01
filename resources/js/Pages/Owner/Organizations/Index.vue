<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { watch } from 'vue'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import Pagination from '@/Components/Pagination.vue'
import Badge from '@/Components/Badge.vue'
import { formatDateTime, formatEnum } from '@/utils/format'

const props = defineProps<{
  filters: { q:string; status:string; plan:string }
  plans: { id:number; key:string; name:string }[]
  organizations: { data:any[]; links:any[] }
}>()

const form = useForm({
  q: props.filters.q || '',
  status: props.filters.status || '',
  plan: props.filters.plan || '',
})

let timeout: any = null

watch(
  () => [form.q, form.status, form.plan],
  () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
      form.get('/owner/organizations', {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      })
    }, 300)
  }
)

function statusTone(s:string) {
  if (s === 'active') return 'green'
  if (s === 'trial') return 'blue'
  if (s === 'past_due') return 'amber'
  if (s === 'suspended') return 'red'
  return 'gray'
}
</script>

<template>
  <OwnerLayout>
    <Head title="Organizations" />

    <div class="space-y-6">
      <SectionCard title="Organizations" subtitle="Search, filter, and manage subscriptions.">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
          <div class="md:col-span-7">
            <input v-model="form.q" class="w-full rounded-lg border p-2 text-sm" placeholder="Search organizations..." />
          </div>

          <div class="md:col-span-3">
            <select v-model="form.status" class="w-full rounded-lg border p-2 text-sm">
              <option value="">All statuses</option>
              <option value="active">Active</option>
              <option value="trial">Trial</option>
              <option value="past_due">Past due</option>
              <option value="suspended">Suspended</option>
              <option value="free">Free</option>
            </select>
          </div>

          <div class="md:col-span-2">
            <select v-model="form.plan" class="w-full rounded-lg border p-2 text-sm">
              <option value="">All plans</option>
              <option v-for="p in plans" :key="p.key" :value="p.key">{{ p.name }}</option>
            </select>
          </div>
        </div>

        <div class="mt-6 overflow-x-auto">
          <table class="min-w-full border rounded-lg overflow-hidden">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Org</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Plan</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Trial</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Paid until</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600"></th>
              </tr>
            </thead>

            <tbody class="divide-y">
              <tr v-for="o in organizations.data" :key="o.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ o.name }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ o.plan?.name || '—' }}</td>
                <td class="px-4 py-3 text-sm">
                  <Badge :text="formatEnum(o.subscription_status)" :tone="statusTone(o.subscription_status) as any" />
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ formatDateTime(o.trial_ends_at) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ formatDateTime(o.paid_until) }}</td>
                <td class="px-4 py-3 text-right text-sm">
                  <Link :href="`/owner/organizations/${o.id}`" class="text-indigo-600 hover:underline">
                    View
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <Pagination :links="organizations.links" />
      </SectionCard>
    </div>
  </OwnerLayout>
</template>
