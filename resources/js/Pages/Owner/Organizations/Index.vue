<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { watch, computed } from 'vue'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import StatCard from '@/Components/StatCard.vue'
import { formatDateTime, formatEnum } from '@/utils/format'

const props = defineProps<{
  filters: { q:string; status:string; plan:string }
  plans: { id:number; key:string; name:string }[]
  organizations: { data:any[]; links:any[]; total: number }
  metrics: { total: number; active: number; trial: number; expired: number }
}>()

const form = useForm({
  q: props.filters.q || '',
  status: props.filters.status || '',
  plan: props.filters.plan || '',
})

let timeout: any = null

watch(
  () => [form.q, form.status, form.plan],
  () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
      form.get('/owner/organizations', {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      })
    }, 300)
  }
)

function statusStyles(s:string) {
  if (s === 'active') return 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200'
  if (s === 'trial') return 'bg-blue-100 text-blue-700 ring-1 ring-blue-200'
  if (s === 'past_due') return 'bg-amber-100 text-amber-700 ring-1 ring-amber-200'
  if (s === 'suspended') return 'bg-red-100 text-red-700 ring-1 ring-red-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}
</script>

<template>
  <OwnerLayout>
    <Head title="Organizations" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Organizations</h1>
          <p class="mt-1 text-sm text-gray-500">Manage your customer base and subscriptions.</p>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <StatCard title="Total Organizations" :value="metrics.total" icon="folder" />
        <StatCard title="Active Subscriptions" :value="metrics.active" icon="activity" variant="success" />
        <StatCard title="On Trial" :value="metrics.trial" icon="users" variant="primary" />
        <StatCard 
          title="Past Due / Expired" 
          :value="metrics.expired" 
          icon="issue" 
          :variant="metrics.expired > 0 ? 'warning' : 'default'" 
        />
      </div>

      <!-- Filters Bar -->
      <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
          <div class="md:col-span-5 relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input 
                v-model="form.q" 
                class="block w-full rounded-lg border-0 bg-gray-50/50 py-2.5 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-500 sm:text-sm sm:leading-6" 
                placeholder="Search by name, email, or domain..." 
            />
          </div>

          <div class="md:col-span-3">
            <select v-model="form.status" class="block w-full rounded-lg border-0 bg-gray-50/50 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-500 sm:text-sm sm:leading-6">
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="trial">Trial</option>
              <option value="past_due">Past Due</option>
              <option value="suspended">Suspended</option>
              <option value="free">Free</option>
            </select>
          </div>

          <div class="md:col-span-3">
            <select v-model="form.plan" class="block w-full rounded-lg border-0 bg-gray-50/50 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-500 sm:text-sm sm:leading-6">
              <option value="">All Plans</option>
              <option v-for="p in plans" :key="p.key" :value="p.key">{{ p.name }}</option>
            </select>
          </div>
          
           <div class="md:col-span-1 flex justify-end">
               <!-- Can add export button here later -->
           </div>
        </div>
      </div>

      <!-- Organizations Grid/List -->
      <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
        <ul role="list" class="divide-y divide-gray-100">
          <li v-for="org in organizations.data" :key="org.id" class="relative group hover:bg-gray-50 transition-colors">
            <Link :href="`/owner/organizations/${org.id}`" class="block p-6">
                <div class="flex items-center justify-between gap-x-6 sm:items-start">
                    <div class="flex gap-x-4">
                        <!-- Avatar/Logo -->
                        <div class="h-12 w-12 flex-none rounded-lg bg-gray-100 flex items-center justify-center text-lg font-bold text-gray-500 overflow-hidden ring-1 ring-gray-200">
                             <img v-if="org.brand_logo_path" :src="org.brand_logo_path" class="h-full w-full object-cover" />
                             <span v-else>{{ org.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm font-semibold leading-6 text-gray-900 group-hover:text-brand-600 transition-colors">
                                {{ org.name }}
                                <span v-if="org.brand_email" class="ml-2 font-normal text-gray-400 text-xs">({{ org.brand_email }})</span>
                            </p>
                            <p class="mt-1 flex text-xs leading-5 text-gray-500">
                                <span class="truncate">Plan: {{ org.plan?.name || 'Free' }}</span>
                                <svg viewBox="0 0 2 2" class="mx-2 inline h-0.5 w-0.5 fill-current bg-gray-500 content-center self-center" aria-hidden="true"><circle cx="1" cy="1" r="1" /></svg>
                                <span class="truncate">Created {{ formatDateTime(org.created_at) }}</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-end gap-y-2">
                        <span 
                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium capitalize ring-1 ring-inset"
                            :class="statusStyles(org.subscription_status)"
                        >
                            {{ org.subscription_status?.replace('_', ' ') || 'Unknown' }}
                        </span>
                        
                        <div v-if="org.trial_ends_at && org.subscription_status === 'trial'" class="text-xs text-gray-500">
                            Trial ends {{ formatDateTime(org.trial_ends_at) }}
                        </div>
                    </div>
                     <svg class="h-5 w-5 flex-none text-gray-400 group-hover:text-brand-400 absolute right-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </Link>
          </li>
          
           <li v-if="organizations.data.length === 0" class="py-12 text-center">
              <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <h3 class="mt-2 text-sm font-semibold text-gray-900">No organizations found</h3>
              <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters.</p>
          </li>
        </ul>
      </div>

      <Pagination :links="organizations.links" />
    </div>
  </OwnerLayout>
</template>
