<script setup lang="ts">
import ProjectLayout from '@/Layouts/ProjectLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps<{ project: any }>()
</script>

<template>
  <ProjectLayout :project="project" active="overview">
    <Head :title="project.name" />

    <div class="rounded-xl bg-white p-6 shadow">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
           <!-- Additional header content if needed, but main title is in layout now -->
           <h2 class="text-lg font-semibold text-gray-900">Project Overview</h2>
        </div>

        <Link
          :href="`/app/projects/${project.id}/team`"
          class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200"
        >
          Manage Team
        </Link>
      </div>

      <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-lg border p-4">
          <div class="text-xs text-gray-500">Client</div>
          <div class="mt-1 text-sm font-semibold text-gray-900">{{ project.client_name || '—' }}</div>
          <div class="mt-1 text-sm text-gray-600">{{ project.client_phone || '' }}</div>
          <div class="text-sm text-gray-600">{{ project.client_email || '' }}</div>
        </div>

        <div class="rounded-lg border p-4">
          <div class="text-xs text-gray-500">Timeline</div>
          <div class="mt-1 text-sm text-gray-700">
            Start: <span class="font-semibold text-gray-900">{{ project.start_date || '—' }}</span>
          </div>
          <div class="mt-1 text-sm text-gray-700">
            End: <span class="font-semibold text-gray-900">{{ project.end_date || '—' }}</span>
          </div>
        </div>
      </div>

      <div class="mt-6">
        <div class="text-xs text-gray-500">Description</div>
        <div class="mt-1 whitespace-pre-wrap text-sm text-gray-700">{{ project.description || '—' }}</div>
      </div>

      <div class="mt-8 rounded-lg border border-dashed p-6 text-sm text-gray-600">
        Next: we’ll add Tabs (Today, Logs, Tasks, Issues, Media, Costs, Reports) + Team management.
      </div>
    </div>
  </ProjectLayout>
</template>
