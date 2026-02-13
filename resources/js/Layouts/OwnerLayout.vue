<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import FlashMessage from '@/Components/FlashMessage.vue'
import NotificationsBell from '@/Components/NotificationsBell.vue'

const page = usePage<any>()
const admin = computed(() => page.props.ownerAuth?.admin)
const sidebarOpen = ref(false)

const navItems = [
  { label: 'Dashboard', href: '/owner/dashboard', icon: 'dashboard', match: '/owner/dashboard' },
  { label: 'Organizations', href: '/owner/organizations', icon: 'orgs', match: '/owner/organizations' },
  { label: 'Billing', href: '/owner/billing/payments', icon: 'billing', match: '/owner/billing' },
  { label: 'Announcements', href: '/owner/announcements', icon: 'announcements', match: '/owner/announcements' },
  { label: 'Finance', href: '/owner/finance', icon: 'finance', match: '/owner/finance' },
  { label: 'Blog', href: '/owner/blog', icon: 'blog', match: '/owner/blog' },
  { label: 'Support', href: '/owner/support', icon: 'support', match: '/owner/support' },
  { label: 'Admins', href: '/owner/admins', icon: 'admins', match: '/owner/admins' },
  { label: 'Settings', href: '/owner/settings', icon: 'settings', match: '/owner/settings' },
]

const isActive = (match: string) => page.url.startsWith(match)
</script>

<template>
  <div class="min-h-screen bg-slate-950 text-white">
    <!-- Mobile sidebar backdrop -->
    <div 
      v-if="sidebarOpen" 
      class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside 
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 transform bg-slate-900 transition-transform duration-300 ease-in-out lg:translate-x-0',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <!-- Orange accent line -->
      <div class="absolute top-0 right-0 h-full w-1 bg-gradient-to-b from-brand-400 via-brand-500 to-brand-600"></div>
      
      <div class="flex h-full flex-col">
        <!-- Logo -->
        <div class="flex h-16 items-center gap-3 px-6 border-b border-slate-800">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500 shadow-lg shadow-brand-500/30">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div>
            <div class="text-sm font-bold text-white">BuildFlow</div>
            <div class="text-[10px] font-medium uppercase tracking-wider text-brand-400">Owner Console</div>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-1 px-3 py-4 overflow-y-auto">
          <Link 
            v-for="item in navItems" 
            :key="item.href"
            :href="item.href"
            :class="[
              'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150',
              isActive(item.match) 
                ? 'bg-brand-500/20 text-brand-400 ring-1 ring-brand-500/50' 
                : 'text-gray-400 hover:bg-slate-800 hover:text-white'
            ]"
          >
            <!-- Dashboard Icon -->
            <svg v-if="item.icon === 'dashboard'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
            <!-- Organizations Icon -->
            <svg v-else-if="item.icon === 'orgs'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <!-- Billing Icon -->
            <svg v-else-if="item.icon === 'billing'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <!-- Announcements Icon -->
            <svg v-else-if="item.icon === 'announcements'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            </svg>
            <!-- Support Icon -->
            <svg v-else-if="item.icon === 'support'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <!-- Admins Icon -->
            <svg v-else-if="item.icon === 'admins'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <!-- Blog Icon -->
            <svg v-else-if="item.icon === 'blog'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <!-- Finance Icon -->
            <svg v-else-if="item.icon === 'finance'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <!-- Settings Icon -->
            <svg v-else-if="item.icon === 'settings'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ item.label }}
          </Link>
        </nav>

        <!-- Admin Profile Section -->
        <div class="border-t border-slate-800 p-4">
          <Link 
            href="/owner/profile" 
            class="flex items-center gap-3 rounded-lg p-2 transition hover:bg-slate-800"
          >
            <div class="h-10 w-10 shrink-0 rounded-full overflow-hidden shadow-lg shadow-brand-500/30 ring-2 ring-brand-500/50">
              <img 
                v-if="admin?.avatar_url" 
                :src="admin.avatar_url" 
                :alt="admin?.name"
                class="h-full w-full object-cover"
              />
              <div 
                v-else 
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-400 to-brand-600 text-sm font-bold text-white"
              >
                {{ admin?.name?.charAt(0)?.toUpperCase() || 'A' }}
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-medium text-white truncate">{{ admin?.name }}</div>
              <div class="flex items-center gap-1.5">
                <span v-if="admin?.is_super" class="inline-flex items-center rounded-full bg-brand-500/20 px-2 py-0.5 text-[10px] font-medium text-brand-400 ring-1 ring-brand-500/30">
                  Super Admin
                </span>
                <span v-else class="text-xs text-gray-500">Admin</span>
              </div>
            </div>
            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </Link>
          
          <form method="post" action="/owner/logout" class="mt-3">
            <input type="hidden" name="_token" :value="page.props.csrf_token" />
            <button class="flex w-full items-center justify-center gap-2 rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-gray-400 transition hover:bg-slate-700 hover:text-white">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Sign out
            </button>
          </form>
        </div>
      </div>
    </aside>

    <!-- Main content area -->
    <div class="lg:pl-64 min-h-screen flex flex-col bg-neutral-50">
      <!-- Mobile header -->
      <header class="sticky top-0 z-30 flex h-14 items-center justify-between bg-slate-900 px-4 border-b border-slate-800 lg:hidden">
        <button 
          @click="sidebarOpen = true"
          class="rounded-md p-2 text-gray-400 hover:bg-slate-800 hover:text-white focus:outline-none"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        
        <div class="flex items-center gap-2">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500">
            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <span class="text-sm font-bold text-white">Owner Console</span>
        </div>

        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-xs font-bold text-white">
          {{ admin?.name?.charAt(0)?.toUpperCase() || 'A' }}
        </div>
      </header>

      <!-- Desktop header -->
      <header class="hidden lg:flex h-14 items-center justify-between bg-white border-b border-gray-200 px-6 shadow-sm">
        <div class="flex items-center gap-2 text-sm text-gray-600">
          <div class="h-2 w-2 rounded-full bg-brand-500 animate-pulse"></div>
          Owner Console
        </div>
        <div class="flex items-center gap-3">
            <NotificationsBell :is-admin="true" class="text-gray-500 hover:text-brand-500 transition" />
            <div class="h-6 w-px bg-gray-200"></div>
            <span v-if="admin?.is_super" class="inline-flex items-center rounded-full bg-brand-500 px-2.5 py-0.5 text-xs font-semibold text-white shadow-sm">
                Super Admin
            </span>
            <span v-else class="text-sm text-gray-600">Admin</span>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-6 lg:p-8">
        <div class="mx-auto max-w-6xl">
          <slot />
        </div>
      </main>

      <!-- Footer -->
      <footer class="border-t border-gray-200 bg-white py-6 px-6">
        <div class="mx-auto max-w-7xl flex flex-col items-center justify-between gap-4 sm:flex-row">
          <div class="text-sm text-gray-500">
            © {{ new Date().getFullYear() }} BuildFlow. All rights reserved.
          </div>
          <div class="flex items-center gap-6">
            <div class="flex items-center gap-4">
                <span class="text-xs text-gray-400">v1.0.0</span>
                <div class="h-1.5 w-1.5 rounded-full bg-brand-500"></div>
            </div>
          </div>
        </div>
      </footer>
    </div>

    <FlashMessage />
  </div>
</template>
