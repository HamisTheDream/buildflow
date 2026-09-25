<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { formatDateTime } from '@/utils/format'

const props = defineProps<{
  organizations: { data:any[]; links:any[]; total: number }
}>()

const restoreForm = useForm({})

function restore(id: number) {
  restoreForm.post(`/owner/organizations/${id}/restore`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <OwnerLayout>
    <Head title="Trash — Organizations" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Trash</h1>
          <p class="mt-1 text-sm text-gray-500">
            Deleted organizations and all of their data. Restoring an organization
            brings back everything that was deleted with it.
          </p>
        </div>
        <Link
          href="/owner/organizations"
          class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
        >
          <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Organizations
        </Link>
      </div>

      <!-- Trashed list -->
      <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
        <ul role="list" class="divide-y divide-gray-100">
          <li v-for="org in organizations.data" :key="org.id" class="p-6">
            <div class="flex items-center justify-between gap-x-6">
              <div class="flex gap-x-4">
                <div class="h-12 w-12 flex-none rounded-lg bg-red-50 flex items-center justify-center text-lg font-bold text-red-400 overflow-hidden ring-1 ring-red-100">
                  {{ org.name.charAt(0).toUpperCase() }}
                </div>
                <div class="min-w-0 flex-auto">
                  <p class="text-sm font-semibold leading-6 text-gray-900">
                    {{ org.name }}
                  </p>
                  <p class="mt-1 flex text-xs leading-5 text-gray-500">
                    <span class="truncate">Deleted {{ formatDateTime(org.deleted_at) }}</span>
                  </p>
                </div>
              </div>

              <PrimaryButton @click="restore(org.id)" :disabled="restoreForm.processing">
                Restore
              </PrimaryButton>
            </div>
          </li>

          <li v-if="organizations.data.length === 0" class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">Trash is empty</h3>
            <p class="mt-1 text-sm text-gray-500">Deleted organizations will appear here.</p>
          </li>
        </ul>
      </div>

      <Pagination :links="organizations.links" />
    </div>
  </OwnerLayout>
</template>
