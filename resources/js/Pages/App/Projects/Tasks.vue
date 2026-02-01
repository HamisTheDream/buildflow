<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

import SectionCard from '@/Components/SectionCard.vue'
import SectionTitle from '@/Components/SectionTitle.vue'
import EmptyState from '@/Components/EmptyState.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'

import { formatDate, formatDateTime, formatEnum, truncate } from '@/utils/format'

const page = usePage<any>()
const project = computed(() => page.props.project ?? page.props.data?.project ?? null)

// Accept either tasks paginator or array
const paginator = computed(() => page.props.tasks ?? page.props.rows ?? null)

const tasks = computed<any[]>(() => {
  const p = paginator.value
  if (!p) return page.props.items ?? []
  if (Array.isArray(p)) return p
  if (Array.isArray(p.data)) return p.data
  return []
})

const links = computed(() => {
  const p = paginator.value
  return p && !Array.isArray(p) ? p.links : null
})

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
  <SectionCard>
    <SectionTitle title="Tasks" subtitle="Assign work and track progress clearly.">
      <template #default>
        <Link
          v-if="project?.id"
          :href="`/app/projects/${project.id}/tasks?new=1`"
          class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-black"
        >
          New task
        </Link>
      </template>
    </SectionTitle>

    <div class="mt-4" v-if="!tasks || tasks.length === 0">
      <EmptyState
        title="No tasks yet"
        description="Create your first task to start tracking project work."
        actionLabel="New task"
        :actionHref="project?.id ? `/app/projects/${project.id}/tasks?new=1` : undefined"
      />
    </div>

    <div v-else class="mt-4 space-y-3">
      <div
        v-for="t in tasks"
        :key="t.id"
        class="rounded-2xl border bg-white p-4 hover:bg-gray-50"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-900">
              {{ truncate(t.title || t.name || 'Task', 100) }}
            </div>

            <div class="mt-1 text-xs text-gray-500">
              <span v-if="t.assignee_name">Assigned to {{ t.assignee_name }}</span>
              <span v-else-if="t.assignee?.name">Assigned to {{ t.assignee.name }}</span>
              <span v-else>Unassigned</span>

              <span v-if="t.due_on || t.due_date" class="ml-2">• Due {{ formatDate(t.due_on || t.due_date) }}</span>
              <span v-else-if="t.created_at" class="ml-2">• Created {{ formatDateTime(t.created_at) }}</span>
            </div>

            <div v-if="t.description" class="mt-2 text-sm text-gray-700">
              {{ truncate(t.description, 160) }}
            </div>
          </div>

          <div class="flex flex-col items-end gap-2">
            <Badge
              :text="formatEnum(t.status || 'pending')"
              :tone="statusTone(t.status || '') as any"
            />
            <Badge
              v-if="t.priority"
              :text="formatEnum(t.priority)"
              :tone="priorityTone(t.priority) as any"
            />

            <div class="flex items-center gap-3">
              <Link
                v-if="project?.id && t.id"
                :href="`/app/projects/${project.id}/tasks/${t.id}`"
                class="text-sm font-semibold text-indigo-600 hover:underline"
              >
                View
              </Link>
              <Link
                v-if="project?.id && t.id"
                :href="`/app/projects/${project.id}/tasks/${t.id}/edit`"
                class="text-sm font-semibold text-gray-700 hover:underline"
              >
                Edit
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-5" v-if="links">
      <Pagination :links="links" />
    </div>
  </SectionCard>
</template>
