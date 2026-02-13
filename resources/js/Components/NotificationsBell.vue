<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

interface Notification {
  id: number
  type: string
  title: string
  body: string | null
  data: Record<string, any> | null
  read_at: string | null
  created_at: string
}

const props = defineProps({
  isAdmin: { type: Boolean, default: false }
})

const isOpen = ref(false)
const notifications = ref<Notification[]>([])
const unreadCount = ref(0)
const loading = ref(false)
let pollInterval: ReturnType<typeof setInterval> | null = null

const baseUrl = computed(() => props.isAdmin ? '/owner' : '/app')

async function fetchNotifications() {
  try {
    const res = await fetch(`${baseUrl.value}/notifications`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    if (res.ok) {
      const data = await res.json()
      notifications.value = data.notifications || []
      unreadCount.value = data.unread_count || 0
    }
  } catch (e) {
    // Silently fail
  }
}

async function markAsRead(id: number) {
  await fetch(`${baseUrl.value}/notifications/${id}/read`, {
    method: 'POST',
    headers: { 
      'Accept': 'application/json',
      'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content 
    }
  })
  const n = notifications.value.find(n => n.id === id)
  if (n) n.read_at = new Date().toISOString()
  unreadCount.value = Math.max(0, unreadCount.value - 1)
}

async function markAllAsRead() {
  await fetch(`${baseUrl.value}/notifications/mark-all-read`, {
    method: 'POST',
    headers: { 
      'Accept': 'application/json',
      'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content 
    }
  })
  notifications.value.forEach(n => n.read_at = n.read_at || new Date().toISOString())
  unreadCount.value = 0
}

function toggle() {
  isOpen.value = !isOpen.value
  if (isOpen.value && notifications.value.length === 0) {
    fetchNotifications()
  }
}

function getIcon(type: string): string {
  switch (type) {
    case 'task_assigned': return '📋'
    case 'issue_reported': case 'issue_assigned': return '⚠️'
    case 'member_invited': return '👤'
    default: return '🔔'
  }
}

function getLink(n: Notification): string | null {
  const data = n.data || {}
  if (data.project_id) return `/app/projects/${data.project_id}`
  if (data.organization_id) return '/app/dashboard'
  return null
}

function formatTimeAgo(dateStr: string): string {
  const date = new Date(dateStr)
  const now = new Date()
  const diff = Math.floor((now.getTime() - date.getTime()) / 1000)
  
  if (diff < 60) return 'just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  const days = Math.floor(diff / 86400)
  if (days < 7) return `${days}d ago`
  return date.toLocaleDateString()
}

onMounted(() => {
  fetchNotifications()
  pollInterval = setInterval(fetchNotifications, 60000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>

<template>
  <div class="relative notification-bell-wrapper">
    <!-- Bell Button -->
    <button 
      @click.stop="toggle"
      class="relative rounded-lg p-2 text-gray-400 hover:bg-slate-700 hover:text-white transition"
      title="Notifications"
    >
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
      </svg>
      
      <!-- Unread Badge -->
      <span 
        v-if="unreadCount > 0"
        class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-slate-900"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div 
        v-if="isOpen"
        class="absolute bottom-full left-0 mb-2 w-80 rounded-xl bg-white shadow-2xl ring-1 ring-gray-900/10 overflow-hidden z-50"
      >
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
          <span class="text-sm font-semibold text-gray-900">Notifications</span>
          <button 
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="text-xs font-medium text-brand-600 hover:text-brand-700"
          >
            Mark all read
          </button>
        </div>

        <div class="max-h-80 overflow-y-auto">
          <div v-if="loading" class="py-8 text-center text-sm text-gray-500">
            Loading...
          </div>

          <div v-else-if="notifications.length === 0" class="px-4 py-8 text-center">
            <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <p class="mt-2 text-sm text-gray-500">No notifications yet</p>
          </div>

          <div v-else>
            <component
              v-for="n in notifications" 
              :key="n.id"
              :is="getLink(n) ? 'a' : 'div'"
              :href="getLink(n)"
              @click="markAsRead(n.id)"
              class="flex items-start gap-3 border-b border-gray-50 px-4 py-3 hover:bg-gray-50 cursor-pointer transition"
              :class="n.read_at ? 'bg-white' : 'bg-blue-50/50'"
            >
              <span class="mt-0.5 text-lg leading-none shrink-0">{{ getIcon(n.type) }}</span>
              <div class="min-w-0 flex-1">
                <div class="text-sm font-medium text-gray-900 truncate">{{ n.title }}</div>
                <div v-if="n.body" class="mt-0.5 text-xs text-gray-600 line-clamp-2">{{ n.body }}</div>
                <div class="mt-1 text-[10px] text-gray-400">{{ formatTimeAgo(n.created_at) }}</div>
              </div>
              <div v-if="!n.read_at" class="mt-2 h-2 w-2 shrink-0 rounded-full bg-brand-500"></div>
            </component>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Click outside to close -->
    <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-40"></div>
  </div>
</template>
