<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { formatDate, formatDateTime, truncate } from '@/utils/format'

// Using raw Tailwind classes for public page to avoid dependency on Auth components if they assume auth user
// But we can import stateless components if they are clean.
import Badge from '@/Components/Badge.vue'

const props = defineProps<{
  token: string
  title: string
  projectName: string
  from: string
  to: string
  requiresPassword: boolean

  branding?: {
    name?: string | null
    logo?: string | null
    email?: string | null
    phone?: string | null
    address?: string | null
  } | null

  evidence?: {
    attachments: any[]
    media: any[]
  } | null
}>()

const form = useForm({
  password: '',
})

function unlock() {
  form.post(route('reports.share.unlock', props.token))
}

const hasEvidence = computed(() => {
  if (!props.evidence) return false
  return (props.evidence.attachments?.length > 0) || (props.evidence.media?.length > 0)
})

declare const route: any;
</script>

<template>
  <Head :title="title" />

  <div class="min-h-screen bg-gray-50 font-sans text-gray-900 selection:bg-indigo-100 selection:text-indigo-700">
    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
      
      <!-- Password Protection -->
      <div v-if="requiresPassword" class="mx-auto max-w-md rounded-2xl bg-white p-8 shadow-xl">
        <div class="text-center">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50">
            <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <h2 class="mt-6 text-2xl font-bold tracking-tight text-gray-900">{{ title }}</h2>
          <p class="mt-2 text-sm text-gray-600">{{ projectName }}</p>
          <p class="mt-4 text-sm text-gray-500">This report is password protected.</p>
        </div>

        <form @submit.prevent="unlock" class="mt-8 space-y-4">
          <div>
            <label class="sr-only">Password</label>
            <input 
              v-model="form.password"
              type="password" 
              class="w-full rounded-xl border-gray-300 p-3 text-center text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              placeholder="Enter password..."
              autofocus
            />
          </div>

          <div v-if="form.errors.password" class="text-xs text-red-600 text-center font-medium">
            {{ form.errors.password }}
          </div>

          <button 
            type="submit"
            class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700 hover:shadow-indigo-300 disabled:opacity-70"
            :disabled="form.processing"
          >
            Unlock Report
          </button>
        </form>
      </div>

      <!-- Unlocked Report -->
      <div v-else class="space-y-8">
        
        <!-- Header / Branding -->
        <div class="overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-gray-900/5">
          <div class="px-6 py-8 sm:px-10 sm:py-10">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
              <div>
                <div v-if="branding?.name" class="text-sm font-bold uppercase tracking-widest text-indigo-600">
                  {{ branding.name }}
                </div>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                  {{ title }}
                </h1>
                <div class="mt-2 text-lg text-gray-600">
                  {{ projectName }}
                </div>
                
                <div class="mt-4 flex flex-wrap items-center gap-3 text-sm text-gray-500">
                  <div class="flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 font-medium">
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ formatDate(from) }} — {{ formatDate(to) }}
                  </div>
                </div>
              </div>

              <!-- Brand Contact Info -->
              <div v-if="branding" class="text-right text-sm text-gray-500 hidden sm:block">
                <div v-if="branding.email">{{ branding.email }}</div>
                <div v-if="branding.phone">{{ branding.phone }}</div>
                <div v-if="branding.address" class="whitespace-pre-wrap max-w-[200px]">{{ branding.address }}</div>
              </div>
            </div>

             <div class="mt-10 flex flex-col sm:flex-row gap-4">
               <a 
                 :href="route('reports.share.download', token)" 
                 class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700 hover:shadow-indigo-300 hover:-translate-y-0.5"
               >
                 <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                 </svg>
                 Download PDF
               </a>
             </div>
          </div>
        </div>

        <!-- Evidence Section -->
        <div v-if="hasEvidence" class="space-y-6">
          <h3 class="ml-2 text-lg font-bold text-gray-900">Project evidence</h3>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
             <!-- Media Items -->
             <template v-if="evidence?.media">
               <div 
                  v-for="m in evidence.media" 
                  :key="m.id" 
                  class="group relative overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-900/5 transition hover:shadow-lg"
                >
                 <div class="aspect-video w-full overflow-hidden bg-gray-100">
                    <img 
                      v-if="m.url" 
                      :src="m.url" 
                      loading="lazy"
                      class="h-full w-full object-cover transition duration-500 group-hover:scale-105" 
                    />
                    <div v-else class="flex h-full items-center justify-center text-gray-400">
                      No Preview
                    </div>
                 </div>
                 <div class="p-4">
                    <div class="text-sm font-medium text-gray-900 line-clamp-1">
                      {{ m.caption || 'Project Media' }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                       {{ formatDateTime(m.created_at) }}
                    </div>
                 </div>
               </div>
             </template>

             <!-- File Attachments -->
             <template v-if="evidence?.attachments">
                <a 
                  v-for="a in evidence.attachments" 
                  :key="a.id"
                  :href="a.url"
                  target="_blank"
                  class="group flex items-start gap-3 rounded-2xl bg-white p-4 shadow-md ring-1 ring-gray-900/5 transition hover:bg-gray-50 hover:shadow-lg"
                >
                   <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100 transition">
                      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                   </div>
                   <div class="min-w-0">
                      <div class="text-sm font-semibold text-gray-900 truncate">
                        {{ a.caption || a.name }}
                      </div>
                      <div class="mt-0.5 text-xs text-gray-500">
                        {{ formatDateTime(a.created_at) }} &middot; {{ (a.size / 1024).toFixed(0) }} KB
                        <span v-if="a.uploader">by {{ a.uploader }}</span>
                      </div>
                   </div>
                   <div class="ml-auto">
                     <svg class="h-4 w-4 text-gray-400 group-hover:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                     </svg>
                   </div>
                </a>
             </template>
          </div>
        </div>
        
        <div class="text-center pt-8 pb-4">
           <p class="text-xs font-medium text-gray-400">
             Securely shared via BuildFlow
           </p>
        </div>
      </div>

    </div>
  </div>
</template>
