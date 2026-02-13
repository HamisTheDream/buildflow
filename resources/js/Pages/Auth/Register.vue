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
    <Head :title="($page.props.site_settings?.site_title || 'Create Account') + ' — BuildFlow'" />

    <div>
      <h2 class="text-2xl font-bold tracking-tight text-gray-900">Create your account</h2>
      <p class="mt-2 text-sm text-gray-600">
        Already have an account?
        <Link :href="route('login')" class="font-semibold text-brand-600 hover:text-brand-500">
          Sign in
        </Link>
      </p>
    </div>

    <form @submit.prevent="submit" class="mt-8 space-y-5">
      <!-- Account Type Selection -->
      <div>
        <InputLabel value="I am a..." />
        <div class="mt-2 grid grid-cols-2 gap-3">
          <button
            type="button"
            class="flex flex-col items-center rounded-xl border-2 p-4 text-center transition-all hover:border-brand-300"
            :class="form.account_type === 'individual' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-500/20' : 'border-gray-200'"
            @click="form.account_type = 'individual'"
          >
            <div class="flex h-10 w-10 items-center justify-center rounded-full" :class="form.account_type === 'individual' ? 'bg-brand-100' : 'bg-gray-100'">
              <svg class="h-5 w-5" :class="form.account_type === 'individual' ? 'text-brand-600' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div class="mt-2 text-sm font-semibold" :class="form.account_type === 'individual' ? 'text-brand-700' : 'text-gray-900'">Individual</div>
            <div class="text-xs text-gray-500">Solo contractor</div>
          </button>

          <button
            type="button"
            class="flex flex-col items-center rounded-xl border-2 p-4 text-center transition-all hover:border-brand-300"
            :class="form.account_type === 'company' ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-500/20' : 'border-gray-200'"
            @click="form.account_type = 'company'"
          >
            <div class="flex h-10 w-10 items-center justify-center rounded-full" :class="form.account_type === 'company' ? 'bg-brand-100' : 'bg-gray-100'">
              <svg class="h-5 w-5" :class="form.account_type === 'company' ? 'text-brand-600' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <div class="mt-2 text-sm font-semibold" :class="form.account_type === 'company' ? 'text-brand-700' : 'text-gray-900'">Company</div>
            <div class="text-xs text-gray-500">Team & roles</div>
          </button>
        </div>
        <InputError class="mt-2" :message="form.errors.account_type" />
      </div>

      <!-- Company Name (conditional) -->
      <div v-if="needsOrgName" class="animate-fade-in">
        <InputLabel for="organization_name" value="Company name" />
        <TextInput
          id="organization_name"
          type="text"
          class="mt-2 block w-full"
          v-model="form.organization_name"
          placeholder="Acme Construction Ltd"
        />
        <InputError class="mt-2" :message="form.errors.organization_name" />
      </div>

      <!-- Name -->
      <div>
        <InputLabel for="name" value="Full name" />
        <TextInput 
          id="name" 
          type="text" 
          class="mt-2 block w-full" 
          v-model="form.name" 
          required 
          autofocus 
          placeholder="John Doe"
        />
        <InputError class="mt-2" :message="form.errors.name" />
      </div>

      <!-- Email -->
      <div>
        <InputLabel for="email" value="Work email" />
        <TextInput 
          id="email" 
          type="email" 
          class="mt-2 block w-full" 
          v-model="form.email" 
          required 
          placeholder="john@company.com"
        />
        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <!-- Phone -->
      <div>
        <InputLabel for="phone" value="Phone number (optional)" />
        <TextInput 
          id="phone" 
          type="tel" 
          class="mt-2 block w-full" 
          v-model="form.phone" 
          placeholder="+234 800 000 0000"
        />
        <InputError class="mt-2" :message="form.errors.phone" />
      </div>

      <!-- Password -->
      <div>
        <InputLabel for="password" value="Password" />
        <TextInput 
          id="password" 
          type="password" 
          class="mt-2 block w-full" 
          v-model="form.password" 
          required 
          placeholder="Min. 8 characters"
        />
        <InputError class="mt-2" :message="form.errors.password" />
      </div>

      <!-- Confirm Password -->
      <div>
        <InputLabel for="password_confirmation" value="Confirm password" />
        <TextInput
          id="password_confirmation"
          type="password"
          class="mt-2 block w-full"
          v-model="form.password_confirmation"
          required
          placeholder="••••••••"
        />
        <InputError class="mt-2" :message="form.errors.password_confirmation" />
      </div>

      <PrimaryButton 
        type="submit"
        class="w-full justify-center" 
        :class="{ 'opacity-50': form.processing }" 
        :disabled="form.processing"
      >
        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span v-else>Create account</span>
      </PrimaryButton>

      <p class="text-center text-xs text-gray-500">
        By signing up, you agree to our 
        <a href="#" class="underline hover:text-gray-700">Terms of Service</a> and 
        <a href="#" class="underline hover:text-gray-700">Privacy Policy</a>.
      </p>
    </form>
  </GuestLayout>
</template>
