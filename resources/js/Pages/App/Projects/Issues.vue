<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Link, usePage, useForm, Head } from '@inertiajs/vue3'

import ProjectLayout from '@/Layouts/ProjectLayout.vue'
import SectionTitle from '@/Components/SectionTitle.vue'
import EmptyState from '@/Components/EmptyState.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'
import PermissionNotice from '@/Components/PermissionNotice.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

import { formatDateTime, formatEnum, truncate, formatDate } from '@/utils/format'

const props = defineProps<{
    project: any;
    canManage: boolean;
    issues: any;
    filters: any;
    assignees: any[];
    units: any[];
}>()

const page = usePage<any>()

// Filter Logic
const filterForm = useForm({
    status: props.filters.status || '',
    severity: props.filters.severity || '',
    assignee: props.filters.assignee || '',
    unit: props.filters.unit || '',
    q: props.filters.q || '',
})

function applyFilters() {
    filterForm.get(`/app/projects/${props.project.id}/issues`, { preserveScroll: true, preserveState: true })
}

watch(() => filterForm.data(), () => {
    // optional debounce here if needed, but manual button usually better for complex filters
}, { deep: true })

// Create/Edit Modal
const showModal = ref(false)
const editingIssue = ref<any>(null)

const form = useForm({
    title: '',
    description: '',
    status: 'open',
    severity: 'medium',
    category: 'general',
    due_date: '',
    assigned_to: '',
    project_unit_id: '',
})

function openCreate() {
    editingIssue.value = null
    form.reset()
    form.status = 'open'
    form.severity = 'medium'
    
    // Check if new=1 present
    const params = new URLSearchParams(window.location.search)
    if (params.get('new')) {
         // window.history.replaceState({}, '', window.location.pathname)
    }
    
    showModal.value = true
}

function openEdit(issue: any) {
    editingIssue.value = issue
    form.title = issue.title
    form.description = issue.description || ''
    form.status = issue.status
    form.severity = issue.severity
    form.category = issue.category || 'general'
    form.due_date = issue.due_date || ''
    form.assigned_to = issue.assigned_to || ''
    form.project_unit_id = issue.unit?.id || ''
    showModal.value = true
}

function submit() {
    if (editingIssue.value) {
        form.patch(`/app/projects/${props.project.id}/issues/${editingIssue.value.id}`, {
            onSuccess: () => showModal.value = false
        })
    } else {
        form.post(`/app/projects/${props.project.id}/issues`, {
            onSuccess: () => showModal.value = false
        })
    }
}

function deleteIssue(issue: any) {
    if(!confirm('Are you sure you want to delete this issue?')) return
    useForm({}).delete(`/app/projects/${props.project.id}/issues/${issue.id}`)
}

// Helpers
function statusTone(s: string) {
  const v = (s || '').toLowerCase()
  if (v.includes('resolved') || v.includes('closed')) return 'green'
  if (v.includes('investig') || v.includes('progress')) return 'blue'
  if (v.includes('blocked') || v.includes('critical')) return 'red'
  return 'amber'
}

function severityTone(s: string) {
  const v = (s || '').toLowerCase()
  if (v.includes('critical')) return 'red'
  if (v.includes('high')) return 'amber'
  if (v.includes('medium')) return 'blue'
  return 'gray'
}
</script>

<template>
  <ProjectLayout :project="project" active="issues">
    <Head :title="project?.name ? `Issues — ${project.name}` : 'Issues'" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
             <div>
                <h2 class="text-lg font-medium text-gray-900">Issues</h2>
                <p class="mt-1 text-sm text-gray-500">Track blockers, defects, and incidents.</p>
             </div>
             
             <div v-if="project?.id">
                <button type="button" v-if="canManage" @click="openCreate()" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">
                     <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                      New issue
                </button>
             </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2 items-center bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
            <select v-model="filterForm.status" class="rounded-lg border-gray-300 text-sm py-1.5 focus:border-red-500 focus:ring-red-500">
                <option value="">All Statuses</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="blocked">Blocked</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
            </select>
            <select v-model="filterForm.severity" class="rounded-lg border-gray-300 text-sm py-1.5 focus:border-red-500 focus:ring-red-500">
                <option value="">All Severities</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
            </select>
             <select v-model="filterForm.assignee" class="rounded-lg border-gray-300 text-sm py-1.5 focus:border-red-500 focus:ring-red-500">
                <option value="">All Assignees</option>
                 <option v-for="a in assignees" :key="a.id" :value="a.id">{{ a.name }}</option>
            </select>
             <select v-model="filterForm.unit" class="rounded-lg border-gray-300 text-sm py-1.5 focus:border-red-500 focus:ring-red-500">
                <option value="">All Units</option>
                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
             
             <div class="relative flex-1 min-w-[200px]">
                <input 
                    v-model="filterForm.q" 
                    placeholder="Search issues..." 
                    class="w-full rounded-lg border-gray-300 text-sm py-1.5 pl-8 focus:border-red-500 focus:ring-red-500" 
                />
                 <svg class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
             </div>
             
             <button type="button" @click="applyFilters" class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium hover:bg-gray-200 transition">Filter</button>
        </div>

      <PermissionNotice v-if="!canManage" />

      <div v-if="!issues.data || issues.data.length === 0" class="rounded-xl border-2 border-dashed border-gray-200 p-12">
        <EmptyState
          icon="issue"
          title="No issues reported"
          description="Issues help flag problems on site. Add one when something needs attention."
          :actionText="canManage ? 'New issue' : undefined"
          :actionCallback="canManage ? openCreate : undefined"
          compact
        />
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="i in issues.data"
          :key="i.id"
          class="group relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:border-red-200 hover:shadow-md cursor-pointer"
          @click="openEdit(i)"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-900 group-hover:text-red-700 transition-colors">
                  {{ truncate(i.title || i.name || 'Issue', 110) }}
                </span>
                 <span v-if="i.unit" class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">
                     {{ i.unit.name }}
                 </span>
                <Badge
                  :text="formatEnum(i.status || 'open')"
                  :tone="statusTone(i.status || '') as any"
                  size="sm"
                  dot
                />
              </div>

              <div class="mt-1.5 flex flex-wrap items-center gap-3 text-xs text-gray-500">
                <span>{{ formatDateTime(i.created_at) }}</span>
                <span v-if="i.reporter?.name"> • by {{ i.reporter.name }}</span>
                 <span v-if="i.assignee?.name"> • assigned to {{ i.assignee.name }}</span>

                <span v-if="i.location" class="flex items-center gap-1 ml-1 text-gray-600">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  {{ truncate(i.location, 40) }}
                </span>
              </div>

              <div v-if="i.description" class="mt-3 text-sm text-gray-600 line-clamp-2">
                {{ i.description }}
              </div>
            </div>

            <div class="flex flex-col items-end gap-2">
              <Badge
                v-if="i.severity"
                :text="formatEnum(i.severity)"
                :tone="severityTone(i.severity) as any"
                size="sm"
              />

              <div v-if="canManage" class="flex items-center gap-3 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button type="button" @click.stop="openEdit(i)" class="text-sm font-medium text-gray-500 hover:text-gray-700">Edit</button>
                 <button type="button" @click.stop="deleteIssue(i)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6" v-if="issues.links">
        <Pagination :links="issues.links" />
      </div>
    </div>

    <!-- Modal -->
    <Modal :show="showModal" @close="showModal = false">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-6">{{ editingIssue ? 'Edit Issue' : 'Log New Issue' }}</h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel value="Title" />
                    <TextInput v-model="form.title" class="mt-1 block w-full" placeholder="What's the issue?" autofocus required />
                </div>
                 <div>
                    <InputLabel value="Description" />
                    <TextArea v-model="form.description" class="mt-1 block w-full" rows="3" />
                </div>
                 <div class="grid grid-cols-2 gap-4">
                      <div>
                        <InputLabel value="Unit (Optional)" />
                        <select v-model="form.project_unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">No Unit</option>
                            <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                     </div>
                     <div>
                        <InputLabel value="Category" />
                        <select v-model="form.category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="general">General</option>
                            <option value="quality">Quality</option>
                            <option value="safety">Safety</option>
                            <option value="material">Material</option>
                            <option value="labor">Labor</option>
                            <option value="client">Client</option>
                            <option value="finance">Finance</option>
                            <option value="scope">Scope</option>
                            <option value="other">Other</option>
                        </select>
                     </div>
                 </div>
                  <div class="grid grid-cols-2 gap-4">
                      <div>
                         <InputLabel value="Status" />
                        <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="blocked">Blocked</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                      </div>
                      <div>
                          <InputLabel value="Severity" />
                        <select v-model="form.severity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                      </div>
                  </div>
                   <div class="grid grid-cols-2 gap-4">
                       <div>
                            <InputLabel value="Assign To (Optional)" />
                            <select v-model="form.assigned_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Unassigned</option>
                                <option v-for="a in assignees" :key="a.id" :value="a.id">{{ a.name }}</option>
                            </select>
                       </div>
                        <div>
                        <InputLabel value="Due Date" />
                         <TextInput type="date" v-model="form.due_date" class="mt-1 block w-full" />
                    </div>
                   </div>
                
                    <div class="flex justify-end gap-2 pt-4">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">Save Issue</PrimaryButton>
                    </div>
            </form>
        </div>
    </Modal>
  </ProjectLayout>
</template>
