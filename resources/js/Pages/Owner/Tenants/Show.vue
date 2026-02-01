<script setup lang="ts">
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps<{
  organization: any
  usage: any
}>()
</script>

<template>
  <OwnerLayout>
    <Head :title="organization.name" />

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
            <Link :href="route('owner.tenants.index')" class="text-sm text-gray-400 hover:text-white">← Back to Tenants</Link>
            <h1 class="text-2xl font-bold text-white">{{ organization.name }}</h1>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Details -->
            <div class="rounded-lg bg-gray-800 p-6 shadow">
                <h3 class="text-lg font-medium text-white">Organization Details</h3>
                <dl class="mt-4 grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-400">Current Plan</dt>
                        <dd class="mt-1 text-sm text-white">{{ organization.plan?.name || 'Free' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-400">Subscription Status</dt>
                        <dd class="mt-1 text-sm text-white">{{ organization.subscription_status || 'Inactive' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-400">Created At</dt>
                        <dd class="mt-1 text-sm text-white">{{ new Date(organization.created_at).toLocaleDateString() }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Usage -->
            <div class="rounded-lg bg-gray-800 p-6 shadow">
                <h3 class="text-lg font-medium text-white">Current Usage</h3>
                <dl class="mt-4 grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-400">Projects</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white">{{ usage.projects }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-400">Members</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white">{{ usage.members }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-400">Storage</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white">{{ usage.storage_mb }} MB</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Users List -->
        <div class="mt-8">
            <h3 class="text-lg font-medium text-white">Users</h3>
            <div class="mt-4 overflow-hidden rounded-lg bg-gray-800 shadow ring-1 ring-white/5">
                <ul role="list" class="divide-y divide-gray-700">
                    <li v-for="user in organization.users" :key="user.id" class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="truncate text-sm font-medium text-indigo-300">{{ user.name }}</div>
                                <div class="ml-2 truncate text-sm text-gray-500">{{ user.email }}</div>
                            </div>
                            <div class="hidden text-sm text-gray-400 sm:block">
                                {{ user.pivot?.role || 'Member' }}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

      </div>
    </div>
  </OwnerLayout>
</template>
