<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import Pagination from '@/Components/Pagination.vue'
import Badge from '@/Components/Badge.vue'
import { formatDateTime } from '@/utils/format'

const props = defineProps<{
  filters: { q:string; active:string }
  announcements: { data:any[]; links:any[] }
}>()

const form = useForm({
  q: props.filters.q || '',
  active: props.filters.active || '',
})

function apply() {
  form.get('/owner/announcements', { preserveScroll:true, preserveState:true })
}

function toneBadge(t:string) {
  if (t === 'success') return 'green'
  if (t === 'warning') return 'amber'
  if (t === 'danger') return 'red'
  return 'blue'
}

function targetingText(a:any) {
  if (a.is_global) return 'Global'
  if (a.organization) return `Org: ${a.organization.name}`
  if (a.plan) return `Plan: ${a.plan.name}`
  return 'Targeted'
}
</script>

<template>
  <OwnerLayout>
    <Head title="Announcements" />

    <div class="space-y-6">
      <SectionCard title="Announcements" subtitle="Create targeted in-app banners and notices.">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:w-3/4">
            <div class="md:col-span-8">
              <input v-model="form.q" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Search title/body..." />
            </div>
            <div class="md:col-span-3">
              <select v-model="form.active" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">All</option>
                <option value="1">Active only</option>
                <option value="0">Inactive only</option>
              </select>
            </div>
            <div class="md:col-span-1">
              <button @click="apply" class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Go
              </button>
            </div>
          </div>

          <Link href="/owner/announcements/create" class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-black">
            New
          </Link>
        </div>

        <div class="mt-6 overflow-x-auto">
          <table class="min-w-full border rounded-lg overflow-hidden">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Title</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Tone</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Target</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Active</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Window</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600"></th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="a in announcements.data" :key="a.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ a.title }}</td>
                <td class="px-4 py-3 text-sm">
                  <Badge :text="a.tone" :tone="toneBadge(a.tone) as any" />
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ targetingText(a) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ a.is_active ? 'Yes' : 'No' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">
                  {{ formatDateTime(a.starts_at) }} → {{ formatDateTime(a.ends_at) }}
                </td>
                <td class="px-4 py-3 text-right text-sm">
                  <Link :href="`/owner/announcements/${a.id}/edit`" class="text-indigo-600 hover:underline">
                    Edit
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <Pagination :links="announcements.links" />
      </SectionCard>
    </div>
  </OwnerLayout>
</template>
