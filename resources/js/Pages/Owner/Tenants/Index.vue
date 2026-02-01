<script setup lang="ts">
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps<{
  orgs: any
  filters: any
}>()

const form = useForm({
  search: props.filters.search || '',
})

watch(() => form.search, () => {
  form.get(route('owner.tenants.index'), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
})

function planColor(key: string) {
  switch (key) {
    case 'starter': return 'bg-blue-100 text-blue-800'
    case 'pro': return 'bg-indigo-100 text-indigo-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}
</script>

<template>
  <OwnerLayout>
    <Head title="Tenants" />

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-white">Tenants</h1>
            <input 
                v-model="form.search"
                type="text" 
                placeholder="Search organizations..." 
                class="rounded-lg border-gray-700 bg-gray-800 text-sm text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500"
            />
        </div>

        <div class="mt-8 overflow-hidden rounded-lg bg-gray-800 shadow ring-1 ring-white/5">
          <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-700/50">
              <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-white sm:pl-6">Name</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Plan</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Users</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Projects</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Created At</th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                  <span class="sr-only">View</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-700 bg-gray-800">
              <tr v-for="org in orgs.data" :key="org.id">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-white sm:pl-6">
                    <div>{{ org.name }}</div>
                    <div class="text-xs text-gray-400">{{ org.email || 'No email' }}</div>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-300">
                    <span :class="['inline-flex rounded-full px-2 text-xs font-semibold leading-5', planColor(org.plan?.key)]">
                        {{ org.plan?.name || 'Free' }}
                    </span>
                    <span v-if="org.subscription_status === 'active'" class="ml-2 text-xs text-green-400">Active</span>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-300">{{ org.users_count }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-300">{{ org.projects_count }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ new Date(org.created_at).toLocaleDateString() }}</td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                  <Link :href="route('owner.tenants.show', org.id)" class="text-indigo-400 hover:text-indigo-300">View</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination logic could go here -->
      </div>
    </div>
  </OwnerLayout>
</template>
