<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps<{
  organization: { id:number; name:string; type:'individual'|'company' }
  orgRole: string
  canCreate: boolean
  projects: any // paginator
}>()

import { useEntitlements } from '@/composables/useEntitlements'
const { can } = useEntitlements()
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Projects" />

    <div class="p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Projects</h1>
          <p class="mt-1 text-sm text-gray-600">
            Manage projects in <span class="font-semibold">{{ organization.name }}</span>.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <Link
            v-if="canCreate && can.create_project"
            href="/app/projects/create"
            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
          >
            New Project
          </Link>
          <div v-else-if="!can.create_project" class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">
            Project limit reached.
          </div>
        </div>
      </div>

      <div class="mt-6 rounded-xl bg-white p-4 shadow">
        <div v-if="projects.data.length === 0" class="rounded-lg border border-dashed p-10 text-center">
          <div class="text-sm font-semibold text-gray-900">No projects yet</div>
          <div class="mt-1 text-sm text-gray-600">Create your first project to start tracking work.</div>
        </div>

        <div v-else class="space-y-2">
          <Link
            v-for="p in projects.data"
            :key="p.id"
            :href="`/app/projects/${p.id}`"
            class="block rounded-lg border p-4 hover:bg-gray-50"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="truncate text-sm font-semibold text-gray-900">{{ p.name }}</div>
                <div class="mt-1 text-xs text-gray-500">
                  <span v-if="p.location">{{ p.location }} • </span>
                  <span class="capitalize">{{ p.status }}</span>
                  <span v-if="p.client_name"> • Client: {{ p.client_name }}</span>
                </div>
              </div>
              <span class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700 capitalize">{{ p.status }}</span>
            </div>
          </Link>
        </div>

        <!-- Pagination -->
        <div v-if="projects.links?.length" class="mt-4 flex flex-wrap gap-2">
          <Link
            v-for="l in projects.links"
            :key="l.label"
            :href="l.url || ''"
            v-html="l.label"
            class="rounded border px-3 py-1 text-sm"
            :class="[
              l.active ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-white text-gray-700',
              !l.url ? 'opacity-50 pointer-events-none' : 'hover:bg-gray-50'
            ]"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
