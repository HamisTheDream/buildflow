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
  description: '',
})

function submit() {
  form.post('/app/projects', { preserveScroll: true })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="New Project" />

    <div class="p-6">
      <div class="mb-4">
        <Link href="/app/projects" class="text-sm text-indigo-600 hover:underline">← Back to Projects</Link>
      </div>

      <div class="mx-auto max-w-3xl rounded-xl bg-white p-6 shadow">
        <h1 class="text-xl font-semibold text-gray-900">Create Project</h1>
        <p class="mt-1 text-sm text-gray-600">Add basic project details. You can invite team members later.</p>

        <form class="mt-6 space-y-4" @submit.prevent="submit">
          <div>
            <label class="text-sm text-gray-700">Project name</label>
            <input v-model="form.name" class="mt-1 w-full rounded-lg border p-2" />
            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm text-gray-700">Code (optional)</label>
              <input v-model="form.code" class="mt-1 w-full rounded-lg border p-2" placeholder="e.g. BF-001" />
            </div>
            <div>
              <label class="text-sm text-gray-700">Status</label>
              <select v-model="form.status" class="mt-1 w-full rounded-lg border p-2">
                <option value="active">Active</option>
                <option value="paused">Paused</option>
                <option value="completed">Completed</option>
                <option value="archived">Archived</option>
              </select>
              <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm text-gray-700">Client name</label>
              <input v-model="form.client_name" class="mt-1 w-full rounded-lg border p-2" />
            </div>
            <div>
              <label class="text-sm text-gray-700">Client phone</label>
              <input v-model="form.client_phone" class="mt-1 w-full rounded-lg border p-2" />
            </div>
          </div>

          <div>
            <label class="text-sm text-gray-700">Client email</label>
            <input v-model="form.client_email" class="mt-1 w-full rounded-lg border p-2" />
            <div v-if="form.errors.client_email" class="mt-1 text-sm text-red-600">{{ form.errors.client_email }}</div>
          </div>

          <div>
            <label class="text-sm text-gray-700">Location</label>
            <input v-model="form.location" class="mt-1 w-full rounded-lg border p-2" />
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm text-gray-700">Start date</label>
              <input type="date" v-model="form.start_date" class="mt-1 w-full rounded-lg border p-2" />
            </div>
            <div>
              <label class="text-sm text-gray-700">End date</label>
              <input type="date" v-model="form.end_date" class="mt-1 w-full rounded-lg border p-2" />
              <div v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">{{ form.errors.end_date }}</div>
            </div>
          </div>

          <div>
            <label class="text-sm text-gray-700">Description</label>
            <textarea v-model="form.description" class="mt-1 w-full rounded-lg border p-2" rows="4"></textarea>
          </div>

          <button
            class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
            :disabled="form.processing"
          >
            Create Project
          </button>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
