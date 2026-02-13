<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import { formatMoneyKobo, formatDateTime } from '@/utils/format'
import { computed } from 'vue'

const props = defineProps<{
  metrics: {
    orgs_total: number
    orgs_active: number
    orgs_trial: number
    orgs_expired: number
    orgs_this_month: number
    users_total: number
    users_this_month: number
    tickets_open: number
    tickets_total: number
    payments_today: number
    revenue_today_kobo: number
    revenue_month_kobo: number
    mrr_kobo: number
    churn_rate: number
    ltv_kobo: number
    visitors_total: number
    visitors_today: number
    visitors_unique: number
  }
  revenueChart: { month: string; revenue: number }[]
  signupsChart: { month: string; signups: number }[]
  visitorsChart: { month: string; visitors: number }[]
  recentTickets: { id: number; user_id: number; subject: string; status: string; created_at: string; user?: { id: number; name: string } }[]
}>()

// Simple bar chart scaling
const maxRevenue = computed(() => Math.max(...props.revenueChart.map(r => r.revenue), 1))
const maxSignups = computed(() => Math.max(...props.signupsChart.map(s => s.signups), 1))
const maxVisitors = computed(() => Math.max(...props.visitorsChart.map(v => v.visitors), 1))

// Revenue bar colors - gradient from orange to warm
const revenueColors = [
  'from-amber-400 to-orange-500',
  'from-orange-400 to-red-500',
  'from-brand-400 to-brand-600',
  'from-rose-400 to-pink-500',
  'from-brand-500 to-orange-600',
  'from-amber-500 to-brand-500',
]

// Signup bar colors - vibrant greens and teals
const signupColors = [
  'from-emerald-400 to-teal-500',
  'from-green-400 to-emerald-500',
  'from-teal-400 to-cyan-500',
  'from-emerald-500 to-green-600',
  'from-green-500 to-teal-600',
  'from-teal-500 to-emerald-600',
]

// Visitor bar colors - purples and indigos
const visitorColors = [
  'from-violet-400 to-indigo-500',
  'from-indigo-400 to-blue-500',
  'from-blue-400 to-sky-500',
  'from-violet-500 to-purple-600',
  'from-purple-500 to-indigo-600',
  'from-sky-500 to-blue-600',
]
</script>

<template>
  <OwnerLayout>
    <Head title="Owner Dashboard" />

    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
          <p class="mt-1 text-sm text-gray-500">Overview of your platform metrics</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500">
          <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
          Live
        </div>
      </div>

      <!-- Quick Stats Row -->
      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <StatCard title="Total Organizations" :value="metrics.orgs_total" icon="folder" />
        <StatCard title="Active Subscriptions" :value="metrics.orgs_active" icon="activity" variant="success" />
        <StatCard title="Total Users" :value="metrics.users_total" icon="folder" />
        <StatCard 
          title="Open Tickets" 
          :value="metrics.tickets_open" 
          icon="issue" 
          :variant="metrics.tickets_open > 0 ? 'warning' : 'default'" 
        />
      </div>

      <!-- Revenue Stats -->
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">Revenue</h2>
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-400 to-brand-600">
            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-6">
          <div class="rounded-xl bg-gradient-to-br from-brand-50 to-orange-50 p-4 ring-1 ring-brand-100">
            <div class="text-xs font-medium text-brand-600 uppercase tracking-wide">MRR</div>
            <div class="mt-1 text-xl font-semibold text-gray-900">{{ formatMoneyKobo(metrics.mrr_kobo, 'NGN') }}</div>
          </div>
          <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 p-4 ring-1 ring-emerald-100">
            <div class="text-xs font-medium text-emerald-600 uppercase tracking-wide">This Month</div>
            <div class="mt-1 text-xl font-semibold text-gray-900">{{ formatMoneyKobo(metrics.revenue_month_kobo, 'NGN') }}</div>
          </div>
          <div class="rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 p-4 ring-1 ring-blue-100">
            <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Today</div>
            <div class="mt-1 text-xl font-semibold text-gray-900">{{ formatMoneyKobo(metrics.revenue_today_kobo, 'NGN') }}</div>
          </div>
          <div class="rounded-xl bg-gradient-to-br from-purple-50 to-pink-50 p-4 ring-1 ring-purple-100">
            <div class="text-xs font-medium text-purple-600 uppercase tracking-wide">Payments Today</div>
            <div class="mt-1 text-xl font-semibold text-gray-900">{{ metrics.payments_today }}</div>
          </div>
          <div class="rounded-xl bg-gradient-to-br from-red-50 to-orange-50 p-4 ring-1 ring-red-100">
            <div class="text-xs font-medium text-red-600 uppercase tracking-wide">Churn Rate</div>
            <div class="mt-1 text-xl font-semibold text-gray-900">{{ metrics.churn_rate.toFixed(1) }}%</div>
          </div>
          <div class="rounded-xl bg-gradient-to-br from-indigo-50 to-violet-50 p-4 ring-1 ring-indigo-100">
            <div class="text-xs font-medium text-indigo-600 uppercase tracking-wide">LTV (Est.)</div>
            <div class="mt-1 text-xl font-semibold text-gray-900">{{ formatMoneyKobo(metrics.ltv_kobo, 'NGN') }}</div>
          </div>
        </div>

        <!-- Revenue Chart (Colorful Gradient Bars) -->
        <div class="mt-6">
          <div class="text-sm font-medium text-gray-700 mb-3">Revenue (Last 6 Months)</div>
          <div class="flex items-end gap-3 h-36 px-2">
            <div 
              v-for="(item, index) in revenueChart" 
              :key="item.month" 
              class="flex-1 flex flex-col items-center group"
            >
              <div class="relative w-full flex flex-col items-center">
                <!-- Value tooltip on hover -->
                <div class="absolute -top-6 opacity-0 group-hover:opacity-100 transition-opacity text-xs font-medium text-gray-700 bg-white px-2 py-1 rounded shadow-lg">
                  {{ formatMoneyKobo(item.revenue, 'NGN') }}
                </div>
                <div 
                  :class="['w-full rounded-t-lg bg-gradient-to-t shadow-sm transition-all duration-300 hover:scale-105', revenueColors[index % revenueColors.length]]"
                  :style="{ height: `${Math.max((item.revenue / maxRevenue) * 100, 8)}%`, minHeight: '8px' }"
                ></div>
              </div>
              <div class="mt-2 text-xs font-medium text-gray-500">{{ item.month }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Two Column: Signups + Tickets -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Signups Chart -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Organization Signups</h2>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500">
              <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
          </div>
          <div class="mt-4 grid grid-cols-2 gap-4">
            <div class="rounded-xl bg-gradient-to-br from-gray-50 to-slate-50 p-4 ring-1 ring-gray-100">
              <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</div>
              <div class="mt-1 text-2xl font-bold text-gray-900">{{ metrics.orgs_total }}</div>
            </div>
            <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-green-50 p-4 ring-1 ring-emerald-100">
              <div class="text-xs font-medium text-emerald-600 uppercase tracking-wide">This Month</div>
              <div class="mt-1 text-2xl font-bold text-emerald-600">+{{ metrics.orgs_this_month }}</div>
            </div>
          </div>

          <div class="mt-6">
            <div class="text-sm font-medium text-gray-700 mb-3">Signups (Last 6 Months)</div>
            <div class="flex items-end gap-2 h-28 px-2">
              <div 
                v-for="(item, index) in signupsChart" 
                :key="item.month" 
                class="flex-1 flex flex-col items-center group"
              >
                <div class="relative w-full flex flex-col items-center">
                  <div class="absolute -top-5 opacity-0 group-hover:opacity-100 transition-opacity text-xs font-medium text-gray-700 bg-white px-1.5 py-0.5 rounded shadow">
                    {{ item.signups }}
                  </div>
                  <div 
                    :class="['w-full rounded-t-lg bg-gradient-to-t shadow-sm transition-all duration-300 hover:scale-105', signupColors[index % signupColors.length]]"
                    :style="{ height: `${Math.max((item.signups / maxSignups) * 100, 8)}%`, minHeight: '8px' }"
                  ></div>
                </div>
                <div class="mt-2 text-xs font-medium text-gray-500">{{ item.month }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Tickets -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Recent Support Tickets</h2>
            <Link href="/owner/support" class="text-sm font-medium text-brand-600 hover:text-brand-500 transition">View all →</Link>
          </div>
          
          <div class="mt-4 space-y-3">
            <div 
              v-for="ticket in recentTickets" 
              :key="ticket.id"
              class="flex items-center justify-between rounded-xl border border-gray-100 p-4 hover:border-brand-200 hover:bg-brand-50/30 transition-all"
            >
              <div class="min-w-0 flex-1">
                <div class="truncate text-sm font-medium text-gray-900">{{ ticket.subject }}</div>
                <div class="mt-0.5 text-xs text-gray-500">
                  {{ ticket.user?.name || 'Unknown' }} · {{ formatDateTime(ticket.created_at) }}
                </div>
              </div>
              <span 
                class="ml-2 shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                :class="{
                  'bg-amber-100 text-amber-700 ring-1 ring-amber-200': ticket.status === 'open' || ticket.status === 'pending',
                  'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200': ticket.status === 'resolved' || ticket.status === 'closed',
                  'bg-blue-100 text-blue-700 ring-1 ring-blue-200': ticket.status === 'in_progress',
                }"
              >
                {{ ticket.status.replace('_', ' ') }}
              </span>
            </div>

            <div v-if="recentTickets.length === 0" class="py-8 text-center">
              <div class="mx-auto h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center">
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
              </div>
              <div class="mt-2 text-sm text-gray-500">No support tickets yet</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Subscription Breakdown -->
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">Subscription Status</h2>
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-400 to-purple-500">
            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
          <div class="text-center rounded-xl bg-gradient-to-br from-emerald-50 to-green-50 p-5 ring-1 ring-emerald-200">
            <div class="text-3xl font-bold text-emerald-600">{{ metrics.orgs_active }}</div>
            <div class="mt-1 text-sm font-medium text-emerald-700">Active</div>
          </div>
          <div class="text-center rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 p-5 ring-1 ring-blue-200">
            <div class="text-3xl font-bold text-blue-600">{{ metrics.orgs_trial }}</div>
            <div class="mt-1 text-sm font-medium text-blue-700">Trial</div>
          </div>
          <div class="text-center rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 p-5 ring-1 ring-amber-200">
            <div class="text-3xl font-bold text-amber-600">{{ metrics.orgs_expired }}</div>
            <div class="mt-1 text-sm font-medium text-amber-700">Past Due / Expired</div>
          </div>
          <div class="text-center rounded-xl bg-gradient-to-br from-gray-50 to-slate-50 p-5 ring-1 ring-gray-200">
            <div class="text-3xl font-bold text-gray-500">{{ metrics.orgs_total - metrics.orgs_active - metrics.orgs_trial - metrics.orgs_expired }}</div>
            <div class="mt-1 text-sm font-medium text-gray-600">Other</div>
          </div>
        </div>
        </div>
      </div>

      <!-- Visitor Insights -->
      <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">Visitor Insights</h2>
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-400 to-blue-500">
            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.067 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </div>
        </div>
        
        <div class="mt-4 grid grid-cols-1 lg:grid-cols-3 gap-6">
           <div class="space-y-4">
               <div class="rounded-xl bg-gradient-to-br from-indigo-50 to-blue-50 p-4 ring-1 ring-indigo-100">
                  <div class="text-xs font-medium text-indigo-600 uppercase tracking-wide">Total Visitors</div>
                  <div class="mt-1 text-2xl font-bold text-gray-900">{{ metrics.visitors_total }}</div>
               </div>
               <div class="rounded-xl bg-gradient-to-br from-violet-50 to-purple-50 p-4 ring-1 ring-violet-100">
                  <div class="text-xs font-medium text-violet-600 uppercase tracking-wide">Today</div>
                  <div class="mt-1 text-2xl font-bold text-gray-900">{{ metrics.visitors_today }}</div>
               </div>
               <div class="rounded-xl bg-gradient-to-br from-fuchsia-50 to-pink-50 p-4 ring-1 ring-fuchsia-100">
                  <div class="text-xs font-medium text-fuchsia-600 uppercase tracking-wide">Unique IPs</div>
                  <div class="mt-1 text-2xl font-bold text-gray-900">{{ metrics.visitors_unique }}</div>
               </div>
           </div>

           <div class="lg:col-span-2">
              <div class="text-sm font-medium text-gray-700 mb-3">Traffic Trend (Last 6 Months)</div>
              <div class="flex items-end gap-2 h-48 px-2">
                <div 
                  v-for="(item, index) in visitorsChart" 
                  :key="item.month" 
                  class="flex-1 flex flex-col items-center group"
                >
                  <div class="relative w-full flex flex-col items-center h-full justify-end">
                    <div class="absolute -top-8 opacity-0 group-hover:opacity-100 transition-opacity text-xs font-medium text-gray-700 bg-white px-2 py-1 rounded shadow z-10 whitespace-nowrap">
                      {{ item.visitors }} visitors
                    </div>
                    <div 
                      :class="['w-full rounded-t-lg bg-gradient-to-t shadow-sm transition-all duration-300 hover:scale-105', visitorColors[index % visitorColors.length]]"
                      :style="{ height: `${Math.max((item.visitors / maxVisitors) * 100, 5)}%` }"
                    ></div>
                  </div>
                  <div class="mt-2 text-xs font-medium text-gray-500">{{ item.month }}</div>
                </div>
              </div>
           </div>
        </div>
      </div>
    </div>
  </OwnerLayout>
</template>
