<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  organization: any
  plans: any[]
  gate: any
}>()

const form = useForm({
  plan_key: ''
})

import { computed } from 'vue'
const currentPrice = computed(() => {
  // If no plan matched (e.g. trial or weird state), assume 0 to show upgrades
  const found = props.plans.find(p => p.key === props.organization.plan)
  return found ? found.price_monthly_cents : 0
})

function upgrade(key: string) {
  form.plan_key = key
  form.post(route('billing.upgrade'))
}

function moneyCents(c:number) {
  if (!c) return 'Free'
  const n = c / 100
  return `₦${n.toLocaleString()}/mo`
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Plan & Usage" />

    <div class="mx-auto max-w-5xl space-y-6">
      <div class="rounded-xl bg-white p-6 shadow">
        <h1 class="text-xl font-bold text-gray-900">Plan & Usage</h1>
        <p class="mt-1 text-sm text-gray-600">Your organization plan limits and usage.</p>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Projects</div>
            <div class="mt-2 text-2xl font-bold">{{ gate.usage.projects }}</div>
            <div class="text-sm text-gray-600">Limit: {{ gate.limits.max_projects }}</div>
          </div>
          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Members</div>
            <div class="mt-2 text-2xl font-bold">{{ gate.usage.members }}</div>
            <div class="text-sm text-gray-600">Limit: {{ gate.limits.max_members }}</div>
          </div>
          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Storage</div>
            <div class="mt-2 text-2xl font-bold">{{ gate.usage.storage_mb }} MB</div>
            <div class="text-sm text-gray-600">Limit: {{ gate.limits.max_storage_mb }} MB</div>
          </div>
        </div>
      </div>

      <div class="rounded-xl bg-white p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-900">Available Plans</h2>
        <p class="mt-1 text-sm text-gray-600">Choose a plan to upgrade.</p>
        
        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
          <div v-for="p in plans" :key="p.key" class="rounded-xl border p-5">
            <div class="text-sm font-semibold text-gray-900">{{ p.name }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ moneyCents(p.price_monthly_cents) }}</div>

            <ul class="mt-3 space-y-1 text-sm text-gray-700">
              <li>Projects: {{ p.max_projects }}</li>
              <li>Members: {{ p.max_members }}</li>
              <li>Storage: {{ p.max_storage_mb }} MB</li>
              <li>Password share links: <strong>{{ p.can_password_protect_reports ? 'Yes' : 'No' }}</strong></li>
            </ul>

    <div v-if="organization.plan === p.key" class="mt-4 rounded-lg bg-indigo-50 p-3 text-sm text-indigo-800">
              Current plan
            </div>
            <button
              v-else-if="p.price_monthly_cents > 0"
              @click="upgrade(p.key)"
              :disabled="form.processing"
              class="mt-4 block w-full rounded-lg bg-indigo-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
            >
              {{ form.processing && form.plan_key === p.key ? 'Processing...' : (p.price_monthly_cents < currentPrice ? 'Downgrade' : 'Upgrade') }}
            </button>
            <div v-else class="mt-4 text-center text-sm text-gray-500">
              Contact support to downgrade to Free.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
