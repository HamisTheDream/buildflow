<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { formatDateTime, formatDate } from '@/utils/format'
import Badge from '@/Components/Badge.vue'
import EmptyState from '@/Components/EmptyState.vue'
import Pagination from '@/Components/Pagination.vue'

defineProps<{
  tickets: {
      data: any[],
      links: any[]
  }
}>()

const statusColor = (status: string) => {
    switch (status) {
        case 'open': return 'blue'
        case 'in_progress': return 'amber'
        case 'resolved': return 'green'
        case 'closed': return 'gray'
        default: return 'gray'
    }
}
</script>

<template>
  <Head title="Support Tickets" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold leading-tight text-gray-800">Support</h2>
          <Link
            :href="route('app.support.create')"
            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
          >
            New Ticket
          </Link>
      </div>
    </template>

    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div v-if="tickets.data.length > 0">
            <ul role="list" class="divide-y divide-gray-100">
                <li v-for="ticket in tickets.data" :key="ticket.id" class="relative flex justify-between gap-x-6 px-4 py-5 hover:bg-gray-50 sm:px-6">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm font-semibold leading-6 text-gray-900">
                                <Link :href="route('app.support.show', ticket.id)">
                                    <span class="absolute inset-x-0 -top-px bottom-0" />
                                    {{ ticket.subject }}
                                </Link>
                            </p>
                            <p class="mt-1 flex text-xs leading-5 text-gray-500">
                                <span class="truncate">#{{ ticket.id }} &middot; {{ ticket.category }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-x-4">
                        <div class="hidden sm:flex sm:flex-col sm:items-end">
                            <Badge :color="statusColor(ticket.status)">{{ ticket.status.replace('_', ' ') }}</Badge>
                            <p class="mt-1 text-xs leading-5 text-gray-500">
                                Updated {{ formatDateTime(ticket.updated_at) }}
                            </p>
                        </div>
                        <svg class="h-5 w-5 flex-none text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.16 8 7.23 4.29a.75.75 0 011.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </li>
            </ul>
             <div class="border-t border-gray-200 px-4 py-3 sm:px-6">
                <Pagination :links="tickets.links" />
            </div>
        </div>
        
        <div v-else>
             <EmptyState
                title="No support tickets"
                description="Need help? Create a support ticket and we'll get back to you."
                icon="chat"
                action-text="Create Ticket"
                :action-url="route('app.support.create')"
             />
        </div>
    </div>
  </AuthenticatedLayout>
</template>
