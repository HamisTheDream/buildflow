<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{
  project: { id: number; name: string; status?: string }
  active: string
}>()

const tabs = computed(() => [
  { key: 'overview', label: 'Overview', href: `/app/projects/${props.project.id}` },
  { key: 'today', label: 'Today Log', href: `/app/projects/${props.project.id}/today` },
  { key: 'logs', label: 'Logs', href: `/app/projects/${props.project.id}/logs` },
  { key: 'tasks', label: 'Tasks', href: `/app/projects/${props.project.id}/tasks` },
  { key: 'issues', label: 'Issues', href: `/app/projects/${props.project.id}/issues` },
  { key: 'media', label: 'Media', href: `/app/projects/${props.project.id}/media` },
  { key: 'costs', label: 'Costs', href: `/app/projects/${props.project.id}/costs` },
  { key: 'reports', label: 'Reports', href: `/app/projects/${props.project.id}/reports` },
  { key: 'team', label: 'Team', href: `/app/projects/${props.project.id}/team` },
  { key: 'activity', label: 'Activity', href: `/app/projects/${props.project.id}/activity` },
])
</script>

<template>
  <AuthenticatedLayout>
    <div class="p-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">{{ props.project.name }}</h1>
          <p v-if="props.project.status" class="mt-1 text-sm text-gray-600 capitalize">Status: {{ props.project.status }}</p>
        </div>

        <Link href="/app/projects" class="text-sm text-indigo-600 hover:underline">
          ← All Projects
        </Link>
      </div>

      <div class="mt-5 overflow-x-auto">
        <div class="inline-flex rounded-xl border bg-white p-1 shadow-sm">
          <Link
            v-for="t in tabs"
            :key="t.key"
            :href="t.href"
            class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium"
            :class="t.key === active ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
          >
            {{ t.label }}
          </Link>
        </div>
      </div>

      <div class="mt-6">
        <slot />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
