<script setup lang="ts">
import ProjectLayout from '@/Layouts/ProjectLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AttachmentsBox from '@/Components/AttachmentsBox.vue'

const props = defineProps<{
  project: { id: number; name: string; status: string }
  myLog: any | null
  canSeeAll: boolean
  todayLogs: any[]
  today: string
}>()

const page = usePage()
const flash = computed(() => (page.props.flash as any) ?? {})

const form = useForm({
  work_done: props.myLog?.work_done ?? '',
  blockers: props.myLog?.blockers ?? '',
  next_steps: props.myLog?.next_steps ?? '',
  progress_percent: props.myLog?.progress_percent ?? '',
  weather: props.myLog?.weather ?? '',
})

function save() {
  form.post(`/app/projects/${props.project.id}/today`, { preserveScroll: true })
}
</script>

<template>
  <ProjectLayout :project="project" active="today">
    <Head :title="`Today — ${project.name}`" />

    <div v-if="flash.success" class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">
      {{ flash.success }}
    </div>
    <div v-if="flash.error" class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">
      {{ flash.error }}
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- My log -->
      <div class="rounded-xl bg-white p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-900">My Today Log</h2>
        <p class="mt-1 text-sm text-gray-600">Date: {{ today }}</p>

        <form class="mt-5 space-y-4" @submit.prevent="save">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm text-gray-700">Progress (%)</label>
              <input v-model="form.progress_percent" type="number" min="0" max="100" class="mt-1 w-full rounded-lg border p-2" />
              <div v-if="form.errors.progress_percent" class="mt-1 text-sm text-red-600">{{ form.errors.progress_percent }}</div>
            </div>
            <div>
              <label class="text-sm text-gray-700">Weather (optional)</label>
              <input v-model="form.weather" class="mt-1 w-full rounded-lg border p-2" placeholder="Sunny, Rainy..." />
              <div v-if="form.errors.weather" class="mt-1 text-sm text-red-600">{{ form.errors.weather }}</div>
            </div>
          </div>

          <div>
            <label class="text-sm text-gray-700">Work done</label>
            <textarea v-model="form.work_done" class="mt-1 w-full rounded-lg border p-2" rows="4" />
            <div v-if="form.errors.work_done" class="mt-1 text-sm text-red-600">{{ form.errors.work_done }}</div>
          </div>

          <div>
            <label class="text-sm text-gray-700">Blockers</label>
            <textarea v-model="form.blockers" class="mt-1 w-full rounded-lg border p-2" rows="3" />
          </div>

          <div>
            <label class="text-sm text-gray-700">Next steps</label>
            <textarea v-model="form.next_steps" class="mt-1 w-full rounded-lg border p-2" rows="3" />
          </div>

          <button
            class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
            :disabled="form.processing"
          >
            Save Today Log
          </button>
        </form>

        <AttachmentsBox
          v-if="myLog"
          :projectId="project.id"
          attachableType="today_log"
          :attachableId="myLog.id"
          :canManage="true"
          :attachments="myLog.attachments"
          class="mt-6 border-t pt-4"
        />
      </div>

      <!-- Today logs (for PM/Owner/Admin) -->
      <div class="rounded-xl bg-white p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-900">Today’s Logs</h2>
        <p class="mt-1 text-sm text-gray-600">
          <span v-if="canSeeAll">Visible because you have management access.</span>
          <span v-else>Only your own log is visible.</span>
        </p>

        <div v-if="!canSeeAll" class="mt-4 rounded-lg border border-dashed p-6 text-sm text-gray-600">
          No access to view team logs.
        </div>

        <div v-else class="mt-4 space-y-3">
          <div v-for="l in todayLogs" :key="l.id" class="rounded-lg border p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="truncate text-sm font-semibold text-gray-900">{{ l.user.name }}</div>
                <div class="text-xs text-gray-500">{{ l.user.email }} • Updated: {{ l.updated_at }}</div>
              </div>
              <div class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700">
                {{ l.progress_percent ?? '—' }}%
              </div>
            </div>

            <div class="mt-3 space-y-2 text-sm text-gray-700">
              <div>
                <div class="text-xs text-gray-500">Work done</div>
                <div class="whitespace-pre-wrap">{{ l.work_done || '—' }}</div>
              </div>
              <div>
                <div class="text-xs text-gray-500">Blockers</div>
                <div class="whitespace-pre-wrap">{{ l.blockers || '—' }}</div>
              </div>
              <div>
                <div class="text-xs text-gray-500">Next steps</div>
                <div class="whitespace-pre-wrap">{{ l.next_steps || '—' }}</div>
              </div>
            </div>

            <AttachmentsBox
              :projectId="project.id"
              attachableType="today_log"
              :attachableId="l.id"
              :canManage="false"
              :attachments="l.attachments"
              class="mt-4"
            />
          </div>

          <div v-if="todayLogs.length === 0" class="rounded-lg border border-dashed p-8 text-center text-gray-600">
            No logs yet today.
          </div>
        </div>
      </div>
    </div>
  </ProjectLayout>
</template>
