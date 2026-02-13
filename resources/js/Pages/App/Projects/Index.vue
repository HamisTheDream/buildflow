<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Badge from '@/Components/Badge.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { formatDate } from '@/utils/format'

const props = defineProps<{
  organization: { id:number; name:string; type:'individual'|'company' }
  orgRole: string
  canCreate: boolean
  projects: any // paginator
}>()

import { useEntitlements } from '@/composables/useEntitlements'
const { can } = useEntitlements()

// Search/filter state
const search = ref('')
const statusFilter = ref('all')

const filteredProjects = computed(() => {
  let items = props.projects.data || []
  
  if (search.value) {
    const q = search.value.toLowerCase()
    items = items.filter((p: any) => 
      p.name?.toLowerCase().includes(q) || 
      p.client_name?.toLowerCase().includes(q) ||
      p.location?.toLowerCase().includes(q)
    )
  }
  
  if (statusFilter.value !== 'all') {
    items = items.filter((p: any) => p.status === statusFilter.value)
  }
  
  return items
})

function statusTone(status: string) {
  if (status === 'active') return 'green'
  if (status === 'paused') return 'amber'
  return 'gray'
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Projects" />

    <div class="min-h-screen bg-gray-50/50">
      <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Projects</h1>
            <p class="mt-1 text-sm text-gray-500">
              Manage projects in <span class="font-medium text-gray-700">{{ organization.name }}</span>
            </p>
          </div>

          <div class="flex items-center gap-3">
            <Link
              v-if="canCreate && can.create_project"
              href="/app/projects/create"
              class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
              </svg>
              New Project
            </Link>
            <div v-else-if="!can.create_project" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800">
              Project limit reached
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
          <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Search projects..."
              class="w-full rounded-lg border-gray-300 pl-10 pr-4 py-2.5 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:ring-brand-500"
            />
          </div>
          <select
            v-model="statusFilter"
            class="rounded-lg border-gray-300 py-2.5 pl-3 pr-10 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
          >
            <option value="all">All statuses</option>
            <option value="active">Active</option>
            <option value="paused">Paused</option>
            <option value="completed">Completed</option>
            <option value="archived">Archived</option>
          </select>
        </div>

        <!-- Project Grid -->
        <div class="mt-6">
          <div v-if="filteredProjects.length === 0" class="rounded-2xl border-2 border-dashed border-gray-200 bg-white p-12 text-center">
            <EmptyState
              icon="folder"
              title="No projects found"
              :description="search || statusFilter !== 'all' ? 'Try adjusting your search or filters.' : 'Create your first project to get started.'"
              :actionText="canCreate && can.create_project ? 'New Project' : undefined"
              :actionHref="canCreate && can.create_project ? '/app/projects/create' : undefined"
            />
          </div>

          <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="p in filteredProjects"
              :key="p.id"
              :href="`/app/projects/${p.id}`"
              class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-card transition-all hover:border-brand-200 hover:shadow-card-hover"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                  <div class="truncate text-base font-semibold text-gray-900 group-hover:text-brand-600 transition">
                    {{ p.name }}
                  </div>
                  <div class="mt-1 text-sm text-gray-500 truncate">
                    {{ p.client_name || 'No client assigned' }}
                  </div>
                </div>
                <Badge :text="p.status" :tone="statusTone(p.status)" dot />
              </div>
              
              <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-gray-400">
                <div v-if="p.location" class="flex items-center gap-1">
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  </svg>
                  <span class="truncate">{{ p.location }}</span>
                </div>
                <div v-if="p.start_date" class="flex items-center gap-1">
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span>{{ formatDate(p.start_date) }}</span>
                </div>
              </div>
            </Link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="projects.links?.length > 3" class="mt-6 flex flex-wrap justify-center gap-1">
          <Link
            v-for="l in projects.links"
            :key="l.label"
            :href="l.url || ''"
            v-html="l.label"
            :class="[
              'rounded-lg border px-3 py-2 text-sm font-medium transition',
              l.active 
                ? 'bg-brand-50 border-brand-200 text-brand-700' 
                : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50',
              !l.url ? 'opacity-50 pointer-events-none' : ''
            ]"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
