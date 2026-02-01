<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import { formatMoneyKobo } from '@/utils/format'

defineProps<{
  metrics: {
    orgs_total: number
    orgs_active: number
    orgs_trial: number
    orgs_expired: number
    payments_today: number
    revenue_today_kobo: number
  }
}>()
</script>

<template>
  <OwnerLayout>
    <Head title="Owner Dashboard" />

    <div class="space-y-6">
      <SectionCard title="Overview" subtitle="Platform health at a glance.">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Organizations</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ metrics.orgs_total }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Active subscriptions</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ metrics.orgs_active }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Trials</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ metrics.orgs_trial }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Expired / Past due</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ metrics.orgs_expired }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Payments today</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ metrics.payments_today }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Revenue today</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">
              {{ formatMoneyKobo(metrics.revenue_today_kobo, 'NGN') }}
            </div>
          </div>
        </div>
      </SectionCard>

      <SectionCard title="Next" subtitle="We’ll add CRM views next: search orgs, view subscriptions, extend trials, suspend orgs.">
        <div class="text-sm text-gray-700">
          Owner auth is ready. Proceed to Organizations CRM (O2).
        </div>
      </SectionCard>
    </div>
  </OwnerLayout>
</template>
