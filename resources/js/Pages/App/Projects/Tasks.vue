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

import { formatDate, formatDateTime, formatEnum, truncate } from '@/utils/format'

const props = defineProps<{
    project: any;
    canManage: boolean;
    tasks: any;
    filters: any;
    assignees: any[];
    units: any[];
}>()

const page = usePage<any>()

// Filter Logic
const filterForm = useForm({
    status: props.filters.status || '',
    assignee: props.filters.assignee || '',
    unit: props.filters.unit || '',
    q: props.filters.q || '',
})

function applyFilters() {
    filterForm.get(`/app/projects/${props.project.id}/tasks`, { preserveScroll: true, preserveState: true })
}

watch(() => filterForm.data(), () => {
    // optional debounce here if needed, but manual button usually better for complex filters
}, { deep: true })

// Create/Edit Modal
const showModal = ref(false)
const editingTask = ref<any>(null)

const form = useForm({
    title: '',
    description: '',
    status: 'todo',
    priority: 'normal',
    due_date: '',
    assigned_to: '',
    project_unit_id: '',
})

function openCreate() {
    editingTask.value = null
    form.reset()
    form.status = 'todo'
    form.priority = 'normal'
    
    // Check if new=1 present (optional, but good for linking)
    const params = new URLSearchParams(window.location.search)
    if (params.get('new')) {
         // Clear param?
         // window.history.replaceState({}, '', window.location.pathname)
    }
    
    showModal.value = true
}

function openEdit(task: any) {
    editingTask.value = task
    form.title = task.title
    form.description = task.description || ''
    form.status = task.status
    form.priority = task.priority
    form.due_date = task.due_date || ''
    form.assigned_to = task.assigned_to || ''
    form.project_unit_id = task.unit?.id || ''
    showModal.value = true
}

function submit() {
    console.log('Submit called, form data:', form.data())
    
    // Clean empty values to null for backend compatibility
    if (!form.assigned_to) form.assigned_to = null as any
    if (!form.project_unit_id) form.project_unit_id = null as any
    if (!form.due_date) form.due_date = null as any

    const url = editingTask.value 
        ? `/app/projects/${props.project.id}/tasks/${editingTask.value.id}`
        : `/app/projects/${props.project.id}/tasks`
    
    const method = editingTask.value ? 'patch' : 'post'
    console.log('Submitting to:', method.toUpperCase(), url)

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Task saved successfully')
            showModal.value = false
            form.reset()
        },
        onError: (errors: any) => {
            console.error('Task save errors:', errors)
            alert('Failed to save task. Check console for details.')
        },
        onFinish: () => {
            console.log('Request finished, form.processing:', form.processing, 'form.errors:', form.errors)
        }
    })
}

function deleteTask(task: any) {
    if(!confirm('Are you sure you want to delete this task?')) return
    useForm({}).delete(`/app/projects/${props.project.id}/tasks/${task.id}`)
}

// Helpers
function statusTone(s: string) {
  const v = (s || '').toLowerCase()
  if (v.includes('done') || v.includes('completed')) return 'green'
  if (v.includes('progress') || v.includes('doing')) return 'blue'
  if (v.includes('blocked')) return 'red'
  return 'amber'
}

function priorityTone(p: string) {
  const v = (p || '').toLowerCase()
  if (v.includes('urgent') || v.includes('high')) return 'red'
  if (v.includes('medium')) return 'amber'
  return 'gray'
}
</script>

<template>
  <ProjectLayout :project="project" active="tasks">
    <Head :title="project?.name ? `Tasks — ${project.name}` : 'Tasks'" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
             <div>
                <h2 class="text-lg font-medium text-gray-900">Tasks</h2>
                <p class="mt-1 text-sm text-gray-500">Assign work and track progress clearly.</p>
             </div>
             
             <div v-if="project?.id">
                 <button v-if="canManage" @click="openCreate()" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                      New task
                 </button>
             </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2 items-center bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
            <select v-model="filterForm.status" class="rounded-lg border-gray-300 text-sm py-1.5 focus:border-brand-500 focus:ring-brand-500">
                <option value="">All Statuses</option>
                <option value="todo">To Do</option>
                <option value="doing">In Progress</option>
                <option value="blocked">Blocked</option>
                <option value="done">Done</option>
            </select>
            <select v-model="filterForm.assignee" class="rounded-lg border-gray-300 text-sm py-1.5 focus:border-brand-500 focus:ring-brand-500">
                <option value="">All Assignees</option>
                <option v-for="a in assignees" :key="a.id" :value="a.id">{{ a.name }}</option>
            </select>
             <select v-model="filterForm.unit" class="rounded-lg border-gray-300 text-sm py-1.5 focus:border-brand-500 focus:ring-brand-500">
                <option value="">All Units</option>
                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
             
             <div class="relative flex-1 min-w-[200px]">
                <input 
                    v-model="filterForm.q" 
                    placeholder="Search tasks..." 
                    class="w-full rounded-lg border-gray-300 text-sm py-1.5 pl-8 focus:border-brand-500 focus:ring-brand-500" 
                />
                 <svg class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
             </div>

             <button @click="applyFilters" class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium hover:bg-gray-200 transition">Filter</button>
        </div>

    <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm">
      <PermissionNotice v-if="!canManage" class="mb-4" />

      <div v-if="!tasks.data || tasks.data.length === 0">
        <EmptyState
          icon="task"
          title="No tasks found"
          description="Add tasks to track work items and assign them to team members."
          :actionText="canManage ? 'New task' : undefined"
          :actionCallback="canManage ? openCreate : undefined"
          compact
        />
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="t in tasks.data"
          :key="t.id"
          class="group relative rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:border-brand-200 hover:shadow-md"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition-colors">
                  {{ truncate(t.title || t.name || 'Task', 100) }}
                </span>
                 <span v-if="t.unit" class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">
                     {{ t.unit.name }}
                 </span>
                <Badge
                  :text="formatEnum(t.status || 'pending')"
                  :tone="statusTone(t.status || '') as any"
                  size="sm"
                  dot
                />
              </div>

              <div class="mt-1.5 flex flex-wrap items-center gap-3 text-xs text-gray-500">
                <div class="flex items-center gap-1.5">
                  <div class="h-5 w-5 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-medium text-gray-600">
                    {{ (t.assignee?.name || '?').charAt(0).toUpperCase() }}
                  </div>
                  <span>{{ t.assignee?.name || 'Unassigned' }}</span>
                </div>

                <span v-if="t.due_date" :class="{'text-red-600 font-medium': new Date(t.due_date) < new Date()}">
                  {{ new Date(t.due_date) < new Date() ? 'Overdue' : 'Due' }} {{ formatDate(t.due_date) }}
                </span>
              </div>

              <div v-if="t.description" class="mt-3 text-sm text-gray-600 line-clamp-2">
                {{ t.description }}
              </div>
            </div>

            <div class="flex flex-col items-end gap-2">
              <Badge
                v-if="t.priority"
                :text="formatEnum(t.priority)"
                :tone="priorityTone(t.priority) as any"
                size="sm"
              />

              <div v-if="canManage" class="flex items-center gap-3 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="openEdit(t)" class="text-sm font-medium text-gray-500 hover:text-gray-700">Edit</button>
                 <button @click="deleteTask(t)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>

       <div class="mt-6" v-if="tasks.links">
        <Pagination :links="tasks.links" />
      </div>
    </div>

    </div>

    <!-- Modal -->
    <Modal :show="showModal" @close="showModal = false">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-6">{{ editingTask ? 'Edit Task' : 'New Task' }}</h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel value="Title" />
                    <TextInput v-model="form.title" class="mt-1 block w-full" placeholder="What needs to be done?" autofocus required />
                    <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</div>
                </div>
                 <div>
                    <InputLabel value="Description" />
                    <TextArea v-model="form.description" class="mt-1 block w-full" rows="3" />
                    <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
                </div>
                 <div class="grid grid-cols-2 gap-4">
                      <div>
                        <InputLabel value="Unit (Optional)" />
                        <select v-model="form.project_unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">No Unit</option>
                            <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <div v-if="form.errors.project_unit_id" class="mt-1 text-sm text-red-600">{{ form.errors.project_unit_id }}</div>
                     </div>
                     <div>
                          <InputLabel value="Assignee" />
                        <select v-model="form.assigned_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Unassigned</option>
                            <option v-for="a in assignees" :key="a.id" :value="a.id">{{ a.name }}</option>
                        </select>
                        <div v-if="form.errors.assigned_to" class="mt-1 text-sm text-red-600">{{ form.errors.assigned_to }}</div>
                     </div>
                 </div>
                  <div class="grid grid-cols-2 gap-4">
                      <div>
                         <InputLabel value="Status" />
                        <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="todo">To Do</option>
                            <option value="doing">In Progress</option>
                            <option value="blocked">Blocked</option>
                            <option value="done">Done</option>
                        </select>
                        <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</div>
                      </div>
                      <div>
                          <InputLabel value="Priority" />
                        <select v-model="form.priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="low">Low</option>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <div v-if="form.errors.priority" class="mt-1 text-sm text-red-600">{{ form.errors.priority }}</div>
                      </div>
                  </div>
                   <div>
                        <InputLabel value="Due Date" />
                         <TextInput type="date" v-model="form.due_date" class="mt-1 block w-full" />
                         <div v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</div>
                    </div>
                
                    <div class="flex justify-end gap-2 pt-4">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">Save Task</PrimaryButton>
                    </div>
            </form>
        </div>
    </Modal>
  </ProjectLayout>
</template>
