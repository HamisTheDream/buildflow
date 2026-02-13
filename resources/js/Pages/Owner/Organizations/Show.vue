<script setup lang="ts">
import { ref } from 'vue'
import { Head, usePage, useForm, Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import Badge from '@/Components/Badge.vue'
import ActivityTimeline from '@/Components/Owner/CRM/ActivityTimeline.vue'
import TaskManager from '@/Components/Owner/CRM/TaskManager.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import { formatDateTime, formatEnum, formatMoneyKobo } from '@/utils/format'
import { computed } from 'vue'

const page = usePage<any>()
const admin = page.props.ownerAuth?.admin

const props = defineProps<{
  organization: any
  stats: any
  plans: { id:number; key:string; name:string }[]
  timeline: { data:any[]; links:any[] }
  tasks: any[]
}>()

const crmStages = ['lead', 'onboarding', 'active', 'risk', 'churned']

// Pagination handling
const loadMore = (url: string) => {
    if (!url) return
    useForm({}).get(url, { preserveScroll: true, preserveState: true })
}


const trashTask = (taskId: number) => {
  if (confirm('Delete this task?')) {
    useForm({}).delete(`/owner/tasks/${taskId}`, { preserveScroll: true })
  }
}

const completeTask = (task: any) => {
  useForm({ is_completed: true }).patch(`/owner/tasks/${task.id}`, { preserveScroll: true })
}

function statusTone(s:string) {
  if (s === 'active') return 'green'
  if (s === 'trial') return 'blue'
  if (s === 'past_due') return 'amber'
  if (s === 'suspended') return 'red'
  return 'gray'
}

// Action Forms
const extendTrialForm = useForm({ days: 7, reason: '' })
const compForm = useForm({ plan_key: 'starter', days: 30, reason: '' })
const downgradeForm = useForm({ plan_key: 'free', reason: '' })
const suspendForm = useForm({ reason: '' })
const reactivateForm = useForm({ mode: 'trial', days: 7, reason: '' })

// CRM Stage Modal
const stageModal = ref({
    show: false,
    stage: '',
    processing: false
})

const updateStage = (stage: string) => {
  if (stage === props.organization.crm_stage) return
  stageModal.value.stage = stage
  stageModal.value.show = true
}

const confirmUpdateStage = () => {
  stageModal.value.processing = true
  useForm({ stage: stageModal.value.stage }).patch(`/owner/organizations/${props.organization.id}/stage`, {
    preserveScroll: true,
    onSuccess: () => {
        stageModal.value.show = false
        stageModal.value.processing = false
    },
    onError: () => {
        stageModal.value.processing = false
    }
  })
}
</script>

<template>
  <OwnerLayout>
    <Head :title="organization.name" />

    <div class="space-y-6">
      <!-- CRM Header -->
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
          <div class="h-16 w-16 shrink-0 rounded-xl bg-gray-50 border border-gray-100 p-2">
            <img 
              v-if="organization.logo_url" 
              :src="organization.logo_url" 
              class="h-full w-full object-contain" 
            />
            <div v-else class="flex h-full w-full items-center justify-center font-bold text-gray-400 text-xl">
              {{ organization.name.charAt(0) }}
            </div>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ organization.name }}</h1>
            <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
              <span>{{ organization.owner?.name }}</span>
              <span>&bull;</span>
              <span>{{ organization.owner?.email }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3">
           <div class="flex items-center bg-gray-50 rounded-lg p-1 border border-gray-100">
             <button 
                v-for="stage in crmStages" 
                :key="stage"
                @click="updateStage(stage)"
                type="button"
                class="px-3 py-1.5 text-xs font-medium rounded-md capitalize transition-all"
                :class="organization.crm_stage === stage ? 'bg-white text-brand-600 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:bg-gray-200'"
             >
               {{ stage }}
             </button>
           </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- LEFT: Context (3 cols) -->
        <div class="lg:col-span-3 space-y-6">
          <!-- Overview Card -->
          <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
            <h3 class="font-semibold text-gray-900 border-b border-gray-100 pb-2">Overview</h3>
            
            <div>
              <div class="text-xs text-gray-500">Plan</div>
              <div class="font-medium text-gray-900">{{ organization.plan?.name || '—' }}</div>
            </div>
            
            <div>
              <div class="text-xs text-gray-500">Status</div>
              <div class="mt-1"><Badge :text="formatEnum(organization.subscription_status)" :tone="statusTone(organization.subscription_status) as any" /></div>
            </div>

            <div>
              <div class="text-xs text-gray-500">Created</div>
              <div class="font-medium text-gray-900">{{ formatDateTime(organization.created_at) }}</div>
            </div>

            <div v-if="organization.trial_ends_at">
              <div class="text-xs text-gray-500">Trial Ends</div>
              <div class="font-medium text-gray-900">{{ formatDateTime(organization.trial_ends_at) }}</div>
            </div>

             <div v-if="organization.paid_until">
              <div class="text-xs text-gray-500">Paid Until</div>
              <div class="font-medium text-gray-900">{{ formatDateTime(organization.paid_until) }}</div>
            </div>
          </div>

          <!-- Stats Card -->
           <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
            <h3 class="font-semibold text-gray-900 border-b border-gray-100 pb-2">Metrics</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.members }}</div>
                <div class="text-xs text-gray-500">Members</div>
              </div>
              <div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.projects }}</div>
                <div class="text-xs text-gray-500">Projects</div>
              </div>
            </div>
          </div>
        </div>

        <!-- CENTER: Activity Feed (6 cols) -->
        <div class="lg:col-span-6">
          <ActivityTimeline 
            :organization-id="organization.id"
            :notes="timeline.data"
          />
          <!-- Pagination -->
          <div class="mt-4 flex justify-center gap-2" v-if="timeline.links.length > 3">
             <template v-for="(link, key) in timeline.links" :key="key">
                <Link 
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    class="px-3 py-1 text-sm rounded border"
                    :class="link.active ? 'bg-brand-50 border-brand-200 text-brand-700' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50'"
                    preserve-scroll
                />
             </template>
          </div>
        </div>

        <!-- RIGHT: Actions & Tasks (3 cols) -->
        <div class="lg:col-span-3 space-y-6">
          <!-- Task Manager -->
          <TaskManager :organization-id="organization.id">
            <div v-for="task in tasks" :key="task.id" class="flex gap-2 items-start group p-2 hover:bg-gray-50 rounded-lg transition">
              <button 
                @click="completeTask(task)"
                class="mt-1 h-4 w-4 shrink-0 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
              ><span class="sr-only">Complete</span><div class="h-3 w-3 border-2 border-gray-400 rounded-full hover:border-brand-500"></div></button>
              
              <div class="flex-1 min-w-0">
                <div class="text-sm text-gray-900">{{ task.content }}</div>
                <div class="flex items-center gap-2 mt-1">
                   <div v-if="task.due_at" class="text-xs text-red-500 bg-red-50 px-1.5 rounded">{{ formatDateTime(task.due_at) }}</div>
                    <img v-if="task.assigned_to?.avatar_url" :src="task.assigned_to.avatar_url" class="h-4 w-4 rounded-full" />
                </div>
              </div>

              <button @click="trashTask(task.id)" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500">
                &times;
              </button>
            </div>
             <div v-if="tasks.length === 0" class="text-center py-4 text-sm text-gray-500">
              No active tasks.
            </div>
          </TaskManager>

          <!-- Quick Actions Accordion -->
           <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
             <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 font-semibold text-gray-900">Admin Actions</div>
             
             <div class="p-4 space-y-6">
                <!-- Reactivate -->
                <details class="group">
                  <summary class="flex cursor-pointer items-center justify-between text-sm font-medium text-gray-900">
                    Reactivate Account
                    <span class="ml-2 transition group-open:rotate-180">▼</span>
                  </summary>
                   <form class="mt-3 space-y-3" @submit.prevent="reactivateForm.post(`/owner/organizations/${organization.id}/reactivate`)">
                    <select v-model="reactivateForm.mode" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 text-sm focus:border-brand-500 focus:ring-brand-500">
                      <option value="trial">Trial</option>
                      <option value="active">Active</option>
                      <option value="free">Free</option>
                    </select>
                    <div v-if="reactivateForm.mode !== 'free'">
                       <input v-model="reactivateForm.days" type="number" placeholder="Days" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 text-sm focus:border-brand-500 focus:ring-brand-500" />
                    </div>
                    <button class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm text-white" :disabled="reactivateForm.processing">Reactivate</button>
                   </form>
                </details>

                <!-- Extend Trial -->
                 <details class="group">
                  <summary class="flex cursor-pointer items-center justify-between text-sm font-medium text-gray-900">
                    Extend Trial
                    <span class="ml-2 transition group-open:rotate-180">▼</span>
                  </summary>
                   <form class="mt-3 space-y-3" @submit.prevent="extendTrialForm.post(`/owner/organizations/${organization.id}/extend-trial`)">
                       <input v-model="extendTrialForm.days" type="number" placeholder="Days" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 text-sm focus:border-brand-500 focus:ring-brand-500" />
                       <input v-model="extendTrialForm.reason" placeholder="Reason" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 text-sm focus:border-brand-500 focus:ring-brand-500" />
                    <button class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm text-white" :disabled="extendTrialForm.processing">Extend</button>
                   </form>
                </details>

                <!-- Suspend -->
                <details class="group" v-if="admin?.is_super">
                  <summary class="flex cursor-pointer items-center justify-between text-sm font-medium text-red-600">
                    Suspend Organization
                    <span class="ml-2 transition group-open:rotate-180">▼</span>
                  </summary>
                   <form class="mt-3 space-y-3" @submit.prevent="suspendForm.post(`/owner/organizations/${organization.id}/suspend`)">
                       <input v-model="suspendForm.reason" placeholder="Reason (Required)" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 text-sm focus:border-brand-500 focus:ring-brand-500" />
                    <button class="w-full rounded-lg bg-red-600 px-3 py-2 text-sm text-white" :disabled="suspendForm.processing">Suspend</button>
                   </form>
                </details>
             </div>
           </div>
        </div>

      </div>
    </div>
    <!-- Confirm Stage Change -->
    <ConfirmationModal
        :show="stageModal.show"
        title="Update CRM Stage"
        :content="`Are you sure you want to move this organization to the '${stageModal.stage}' stage?`"
        confirm-text="Update Stage"
        :processing="stageModal.processing"
        @close="stageModal.show = false"
        @confirm="confirmUpdateStage"
    />
  </OwnerLayout>
</template>
