<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import Badge from '@/Components/Badge.vue'
import { formatDateTime } from '@/utils/format'
import { ref, nextTick, watch } from 'vue'

const props = defineProps<{
  ticket: any
  conversation: any[]
  internalNotes: any[]
  statuses: string[]
  priorities: string[]
}>()

const updateForm = useForm({
  status: props.ticket.status,
  priority: props.ticket.priority,
  audit_reason: '',
})

const replyForm = useForm({
  message: '',
})

const noteForm = useForm({
  note: '',
  audit_reason: '',
})

const messagesContainer = ref<HTMLElement | null>(null)

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

watch(() => props.conversation, scrollToBottom, { deep: true, immediate: true })

function statusTone(s:string) {
  if (s === 'open') return 'red'
  if (s === 'pending') return 'amber'
  if (s === 'resolved') return 'green'
  return 'gray'
}

function submitReply() {
  replyForm.post(`/owner/support/${props.ticket.id}/reply`, {
    preserveScroll: true,
    onSuccess: () => {
      replyForm.reset()
      scrollToBottom()
    }
  })
}
</script>

<template>
  <OwnerLayout>
    <Head :title="`Ticket — ${ticket.subject}`" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Conversation Area -->
      <div class="lg:col-span-2 flex flex-col h-[calc(100vh-12rem)] min-h-[500px] bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 bg-white">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold text-gray-900">{{ ticket.subject }}</h2>
              <p class="text-sm text-gray-500">{{ ticket.organization?.name }} • {{ ticket.creator?.name }}</p>
            </div>
            <Badge :text="ticket.status" :tone="statusTone(ticket.status) as any" />
          </div>
        </div>

        <!-- Messages -->
        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50">
          <!-- Original Message -->
          <div class="flex gap-3">
            <div class="h-9 w-9 shrink-0 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
              {{ (ticket.creator?.name || 'U')[0].toUpperCase() }}
            </div>
            <div class="max-w-[80%] space-y-1">
              <div class="text-xs text-gray-500">
                {{ ticket.creator?.name || 'User' }} • {{ formatDateTime(ticket.created_at) }}
              </div>
              <div class="rounded-2xl rounded-tl-none bg-white p-4 shadow-sm text-gray-800 text-sm whitespace-pre-wrap ring-1 ring-gray-900/5">
                {{ ticket.message }}
              </div>
            </div>
          </div>

          <!-- Conversation Loop -->
          <template v-for="msg in conversation" :key="msg.id">
            <!-- Admin Message (Right, styled differently) -->
            <div v-if="msg.is_admin" class="flex flex-row-reverse gap-3">
              <div class="h-9 w-9 shrink-0 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-xs">
                SP
              </div>
              <div class="max-w-[80%] space-y-1 flex flex-col items-end">
                <div class="text-xs text-gray-500">
                  {{ msg.author?.name || 'Support' }} • {{ formatDateTime(msg.created_at) }}
                </div>
                <div class="rounded-2xl rounded-tr-none bg-emerald-600 p-4 shadow-sm text-white text-sm whitespace-pre-wrap">
                  {{ msg.note }}
                </div>
              </div>
            </div>

            <!-- User Message (Left) -->
            <div v-else class="flex gap-3">
              <div class="h-9 w-9 shrink-0 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                {{ (msg.author?.name || 'U')[0].toUpperCase() }}
              </div>
              <div class="max-w-[80%] space-y-1">
                <div class="text-xs text-gray-500">
                  {{ msg.author?.name || 'User' }} • {{ formatDateTime(msg.created_at) }}
                </div>
                <div class="rounded-2xl rounded-tl-none bg-white p-4 shadow-sm text-gray-800 text-sm whitespace-pre-wrap ring-1 ring-gray-900/5">
                  {{ msg.note }}
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- Reply Box -->
        <div class="p-4 bg-white border-t border-gray-100">
          <form @submit.prevent="submitReply" class="relative">
            <textarea
              v-model="replyForm.message"
              rows="3"
              class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 pr-20 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm resize-none"
              placeholder="Reply to customer..."
              required
              @keydown.ctrl.enter="submitReply"
            ></textarea>
            <div class="absolute bottom-2 right-2">
              <button
                type="submit"
                class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50"
                :disabled="replyForm.processing || !replyForm.message"
              >
                Send Reply
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Status & Priority -->
        <SectionCard title="Update Ticket" subtitle="Change status or priority">
          <form class="space-y-4" @submit.prevent="updateForm.patch(`/owner/support/${ticket.id}`)">
            <div>
              <label class="text-xs font-semibold text-gray-700">Status</label>
              <select v-model="updateForm.status" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
              </select>
            </div>

            <div>
              <label class="text-xs font-semibold text-gray-700">Priority</label>
              <select v-model="updateForm.priority" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option v-for="p in priorities" :key="p" :value="p">{{ p }}</option>
              </select>
            </div>

            <div>
              <label class="text-xs font-semibold text-gray-700">Audit reason (optional)</label>
              <input v-model="updateForm.audit_reason" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="e.g. Resolved issue" />
            </div>

            <button class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black" :disabled="updateForm.processing">
              Update
            </button>
          </form>
        </SectionCard>

        <!-- Internal Notes -->
        <SectionCard title="Internal Notes" subtitle="Private notes (not visible to customer)">
          <form class="space-y-3" @submit.prevent="noteForm.post(`/owner/support/${ticket.id}/notes`, { preserveScroll:true, onSuccess: () => noteForm.note='' })">
            <textarea v-model="noteForm.note" rows="3" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Add an internal note..."></textarea>
            <input v-model="noteForm.audit_reason" class="w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Audit reason (optional)" />
            <button class="w-full rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700" :disabled="noteForm.processing">
              Add Note
            </button>
          </form>

          <div v-if="internalNotes.length > 0" class="mt-4 space-y-2">
            <div v-for="n in internalNotes" :key="n.id" class="rounded-lg border border-amber-200 bg-amber-50 p-3">
              <div class="text-sm text-gray-900 whitespace-pre-wrap">{{ n.note }}</div>
              <div class="mt-1 text-xs text-gray-500">
                {{ formatDateTime(n.created_at) }} • {{ n.admin?.name }}
              </div>
            </div>
          </div>
          <div v-else class="mt-4 text-sm text-gray-500 text-center py-4">
            No internal notes yet
          </div>
        </SectionCard>

        <!-- Ticket Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h3 class="font-medium text-gray-900 mb-4">Ticket Details</h3>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
              <dt class="text-gray-500">Category</dt>
              <dd class="font-medium text-gray-900 capitalize">{{ ticket.category || 'General' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Priority</dt>
              <dd class="font-medium text-gray-900 capitalize">{{ ticket.priority }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Created</dt>
              <dd class="font-medium text-gray-900">{{ formatDateTime(ticket.created_at) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Customer</dt>
              <dd class="font-medium text-gray-900">{{ ticket.creator?.email || '—' }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </OwnerLayout>
</template>
