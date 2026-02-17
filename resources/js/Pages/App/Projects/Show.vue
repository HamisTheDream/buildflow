<script setup lang="ts">
import ProjectLayout from '@/Layouts/ProjectLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { formatDate, formatCurrency } from '@/utils/format'
import WhatsAppShareButton from '@/Components/WhatsAppShareButton.vue'
import { computed } from 'vue'

const baseUrl = computed(() => window.location.origin)

const props = defineProps<{ 
    project: any;
    metrics: {
        logs_today: number;
        tasks_completed_today: number;
        open_issues: number;
        overdue_tasks: number;
        total_budget: number;
        total_spend: number;
    }
}>()

const budgetProgress = computed(() => {
    if (props.metrics.total_budget === 0) return 0
    return Math.min(100, Math.round((props.metrics.total_spend / props.metrics.total_budget) * 100))
})

const budgetTone = computed(() => {
    if (budgetProgress.value > 90) return 'red'
    if (budgetProgress.value > 75) return 'amber'
    return 'green'
})

const projectStatusColor = computed(() => {
    if (props.metrics.overdue_tasks > 3 || props.metrics.open_issues > 5) return 'text-red-600 bg-red-50 border-red-200'
    if (props.metrics.overdue_tasks > 0) return 'text-amber-600 bg-amber-50 border-amber-200'
    return 'text-emerald-600 bg-emerald-50 border-emerald-200'
})

const projectStatusText = computed(() => {
    if (props.metrics.overdue_tasks > 3 || props.metrics.open_issues > 5) return 'At Risk'
    if (props.metrics.overdue_tasks > 0) return 'Needs Attention'
    return 'On Track'
})

</script>

<template>
  <ProjectLayout :project="project" active="overview">
    <Head :title="project.name" />

    <div class="space-y-6">
        <!-- Command Header -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                 <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">Project Overview</h1>
                    <span 
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold border"
                        :class="projectStatusColor"
                    >
                        {{ projectStatusText }}
                    </span>
                 </div>
                 <p v-if="project.client_name" class="text-sm text-gray-500 mt-1">
                    Client: <span class="font-medium text-gray-900">{{ project.client_name }}</span>
                 </p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                 <WhatsAppShareButton 
                   :message="`📊 ${project.name} Update:\n• ${metrics.logs_today} logs today\n• ${metrics.open_issues} open issues\n• Budget: ${budgetProgress}% used\n\nView report: ${baseUrl}/app/projects/${project.id}`"
                   variant="outline"
                   size="sm"
                 />
                 <Link 
                    :href="`/app/projects/${project.id}/today`" 
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-600 shadow-sm transition-all hover:shadow-md"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Daily Log
                 </Link>
            </div>
        </div>

        <!-- The Bento Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- 1. Daily Pulse -->
            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100 flex flex-col justify-between h-full">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Daily Pulse</h3>
                    <p class="text-xs text-gray-400 mt-1">Activity for {{ formatDate(new Date().toISOString()) }}</p>
                </div>
                
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="text-center rounded-lg bg-gray-50 p-4">
                        <div class="text-2xl font-bold text-gray-900">{{ metrics.logs_today }}</div>
                        <div class="text-xs font-medium text-gray-500 mt-1">Logs Created</div>
                    </div>
                    <div class="text-center rounded-lg bg-gray-50 p-4">
                        <div class="text-2xl font-bold text-gray-900">{{ metrics.tasks_completed_today }}</div>
                        <div class="text-xs font-medium text-gray-500 mt-1">Tasks Done</div>
                    </div>
                </div>
            </div>

            <!-- 2. Financial Health -->
            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100 flex flex-col justify-between h-full">
                <div>
                     <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Financial Health</h3>
                        <span 
                            class="text-xs font-bold px-2 py-1 rounded bg-gray-100"
                            :class="{
                                'text-green-600 bg-green-50': budgetTone === 'green',
                                'text-amber-600 bg-amber-50': budgetTone === 'amber',
                                'text-red-600 bg-red-50': budgetTone === 'red',
                            }"
                        >
                            {{ budgetProgress }}% Used
                        </span>
                     </div>
                </div>

                <div class="mt-6">
                    <div class="flex items-end gap-1 mb-2 flex-wrap">
                        <span class="text-xl sm:text-2xl font-bold text-gray-900 break-all">{{ formatCurrency(metrics.total_spend) }}</span>
                        <span class="text-xs sm:text-sm text-gray-500 mb-1 break-all">/ {{ formatCurrency(metrics.total_budget) }}</span>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="h-3 w-full rounded-full bg-gray-100 overflow-hidden">
                        <div 
                            class="h-full rounded-full transition-all duration-1000"
                            :class="{
                                'bg-emerald-500': budgetTone === 'green',
                                'bg-amber-500': budgetTone === 'amber',
                                'bg-red-500': budgetTone === 'red',
                            }"
                            :style="{ width: `${budgetProgress}%` }"
                        ></div>
                    </div>
                </div>
            </div>

             <!-- 3. Action Items -->
            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100 flex flex-col h-full">
                 <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Action Items</h3>
                 
                 <div class="flex-1 space-y-3">
                    <Link 
                        v-if="metrics.overdue_tasks > 0"
                        :href="`/app/projects/${project.id}/tasks?filter=overdue`" 
                        class="flex items-center justify-between p-3 rounded-lg bg-red-50 border border-red-100 group hover:bg-red-100 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-red-500"></div>
                            <span class="text-sm font-medium text-red-900">Overdue Tasks</span>
                        </div>
                        <span class="text-sm font-bold text-red-700 group-hover:underline">{{ metrics.overdue_tasks }} &rarr;</span>
                    </Link>
                    <div v-else class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-100 opacity-60">
                         <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-gray-300"></div>
                            <span class="text-sm text-gray-500">Overdue Tasks</span>
                        </div>
                        <span class="text-sm font-medium text-gray-400">0</span>
                    </div>

                    <Link 
                        v-if="metrics.open_issues > 0"
                        :href="`/app/projects/${project.id}/issues`" 
                        class="flex items-center justify-between p-3 rounded-lg bg-orange-50 border border-orange-100 group hover:bg-orange-100 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-orange-500"></div>
                            <span class="text-sm font-medium text-orange-900">Open Issues</span>
                        </div>
                        <span class="text-sm font-bold text-orange-700 group-hover:underline">{{ metrics.open_issues }} &rarr;</span>
                    </Link>
                    <div v-else class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-100 opacity-60">
                         <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-gray-300"></div>
                            <span class="text-sm text-gray-500">Open Issues</span>
                        </div>
                        <span class="text-sm font-medium text-gray-400">0</span>
                    </div>
                 </div>
            </div>
        </div>

        <!-- Quick Access Grid -->
         <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <Link :href="`/app/projects/${project.id}/tasks`" class="group p-4 rounded-xl border border-gray-200 bg-white hover:border-brand-300 hover:shadow-md transition-all">
                <div class="text-gray-400 group-hover:text-brand-500 mb-2">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="font-semibold text-gray-900">Tasks</div>
            </Link>
             <Link :href="`/app/projects/${project.id}/issues`" class="group p-4 rounded-xl border border-gray-200 bg-white hover:border-brand-300 hover:shadow-md transition-all">
                <div class="text-gray-400 group-hover:text-brand-500 mb-2">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="font-semibold text-gray-900">Issues</div>
            </Link>
             <Link :href="`/app/projects/${project.id}/costs`" class="group p-4 rounded-xl border border-gray-200 bg-white hover:border-brand-300 hover:shadow-md transition-all">
                <div class="text-gray-400 group-hover:text-brand-500 mb-2">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="font-semibold text-gray-900">Costs</div>
            </Link>
             <Link :href="`/app/projects/${project.id}/media`" class="group p-4 rounded-xl border border-gray-200 bg-white hover:border-brand-300 hover:shadow-md transition-all">
                <div class="text-gray-400 group-hover:text-brand-500 mb-2">
                   <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="font-semibold text-gray-900">Media</div>
            </Link>
         </div>

        <!-- Project Details (Footer) -->
        <div v-if="project.description" class="rounded-xl bg-gray-50 p-6 border border-gray-100">
            <h4 class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-2">Project Notes</h4>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ project.description }}</p>
        </div>

    </div>
  </ProjectLayout>
</template>
