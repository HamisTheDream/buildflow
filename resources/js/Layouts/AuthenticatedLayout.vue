```vue
<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { formatDateTime } from '@/utils/format';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import FlashMessage from '@/Components/FlashMessage.vue';

const showingNavigationDropdown = ref(false);

const page = usePage();

// Org resolver (supports multiple prop names)
const org = computed(() => {
  return page.props.org || page.props.currentOrg || page.props.currentOrganization || page.props.auth?.organization || null
})

// Determine billing state (fallback to active)
const status = computed(() => (org.value?.subscription?.status || org.value?.status || 'active').toLowerCase())

// Allow browsing of these routes even when locked
const allowedPathsWhenLocked = [
  '/app/billing',
  '/app/settings/profile',
  '/app/support',
  '/logout',
]

// Current path helper
const currentPath = computed(() => {
  // Inertia page url usually includes query; normalize to path only
  const url = page.url || window.location.pathname
  return url.split('?')[0]
})

const isPastDue = computed(() => status.value === 'past_due' || status.value === 'grace')

// Locked statuses
const isLocked = computed(() => {
    // Rely on backend or status string
    const s = status.value;
    if (['suspended', 'blocked', 'expired'].includes(s)) return true;
    
    // Check grace period expiry (if status is past_due but grace has passed)
    if (s === 'past_due' && org.value?.subscription?.grace_ends_at) {
        return new Date(org.value.subscription.grace_ends_at) < new Date();
    }
    return false;
})

const isAllowedWhileLocked = computed(() => {
  return allowedPathsWhenLocked.some(p => currentPath.value.startsWith(p))
})

const showLockOverlay = computed(() => isLocked.value && !isAllowedWhileLocked.value)

const graceEndsAtText = computed(() => {
  const v = org.value?.subscription?.grace_ends_at || org.value?.grace_ends_at
  return v ? formatDateTime(v) : '—'
})
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-50">
            <!-- Top global banner for past due / grace -->
            <div v-if="org && isPastDue" class="border-b bg-amber-50">
              <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                  <div>
                    <div class="text-sm font-semibold text-amber-900">
                      Subscription payment required
                    </div>
                    <div class="mt-0.5 text-sm text-amber-900/80">
                      Your organization <span class="font-semibold">{{ org.name }}</span> is in grace period.
                      <span class="font-semibold">Grace ends:</span> {{ graceEndsAtText }}.
                      Renew to avoid service interruption.
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <Link
                      href="/app/billing"
                      class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black"
                    >
                      Renew now
                    </Link>
                    <Link
                      href="/app/support"
                      class="rounded-lg border bg-white px-4 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-50 bg-amber-50"
                    >
                      Contact support
                    </Link>
                  </div>
                </div>
              </div>
            </div>

            <nav
                class="border-b border-gray-100 bg-white"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    :href="route('projects.index')"
                                    :active="route().current('projects.*')"
                                >
                                    Projects
                                </NavLink>
                                <NavLink
                                    :href="route('app.support.create')"
                                    :active="route().current('app.support.*')"
                                >
                                    Support
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('billing')"
                                        >
                                            Plans & Usage
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('settings.profile')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('projects.index')"
                            :active="route().current('projects.*')"
                        >
                            Projects
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('app.support.create')"
                            :active="route().current('app.support.*')"
                        >
                            Support
                        </ResponsiveNavLink>
                    </div>

                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('billing')"> Plans & Usage </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('settings.profile')"> Profile </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8 relative">
                    <FlashMessage />
                    
                    <!-- Lock Overlay -->
                    <div
                      v-if="showLockOverlay"
                      class="absolute inset-0 z-40 flex items-start justify-center rounded-2xl bg-white/90 p-6 backdrop-blur pt-20"
                    >
                      <div class="w-full max-w-lg text-center">
                        <div class="rounded-full bg-red-100 p-3 mx-auto w-fit mb-4">
                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-red-600">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </div>
                        
                        <div class="text-xl font-bold text-gray-900">
                          Access limited — subscription inactive
                        </div>

                        <div class="mt-2 text-sm text-gray-700">
                          Your organization <span class="font-semibold">{{ org?.name }}</span> status is currently
                          <span class="font-semibold uppercase">{{ status?.replace('_', ' ') }}</span>.
                          Renew your subscription in billing to regain full access.
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                          <Link
                            href="/app/billing"
                            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 shadow-sm transition"
                          >
                            Go to Billing
                          </Link>

                          <Link
                            href="/app/support"
                            class="rounded-lg border bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 hover:bg-gray-50 shadow-sm transition"
                          >
                            Contact Support
                          </Link>
                        </div>
                      </div>
                    </div>

                    <!-- Actual Slot -->
                    <div :class="showLockOverlay ? 'pointer-events-none select-none opacity-20 blur-[1px] transition duration-500' : ''">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
```
