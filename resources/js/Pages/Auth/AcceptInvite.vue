<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  token: string
  invite: {
    organization_name: string
    organization_type: 'individual' | 'company'
    email: string
    role: string
    expires_at: string | null
  }
  existingUser: boolean
}>()

const form = useForm({
  name: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

function submit() {
  form.post(`/invites/${props.token}`, { preserveScroll: true })
}
</script>

<template>
  <GuestLayout>
    <Head title="Accept Invite" />

    <div class="mx-auto w-full max-w-md rounded-xl bg-white p-6 shadow">
      <h1 class="text-xl font-semibold text-gray-900">Join {{ invite.organization_name }}</h1>
      <p class="mt-1 text-sm text-gray-600">
        You’ve been invited as <span class="font-semibold text-gray-900">{{ invite.role }}</span>.
      </p>

      <div class="mt-4 rounded-lg border bg-gray-50 p-3 text-sm text-gray-700">
        <div class="flex items-center justify-between gap-3">
          <span class="text-gray-500">Email</span>
          <span class="font-medium truncate">{{ invite.email }}</span>
        </div>
        <div class="mt-1 flex items-center justify-between gap-3">
          <span class="text-gray-500">Account type</span>
          <span class="font-medium">{{ invite.organization_type }}</span>
        </div>
        <div v-if="invite.expires_at" class="mt-2 text-xs text-gray-500">
          Expires: {{ invite.expires_at }}
        </div>
      </div>

      <!-- If user already exists -->
      <div v-if="existingUser" class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-900">
        An account with this email already exists.
        <div class="mt-2 flex flex-wrap gap-2">
          <Link
            :href="route('login')"
            class="rounded-lg bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-700"
          >
            Login to accept invite
          </Link>
        </div>
      </div>

      <!-- New user signup via invite -->
      <form v-else class="mt-5 space-y-3" @submit.prevent="submit">
        <div>
          <label class="text-sm text-gray-700">Full name</label>
          <input v-model="form.name" class="mt-1 w-full rounded-lg border p-2" autofocus />
          <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
        </div>

        <div>
          <label class="text-sm text-gray-700">Phone (optional)</label>
          <input v-model="form.phone" class="mt-1 w-full rounded-lg border p-2" />
          <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</div>
        </div>

        <div>
          <label class="text-sm text-gray-700">Password</label>
          <input type="password" v-model="form.password" class="mt-1 w-full rounded-lg border p-2" />
          <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
        </div>

        <div>
          <label class="text-sm text-gray-700">Confirm password</label>
          <input type="password" v-model="form.password_confirmation" class="mt-1 w-full rounded-lg border p-2" />
        </div>

        <button
          class="mt-2 w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
          :disabled="form.processing"
        >
          Create account & join
        </button>
      </form>

      <p class="mt-4 text-xs text-gray-500">
        You’ll be able to edit your profile (photo, phone, password) after joining.
      </p>
    </div>
  </GuestLayout>
</template>
