<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3'

const page = usePage<any>()

const form = useForm({
  email: '',
  password: '',
  remember: true,
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <Head title="Owner Login" />

    <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow">
      <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-xl bg-indigo-600"></div>
        <div>
          <div class="text-lg font-bold text-gray-900">BuildFlow Owner</div>
          <div class="text-sm text-gray-600">Sign in to manage the platform</div>
        </div>
      </div>

      <div v-if="page.props.status" class="mt-4 rounded-xl border border-indigo-200 bg-indigo-50 p-3 text-sm text-indigo-800">
        {{ page.props.status }}
      </div>

      <form class="mt-6 space-y-4" @submit.prevent="form.post('/owner/login')">
        <div>
          <label class="text-xs font-semibold text-gray-700">Email</label>
          <input v-model="form.email" type="email" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="admin@buildflow.com" />
          <div v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</div>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-700">Password</label>
          <input v-model="form.password" type="password" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="••••••••" />
          <div v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</div>
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" v-model="form.remember" class="h-4 w-4" />
            Remember me
          </label>
        </div>

        <button
          type="submit"
          class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
          :disabled="form.processing"
        >
          Sign in
        </button>
      </form>

      <div class="mt-6 text-xs text-gray-500">
        This console is restricted to BuildFlow administrators.
      </div>
    </div>
  </div>
</template>
