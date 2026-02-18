<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'

const form = useForm({
  name: '',
  email: '',
  password: '',
  is_super: false,
  is_active: true,
  audit_reason: '',
})
</script>

<template>
  <OwnerLayout>
    <Head title="New Admin" />

    <SectionCard title="Create admin" subtitle="Super admin only. Set a strong password.">
      <form class="space-y-4" @submit.prevent="form.post('/owner/admins')">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="text-xs font-semibold text-gray-700">Name</label>
            <input v-model="form.name" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" />
            <div v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</div>
          </div>

          <div>
            <label class="text-xs font-semibold text-gray-700">Email</label>
            <input v-model="form.email" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" />
            <div v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</div>
          </div>

          <div class="md:col-span-2">
            <label class="text-xs font-semibold text-gray-700">Password</label>
            <input v-model="form.password" type="password" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" />
            <div v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</div>
            <div class="mt-1 text-xs text-gray-500">Minimum 10 characters recommended.</div>
          </div>

          <div>
            <label class="text-xs font-semibold text-gray-700">Super admin</label>
            <select v-model="form.is_super" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500">
              <option :value="false">No</option>
              <option :value="true">Yes</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-semibold text-gray-700">Active</label>
            <select v-model="form.is_active" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500">
              <option :value="true">Yes</option>
              <option :value="false">No</option>
            </select>
          </div>
        </div>

        <div class="rounded-xl border p-4">
          <div class="text-sm font-semibold text-gray-900">Audit reason (optional)</div>
          <input v-model="form.audit_reason" class="mt-2 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="e.g. Hired support staff" />
        </div>

        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                :disabled="form.processing">
          Create admin
        </button>
      </form>
    </SectionCard>
  </OwnerLayout>
</template>
