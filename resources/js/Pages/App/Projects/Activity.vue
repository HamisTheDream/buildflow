<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import SectionTitle from '@/Components/SectionTitle.vue'
import StatCard from '@/Components/StatCard.vue'
import EmptyState from '@/Components/EmptyState.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'

import { formatDateTime, formatEnum, truncate, formatNumber } from '@/utils/format'

const page = usePage<any>()

const project = computed(() => page.props.project ?? page.props.data?.project ?? null)

// Accept either: rows (pagination), activities (pagination), or plain array
const rowsPaginator = computed(() => page.props.rows ?? page.props.activities ?? null)
const rows = computed<any[]>(() => {
  const p = rowsPaginator.value
  if (!p) return page.props.activity ?? []
  if (Array.isArray(p)) return p
  if (Array.isArray(p.data)) return p.data
  return []
})
const links = computed(() => {
  const p = rowsPaginator.value
  return p && !Array.isArray(p) ? p.links : null
})

const stats = computed(() => page.props.stats ?? page.props.activity_stats ?? null)

function actionTone(action: string) {
  const a = (action || '').toLowerCase()
  if (a.includes('create') || a.includes('added')) return 'green'
  if (a.includes('update') || a.includes('edited')) return 'blue'
  if (a.includes('delete') || a.includes('removed')) return 'red'
  if (a.includes('export')) return 'amber'
  return 'gray'
}

function displayTitle(r: any) {
  return (
    r.summary ||
    r.title ||
    (r.action ? formatEnum(r.action) : 'Activity') ||
    'Activity'
  )
}
</script>

<template>
  <AppLayout>
    <Head :title="project?.name ? `Activity — ${project.name}` : 'Activity'" />

    <div class="space-y-6">
      <div class="flex items-center justify-between gap-3">
        <div>
          <div class="text-sm font-semibold text-gray-900">Project activity</div>
          <div class="mt-1 text-sm text-gray-600" v-if="project?.name">
            {{ project.name }}
          </div>
        </div>

        <Link
          v-if="project?.id"
          :href="`/app/projects/${project.id}`"
          class="rounded-lg border bg-white px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-50"
        >
          Back to project
        </Link>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <StatCard label="Total events" :value="formatNumber(stats?.total ?? rows.length)" />
        <StatCard label="Today" :value="formatNumber(stats?.today ?? 0)" />
        <StatCard label="Last 7 days" :value="formatNumber(stats?.week ?? 0)" />
      </div>

      <SectionCard>
        <SectionTitle
          title="Activity feed"
          subtitle="A clean timeline of actions performed by team members."
        />

        <div class="mt-4" v-if="!rows || rows.length === 0">
          <EmptyState
            title="No activity yet"
            description="Actions will appear here once you start adding logs, tasks, issues, media, costs, and reports."
          />
        </div>

        <div v-else class="mt-4 space-y-3">
          <div
            v-for="r in rows"
            :key="r.id ?? `${r.created_at}-${r.action}`"
            class="rounded-2xl border bg-white p-4 hover:bg-gray-50"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="text-sm font-semibold text-gray-900">
                  {{ truncate(displayTitle(r), 140) }}
                </div>

                <div class="mt-1 text-xs text-gray-500">
                  {{ formatDateTime(r.created_at) }}
                  <span v-if="r.actor_name"> • {{ r.actor_name }}</span>
                  <span v-else-if="r.actor?.name"> • {{ r.actor.name }}</span>

                  <span v-if="r.context"> • {{ truncate(r.context, 60) }}</span>
                </div>
              </div>

              <Badge
                :text="formatEnum(r.action || r.type || 'event')"
                :tone="actionTone(r.action || r.type || '') as any"
              />
            </div>

            <div
              v-if="r.details || r.description"
              class="mt-2 whitespace-pre-wrap text-sm text-gray-700"
            >
              {{ r.details || r.description }}
            </div>
          </div>
        </div>

        <div class="mt-5" v-if="links">
          <Pagination :links="links" />
        </div>
      </SectionCard>
    </div>
  </AppLayout>
</template>
