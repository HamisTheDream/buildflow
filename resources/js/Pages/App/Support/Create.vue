<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'

const props = defineProps<{
  categories: string[]
  priorities: string[]
}>()

const page = usePage<any>()

const form = useForm({
  subject: '',
  message: '',
  category: 'other',
  priority: 'normal',
})
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Support" />

    <SectionCard title="Contact Support" subtitle="Tell us what you need and we’ll respond quickly.">
      <form class="space-y-4" @submit.prevent="form.post('/app/support')">
        <div>
          <label class="text-xs font-semibold text-gray-700">Subject</label>
          <input v-model="form.subject" class="mt-1 w-full rounded-lg border p-2 text-sm" placeholder="Brief summary" />
          <div v-if="form.errors.subject" class="mt-1 text-xs text-red-600">{{ form.errors.subject }}</div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="text-xs font-semibold text-gray-700">Category</label>
            <select v-model="form.category" class="mt-1 w-full rounded-lg border p-2 text-sm">
              <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-semibold text-gray-700">Priority</label>
            <select v-model="form.priority" class="mt-1 w-full rounded-lg border p-2 text-sm">
              <option v-for="p in priorities" :key="p" :value="p">{{ p }}</option>
            </select>
          </div>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-700">Message</label>
          <textarea v-model="form.message" rows="7" class="mt-1 w-full rounded-lg border p-2 text-sm" placeholder="Describe the issue with details..."></textarea>
          <div v-if="form.errors.message" class="mt-1 text-xs text-red-600">{{ form.errors.message }}</div>
        </div>

        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                :disabled="form.processing">
          Submit ticket
        </button>

        <div class="text-xs text-gray-500">
          If this is billing-related, include your organization name and payment reference (if any).
        </div>
      </form>
    </SectionCard>
  </AuthenticatedLayout>
</template>
