<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { formatDateTime } from '@/utils/format';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import NotificationsBell from '@/Components/NotificationsBell.vue';
import SkipLink from '@/Components/SkipLink.vue';
import FlashMessage from '@/Components/FlashMessage.vue';

const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(false);
const page = usePage();

// Org resolver
const org = computed(() => {
  return page.props.org || page.props.currentOrg || page.props.currentOrganization || page.props.auth?.organization || null
})

// Subscription status
const status = computed(() => (org.value?.subscription?.status || org.value?.status || 'active').toLowerCase())
const isPastDue = computed(() => status.value === 'past_due' || status.value === 'grace')

// Locked logic
const allowedPathsWhenLocked = ['/app/billing', '/app/settings/profile', '/app/support', '/logout']
const currentPath = computed(() => (page.url || window.location.pathname).split('?')[0])

const isLocked = computed(() => {
    const s = status.value;
    if (['suspended', 'blocked', 'expired'].includes(s)) return true;
    if (s === 'past_due' && org.value?.subscription?.grace_ends_at) {
        return new Date(org.value.subscription.grace_ends_at) < new Date();
    }
    return false;
})

const isAllowedWhileLocked = computed(() => allowedPathsWhenLocked.some(p => currentPath.value.startsWith(p)))
const showLockOverlay = computed(() => isLocked.value && !isAllowedWhileLocked.value)

const graceEndsAtText = computed(() => {
  const v = org.value?.subscription?.grace_ends_at || org.value?.grace_ends_at
  return v ? formatDateTime(v) : '—'
})

// Role-based access
const orgRole = computed(() => page.props.auth?.orgRole || null)
const isOrgManager = computed(() => ['owner', 'admin'].includes(orgRole.value))

// Navigation items - filtered by role
// Navigation items - filtered by role and modules
const modules = computed(() => page.props.auth?.modules || {})

const navItems = computed(() => {
  const items = []

  // 1. Projects (Base Module)
  if (modules.value.projects) {
      items.push({ 
          label: 'Dashboard', 
          route: 'dashboard', 
          active: route().current('dashboard'), 
          icon: 'dashboard' 
      })
      items.push({ 
          label: 'Projects', 
          route: 'projects.index', 
          active: route().current('projects.*'), 
          icon: 'projects' 
      })
  }

  // 2. CRM Module
  if (modules.value.crm) {
      items.push({ 
          label: 'Sales & CRM', 
          route: 'crm.dashboard', // Will create this route later
          active: false, // route().current('crm.*')
          icon: 'crm' 
      })
  }

  // 3. Finance Module
  if (modules.value.finance) {
      items.push({ 
          label: 'Finance', 
          route: 'finance.dashboard', // Will create this route later 
          active: false, // route().current('finance.*')
          icon: 'finance' 
      })
  }

  // 4. HR Module
  if (modules.value.hr) {
      items.push({ 
          label: 'HR & Payroll', 
          route: 'hr.dashboard', // Will create this route later
          active: false, // route().current('hr.*')
          icon: 'hr' 
      })
  }

  // 5. General Items
  if (isOrgManager.value) {
      items.push({ 
          label: 'Members', 
          route: 'org.members', 
          active: route().current('org.members'), 
          icon: 'members' 
      })
  }
  
  items.push({ 
      label: 'Support', 
      route: 'app.support.index', 
      active: route().current('app.support.*'), 
      icon: 'support' 
  })

  return items
})

// User avatar
const user = computed(() => page.props.auth?.user)
const avatarUrl = computed(() => user.value?.avatar_url)
const userInitial = computed(() => user.value?.name?.charAt(0)?.toUpperCase() || 'U')
</script>

<template>
    <SkipLink />
    <div class="min-h-screen bg-neutral-50 font-sans text-gray-900">
        <!-- Mobile sidebar backdrop -->
        <div 
            v-if="sidebarOpen" 
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
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
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500 shadow-glow">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="text-lg font-bold tracking-tight text-white">BuildFlow</span>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 space-y-1 px-3 py-4">
                    <Link 
                        v-for="item in navItems" 
                        :key="item.route"
                        :href="route(item.route)"
                        :class="[
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150',
                            item.active 
                                ? 'bg-slate-800 text-brand-400' 
                                : 'text-gray-400 hover:bg-slate-800 hover:text-white'
                        ]"
                    >
                        <!-- Dashboard Icon -->
                        <svg v-if="item.icon === 'dashboard'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <!-- Projects Icon -->
                        <svg v-else-if="item.icon === 'projects'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <!-- Members Icon -->
                        <svg v-else-if="item.icon === 'members'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Support Icon -->
                        <svg v-else-if="item.icon === 'support'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>

                        <!-- CRM Icon -->
                        <svg v-else-if="item.icon === 'crm'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                             <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3H9m-1.5 3H9m-1.5 3H9m10.5-3h.75m-.75 3h.75m-.75 3h.75m-6.75-10.5h.75m-2.625 10.5h1.5" />
                        </svg>

                        <!-- Finance Icon -->
                        <svg v-else-if="item.icon === 'finance'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                             <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>

                         <!-- HR Icon -->
                        <svg v-else-if="item.icon === 'hr'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                             <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        


                        {{ item.label }}
                    </Link>
                </nav>

                <!-- User Profile Section -->
                <div class="border-t border-slate-800 p-4 relative">
                    <!-- Custom Dropup Menu -->
                    <div v-if="showingNavigationDropdown" class="absolute bottom-full left-4 right-4 mb-2 z-50 rounded-lg bg-slate-800 border border-slate-700 shadow-xl overflow-hidden animate-fade-in-up">
                         <div class="block px-4 py-2 text-xs text-gray-400 font-semibold uppercase tracking-wider">Manage Account</div>
                         <Link 
                            :href="route('billing')" 
                            class="block px-4 py-2 text-sm text-gray-300 hover:bg-slate-700 hover:text-white transition-colors"
                        >
                            Plans & Usage
                         </Link>
                         <Link 
                            :href="route('settings.profile')" 
                            class="block px-4 py-2 text-sm text-gray-300 hover:bg-slate-700 hover:text-white transition-colors"
                        >
                            Profile Settings
                         </Link>
                         <div class="border-t border-slate-700 my-1"></div>
                         <Link 
                            :href="route('logout')" 
                            method="post" 
                            as="button" 
                            class="block w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-slate-700 hover:text-red-300 transition-colors"
                        >
                            Sign Out
                         </Link>
                    </div>

                    <button 
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="flex w-full items-center gap-3 rounded-lg p-2 transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500"
                        :class="{ 'bg-slate-800': showingNavigationDropdown }"
                    >
                        <!-- Avatar or Initial -->
                        <div class="relative h-10 w-10 shrink-0 rounded-full overflow-hidden border border-slate-700">
                            <img 
                                v-if="avatarUrl" 
                                :src="avatarUrl" 
                                :alt="user?.name"
                                class="h-full w-full object-cover"
                            />
                            <div 
                                v-else 
                                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-400 to-brand-600 text-sm font-bold text-white"
                            >
                                {{ userInitial }}
                            </div>
                        </div>
                        <div class="flex-1 text-left min-w-0">
                            <div class="text-sm font-medium text-white truncate">{{ user?.name }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ user?.email }}</div>
                        </div>
                        <svg 
                            class="h-4 w-4 text-gray-500 transition-transform duration-200" 
                            :class="{ 'rotate-180': showingNavigationDropdown }"
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Backdrop -->
                    <div 
                        v-if="showingNavigationDropdown" 
                        class="fixed inset-0 z-40 bg-transparent" 
                        @click="showingNavigationDropdown = false"
                    ></div>
                </div>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="lg:pl-64 flex flex-col min-h-screen">
            <!-- Top Banner for Past Due -->
            <div v-if="org && isPastDue" class="bg-brand-500 px-4 py-2.5 text-white">
                <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 sm:flex-row sm:px-6 lg:px-8">
                    <div class="text-sm font-medium">
                        <span class="font-bold">⚠ Payment Required:</span> 
                        {{ org.name }} is in grace period until {{ graceEndsAtText }}.
                    </div>
                    <Link href="/app/billing" class="rounded-full bg-white px-4 py-1.5 text-xs font-bold text-brand-600 shadow-sm hover:bg-brand-50 transition">
                        Renew Now &rarr;
                    </Link>
                </div>
            </div>

            <!-- Mobile header -->
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between bg-slate-900 px-4 lg:hidden">
                <button 
                    @click="sidebarOpen = true"
                    class="rounded-md p-2 text-gray-400 hover:bg-slate-800 hover:text-white focus:outline-none"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-white">BuildFlow</span>
                </Link>

                <div class="flex items-center gap-2">
                    <NotificationsBell class="text-gray-400 hover:text-brand-400 transition" />
                    <!-- Mobile avatar -->
                    <div class="h-8 w-8 rounded-full overflow-hidden">
                        <img v-if="avatarUrl" :src="avatarUrl" :alt="user?.name" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-400 to-brand-600 text-xs font-bold text-white">
                            {{ userInitial }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Desktop top bar (minimal) -->
            <header class="hidden lg:flex h-14 items-center justify-end gap-4 bg-white border-b border-gray-100 px-6">
                <NotificationsBell class="text-gray-500 hover:text-brand-500 transition" />
            </header>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow-sm border-b border-gray-100">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main id="main-content" role="main" class="relative flex-1">
                <div class="mx-auto max-w-7xl relative px-4 py-6 sm:px-6 lg:px-8">
                    
                    <!-- Lock Overlay -->
                    <div v-if="showLockOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
                        <div class="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-2xl ring-1 ring-gray-900/5">
                            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-brand-50">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-brand-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            
                            <h3 class="text-xl font-bold tracking-tight text-gray-900">Access Restricted</h3>
                            <p class="mt-2 text-sm text-gray-500">
                                Your organization <span class="font-medium text-gray-900">{{ org?.name }}</span> is {{ status?.replace('_', ' ') }}. Renew now to continue.
                            </p>

                            <div class="mt-8 flex flex-col gap-3">
                                <Link href="/app/billing" class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 transition">
                                    Renew Subscription
                                </Link>
                                <Link href="/app/support" class="w-full text-sm font-semibold text-gray-600 hover:text-brand-600 transition">
                                    Contact Support
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div :class="showLockOverlay ? 'pointer-events-none select-none opacity-25 blur-sm' : ''">
                        <slot />
                    </div>
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
