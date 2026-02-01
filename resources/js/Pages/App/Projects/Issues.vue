<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

import SectionCard from '@/Components/SectionCard.vue'
import SectionTitle from '@/Components/SectionTitle.vue'
import EmptyState from '@/Components/EmptyState.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'

import { formatDateTime, formatEnum, truncate } from '@/utils/format'

const page = usePage<any>()
const project = computed(() => page.props.project ?? page.props.data?.project ?? null)

const paginator = computed(() => page.props.issues ?? page.props.rows ?? null)

const issues = computed<any[]>(() => {
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
  <SectionCard>
    <SectionTitle title="Issues" subtitle="Track blockers, defects, and incidents.">
      <template #default>
        <Link
          v-if="project?.id"
          :href="`/app/projects/${project.id}/issues?new=1`"
          class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-black"
        >
          New issue
        </Link>
      </template>
    </SectionTitle>

    <div class="mt-4" v-if="!issues || issues.length === 0">
      <EmptyState
        title="No issues yet"
        description="Create an issue when something needs attention or blocks progress."
        actionLabel="New issue"
        :actionHref="project?.id ? `/app/projects/${project.id}/issues?new=1` : undefined"
      />
    </div>

    <div v-else class="mt-4 space-y-3">
      <div
        v-for="i in issues"
        :key="i.id"
        class="rounded-2xl border bg-white p-4 hover:bg-gray-50"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-900">
              {{ truncate(i.title || i.name || 'Issue', 110) }}
            </div>

            <div class="mt-1 text-xs text-gray-500">
              {{ formatDateTime(i.created_at) }}
              <span v-if="i.reported_by_name"> • {{ i.reported_by_name }}</span>
              <span v-else-if="i.reporter?.name"> • {{ i.reporter.name }}</span>

              <span v-if="i.location" class="ml-2">• {{ truncate(i.location, 40) }}</span>
            </div>

            <div v-if="i.description" class="mt-2 text-sm text-gray-700">
              {{ truncate(i.description, 180) }}
            </div>
          </div>

          <div class="flex flex-col items-end gap-2">
            <Badge
              :text="formatEnum(i.status || 'open')"
              :tone="statusTone(i.status || '') as any"
            />
            <Badge
              v-if="i.severity"
              :text="formatEnum(i.severity)"
              :tone="severityTone(i.severity) as any"
            />

            <div class="flex items-center gap-3">
              <Link
                v-if="project?.id && i.id"
                :href="`/app/projects/${project.id}/issues/${i.id}`"
                class="text-sm font-semibold text-indigo-600 hover:underline"
              >
                View
              </Link>
              <Link
                v-if="project?.id && i.id"
                :href="`/app/projects/${project.id}/issues/${i.id}/edit`"
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
