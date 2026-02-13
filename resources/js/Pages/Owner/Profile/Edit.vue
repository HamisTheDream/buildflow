<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import { ref, computed } from 'vue'

const props = defineProps<{
  admin: {
    id: number
    name: string
    email: string
    avatar_path: string | null
    avatar_url: string | null
    is_super: boolean
    last_login_at: string | null
  }
}>()

const profileForm = useForm({
  name: props.admin.name,
  email: props.admin.email,
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const avatarForm = useForm({
  avatar: null as File | null,
})

const showPasswordForm = ref(false)
const avatarPreview = ref<string | null>(props.admin.avatar_url)
const avatarInput = ref<HTMLInputElement | null>(null)

const userInitial = computed(() => props.admin.name.charAt(0).toUpperCase())

const handleAvatarChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    avatarForm.avatar = file
    avatarPreview.value = URL.createObjectURL(file)
  }
}

const uploadAvatar = () => {
  if (!avatarForm.avatar) return
  
  avatarForm.post('/owner/profile/avatar', {
    preserveScroll: true,
    onSuccess: () => {
      avatarForm.reset()
    },
  })
}

const deleteAvatar = () => {
  if (confirm('Are you sure you want to remove your profile picture?')) {
    router.delete('/owner/profile/avatar', {
      preserveScroll: true,
      onSuccess: () => {
        avatarPreview.value = null
      },
    })
  }
}

const submitProfile = () => {
  profileForm.patch('/owner/profile', {
    preserveScroll: true,
  })
}

const submitPassword = () => {
  passwordForm.patch('/owner/profile/password', {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset()
      showPasswordForm.value = false
    },
  })
}
</script>

<template>
  <OwnerLayout>
    <Head title="Edit Profile" />

    <div class="space-y-6">
      <!-- Page Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
        <p class="mt-1 text-sm text-gray-500">Manage your account information and password</p>
      </div>

      <!-- Avatar Section -->
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Picture</h2>
        
        <div class="flex items-center gap-6">
          <!-- Avatar Display -->
          <div class="relative">
            <div class="h-24 w-24 rounded-full overflow-hidden ring-4 ring-brand-100 shadow-lg">
              <img 
                v-if="avatarPreview" 
                :src="avatarPreview" 
                alt="Profile picture"
                class="h-full w-full object-cover"
              />
              <div 
                v-else 
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-400 to-brand-600 text-3xl font-bold text-white"
              >
                {{ userInitial }}
              </div>
            </div>
            <span 
              v-if="admin.is_super" 
              class="absolute -bottom-1 left-1/2 -translate-x-1/2 inline-flex items-center rounded-full bg-brand-500 px-2 py-0.5 text-[10px] font-semibold text-white shadow"
            >
              Super
            </span>
          </div>

          <!-- Upload Controls -->
          <div class="flex-1 space-y-3">
            <div class="text-sm text-gray-600">
              Upload a new profile picture. Max size: 2MB.
            </div>
            
            <div class="flex items-center gap-3">
              <input
                ref="avatarInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleAvatarChange"
              />
              
              <button
                type="button"
                @click="avatarInput?.click()"
                class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition"
              >
                Choose File
              </button>

              <button
                v-if="avatarForm.avatar"
                type="button"
                @click="uploadAvatar"
                :disabled="avatarForm.processing"
                class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 disabled:opacity-50 transition"
              >
                {{ avatarForm.processing ? 'Uploading...' : 'Upload' }}
              </button>

              <button
                v-if="admin.avatar_path"
                type="button"
                @click="deleteAvatar"
                class="rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-100 transition"
              >
                Remove
              </button>
            </div>

            <div v-if="avatarForm.errors.avatar" class="text-sm text-red-600">
              {{ avatarForm.errors.avatar }}
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Form -->
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h2>

        <form @submit.prevent="submitProfile" class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
            <input
              id="name"
              v-model="profileForm.name"
              type="text"
              class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500"
            />
            <div v-if="profileForm.errors.name" class="mt-1 text-sm text-red-600">{{ profileForm.errors.name }}</div>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input
              id="email"
              v-model="profileForm.email"
              type="email"
              class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500"
            />
            <div v-if="profileForm.errors.email" class="mt-1 text-sm text-red-600">{{ profileForm.errors.email }}</div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button
              type="submit"
              :disabled="profileForm.processing"
              class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 disabled:opacity-50 transition"
            >
              {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Password Section -->
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Password</h2>
            <p class="text-sm text-gray-500">Update your password to keep your account secure</p>
          </div>
          <button
            v-if="!showPasswordForm"
            @click="showPasswordForm = true"
            class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition"
          >
            Change Password
          </button>
        </div>

        <form v-if="showPasswordForm" @submit.prevent="submitPassword" class="mt-6 space-y-4">
          <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
            <input
              id="current_password"
              v-model="passwordForm.current_password"
              type="password"
              class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500"
            />
            <div v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.current_password }}</div>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
            <input
              id="password"
              v-model="passwordForm.password"
              type="password"
              class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500"
            />
            <div v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.password }}</div>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
            <input
              id="password_confirmation"
              v-model="passwordForm.password_confirmation"
              type="password"
              class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500"
            />
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button
              type="button"
              @click="showPasswordForm = false; passwordForm.reset()"
              class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="passwordForm.processing"
              class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 disabled:opacity-50 transition"
            >
              {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Last Login Info -->
      <div v-if="admin.last_login_at" class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">
        Last login: {{ admin.last_login_at }}
      </div>
    </div>
  </OwnerLayout>
</template>
