<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  account_type: 'individual' as 'individual' | 'company',
  organization_name: '',
})

const needsOrgName = computed(() => form.account_type === 'company')

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <GuestLayout>
    <Head title="Register" />

    <div class="mx-auto w-full max-w-md rounded-xl bg-white p-6 shadow">
      <h1 class="text-xl font-semibold text-gray-900">Create your BuildFlow account</h1>
      <p class="mt-1 text-sm text-gray-600">Choose the account type that matches your work.</p>

      <form @submit.prevent="submit" class="mt-6 space-y-4">
        <!-- Account Type -->
        <div>
          <InputLabel value="Account type" />
          <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
            <button
              type="button"
              class="rounded-lg border p-3 text-left hover:bg-gray-50"
              :class="form.account_type === 'individual' ? 'border-indigo-600 ring-2 ring-indigo-100' : 'border-gray-200'"
              @click="form.account_type = 'individual'"
            >
              <div class="text-sm font-semibold text-gray-900">Individual</div>
              <div class="text-xs text-gray-600">Solo developer/contractor</div>
            </button>

            <button
              type="button"
              class="rounded-lg border p-3 text-left hover:bg-gray-50"
              :class="form.account_type === 'company' ? 'border-indigo-600 ring-2 ring-indigo-100' : 'border-gray-200'"
              @click="form.account_type = 'company'"
            >
              <div class="text-sm font-semibold text-gray-900">Company</div>
              <div class="text-xs text-gray-600">Team, employees, roles</div>
            </button>
          </div>

          <InputError class="mt-2" :message="form.errors.account_type" />
        </div>

        <!-- Org Name (Company only) -->
        <div v-if="needsOrgName">
          <InputLabel for="organization_name" value="Company / Organization name" />
          <TextInput
            id="organization_name"
            type="text"
            class="mt-1 block w-full"
            v-model="form.organization_name"
            placeholder="e.g. Kogi Builders Ltd"
          />
          <InputError class="mt-2" :message="form.errors.organization_name" />
        </div>

        <!-- Name -->
        <div>
          <InputLabel for="name" value="Full name" />
          <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
          <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <!-- Email -->
        <div>
          <InputLabel for="email" value="Email" />
          <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required />
          <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <!-- Phone -->
        <div>
          <InputLabel for="phone" value="Phone (optional)" />
          <TextInput id="phone" type="text" class="mt-1 block w-full" v-model="form.phone" />
          <InputError class="mt-2" :message="form.errors.phone" />
        </div>

        <!-- Password -->
        <div>
          <InputLabel for="password" value="Password" />
          <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required />
          <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <!-- Password Confirm -->
        <div>
          <InputLabel for="password_confirmation" value="Confirm password" />
          <TextInput
            id="password_confirmation"
            type="password"
            class="mt-1 block w-full"
            v-model="form.password_confirmation"
            required
          />
          <InputError class="mt-2" :message="form.errors.password_confirmation" />
        </div>

        <PrimaryButton class="w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Create account
        </PrimaryButton>

        <div class="text-center text-sm text-gray-600">
          Already registered?
          <Link :href="route('login')" class="text-indigo-600 hover:underline">Log in</Link>
        </div>
      </form>
    </div>
  </GuestLayout>
</template>
