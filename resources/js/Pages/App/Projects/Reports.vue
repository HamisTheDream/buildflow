<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import SectionTitle from '@/Components/SectionTitle.vue'
import EmptyState from '@/Components/EmptyState.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'

import { formatDateTime, truncate, formatEnum } from '@/utils/format'

const page = usePage<any>()
const project = computed(() => page.props.project ?? page.props.data?.project ?? null)

// Accept either: reports paginator, rows paginator, or plain array
const paginator = computed(() => page.props.reports ?? page.props.rows ?? null)
const reports = computed<any[]>(() => {
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
  <AppLayout>
    <Head :title="project?.name ? `Reports — ${project.name}` : 'Reports'" />

    <div class="space-y-6">
      <div class="flex items-center justify-between gap-3">
        <div>
          <div class="text-sm font-semibold text-gray-900">Project reports</div>
          <div class="mt-1 text-sm text-gray-600" v-if="project?.name">
            {{ project.name }}
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Link
            v-if="project?.id"
            :href="`/app/projects/${project.id}`"
            class="rounded-lg border bg-white px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-50"
          >
            Back
          </Link>

          <!-- Safe default: doesn't break existing flow -->
          <Link
            v-if="project?.id"
            :href="`/app/projects/${project.id}/reports?new=1`"
            class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-black"
          >
            Generate report
          </Link>
        </div>
      </div>

      <SectionCard>
        <SectionTitle
          title="Reports"
          subtitle="Generate polished updates and share them with clients."
        />

        <div class="mt-4" v-if="!reports || reports.length === 0">
          <EmptyState
            title="No reports yet"
            description="Generate your first report to create a shareable project update."
            actionLabel="Generate report"
            :actionHref="project?.id ? `/app/projects/${project.id}/reports?new=1` : undefined"
          />
        </div>

        <div v-else class="mt-4 space-y-3">
          <div
            v-for="r in reports"
            :key="r.id"
            class="rounded-2xl border bg-white p-4 hover:bg-gray-50"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <div class="text-sm font-semibold text-gray-900">
                  {{ truncate(r.title || 'Report', 90) }}
                </div>

                <div class="mt-1 text-xs text-gray-500">
                  {{ formatDateTime(r.created_at) }}
                  <span v-if="r.author_name"> • {{ r.author_name }}</span>
                  <span v-else-if="r.author?.name"> • {{ r.author.name }}</span>
                </div>

                <div v-if="r.summary" class="mt-2 text-sm text-gray-700">
                  {{ truncate(r.summary, 180) }}
                </div>
              </div>

              <div class="flex flex-col items-end gap-2">
                <Badge
                  :text="formatEnum(r.type || 'report')"
                  :tone="typeTone(r.type || '') as any"
                />

                <div class="flex items-center gap-3">
                  <Link
                    v-if="project?.id && r.id"
                    :href="`/app/projects/${project.id}/reports/${r.id}`"
                    class="text-sm font-semibold text-indigo-600 hover:underline"
                  >
                    View
                  </Link>

                  <a
                    v-if="r.share_url"
                    :href="r.share_url"
                    target="_blank"
                    class="text-sm font-semibold text-gray-700 hover:underline"
                  >
                    Share
                  </a>
                </div>
              </div>
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
