<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import { formatMoneyKobo } from '@/utils/format'

defineProps<{
  metrics: {
    total_projects: number
    projects_this_month: number
    total_users: number
    users_this_month: number
  },
  org_status_distribution: Record<string, number>,
  charts: {
    growth: Array<{ month: string, organizations: number, users: number }>,
    revenue: Array<{ month: string, revenue_ngn: number }>
  },
  mixpanel: {
    configured: boolean,
    token: string | null
  }
}>()
</script>

<template>
  <OwnerLayout>
    <Head title="Platform Analytics" />

    <div class="space-y-6">
      <!-- Mixpanel Integration Notice -->
      <SectionCard title="Mixpanel Product Analytics" subtitle="Deep Behavioral Insights">
        <div v-if="mixpanel.configured" class="flex flex-col sm:flex-row gap-4 items-center justify-between p-4 bg-brand-50 border border-brand-200 rounded-xl">
          <div class="flex items-start gap-3">
            <div class="mt-1 bg-brand-100 rounded-lg p-2 text-brand-600">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c-1.105 0-2-.895-2-2m2 2c1.105 0 2-.895 2-2m-2-2V6L3 8v10c0 1.105.895 2 2 2h4zm0-9h12" />
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-gray-900 border-b-0 pb-0">Mixpanel is Active</h3>
              <p class="text-sm text-gray-600 mt-1">Super Admin Events (Active Organizations, Page Views, Project Creations) are being securely sent to your Mixpanel property. To build extensive funnels, cohorts, and dashboards, please log in to your <a href="https://mixpanel.com" target="_blank" class="text-brand-600 hover:text-brand-700 font-medium underline">Mixpanel Console</a>.</p>
              <div class="mt-2 text-xs font-mono text-gray-500 bg-white p-1.5 rounded inline-block border">Token: {{ mixpanel.token }}</div>
            </div>
          </div>
          <a href="https://mixpanel.com" target="_blank" class="shrink-0 inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-black transition-colors">
            Open Mixpanel
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </a>
        </div>
        <div v-else class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
          <p class="text-sm text-gray-600">Mixpanel tracking is not fully configured (Missing VITE_MIXPANEL_TOKEN). Setup Mixpanel in your .env to track user events natively.</p>
        </div>
      </SectionCard>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Overview Cards -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="text-sm font-medium text-gray-500">Total Projects</div>
          <div class="mt-2 text-3xl font-bold text-gray-900">{{ metrics.total_projects }}</div>
          <div class="mt-1 text-xs text-brand-600 font-medium">+{{ metrics.projects_this_month }} this month</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="text-sm font-medium text-gray-500">Total Users</div>
          <div class="mt-2 text-3xl font-bold text-gray-900">{{ metrics.total_users }}</div>
          <div class="mt-1 text-xs text-brand-600 font-medium">+{{ metrics.users_this_month }} this month</div>
        </div>
      </div>

      <!-- Activity Tables via DB -->
      <SectionCard title="12-Month Growth Overview" subtitle="System Native Analytics Extracted from the Database">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 border-t border-b border-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left font-semibold text-gray-900">Month</th>
                <th scope="col" class="px-6 py-3 text-right font-semibold text-gray-900">New Orgs</th>
                <th scope="col" class="px-6 py-3 text-right font-semibold text-gray-900">New Users</th>
                <th scope="col" class="px-6 py-3 text-right font-semibold text-gray-900">Gross Revenue</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="(g, index) in charts.growth" :key="index" class="hover:bg-gray-50 transition">
                <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">{{ g.month }}</td>
                <td class="whitespace-nowrap px-6 py-4 text-right tabular-nums text-gray-700">
                  <span v-if="g.organizations > 0" class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                    +{{ g.organizations }}
                  </span>
                  <span v-else class="text-gray-400">0</span>
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-right tabular-nums text-gray-700">
                   <span v-if="g.users > 0" class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">
                    +{{ g.users }}
                  </span>
                  <span v-else class="text-gray-400">0</span>
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-right tabular-nums font-medium text-gray-900">
                  {{ formatMoneyKobo(charts.revenue[index].revenue_ngn, 'NGN') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </SectionCard>

      <!-- Subscription Status Overview -->
      <SectionCard title="Subscription Distribution" subtitle="Aggregate of current organization statuses">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
          <div v-for="(count, status) in org_status_distribution" :key="status" class="p-4 rounded-lg bg-gray-50 border border-gray-100 flex flex-col items-center justify-center text-center">
            <span class="text-2xl font-bold text-gray-900">{{ count }}</span>
            <span class="text-xs font-medium text-gray-500 uppercase tracking-widest mt-1">{{ status }}</span>
          </div>
        </div>
      </SectionCard>

    </div>
  </OwnerLayout>
</template>
