<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps<{
  user: {
    id: number
    name: string
    email: string
    phone: string | null
    avatar_url: string | null
  }
}>()

const page = usePage()
const flash = computed(() => (page.props.flash as any) ?? {})

const avatarPreview = ref<string | null>(props.user.avatar_url)

const profileForm = useForm({
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone ?? '',
  current_password: '',
  avatar: null as File | null,
})

function onAvatarChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  profileForm.avatar = file
  avatarPreview.value = URL.createObjectURL(file)
}

function saveProfile() {
  profileForm.transform((data) => ({
    ...data,
    _method: 'PATCH',
  })).post('/app/settings/profile', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      profileForm.current_password = ''
    },
  })
}

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

function savePassword() {
  passwordForm.patch('/app/settings/security/password', {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset()
    },
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Profile Settings" />

    <div class="p-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Settings</h1>
          <p class="mt-1 text-sm text-gray-600">Manage your profile and security.</p>
        </div>
      </div>

      <div v-if="flash.success" class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">
        {{ flash.success }}
      </div>
      <div v-if="flash.error" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">
        {{ flash.error }}
      </div>

      <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Profile -->
        <div class="rounded-xl bg-white p-6 shadow">
          <h2 class="text-lg font-semibold text-gray-900">Profile</h2>
          <p class="mt-1 text-sm text-gray-600">Update your identity and contact details.</p>

          <form class="mt-5 space-y-4" @submit.prevent="saveProfile">
            <div class="flex items-center gap-4">
              <div class="h-14 w-14 overflow-hidden rounded-full bg-gray-100">
                <img v-if="avatarPreview" :src="avatarPreview" class="h-full w-full object-cover" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Profile photo</label>
                <input type="file" accept="image/*" class="mt-1 block text-sm" @change="onAvatarChange" />
                <div v-if="profileForm.errors.avatar" class="mt-1 text-sm text-red-600">{{ profileForm.errors.avatar }}</div>
              </div>
            </div>

            <div>
              <label class="text-sm text-gray-700">Full name</label>
              <input v-model="profileForm.name" class="mt-1 w-full rounded-lg border p-2" />
              <div v-if="profileForm.errors.name" class="mt-1 text-sm text-red-600">{{ profileForm.errors.name }}</div>
            </div>

            <div>
              <label class="text-sm text-gray-700">Phone</label>
              <input v-model="profileForm.phone" class="mt-1 w-full rounded-lg border p-2" placeholder="+234..." />
              <div v-if="profileForm.errors.phone" class="mt-1 text-sm text-red-600">{{ profileForm.errors.phone }}</div>
            </div>

            <div>
              <label class="text-sm text-gray-700">Email</label>
              <input v-model="profileForm.email" class="mt-1 w-full rounded-lg border p-2" />
              <div v-if="profileForm.errors.email" class="mt-1 text-sm text-red-600">{{ profileForm.errors.email }}</div>
              <p class="mt-2 text-xs text-gray-500">
                Changing your email requires your current password.
              </p>
            </div>

            <div>
              <label class="text-sm text-gray-700">Current password (only for email change)</label>
              <input type="password" v-model="profileForm.current_password" class="mt-1 w-full rounded-lg border p-2" />
              <div v-if="profileForm.errors.current_password" class="mt-1 text-sm text-red-600">
                {{ profileForm.errors.current_password }}
              </div>
            </div>

            <button
              class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              :disabled="profileForm.processing"
            >
              Save Profile
            </button>
          </form>
        </div>

        <!-- Password -->
        <div class="rounded-xl bg-white p-6 shadow">
          <h2 class="text-lg font-semibold text-gray-900">Security</h2>
          <p class="mt-1 text-sm text-gray-600">Change your password.</p>

          <form class="mt-5 space-y-4" @submit.prevent="savePassword">
            <div>
              <label class="text-sm text-gray-700">Current password</label>
              <input type="password" v-model="passwordForm.current_password" class="mt-1 w-full rounded-lg border p-2" />
              <div v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-600">
                {{ passwordForm.errors.current_password }}
              </div>
            </div>

            <div>
              <label class="text-sm text-gray-700">New password</label>
              <input type="password" v-model="passwordForm.password" class="mt-1 w-full rounded-lg border p-2" />
              <div v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.password }}</div>
            </div>

            <div>
              <label class="text-sm text-gray-700">Confirm new password</label>
              <input type="password" v-model="passwordForm.password_confirmation" class="mt-1 w-full rounded-lg border p-2" />
            </div>

            <button
              class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              :disabled="passwordForm.processing"
            >
              Update Password
            </button>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
