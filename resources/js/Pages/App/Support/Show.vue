<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { ref, computed, nextTick, watch } from 'vue'
import { formatDateTime } from '@/utils/format'
import Badge from '@/Components/Badge.vue'

const props = defineProps<{
  ticket: any
  notes: any[]
}>()

const form = useForm({
  message: '',
})

const messagesContainer = ref<HTMLElement | null>(null)

const scrollToBottom = async () => {
    await nextTick()
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
}

watch(() => props.notes, scrollToBottom, { deep: true, immediate: true })

function submit() {
  form.post(route('app.support.reply', props.ticket.id), {
    preserveScroll: true,
    onSuccess: () => {
        form.reset()
        scrollToBottom()
    }
  })
}

const statusColor = computed(() => {
    switch (props.ticket.status) {
        case 'open': return 'blue'
        case 'in_progress': return 'amber'
        case 'resolved': return 'green'
        case 'closed': return 'gray'
        default: return 'gray'
    }
})

const priorityColor = computed(() => {
    switch (props.ticket.priority) {
        case 'urgent': return 'red'
        case 'high': return 'orange'
        case 'normal': return 'blue'
        case 'low': return 'gray'
        default: return 'gray'
    }
})
</script>

<template>
  <Head :title="`Ticket #${ticket.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
              <Link :href="route('app.support.index')" class="text-gray-500 hover:text-gray-700">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                  </svg>
              </Link>
              <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Ticket #{{ ticket.id }}: {{ ticket.subject }}
              </h2>
          </div>
          <div class="flex gap-2">
               <Badge :color="statusColor">{{ ticket.status.replace('_', ' ') }}</Badge>
               <Badge :color="priorityColor" dot>{{ ticket.priority }}</Badge>
          </div>
      </div>
    </template>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Chat Area -->
        <div class="lg:col-span-2 flex flex-col h-[calc(100vh-16rem)] min-h-[500px] bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
             <!-- Messages -->
             <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50">
                  <!-- Original Request -->
                  <div class="flex gap-4">
                       <div class="h-10 w-10 shrink-0 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                           ME
                       </div>
                       <div class="max-w-[85%] space-y-1">
                           <div class="text-xs text-gray-500">
                               {{ props.ticket.creator?.name || 'You' }} • {{ formatDateTime(ticket.created_at) }}
                           </div>
                           <div class="rounded-2xl rounded-tl-none bg-white p-4 shadow-sm text-gray-800 text-sm whitespace-pre-wrap ring-1 ring-gray-900/5">
                               {{ ticket.message }}
                           </div>
                       </div>
                  </div>

                  <!-- Notes Loop -->
                  <template v-for="note in notes" :key="note.id">
                      <!-- User Message (Right) - current user's messages -->
                      <div v-if="!note.is_admin && note.user_id === $page.props.auth.user.id" class="flex flex-row-reverse gap-4">
                           <div class="h-10 w-10 shrink-0 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                               ME
                           </div>
                           <div class="max-w-[85%] space-y-1 flex flex-col items-end">
                               <div class="text-xs text-gray-500">
                                   {{ formatDateTime(note.created_at) }}
                               </div>
                               <div class="rounded-2xl rounded-tr-none bg-indigo-600 p-4 shadow-sm text-white text-sm whitespace-pre-wrap">
                                   {{ note.note }}
                               </div>
                           </div>
                      </div>

                      <!-- Admin/Support Message (Left) -->
                      <div v-else-if="note.is_admin" class="flex gap-4">
                           <div class="h-10 w-10 shrink-0 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-sm">
                               SP
                           </div>
                           <div class="max-w-[85%] space-y-1">
                               <div class="text-xs text-gray-500">
                                   {{ note.admin?.name || 'Support Team' }} • {{ formatDateTime(note.created_at) }}
                               </div>
                               <div class="rounded-2xl rounded-tl-none bg-white p-4 shadow-sm text-gray-800 text-sm whitespace-pre-wrap ring-1 ring-gray-900/5 border-l-4 border-emerald-500">
                                   {{ note.note }}
                               </div>
                           </div>
                      </div>

                      <!-- Other User Message (Left) -->
                      <div v-else class="flex gap-4">
                           <div class="h-10 w-10 shrink-0 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-sm">
                               {{ (note.user?.name || 'U')[0].toUpperCase() }}
                           </div>
                           <div class="max-w-[85%] space-y-1">
                               <div class="text-xs text-gray-500">
                                   {{ note.user?.name || 'Team member' }} • {{ formatDateTime(note.created_at) }}
                               </div>
                               <div class="rounded-2xl rounded-tl-none bg-white p-4 shadow-sm text-gray-800 text-sm whitespace-pre-wrap ring-1 ring-gray-900/5">
                                   {{ note.note }}
                               </div>
                           </div>
                      </div>
                  </template>
             </div>

             <!-- Reply Box -->
             <div class="p-4 bg-white border-t border-gray-100">
                 <form @submit.prevent="submit" class="relative">
                     <label class="sr-only">Message</label>
                     <textarea 
                        v-model="form.message"
                        rows="3"
                        class="block w-full rounded-lg border-gray-300 pr-20 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm resize-none"
                        placeholder="Type your reply here..."
                        required
                        @keydown.enter.ctrl="submit"
                     ></textarea>
                     <div class="absolute bottom-2 right-2">
                         <button 
                            type="submit" 
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50"
                            :disabled="form.processing || !form.message"
                        >
                             Send
                         </button>
                     </div>
                 </form>
             </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-medium text-gray-900 mb-4">Ticket Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Created</dt>
                        <dd class="font-medium text-gray-900">{{ formatDateTime(ticket.created_at) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Last Updated</dt>
                        <dd class="font-medium text-gray-900">{{ formatDateTime(ticket.updated_at) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Category</dt>
                        <dd class="font-medium text-gray-900 capitalize">{{ ticket.category || 'General' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
  </AuthenticatedLayout>
</template>
