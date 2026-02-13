<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import Badge from '@/Components/Badge.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'

const props = defineProps<{
  project: { id: number; name: string; status?: string; client_name?: string }
  active: string
}>()

// Grouped tabs for better organization
const tabGroups = computed(() => [
  {
    name: 'Field Work',
    tabs: [
      { key: 'overview', label: 'Overview', href: `/app/projects/${props.project.id}` },
      { key: 'today', label: 'Today', href: `/app/projects/${props.project.id}/today` },
      { key: 'logs', label: 'Logs', href: `/app/projects/${props.project.id}/logs` },
      { key: 'tasks', label: 'Tasks', href: `/app/projects/${props.project.id}/tasks` },
      { key: 'issues', label: 'Issues', href: `/app/projects/${props.project.id}/issues` },
      { key: 'media', label: 'Media', href: `/app/projects/${props.project.id}/media` },
    ],
  },
  {
    name: 'Office',
    tabs: [
      { key: 'costs', label: 'Costs', href: `/app/projects/${props.project.id}/costs` },
      { key: 'reports', label: 'Reports', href: `/app/projects/${props.project.id}/reports` },
    ],
  },
  {
    name: 'Admin',
    tabs: [
      { key: 'team', label: 'Team', href: `/app/projects/${props.project.id}/team` },
      { key: 'activity', label: 'Activity', href: `/app/projects/${props.project.id}/activity` },
    ],
  },
])

// Flat tabs for mobile (no grouping)
const allTabs = computed(() => tabGroups.value.flatMap(g => g.tabs))

function statusTone(status: string) {
  if (status === 'active') return 'green'
  if (status === 'paused') return 'amber'
  if (status === 'completed') return 'blue'
  return 'gray'
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="min-h-screen bg-gray-50/50">
      <!-- Project Header -->
      <div class="bg-white border-b border-gray-200 pt-8 pb-0">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="mb-6">
            <Breadcrumbs 
              :items="[
                { label: 'Projects', href: '/app/projects' },
                { label: props.project.name, href: null }
              ]" 
            />
          </div>

          <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between pb-8">
            <div>
              <div class="flex items-center gap-3">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ props.project.name }}</h1>
                <Badge 
                  v-if="props.project.status" 
                  :text="props.project.status" 
                  :tone="statusTone(props.project.status)"
                  size="sm"
                  dot
                />
              </div>
              <p v-if="props.project.client_name" class="mt-2 text-sm text-gray-500">
                Client: <span class="font-medium text-gray-900">{{ props.project.client_name }}</span>
              </p>
            </div>
            
            <div class="flex items-center gap-3">
              <!-- Actions can go here -->
            </div>
          </div>

          <!-- Sticky Tabs -->
          <div class="flex overflow-x-auto border-b border-gray-200">
             <div class="flex gap-8">
                <Link
                  v-for="t in allTabs"
                  :key="t.key"
                  :href="t.href"
                  class="whitespace-nowrap border-b-2 py-4 text-sm font-medium transition-colors"
                  :class="t.key === active 
                    ? 'border-brand-600 text-brand-600' 
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                >
                  {{ t.label }}
                </Link>
             </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <slot />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
