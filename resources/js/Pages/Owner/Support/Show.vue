<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import Badge from '@/Components/Badge.vue'
import { formatDateTime } from '@/utils/format'

const props = defineProps<{
  ticket: any
  notes: any[]
  statuses: string[]
  priorities: string[]
}>()

const updateForm = useForm({
  status: props.ticket.status,
  priority: props.ticket.priority,
  audit_reason: '',
})

const noteForm = useForm({
  note: '',
  audit_reason: '',
})

function statusTone(s:string) {
  if (s === 'open') return 'red'
  if (s === 'pending') return 'amber'
  if (s === 'resolved') return 'green'
  return 'gray'
}
</script>

<template>
  <OwnerLayout>
    <Head :title="`Ticket — ${ticket.subject}`" />

    <div class="space-y-6">
      <SectionCard :title="ticket.subject" subtitle="Ticket details and owner actions.">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Organization</div>
            <div class="mt-2 text-sm font-bold text-gray-900">{{ ticket.organization?.name || '—' }}</div>
          </div>
          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Status</div>
            <div class="mt-2">
              <Badge :text="ticket.status" :tone="statusTone(ticket.status) as any" />
            </div>
          </div>
          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Created</div>
            <div class="mt-2 text-sm font-bold text-gray-900">{{ formatDateTime(ticket.created_at) }}</div>
          </div>
        </div>

        <div class="mt-4 rounded-xl border p-4 bg-gray-50">
          <div class="text-xs text-gray-500">From</div>
          <div class="text-sm font-semibold text-gray-900">
            {{ ticket.creator?.name || 'Unknown' }} <span class="text-gray-500">({{ ticket.creator?.email || '—' }})</span>
          </div>

          <div class="mt-3 text-xs text-gray-500">Message</div>
          <div class="mt-1 whitespace-pre-wrap text-sm text-gray-800">{{ ticket.message }}</div>
        </div>
      </SectionCard>

      <SectionCard title="Update ticket" subtitle="Change status/priority with audit reason.">
        <form class="grid grid-cols-1 gap-4 md:grid-cols-4" @submit.prevent="updateForm.patch(`/owner/support/${ticket.id}`)">
          <div>
            <label class="text-xs font-semibold text-gray-700">Status</label>
            <select v-model="updateForm.status" class="mt-1 w-full rounded-lg border p-2 text-sm">
              <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-semibold text-gray-700">Priority</label>
            <select v-model="updateForm.priority" class="mt-1 w-full rounded-lg border p-2 text-sm">
              <option v-for="p in priorities" :key="p" :value="p">{{ p }}</option>
            </select>
          </div>

          <div class="md:col-span-2">
            <label class="text-xs font-semibold text-gray-700">Audit reason (optional)</label>
            <input v-model="updateForm.audit_reason" class="mt-1 w-full rounded-lg border p-2 text-sm" placeholder="e.g. Investigating issue" />
          </div>

          <div class="md:col-span-4">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700" :disabled="updateForm.processing">
              Save
            </button>
          </div>
        </form>
      </SectionCard>

      <SectionCard title="Internal notes" subtitle="Add private notes only visible in owner console.">
        <form class="space-y-3" @submit.prevent="noteForm.post(`/owner/support/${ticket.id}/notes`, { preserveScroll:true, onSuccess: () => noteForm.note='' })">
          <textarea v-model="noteForm.note" rows="4" class="w-full rounded-lg border p-2 text-sm" placeholder="Add an internal note..."></textarea>
          <input v-model="noteForm.audit_reason" class="w-full rounded-lg border p-2 text-sm" placeholder="Audit reason (optional)" />
          <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black" :disabled="noteForm.processing">
            Add note
          </button>
        </form>

        <div class="mt-4 space-y-2">
          <div v-for="n in notes" :key="n.id" class="rounded-xl border p-4">
            <div class="text-sm text-gray-900 whitespace-pre-wrap">{{ n.note }}</div>
            <div class="mt-1 text-xs text-gray-500">
              {{ formatDateTime(n.created_at) }} • {{ n.admin?.name }} ({{ n.admin?.email }})
            </div>
          </div>
        </div>
      </SectionCard>
    </div>
  </OwnerLayout>
</template>
