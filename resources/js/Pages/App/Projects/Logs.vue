<script setup lang="ts">
import ProjectLayout from '@/Layouts/ProjectLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AttachmentsBox from '@/Components/AttachmentsBox.vue'

const props = defineProps<{
  project: { id:number; name:string; status:string }
  canWrite: boolean
  types: string[]
  authors: { id:number; name:string }[]
  filters: { type:string; author:string; from:string; to:string; q:string }
  logs: any
}>()

const page = usePage()
const flash = computed(() => (page.props.flash as any) ?? {})

const filterForm = useForm({
  type: props.filters.type || '',
  author: props.filters.author || '',
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
  filterForm.from = ''
  filterForm.to = ''
  filterForm.q = ''
  applyFilters()
}

const addForm = useForm({
  log_date: new Date().toISOString().slice(0, 10),
  log_time: '',
  type: 'general',
  title: '',
  body: '',
})

function addLog() {
  addForm.post(`/app/projects/${props.project.id}/logs`, { preserveScroll: true, onSuccess: () => addForm.reset('title','body','log_time') })
}

const editingId = ref<number|null>(null)
const editForm = useForm({
  log_date: '',
  log_time: '',
  type: 'general',
  title: '',
  body: '',
})

function startEdit(l:any) {
  editingId.value = l.id
  editForm.log_date = l.log_date
  editForm.log_time = l.log_time || ''
  editForm.type = l.type
  editForm.title = l.title || ''
  editForm.body = l.body || ''
}

function cancelEdit() {
  editingId.value = null
  editForm.reset()
}

function saveEdit(id:number) {
  editForm.patch(`/app/projects/${props.project.id}/logs/${id}`, { preserveScroll: true, onSuccess: () => cancelEdit() })
}

function deleteLog(id:number) {
  const f = useForm({})
  f.delete(`/app/projects/${props.project.id}/logs/${id}`, { preserveScroll: true })
}
</script>

<template>
  <ProjectLayout :project="project" active="logs">
    <Head :title="`Logs — ${project.name}`" />

    <div v-if="flash.success" class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">
      {{ flash.success }}
    </div>
    <div v-if="flash.error" class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">
      {{ flash.error }}
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Left: Filters + Add -->
      <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
            <a :href="`/app/projects/${project.id}/export/logs`" class="rounded-lg bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200">
              Export CSV
            </a>
          </div>

          <div class="mt-4 space-y-3">
            <div>
              <label class="text-sm text-gray-700">Type</label>
              <select v-model="filterForm.type" class="mt-1 w-full rounded-lg border p-2">
                <option value="">All</option>
                <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
              </select>
            </div>

            <div>
              <label class="text-sm text-gray-700">Author</label>
              <select v-model="filterForm.author" class="mt-1 w-full rounded-lg border p-2">
                <option value="">All</option>
                <option v-for="a in authors" :key="a.id" :value="String(a.id)">{{ a.name }}</option>
              </select>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div>
                <label class="text-sm text-gray-700">From</label>
                <input type="date" v-model="filterForm.from" class="mt-1 w-full rounded-lg border p-2" />
              </div>
              <div>
                <label class="text-sm text-gray-700">To</label>
                <input type="date" v-model="filterForm.to" class="mt-1 w-full rounded-lg border p-2" />
              </div>
            </div>

            <div>
              <label class="text-sm text-gray-700">Search</label>
              <input v-model="filterForm.q" class="mt-1 w-full rounded-lg border p-2" placeholder="title/body..." />
            </div>

            <div class="flex gap-2">
              <button @click="applyFilters" class="flex-1 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Apply
              </button>
              <button @click="clearFilters" class="rounded-lg bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200">
                Clear
              </button>
            </div>
          </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
          <h2 class="text-lg font-semibold text-gray-900">Add Log</h2>
          <p class="mt-1 text-sm text-gray-600" v-if="!canWrite">You don’t have permission to add logs.</p>

          <form v-if="canWrite" class="mt-4 space-y-3" @submit.prevent="addLog">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div>
                <label class="text-sm text-gray-700">Date</label>
                <input type="date" v-model="addForm.log_date" class="mt-1 w-full rounded-lg border p-2" />
                <div v-if="addForm.errors.log_date" class="mt-1 text-sm text-red-600">{{ addForm.errors.log_date }}</div>
              </div>
              <div>
                <label class="text-sm text-gray-700">Time (optional)</label>
                <input type="time" v-model="addForm.log_time" class="mt-1 w-full rounded-lg border p-2" />
                <div v-if="addForm.errors.log_time" class="mt-1 text-sm text-red-600">{{ addForm.errors.log_time }}</div>
              </div>
            </div>

            <div>
              <label class="text-sm text-gray-700">Type</label>
              <select v-model="addForm.type" class="mt-1 w-full rounded-lg border p-2">
                <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
              </select>
              <div v-if="addForm.errors.type" class="mt-1 text-sm text-red-600">{{ addForm.errors.type }}</div>
            </div>

            <div>
              <label class="text-sm text-gray-700">Title (optional)</label>
              <input v-model="addForm.title" class="mt-1 w-full rounded-lg border p-2" placeholder="Short summary..." />
            </div>

            <div>
              <label class="text-sm text-gray-700">Details</label>
              <textarea v-model="addForm.body" class="mt-1 w-full rounded-lg border p-2" rows="4" placeholder="What happened? What changed? Measurements, photos references..." />
            </div>

            <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50" :disabled="addForm.processing">
              Add Log Entry
            </button>
          </form>
        </div>
      </div>

      <!-- Right: Timeline -->
      <div class="lg:col-span-2 rounded-xl bg-white p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-900">Timeline</h2>
        <p class="mt-1 text-sm text-gray-600">A chronological history of project activities.</p>

        <div class="mt-4 space-y-3">
          <div v-for="l in logs.data" :key="l.id" class="rounded-lg border p-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
              <div class="min-w-0">
                <div class="text-xs text-gray-500">
                  {{ l.log_date }} <span v-if="l.log_time">• {{ l.log_time }}</span> • <span class="capitalize">{{ l.type }}</span>
                </div>
                <div class="mt-1 truncate text-sm font-semibold text-gray-900">
                  {{ l.title || '(No title)' }}
                </div>
                <div class="mt-1 text-xs text-gray-500">
                  By {{ l.user?.name || '—' }}
                </div>
              </div>

              <div class="flex flex-wrap gap-2" v-if="canWrite">
                <button class="rounded bg-gray-100 px-3 py-1 text-sm hover:bg-gray-200" @click="startEdit(l)">Edit</button>
                <button class="rounded bg-red-50 px-3 py-1 text-sm text-red-700 hover:bg-red-100" @click="deleteLog(l.id)">Delete</button>
              </div>
            </div>

            <!-- Body -->
            <div class="mt-3 whitespace-pre-wrap text-sm text-gray-700">
              {{ l.body || '—' }}
            </div>

            <!-- Inline editor -->
            <div v-if="editingId === l.id" class="mt-4 rounded-lg bg-gray-50 p-4">
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                  <label class="text-sm text-gray-700">Date</label>
                  <input type="date" v-model="editForm.log_date" class="mt-1 w-full rounded-lg border p-2" />
                </div>
                <div>
                  <label class="text-sm text-gray-700">Time</label>
                  <input type="time" v-model="editForm.log_time" class="mt-1 w-full rounded-lg border p-2" />
                </div>
              </div>

              <div class="mt-3">
                <label class="text-sm text-gray-700">Type</label>
                <select v-model="editForm.type" class="mt-1 w-full rounded-lg border p-2">
                  <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                </select>
              </div>

              <div class="mt-3">
                <label class="text-sm text-gray-700">Title</label>
                <input v-model="editForm.title" class="mt-1 w-full rounded-lg border p-2" />
              </div>

              <div class="mt-3">
                <label class="text-sm text-gray-700">Details</label>
                <textarea v-model="editForm.body" class="mt-1 w-full rounded-lg border p-2" rows="4" />
              </div>

              <div class="mt-3 flex gap-2">
                <button class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700" @click="saveEdit(l.id)">
                  Save
                </button>
                <button class="rounded-lg bg-gray-100 px-4 py-2 text-sm hover:bg-gray-200" @click="cancelEdit">
                  Cancel
                </button>
              </div>
            </div>

            <AttachmentsBox
              :projectId="project.id"
              attachableType="log"
              :attachableId="l.id"
              :canManage="canWrite && l.user?.id === $page.props.auth.user.id"
              :attachments="l.attachments"
              class="mt-4"
            />
          </div>

          <div v-if="logs.data.length === 0" class="rounded-lg border border-dashed p-10 text-center text-gray-600">
            No logs found. Add your first log entry.
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="logs.links?.length" class="mt-4 flex flex-wrap gap-2">
          <Link
            v-for="lnk in logs.links"
            :key="lnk.label"
            :href="lnk.url || ''"
            v-html="lnk.label"
            class="rounded border px-3 py-1 text-sm"
            :class="[
              lnk.active ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-white text-gray-700',
              !lnk.url ? 'opacity-50 pointer-events-none' : 'hover:bg-gray-50'
            ]"
          />
        </div>
      </div>
    </div>
  </ProjectLayout>
</template>
