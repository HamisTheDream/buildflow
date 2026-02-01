<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'

const page = usePage()
const user: any = page.props.auth?.user
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Dashboard" />

    <div class="p-6">
      <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>

      <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow">
          <div class="text-sm text-gray-500">Logged in as</div>
          <div class="mt-1 text-lg font-semibold text-gray-900">{{ user?.name }}</div>
          <div class="text-sm text-gray-600">{{ user?.email }}</div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow">
          <div class="text-sm text-gray-500">Current organization</div>
          <div class="mt-1 text-lg font-semibold text-gray-900">
            {{ user?.current_organization_id ? 'Selected' : 'None' }}
          </div>
          <div class="text-sm text-gray-600">Account type is set during registration.</div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
