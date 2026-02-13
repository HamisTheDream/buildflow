<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps<{
  organization: any
  plans: any[]
  gate: any
}>()

const page = usePage()
const billingCycle = ref<'monthly' | 'annual'>('monthly')
const discountSettings = computed(() => (page.props as any).annualDiscount || { type: 'free_months', value: 2 })
const orgCurrency = computed(() => (page.props as any).orgCurrency || { code: 'NGN', symbol: '₦' })

const form = useForm({ plan_key: '', billing_cycle: 'monthly' })

const currentPrice = computed(() => {
  const found = props.plans.find(p => p.key === props.organization.plan)
  return found ? found.price_monthly_cents : 0
})

function getAnnualPrice(monthlyPriceCents: number): number {
  if (!monthlyPriceCents) return 0
  const yearly = monthlyPriceCents * 12
  const d = discountSettings.value
  if (d.type === 'percentage') return Math.round(yearly * (1 - d.value / 100))
  if (d.type === 'free_months') return monthlyPriceCents * (12 - d.value)
  return yearly
}

function getSavings(monthlyPriceCents: number): number {
  if (!monthlyPriceCents) return 0
  return (monthlyPriceCents * 12) - getAnnualPrice(monthlyPriceCents)
}

function displayPrice(priceCents: number): string {
  if (!priceCents) return 'Free'
  const s = orgCurrency.value.symbol
  if (billingCycle.value === 'annual') {
    const annual = getAnnualPrice(priceCents)
    const monthly = Math.round(annual / 12)
    return `${s}${(monthly / 100).toLocaleString()}/mo`
  }
  return `${s}${(priceCents / 100).toLocaleString()}/mo`
}

function savingsText(priceCents: number): string {
  const savings = getSavings(priceCents)
  if (savings <= 0) return ''
  const s = orgCurrency.value.symbol
  return `Save ${s}${(savings / 100).toLocaleString()}/yr`
}

function upgrade(key: string) {
  form.plan_key = key
  form.billing_cycle = billingCycle.value
  form.post(route('billing.upgrade'))
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Plan & Usage" />

    <div class="mx-auto max-w-5xl px-4 py-6 space-y-6 sm:px-6 sm:py-8">
      <!-- Usage Card -->
      <div class="rounded-xl bg-white p-5 shadow-card sm:p-6">
        <h1 class="text-xl font-bold text-gray-900">Plan & Usage</h1>
        <p class="mt-1 text-sm text-gray-600">Your organization plan limits and usage.</p>

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
          <div class="rounded-xl border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Projects</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ gate.usage.projects }}</div>
            <div class="mt-1 text-sm text-gray-500">of {{ gate.limits.max_projects }}</div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
              <div 
                class="h-full rounded-full transition-all"
                :class="gate.usage.projects >= gate.limits.max_projects ? 'bg-red-500' : 'bg-brand-500'"
                :style="{ width: Math.min(100, (gate.usage.projects / gate.limits.max_projects) * 100) + '%' }"
              ></div>
            </div>
          </div>
          <div class="rounded-xl border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Members</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ gate.usage.members }}</div>
            <div class="mt-1 text-sm text-gray-500">of {{ gate.limits.max_members }}</div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
              <div 
                class="h-full rounded-full transition-all"
                :class="gate.usage.members >= gate.limits.max_members ? 'bg-red-500' : 'bg-brand-500'"
                :style="{ width: Math.min(100, (gate.usage.members / gate.limits.max_members) * 100) + '%' }"
              ></div>
            </div>
          </div>
          <div class="rounded-xl border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Storage</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ gate.usage.storage_mb }} MB</div>
            <div class="mt-1 text-sm text-gray-500">of {{ gate.limits.max_storage_mb }} MB</div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
              <div 
                class="h-full rounded-full transition-all"
                :class="gate.usage.storage_mb >= gate.limits.max_storage_mb ? 'bg-red-500' : 'bg-brand-500'"
                :style="{ width: Math.min(100, (gate.usage.storage_mb / gate.limits.max_storage_mb) * 100) + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Plans Section -->
      <div class="rounded-xl bg-white p-5 shadow-card sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Available Plans</h2>
            <p class="mt-1 text-sm text-gray-600">Choose a plan that fits your needs.</p>
          </div>
          <!-- Billing Cycle Toggle -->
          <div class="flex items-center gap-3 rounded-full bg-gray-100 p-1">
            <button
              @click="billingCycle = 'monthly'"
              :class="[
                'rounded-full px-4 py-2 text-sm font-medium transition-all',
                billingCycle === 'monthly' 
                  ? 'bg-white text-gray-900 shadow-sm' 
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              Monthly
            </button>
            <button
              @click="billingCycle = 'annual'"
              :class="[
                'rounded-full px-4 py-2 text-sm font-medium transition-all',
                billingCycle === 'annual' 
                  ? 'bg-white text-gray-900 shadow-sm' 
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              Annual
              <span class="ml-1 inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold text-green-700">
                Save {{ discountSettings.type === 'percentage' ? discountSettings.value + '%' : discountSettings.value + ' mo' }}
              </span>
            </button>
          </div>
        </div>
        
        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
          <div 
            v-for="p in plans" 
            :key="p.key" 
            class="relative rounded-2xl border-2 p-5 transition-all"
            :class="organization.plan === p.key ? 'border-brand-500 bg-brand-50/30' : 'border-gray-200 hover:border-gray-300'"
          >
            <!-- Current Plan Badge -->
            <div 
              v-if="organization.plan === p.key" 
              class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-brand-500 px-3 py-0.5 text-xs font-bold text-white"
            >
              Current Plan
            </div>

            <div class="text-sm font-semibold text-gray-900">{{ p.name }}</div>
            
            <div class="mt-3">
              <span class="text-3xl font-bold text-gray-900">{{ displayPrice(p.price_monthly_cents) }}</span>
              <div v-if="billingCycle === 'annual' && p.price_monthly_cents > 0" class="mt-1 text-xs font-medium text-green-600">
                {{ savingsText(p.price_monthly_cents) }}
              </div>
            </div>

            <ul class="mt-4 space-y-2 text-sm text-gray-700">
              <li class="flex items-center gap-2">
                <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                {{ p.max_projects }} Projects
              </li>
              <li class="flex items-center gap-2">
                <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                {{ p.max_members }} Members
              </li>
              <li class="flex items-center gap-2">
                <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                {{ p.max_storage_mb }} MB Storage
              </li>
              <li class="flex items-center gap-2">
                <svg :class="['h-4 w-4', p.can_password_protect_reports ? 'text-green-500' : 'text-gray-300']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span :class="!p.can_password_protect_reports && 'text-gray-400'">Password-protected reports</span>
              </li>
            </ul>

            <button
              v-if="organization.plan !== p.key && p.price_monthly_cents > 0"
              @click="upgrade(p.key)"
              :disabled="form.processing"
              class="mt-5 block w-full rounded-xl bg-brand-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-brand-700 disabled:opacity-50 transition"
            >
              {{ form.processing && form.plan_key === p.key ? 'Processing...' : (p.price_monthly_cents < currentPrice ? 'Downgrade' : 'Upgrade') }}
            </button>
            <div v-else-if="organization.plan !== p.key && p.price_monthly_cents === 0" class="mt-5 text-center text-sm text-gray-500">
              Contact support to downgrade.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
