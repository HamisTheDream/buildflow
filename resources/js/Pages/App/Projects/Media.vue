<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

import SectionCard from '@/Components/SectionCard.vue'
import SectionTitle from '@/Components/SectionTitle.vue'
import EmptyState from '@/Components/EmptyState.vue'
import Pagination from '@/Components/Pagination.vue'
import Badge from '@/Components/Badge.vue'

import { formatDateTime, truncate, formatEnum } from '@/utils/format'

const page = usePage<any>()
const project = computed(() => page.props.project ?? page.props.data?.project ?? null)

const paginator = computed(() => page.props.media ?? page.props.rows ?? null)

const media = computed<any[]>(() => {
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

function kindTone(k: string) {
  const v = (k || '').toLowerCase()
  if (v.includes('image')) return 'blue'
  if (v.includes('video')) return 'amber'
  if (v.includes('pdf') || v.includes('doc')) return 'gray'
  return 'green'
}

function fileKind(m:any) {
  return m.type || m.mime || m.kind || 'file'
}

function thumbUrl(m:any) {
  return m.thumb_url || m.thumbnail_url || null
}

function openUrl(m:any) {
  return m.url || m.download_url || m.public_url || '#'
}
</script>

<template>
  <SectionCard>
    <SectionTitle title="Media" subtitle="Upload evidence, photos, videos and documents.">
      <template #default>
        <Link
          v-if="project?.id"
          :href="`/app/projects/${project.id}/media?new=1`"
          class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-black"
        >
          Upload
        </Link>
      </template>
    </SectionTitle>

    <div class="mt-4" v-if="!media || media.length === 0">
      <EmptyState
        title="No media yet"
        description="Upload photos, videos, and files as evidence for reports, tasks, issues and costs."
        actionLabel="Upload"
        :actionHref="project?.id ? `/app/projects/${project.id}/media?new=1` : undefined"
      />
    </div>

    <div v-else class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <a
        v-for="m in media"
        :key="m.id"
        :href="openUrl(m)"
        target="_blank"
        class="group rounded-2xl border bg-white p-3 hover:bg-gray-50"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-900">
              {{ truncate(m.name || m.filename || 'File', 45) }}
            </div>
            <div class="mt-1 text-xs text-gray-500">
              {{ formatDateTime(m.created_at) }}
            </div>
          </div>

          <Badge :text="formatEnum(fileKind(m))" :tone="kindTone(fileKind(m)) as any" />
        </div>

        <div class="mt-3 overflow-hidden rounded-xl border bg-gray-50">
          <img
            v-if="thumbUrl(m)"
            :src="thumbUrl(m)"
            class="h-40 w-full object-cover transition group-hover:scale-[1.02]"
            alt=""
          />
          <div v-else class="flex h-40 items-center justify-center text-xs font-semibold text-gray-500">
            No preview
          </div>
        </div>

        <div class="mt-3 text-xs font-semibold text-indigo-600">
          Open
        </div>
      </a>
    </div>

    <div class="mt-5" v-if="links">
      <Pagination :links="links" />
    </div>
  </SectionCard>
</template>
