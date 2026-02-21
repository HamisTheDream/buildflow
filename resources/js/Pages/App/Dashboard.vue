<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import StatCard from '@/Components/StatCard.vue'
import Badge from '@/Components/Badge.vue'

import DoughnutChart from '@/Components/Charts/DoughnutChart.vue'
import BarChart from '@/Components/Charts/BarChart.vue'
import LineChart from '@/Components/Charts/LineChart.vue'
import { formatDateTime } from '@/utils/format'

const page = usePage()
const user = computed(() => (page.props as any).auth?.user)
const org = computed(() => (page.props as any).auth?.organization)

const stats = computed(() => (page.props as any).stats || {})

const recentProjects = computed(() => ((page.props as any).recentProjects as any[]) || [])
const recentActivity = computed(() => ((page.props as any).recentActivity as any[]) || [])
const chartData = computed(() => (page.props as any).chartData || null)
const orgCurrency = computed(() => (page.props as any).orgCurrency || { code: 'NGN', symbol: '₦' })

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return 'Good morning'
  if (hour < 18) return 'Good afternoon'
  return 'Good evening'
})

const orgRole = computed(() => (page.props as any).auth?.orgRole || null)
const userDept = computed(() => (page.props as any).auth?.userContext?.department?.name?.toLowerCase() || '')
const isOrgManager = computed(() => ['owner', 'admin'].includes(orgRole.value))

// Determine visible modules based on role and department
const showFinance = computed(() => isOrgManager.value || userDept.value.includes('account'))
const showHR = computed(() => isOrgManager.value || userDept.value.includes('hr') || userDept.value.includes('human resource'))
const showCRM = computed(() => isOrgManager.value || userDept.value.includes('sale') || userDept.value.includes('marketing'))

const formatMoney = (cents: number) => {
  const c = orgCurrency.value
  return new Intl.NumberFormat('en-NG', {
    style: 'currency', currency: c.code, minimumFractionDigits: 0
  }).format(cents / 100)
}

// Chart computed data
const projectStatusChart = computed(() => {
  if (!chartData.value?.projectStatuses) return null
  const data = chartData.value.projectStatuses
  const statusMap: Record<string, { label: string; color: string }> = {
    active: { label: 'Active', color: '#10b981' },
    completed: { label: 'Completed', color: '#3b82f6' },
    paused: { label: 'Paused', color: '#eab308' },
    cancelled: { label: 'Cancelled', color: '#ef4444' },
    draft: { label: 'Draft', color: '#94a3b8' },
  }
  const labels = Object.keys(data).map(k => statusMap[k]?.label || k)
  const values = Object.values(data) as number[]
  const colors = Object.keys(data).map(k => statusMap[k]?.color || '#6b7280')
  return { labels, values, colors }
})

const issueChart = computed(() => {
  if (!chartData.value?.issueSeverities) return null
  const data = chartData.value.issueSeverities
  const sevMap: Record<string, { label: string; color: string }> = {
    critical: { label: 'Critical', color: '#ef4444' },
    high: { label: 'High', color: '#f97316' },
    medium: { label: 'Medium', color: '#eab308' },
    low: { label: 'Low', color: '#10b981' },
  }
  const labels = Object.keys(data).map(k => sevMap[k]?.label || k)
  const values = Object.values(data) as number[]
  const colors = Object.keys(data).map(k => sevMap[k]?.color || '#6b7280')
  return { labels, values, colors }
})

const budgetChart = computed(() => {
  if (!chartData.value?.budgetVsActual) return null
  const d = chartData.value.budgetVsActual
  return {
    labels: d.labels,
    datasets: [
      { label: 'Budget', data: d.budget, color: '#3b82f6' },
      { label: 'Actual', data: d.actual, color: '#f97316' },
    ]
  }
})

const activityChart = computed(() => {
  if (!chartData.value?.activityTrend) return null
  const d = chartData.value.activityTrend
  return {
    labels: d.labels,
    datasets: [{ label: 'Activities', data: d.data, color: '#6366f1' }]
  }
})

const hasChartData = computed(() => {
  return chartData.value && (
    Object.keys(chartData.value.projectStatuses || {}).length > 0 ||
    (chartData.value.activityTrend?.data || []).some((v: number) => v > 0)
  )
})
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-gray-50/50">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <!-- Hero Section -->
        <div class="mb-6 sm:mb-8">
          <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl lg:text-3xl">
            {{ greeting }}, {{ user?.name?.split(' ')[0] || 'there' }}
          </h1>
          <div class="mt-2 flex items-center gap-3">
            <span v-if="org" class="text-sm text-gray-600">{{ org.name }}</span>
            <Badge v-if="org?.plan" :text="org.plan" tone="brand" size="sm" />
          </div>
        </div>

        <!-- Stats Grid — Row 1 -->
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
          <StatCard title="Projects" :value="stats.projects_count ?? 0" icon="folder" variant="brand" />
          
          <StatCard 
            v-if="showFinance && stats.total_revenue !== undefined"
            title="Total Revenue" :value="formatMoney(stats.total_revenue)" icon="revenue" variant="success" 
          />
          
          <StatCard 
            v-if="stats.open_issues_count !== undefined"
            title="Open Issues" :value="stats.open_issues_count" icon="issue"
            :variant="stats.open_issues_count > 0 ? 'warning' : 'default'"
          />
          
          <StatCard 
            v-if="showHR && stats.employees_count !== undefined"
            title="Team Members" :value="stats.employees_count" icon="users" 
          />
        </div>

        <!-- Stats Grid — Row 2 -->
        <div class="mt-3 grid grid-cols-2 gap-3 sm:mt-4 sm:gap-4 lg:grid-cols-4">
          <StatCard 
            v-if="showCRM && stats.leads_count !== undefined"
            title="CRM Leads" :value="stats.leads_count" :hint="stats.deals_count + ' deals'" 
          />
          
          <StatCard 
            v-if="showFinance && stats.outstanding_invoices !== undefined"
            title="Outstanding" :value="formatMoney(stats.outstanding_invoices)" icon="cost"
            :variant="stats.outstanding_invoices > 0 ? 'warning' : 'default'"
          />
          
          <StatCard 
            v-if="showFinance && stats.total_expenses !== undefined"
            title="Expenses" :value="formatMoney(stats.total_expenses)" icon="cost" 
          />
          
          <StatCard 
            v-if="stats.overdue_tasks_count !== undefined"
            title="Overdue Tasks" :value="stats.overdue_tasks_count" icon="issue"
            :variant="stats.overdue_tasks_count > 0 ? 'error' : 'default'"
            :hint="stats.overdue_tasks_count > 0 ? 'Needs attention' : 'On track'"
          />
        </div>

        <!-- Analytics Charts Section (Managers Only) -->
        <div v-if="isOrgManager && hasChartData" class="mt-6 sm:mt-8">
          <h2 class="mb-4 text-lg font-semibold text-gray-900">Analytics</h2>
          <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
            <!-- Project Health -->
            <div v-if="projectStatusChart" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-card">
              <DoughnutChart 
                title="Project Status" 
                :labels="projectStatusChart.labels" 
                :values="projectStatusChart.values" 
                :colors="projectStatusChart.colors"
                :height="220"
              />
            </div>

            <!-- Issue Severity -->
            <div v-if="issueChart" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-card">
              <DoughnutChart 
                title="Open Issues by Severity" 
                :labels="issueChart.labels" 
                :values="issueChart.values" 
                :colors="issueChart.colors"
                :height="220"
              />
            </div>

            <!-- Budget vs Actual (Finance) -->
            <div v-if="budgetChart && budgetChart.labels.length" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-card">
              <BarChart 
                title="Budget vs Actual Spend" 
                :labels="budgetChart.labels" 
                :datasets="budgetChart.datasets"
                :height="240"
              />
            </div>

            <!-- Activity Trend -->
            <div v-if="activityChart" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-card">
              <LineChart 
                title="Activity Trend (6 Months)" 
                :labels="activityChart.labels" 
                :datasets="activityChart.datasets"
                :height="240"
              />
            </div>
          </div>
        </div>

        <!-- Module Quick Actions -->
        <div class="mt-6 grid grid-cols-1 gap-4 sm:mt-8 sm:grid-cols-3 sm:gap-5">
          <Link
            v-if="showCRM && stats.leads_count !== undefined"
            href="/app/crm"
            class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 p-5 text-white shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5 sm:p-6"
          >
            <div class="relative z-10">
              <div class="text-sm font-medium text-blue-200">CRM</div>
              <div class="mt-1 text-lg font-bold sm:text-xl">{{ stats.leads_count }} Leads · {{ stats.deals_count }} Deals</div>
              <div class="mt-2 text-sm text-blue-200 group-hover:text-white transition sm:mt-3">Manage clients & pipeline →</div>
            </div>
            <div class="absolute -right-4 -top-4 text-6xl opacity-10 sm:text-7xl">🤝</div>
          </Link>

          <Link
            v-if="showFinance && stats.total_revenue !== undefined"
            href="/app/finance"
            class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 p-5 text-white shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5 sm:p-6"
          >
            <div class="relative z-10">
              <div class="text-sm font-medium text-emerald-200">Finance</div>
              <div class="mt-1 text-lg font-bold sm:text-xl">{{ formatMoney(stats.total_revenue) }} Revenue</div>
              <div class="mt-2 text-sm text-emerald-200 group-hover:text-white transition sm:mt-3">Invoices, expenses & budgets →</div>
            </div>
            <div class="absolute -right-4 -top-4 text-6xl opacity-10 sm:text-7xl">💰</div>
          </Link>

          <Link
            v-if="showHR && stats.employees_count !== undefined"
            href="/app/hr"
            class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-600 to-violet-700 p-5 text-white shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5 sm:p-6"
          >
            <div class="relative z-10">
              <div class="text-sm font-medium text-purple-200">HR</div>
              <div class="mt-1 text-lg font-bold sm:text-xl">{{ stats.employees_count }} Employees</div>
              <div class="mt-2 text-sm text-purple-200 group-hover:text-white transition sm:mt-3">Staff, payroll & attendance →</div>
            </div>
            <div class="absolute -right-4 -top-4 text-6xl opacity-10 sm:text-7xl">👥</div>
          </Link>
        </div>

        <!-- Two Column Layout: Projects + Activity -->
        <div class="mt-6 grid grid-cols-1 gap-5 sm:mt-8 sm:gap-6 lg:grid-cols-3">
          <!-- Recent Projects (2 cols) -->
          <div class="lg:col-span-2">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900">Recent Projects</h2>
              <Link href="/app/projects" class="text-sm font-medium text-brand-600 hover:text-brand-700 transition">
                View all →
              </Link>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
              <Link
                v-for="project in recentProjects.slice(0, 6)"
                :key="project.id"
                :href="`/app/projects/${project.id}`"
                class="group rounded-2xl border border-gray-200 bg-white p-4 shadow-card transition-all hover:border-brand-200 hover:shadow-card-hover sm:p-5"
              >
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition sm:text-base">
                      {{ project.name }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 sm:text-sm">
                      <span v-if="project.client_name">{{ project.client_name }}</span>
                      <span v-else class="text-gray-400">No client assigned</span>
                    </div>
                  </div>
                  <Badge 
                    :text="project.status"
                    :tone="project.status === 'active' ? 'green' : project.status === 'paused' ? 'amber' : 'gray'"
                    dot
                  />
                </div>
                <div v-if="project.location" class="mt-2 flex items-center gap-2 text-xs text-gray-400 sm:mt-3">
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span>{{ project.location }}</span>
                </div>
              </Link>

              <!-- Empty state -->
              <div 
                v-if="recentProjects.length === 0" 
                class="col-span-full rounded-2xl border-2 border-dashed border-gray-200 bg-white p-8 text-center sm:p-10"
              >
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50">
                  <svg class="h-7 w-7 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                  </svg>
                </div>
                <template v-if="isOrgManager">
                  <h3 class="mt-4 text-base font-semibold text-gray-900">Your projects will appear here</h3>
                  <p class="mt-1 text-sm text-gray-500">Create your first project to start tracking site activity.</p>
                  <Link 
                    href="/app/projects/create" 
                    class="mt-5 inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Project
                  </Link>
                </template>
                <template v-else>
                  <h3 class="mt-4 text-base font-semibold text-gray-900">No projects assigned yet</h3>
                  <p class="mt-1 text-sm text-gray-500">You'll see projects here once you're added to a team.</p>
                </template>
              </div>
            </div>
          </div>

          <!-- Recent Activity Feed (1 col) -->
          <div class="lg:col-span-1">
            <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
            <div class="mt-4 rounded-2xl border border-gray-200 bg-white shadow-card overflow-hidden">
              <div v-if="recentActivity.length === 0" class="p-8 text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100">
                  <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
                <p class="mt-3 text-sm text-gray-500">No recent activity</p>
              </div>
              <ul v-else class="divide-y divide-gray-100">
                <li 
                  v-for="activity in recentActivity" 
                  :key="activity.id"
                  class="px-4 py-3 hover:bg-gray-50 transition sm:py-3.5"
                >
                  <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-600 text-xs font-semibold text-white shadow-sm sm:h-9 sm:w-9">
                      {{ activity.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                    </div>
                    <div class="min-w-0 flex-1">
                      <div class="text-sm text-gray-900">
                        <span class="font-semibold">{{ activity.user?.name || 'Someone' }}</span>
                        <span class="text-gray-600"> {{ activity.title || activity.body || 'logged activity' }}</span>
                      </div>
                      <div class="mt-1 flex items-center gap-2 text-xs text-gray-500">
                        <Link 
                          v-if="activity.project" 
                          :href="`/app/projects/${activity.project.id}`" 
                          class="font-medium text-brand-600 hover:text-brand-700"
                        >
                          {{ activity.project.name }}
                        </Link>
                        <span v-if="activity.project">·</span>
                        <span>{{ formatDateTime(activity.created_at) }}</span>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

  </AuthenticatedLayout>
</template>
