<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

import ProjectLayout from '@/Layouts/ProjectLayout.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import Checkbox from '@/Components/Checkbox.vue'

import { formatDateTime, truncate, formatEnum, timeAgo } from '@/utils/format'

const page = usePage<any>()
const project = computed(() => page.props.project ?? page.props.data?.project ?? null)

const props = defineProps<{
    project: any;
    reports: any;
    metrics: {
        total_count: number;
        active_shares: number;
        last_generated_at: string | null;
    }
}>()

const metrics = computed(() => props.metrics)

// Generator Modal State
const showGenerator = ref(false)
const form = useForm({
    title: '',
    type: 'weekly',
    from_date: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    to_date: new Date().toISOString().split('T')[0],
    options: {
        include_today_logs: true,
        include_logs: true,
        include_tasks: true,
        include_issues: true,
        include_costs: true,
    },
    share_expires_days: 30,
    send_email: false,
    email_to: '',
})

function openGenerator() {
    form.reset()
    // Default title
    form.title = `${props.project.name} - Status Update`
    showGenerator.value = true
}

function generate() {
    form.post(`/app/projects/${props.project.id}/reports/generate`, {
        onSuccess: () => {
            showGenerator.value = false
            form.reset()
        },
        onError: (errors) => {
            console.error('Report generation failed:', errors)
            alert('Failed to generate report. Please check the form for errors or try again.')
        },
        onFinish: () => {
            console.log('Report generation request finished')
        }
    })
}

function typeTone(type: string) {
  const t = (type || '').toLowerCase()
  if (t.includes('daily')) return 'blue'
  if (t.includes('weekly')) return 'green'
  if (t.includes('incident')) return 'red'
  if (t.includes('client')) return 'amber'
  return 'gray'
}
</script>

<template>
  <ProjectLayout :project="project" active="reports">
    <Head :title="project?.name ? `Reports — ${project.name}` : 'Reports'" />

    <div class="space-y-8">
      
      <!-- Report Command Center -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Metric 1: Last Activity -->
          <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
              <div class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">Last Update</div>
              <div class="text-2xl font-bold text-gray-900">
                  {{ metrics.last_generated_at ? timeAgo(metrics.last_generated_at) : 'Never' }}
              </div>
              <div class="mt-1 text-xs text-gray-400">Most recent report</div>
          </div>

           <!-- Metric 2: Active Shares -->
          <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
              <div class="flex items-center justify-between mb-1">
                  <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Active Shares</div>
                  <span v-if="metrics.active_shares > 0" class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
              </div>
              <div class="text-2xl font-bold text-gray-900">{{ metrics.active_shares }}</div>
              <div class="mt-1 text-xs text-gray-400">Live accessible links</div>
          </div>

           <!-- Metric 3: CTA -->
           <button 
                @click="openGenerator"
                class="rounded-xl bg-slate-900 p-6 shadow-lg text-left group hover:bg-slate-800 transition-all hover:translate-y-[-2px]"
            >
                <div class="flex items-center justify-between text-white mb-2">
                    <span class="text-lg font-bold">New Report</span>
                    <svg class="h-6 w-6 text-brand-500 group-hover:text-brand-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                </div>
                <div class="text-sm text-slate-400 group-hover:text-slate-300">Generate PDF status update</div>
           </button>
      </div>

      <!-- Report History -->
      <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
              <h3 class="text-sm font-semibold text-gray-900">Report History</h3>
          </div>

          <div v-if="!reports.data || reports.data.length === 0" class="p-12 text-center">
                <div class="mx-auto h-12 w-12 text-gray-300 mb-4">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No reports yet</h3>
                <p class="mt-1 text-sm text-gray-500">Generate your first status report to share with clients.</p>
                <div class="mt-6">
                     <button @click="openGenerator" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700">
                        Create Report
                    </button>
                </div>
          </div>

          <div v-else class="divide-y divide-gray-100">
              <div v-for="report in reports.data" :key="report.id" class="p-4 hover:bg-gray-50 transition flex items-center justify-between gap-4">
                  <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2 mb-1">
                          <span class="text-sm font-bold text-gray-900 truncate">{{ report.title }}</span>
                          <Badge :text="formatEnum(report.type)" :tone="typeTone(report.type)" size="sm" />
                      </div>
                      <div class="flex items-center gap-3 text-xs text-gray-500">
                          <span>{{ formatDateTime(report.created_at) }}</span>
                          <span>&bull;</span>
                          <span>{{ report.generator?.name || 'System' }}</span>
                      </div>
                  </div>
                  
                  <div class="flex items-center gap-3">
                       <a 
                            v-if="report.share_token"
                            :href="`/share/report/${report.share_token}`" 
                            target="_blank"
                            class="text-sm font-medium text-gray-600 hover:text-brand-600 hover:underline"
                        >
                            Share Link
                        </a>
                       <a :href="`/app/projects/${project.id}/reports/${report.id}/download`" class="p-2 text-gray-400 hover:text-gray-900 bg-gray-50 rounded-lg hover:bg-gray-200 transition">
                             <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                       </a>
                  </div>
              </div>
          </div>

          <div v-if="reports.links" class="p-4 border-t border-gray-100">
               <Pagination :links="reports.links" />
          </div>
      </div>
    </div>

    <!-- Generator Modal -->
    <Modal :show="showGenerator" @close="showGenerator = false">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-6">Generate New Report</h2>
            
            <form @submit.prevent="generate" class="space-y-6">
                <!-- Type & Title -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Report Type" />
                        <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            <option value="daily">Daily Log</option>
                            <option value="weekly">Weekly Status</option>
                            <option value="cost">Financial Summary</option>
                            <option value="incident">Incident Report</option>
                        </select>
                        <InputError :message="form.errors.type" class="mt-2" />
                    </div>
                     <div>
                        <InputLabel value="Report Title" />
                        <TextInput v-model="form.title" class="mt-1 block w-full" placeholder="e.g. Week 42 Update" />
                        <InputError :message="form.errors.title" class="mt-2" />
                    </div>
                </div>

                <!-- Date Range -->
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="From Date" />
                        <TextInput type="date" v-model="form.from_date" class="mt-1 block w-full" />
                        <InputError :message="form.errors.from_date" class="mt-2" />
                    </div>
                     <div>
                        <InputLabel value="To Date" />
                        <TextInput type="date" v-model="form.to_date" class="mt-1 block w-full" />
                        <InputError :message="form.errors.to_date" class="mt-2" />
                    </div>
                </div>

                <!-- Content Options -->
                <div>
                     <InputLabel value="Include Content" class="mb-3" />
                     <div class="grid grid-cols-2 gap-3">
                         <label class="flex items-center space-x-2">
                             <Checkbox v-model="form.options.include_logs" />
                             <span class="text-sm text-gray-700">Daily Logs</span>
                         </label>
                         <label class="flex items-center space-x-2">
                             <Checkbox v-model="form.options.include_tasks" />
                             <span class="text-sm text-gray-700">Task Progress</span>
                         </label>
                          <label class="flex items-center space-x-2">
                             <Checkbox v-model="form.options.include_issues" />
                             <span class="text-sm text-gray-700">Open Issues</span>
                         </label>
                          <label class="flex items-center space-x-2">
                             <Checkbox v-model="form.options.include_costs" />
                             <span class="text-sm text-gray-700">Financials</span>
                         </label>
                     </div>
                     <InputError :message="form.errors.options" class="mt-2" />
                </div>
                
                <!-- Actions -->
                <div class="pt-4 border-t border-gray-100 flex justify-end gap-3 flex-col sm:flex-row items-center">
                   <div v-if="form.hasErrors" class="text-sm text-red-600 mr-auto">
                        Please check the form for errors.
                   </div>
                    <SecondaryButton @click="showGenerator = false">Cancel</SecondaryButton>
                    <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Generate PDF
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
  </ProjectLayout>
</template>
