<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'

const props = defineProps<{
  organization: { id:number; name:string; type:'individual'|'company' }
}>()

const form = useForm({
  name: '',
  code: '',
  client_name: '',
  client_phone: '',
  client_email: '',
  location: '',
  start_date: '',
  end_date: '',
  status: 'active',
  budget: '',
  description: '',
})

function submit() {
  form.post('/app/projects', { preserveScroll: true })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="New Project" />

    <div class="min-h-screen bg-gray-50/50">
      <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
          <Link href="/app/projects" class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-700 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Projects
          </Link>
        </div>

        <!-- Form Card -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-card">
          <div class="border-b border-gray-100 px-6 py-5">
            <h1 class="text-xl font-semibold text-gray-900">Create Project</h1>
            <p class="mt-1 text-sm text-gray-500">Add basic project details. You can invite team members later.</p>
          </div>

          <form @submit.prevent="submit" class="divide-y divide-gray-100">
            <!-- Project Details Section -->
            <div class="px-6 py-6">
              <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Project Details</h2>
              
              <div class="mt-5 space-y-5">
                <div>
                  <label class="block text-sm font-medium text-gray-700">
                    Project name <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model="form.name" 
                    type="text"
                    class="mt-1.5 block w-full rounded-lg border-gray-300 px-3.5 py-2.5 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:ring-brand-500" 
                    placeholder="e.g. Downtown Office Complex" 
                  />
                  <div v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Project Code</label>
                    <input 
                      v-model="form.code" 
                      type="text"
                      class="mt-1.5 block w-full rounded-lg border-gray-300 px-3.5 py-2.5 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:ring-brand-500" 
                      placeholder="e.g. BF-001" 
                    />
                    <p class="mt-1.5 text-xs text-gray-500">Short reference code. Auto-generated if blank.</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select 
                      v-model="form.status" 
                      class="mt-1.5 block w-full rounded-lg border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                    >
                      <option value="active">Active</option>
                      <option value="paused">Paused</option>
                      <option value="completed">Completed</option>
                      <option value="archived">Archived</option>
                    </select>
                    <div v-if="form.errors.status" class="mt-1.5 text-sm text-red-600">{{ form.errors.status }}</div>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Project Budget</label>
                  <div class="relative mt-1.5 rounded-md shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                      <span class="text-gray-500 sm:text-sm">₦</span>
                    </div>
                    <input 
                      v-model="form.budget" 
                      type="number"
                      step="0.01"
                      class="block w-full rounded-lg border-gray-300 py-2.5 pl-7 pr-16 text-sm focus:border-brand-500 focus:ring-brand-500" 
                      placeholder="0.00" 
                    />
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                      <span class="text-gray-500 sm:text-sm">NGN</span>
                    </div>
                  </div>
                  <p class="mt-1.5 text-xs text-gray-500">Estimated total budget for this project.</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Location</label>
                  <input 
                    v-model="form.location" 
                    type="text"
                    class="mt-1.5 block w-full rounded-lg border-gray-300 px-3.5 py-2.5 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:ring-brand-500" 
                    placeholder="Site address or coordinates" 
                  />
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input 
                      type="date" 
                      v-model="form.start_date" 
                      class="mt-1.5 block w-full rounded-lg border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" 
                    />
                    <p class="mt-1.5 text-xs text-gray-500">When work begins on site.</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input 
                      type="date" 
                      v-model="form.end_date" 
                      class="mt-1.5 block w-full rounded-lg border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" 
                    />
                    <p class="mt-1.5 text-xs text-gray-500">Expected completion date.</p>
                    <div v-if="form.errors.end_date" class="mt-1.5 text-sm text-red-600">{{ form.errors.end_date }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Client Information Section -->
            <div class="px-6 py-6">
              <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Client Information</h2>
              
              <div class="mt-5 space-y-5">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Client Name</label>
                    <input 
                      v-model="form.client_name" 
                      type="text"
                      class="mt-1.5 block w-full rounded-lg border-gray-300 px-3.5 py-2.5 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:ring-brand-500" 
                      placeholder="Company or individual name" 
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Client Phone</label>
                    <input 
                      v-model="form.client_phone" 
                      type="text"
                      class="mt-1.5 block w-full rounded-lg border-gray-300 px-3.5 py-2.5 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:ring-brand-500" 
                      placeholder="+234 800 000 0000" 
                    />
                    <p class="mt-1.5 text-xs text-gray-500">Include country code for SMS notifications.</p>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Client Email</label>
                  <input 
                    v-model="form.client_email" 
                    type="email"
                    class="mt-1.5 block w-full rounded-lg border-gray-300 px-3.5 py-2.5 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:ring-brand-500" 
                    placeholder="client@example.com" 
                  />
                  <div v-if="form.errors.client_email" class="mt-1.5 text-sm text-red-600">{{ form.errors.client_email }}</div>
                </div>
              </div>
            </div>

            <!-- Description Section -->
            <div class="px-6 py-6">
              <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Additional Details</h2>
              
              <div class="mt-5">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea 
                  v-model="form.description" 
                  rows="4"
                  class="mt-1.5 block w-full rounded-lg border-gray-300 px-3.5 py-2.5 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:ring-brand-500" 
                  placeholder="Brief project overview, scope, or notes..."
                ></textarea>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 px-6 py-5 bg-gray-50 rounded-b-2xl">
              <Link 
                href="/app/projects" 
                class="w-full sm:w-auto text-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition"
              >
                Cancel
              </Link>
              <button
                type="submit"
                class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="form.processing"
              >
                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span v-if="form.processing">Creating...</span>
                <span v-else>Create Project</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
