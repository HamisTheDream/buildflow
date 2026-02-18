<script setup lang="ts">
import ProjectLayout from '@/Layouts/ProjectLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AttachmentsBox from '@/Components/AttachmentsBox.vue'
import EmptyState from '@/Components/EmptyState.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps<{
  project: { id:number; name:string; status:string }
  canWrite: boolean
  types: string[]
  authors: { id:number; name:string }[]
  filters: { type:string; author:string; from:string; to:string; q:string }
  logs: any
  units: { id:number; name:string }[]
}>()

const page = usePage()
const flash = computed(() => (page.props.flash as any) ?? {})

const filterForm = useForm({
  type: props.filters.type || '',
  author: props.filters.author || '',
  unit: props.filters.unit || '',
  from: props.filters.from || '',
  to: props.filters.to || '',
  q: props.filters.q || '',
})

function applyFilters() {
  filterForm.get(`/app/projects/${props.project.id}/logs`, {
    preserveScroll: true,
    preserveState: true,
  })
}

function clearFilters() {
  filterForm.type = ''
  filterForm.author = ''
  filterForm.unit = ''
  filterForm.from = ''
  filterForm.to = ''
  filterForm.q = ''
  applyFilters()
}

// "New Log" is now "New Site Report"
const addForm = useForm({
  log_date: new Date().toISOString().slice(0, 10),
  log_time: '',
  type: 'general',
  title: '',
  workforce_count: '' as string | number,
  weather: '' as string,
  body: '',
  materials_delivered: '',
  blockers: '', // Critical
  next_day_plan: '', // Continuity
  project_unit_id: '' as string | number,
})

function addLog() {
  addForm.post(`/app/projects/${props.project.id}/logs`, { preserveScroll: true, onSuccess: () => {
    addForm.reset('title','body','log_time', 'workforce_count', 'weather', 'materials_delivered', 'blockers', 'next_day_plan', 'project_unit_id')
  }})
}

const editingId = ref<number|null>(null)
const editForm = useForm({
  log_date: '',
  log_time: '',
  type: 'general',
  title: '',
  workforce_count: '' as string | number,
  weather: '' as string,
  body: '',
  materials_delivered: '',
  blockers: '',
  next_day_plan: '',
  project_unit_id: '' as string | number,
})

function startEdit(l:any) {
  editingId.value = l.id
  editForm.log_date = l.log_date
  editForm.log_time = l.log_time || ''
  editForm.type = l.type
  editForm.title = l.title || ''
  editForm.workforce_count = l.workforce_count || ''
  editForm.weather = l.weather || ''
  editForm.body = l.body || ''
  editForm.materials_delivered = l.materials_delivered || ''
  editForm.blockers = l.blockers || ''
  editForm.next_day_plan = l.next_day_plan || ''
  editForm.project_unit_id = l.unit?.id || ''
}

function cancelEdit() {
  editingId.value = null
  editForm.reset()
}

function saveEdit(id:number) {
  editForm.patch(`/app/projects/${props.project.id}/logs/${id}`, { preserveScroll: true, onSuccess: () => cancelEdit() })
}

function deleteLog(id:number) {
  if (!confirm('Are you sure you want to delete this log?')) return
  const f = useForm({})
  f.delete(`/app/projects/${props.project.id}/logs/${id}`, { preserveScroll: true })
}
</script>

<template>
  <ProjectLayout :project="project" active="logs">
    <Head :title="`Logs — ${project.name}`" />

    <div v-if="flash.success" class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 border border-green-200">
      {{ flash.success }}
    </div>

    <!-- Layout: Left (Filters + Add Form) | Right (Timeline) -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
      
      <!-- Left Column -->
      <div class="space-y-6">
         <!-- Add Report Card -->
         <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
             <div class="bg-gray-900 px-6 py-4 border-b border-gray-800">
                  <h2 class="text-lg font-bold text-white flex items-center gap-2">
                      <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                      New Site Report
                  </h2>
             </div>
             
             <div v-if="!canWrite" class="p-6 text-sm text-gray-500 text-center">
                 You have view-only access.
             </div>

             <form v-else class="p-6 space-y-5" @submit.prevent="addLog">
                <!-- Row 1: Date/Time/Type -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-4">
                         <div>
                            <InputLabel value="Date" />
                            <TextInput type="date" v-model="addForm.log_date" class="mt-1 block w-full" />
                        </div>
                        <div>
                             <InputLabel value="Type" />
                             <select v-model="addForm.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                 <option v-for="t in types" :key="t" :value="t" class="capitalize">{{ t }}</option>
                             </select>
                        </div>
                    </div>
                     <div class="space-y-4">
                         <div>
                            <InputLabel value="Unit (Optional)" />
                            <select v-model="addForm.project_unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">No Specific Unit</option>
                                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                        </div>
                         <div>
                             <InputLabel value="Workforce Count" />
                             <TextInput type="number" v-model="addForm.workforce_count" placeholder="#" class="mt-1 block w-full" />
                        </div>
                    </div>
                </div>

                 <!-- Row 2: Metrics -->
                <div class="grid grid-cols-1 gap-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <div>
                        <InputLabel value="Weather" />
                         <TextInput type="text" v-model="addForm.weather" placeholder="e.g. Sunny" class="mt-1 block w-full" />
                    </div>
                </div>

                <!-- Sections -->
                <div>
                    <InputLabel value="Activities Completed" />
                    <TextArea v-model="addForm.body" rows="3" placeholder="What happened on site today?" class="mt-1 block w-full" />
                </div>

                <div>
                    <InputLabel value="Materials Delivered" />
                    <TextArea v-model="addForm.materials_delivered" rows="2" placeholder="List items received..." class="mt-1 block w-full" />
                </div>

                 <div>
                    <InputLabel value="Issues / Blockers" class="text-red-700" />
                    <TextArea v-model="addForm.blockers" rows="2" placeholder="Delays, shortage, etc." class="mt-1 block w-full border-red-200 focus:border-red-500 focus:ring-red-500" />
                </div>

                 <div>
                    <InputLabel value="Plan for Tomorrow" class="text-green-700" />
                    <TextArea v-model="addForm.next_day_plan" rows="2" placeholder="What is the focus next?" class="mt-1 block w-full border-green-200 focus:border-green-500 focus:ring-green-500" />
                </div>

                <div class="pt-2">
                    <PrimaryButton type="submit" class="w-full justify-center" :disabled="addForm.processing">
                        Submit Daily Report
                    </PrimaryButton>
                </div>
             </form>
         </div>

         <!-- Filters Card -->
         <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
             <div class="flex items-center justify-between mb-4">
                 <h3 class="font-semibold text-gray-900">Filters</h3>
                 <button @click="clearFilters" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Reset</button>
             </div>
             <div class="space-y-3">
                 <select v-model="filterForm.type" class="w-full rounded-md border-gray-300 text-sm">
                     <option value="">All Types</option>
                     <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                 </select>
                 <select v-model="filterForm.unit" class="w-full rounded-md border-gray-300 text-sm">
                     <option value="">All Units</option>
                     <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                 </select>
                 <select v-model="filterForm.author" class="w-full rounded-md border-gray-300 text-sm">
                     <option value="">All Authors</option>
                     <option v-for="a in authors" :key="a.id" :value="a.id">{{ a.name }}</option>
                 </select>
                 <div class="grid grid-cols-2 gap-2">
                     <input type="date" v-model="filterForm.from" class="rounded-md border-gray-300 text-sm" />
                     <input type="date" v-model="filterForm.to" class="rounded-md border-gray-300 text-sm" />
                 </div>
                 <button @click="applyFilters" class="w-full rounded-md bg-gray-100 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Apply Filter</button>
             </div>
         </div>
      </div>

      <!-- Right Column: Timeline -->
      <div class="lg:col-span-2">
          <div class="flex items-center justify-between mb-4">
               <h2 class="text-xl font-bold text-gray-900">Project Timeline</h2>
               <a :href="`/app/projects/${project.id}/export/logs`" class="text-sm font-medium text-gray-500 hover:text-gray-900">Download CSV</a>
          </div>

          <div class="space-y-6">
              <div v-if="logs.data.length === 0" class="py-12">
                  <EmptyState title="No logs found" description="No daily reports match your filters." icon="document" />
              </div>

              <!-- Log Card -->
              <div v-for="log in logs.data" :key="log.id" class="group relative bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 hover:shadow-md transition-shadow">
                  <!-- Header -->
                  <div class="flex justify-between items-start mb-4">
                      <div class="flex items-center gap-3">
                           <div class="h-10 w-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-sm">
                               {{ log.user?.name ? log.user.name.charAt(0) : '?' }}
                           </div>
                           <div>
                               <div class="text-sm font-bold text-gray-900">
                                   {{ log.user?.name || 'Unknown' }}
                                   <span v-if="log.unit" class="ml-2 inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">
                                       {{ log.unit.name }}
                                   </span>
                               </div>
                               <div class="text-xs text-gray-500">{{ log.log_date }} <span v-if="log.log_time">at {{ log.log_time }}</span> &bull; <span class="capitalize">{{ log.type }} Report</span></div>
                           </div>
                      </div>
                      
                      <!-- Actions -->
                      <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity" v-if="canWrite">
                           <button @click="startEdit(log)" class="p-1 text-gray-400 hover:text-indigo-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></button>
                           <button @click="deleteLog(log.id)" class="p-1 text-gray-400 hover:text-red-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                      </div>
                  </div>

                  <!-- Inline Edit Mode -->
                  <div v-if="editingId === log.id" class="mb-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                       <div class="mb-4">
                           <InputLabel value="Unit" />
                            <select v-model="editForm.project_unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">No Specific Unit</option>
                                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                       </div>
                      <div class="grid grid-cols-2 gap-4 mb-4">
                          <TextInput v-model="editForm.log_date" type="date" />
                          <TextInput v-model="editForm.workforce_count" type="number" placeholder="Workforce Count" />
                      </div>
                      <TextArea v-model="editForm.body" rows="3" class="mb-3 w-full" placeholder="Activities" />
                      <TextArea v-model="editForm.materials_delivered" rows="2" class="mb-3 w-full" placeholder="Materials" />
                      <TextArea v-model="editForm.blockers" rows="2" class="mb-3 w-full border-red-200" placeholder="Blockers" />
                      <TextArea v-model="editForm.next_day_plan" rows="2" class="mb-3 w-full border-green-200" placeholder="Next Day Plan" />
                      <div class="flex justify-end gap-2">
                          <SecondaryButton @click="cancelEdit">Cancel</SecondaryButton>
                          <PrimaryButton @click="saveEdit(log.id)">Save Changes</PrimaryButton>
                      </div>
                  </div>

                  <!-- Read Mode -->
                   <div v-else class="space-y-4">
                       <!-- Key Stats -->
                       <div v-if="log.workforce_count || log.weather" class="flex gap-4 text-sm font-medium text-gray-700 bg-gray-50 p-2 rounded-md inline-block">
                           <span v-if="log.workforce_count" class="flex items-center gap-1"><svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg> {{ log.workforce_count }} Workers</span>
                           <span v-if="log.weather" class="flex items-center gap-1"><svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" /></svg> {{ log.weather }}</span>
                       </div>

                       <div v-if="log.body" class="prose prose-sm max-w-none text-gray-800">
                           <p class="whitespace-pre-wrap">{{ log.body }}</p>
                       </div>

                       <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="log.materials_delivered" class="text-sm bg-blue-50/50 p-3 rounded-lg border border-blue-100">
                                <strong class="block text-blue-900 text-xs uppercase tracking-wide mb-1">Materials Delivered</strong>
                                <span class="text-gray-700 whitespace-pre-wrap">{{ log.materials_delivered }}</span>
                            </div>
                            <div v-if="log.blockers" class="text-sm bg-red-50 p-3 rounded-lg border border-red-100">
                                <strong class="block text-red-900 text-xs uppercase tracking-wide mb-1">⚠️ Issues / Blockers</strong>
                                <span class="text-red-800 font-medium whitespace-pre-wrap">{{ log.blockers }}</span>
                            </div>
                       </div>
                        
                       <div v-if="log.next_day_plan" class="text-sm border-t border-gray-100 pt-3 mt-2">
                            <strong class="text-gray-500 text-xs uppercase tracking-wide mr-2">Plan for Tomorrow:</strong>
                            <span class="text-gray-700">{{ log.next_day_plan }}</span>
                       </div>
                   </div>

                   <AttachmentsBox
                        :projectId="project.id"
                        attachableType="log"
                        :attachableId="log.id"
                        :canManage="canWrite && log.user?.id === $page.props.auth.user.id"
                        :attachments="log.attachments"
                        class="mt-4 border-t border-gray-100 pt-4"
                    />
              </div>

               <div v-if="logs.links" class="pt-4">
                 <!-- Simple pagination links -->
                  <div class="flex gap-1 justify-center">
                     <Link v-for="link in logs.links" :key="link.label" :href="link.url ?? ''" v-html="link.label" class="px-3 py-1 border rounded text-sm hover:bg-gray-50" :class="{'bg-indigo-50 text-indigo-700 border-indigo-200': link.active, 'opacity-50 pointer-events-none': !link.url}" />
                  </div>
              </div>
          </div>
      </div>
    </div>

  </ProjectLayout>
</template>
