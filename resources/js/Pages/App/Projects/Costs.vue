<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import SectionTitle from '@/Components/SectionTitle.vue'
import StatCard from '@/Components/StatCard.vue'
import EmptyState from '@/Components/EmptyState.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'

import { formatDate, formatMoneyKobo, formatEnum, truncate } from '@/utils/format'

const page = usePage<any>()
const project = computed(() => page.props.project ?? page.props.data?.project ?? null)

// Accept either: costs paginator, rows paginator, or plain array
const paginator = computed(() => page.props.costs ?? page.props.rows ?? null)

const costs = computed<any[]>(() => {
  const p = paginator.value
  if (!p) return page.props.items ?? []
  if (Array.isArray(p)) return p
  if (Array.isArray(p.data)) return p.data
  return []
})

const links = computed(() => {
  const p = paginator.value
  return p && !Array.isArray(p) ? p.links : null
})

// Totals can come from backend, otherwise compute client-side
const totals = computed(() => {
  const t = page.props.totals ?? null
  if (t) return t

  const list = costs.value || []
  const total = list.reduce((s, c) => s + Number(c.amount_kobo ?? c.amount_cents ?? 0), 0)
  const paid = list.filter(c => !!c.is_paid).reduce((s, c) => s + Number(c.amount_kobo ?? c.amount_cents ?? 0), 0)
  const unpaid = total - paid
  return { total_kobo: total, paid_kobo: paid, unpaid_kobo: unpaid }
})

function paidTone(isPaid: boolean) {
  return isPaid ? 'green' : 'amber'
}

function amountField(c:any) {
  return c.amount_kobo ?? c.amount_cents ?? 0
}

function currencyField(c:any) {
  return c.currency ?? 'NGN'
}
</script>

<template>
  <AppLayout>
    <Head :title="project?.name ? `Costs — ${project.name}` : 'Costs'" />

    <div class="space-y-6">
      <div class="flex items-center justify-between gap-3">
        <div>
          <div class="text-sm font-semibold text-gray-900">Project costs</div>
          <div class="mt-1 text-sm text-gray-600" v-if="project?.name">
            {{ project.name }}
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Link
            v-if="project?.id"
            :href="`/app/projects/${project.id}`"
            class="rounded-lg border bg-white px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-50"
          >
            Back
          </Link>

          <!-- Safe default: you can keep your existing create flow; this won't break anything -->
          <Link
            v-if="project?.id"
            :href="`/app/projects/${project.id}/costs?new=1`"
            class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-black"
          >
            New cost
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <StatCard label="Total" :value="formatMoneyKobo(totals.total_kobo || 0, 'NGN')" />
        <StatCard label="Paid" :value="formatMoneyKobo(totals.paid_kobo || 0, 'NGN')" />
        <StatCard label="Unpaid" :value="formatMoneyKobo(totals.unpaid_kobo || 0, 'NGN')" />
      </div>

      <SectionCard>
        <SectionTitle
          title="Costs"
          subtitle="Track expenses cleanly. Attach receipts for evidence."
        />

        <div class="mt-4" v-if="!costs || costs.length === 0">
          <EmptyState
            title="No costs yet"
            description="Add your first cost entry to start tracking project expenses."
            actionLabel="New cost"
            :actionHref="project?.id ? `/app/projects/${project.id}/costs?new=1` : undefined"
          />
        </div>

        <div v-else class="mt-4 overflow-x-auto">
          <table class="min-w-full overflow-hidden rounded-xl border">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Item</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Category</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Amount</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Date</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y bg-white">
              <tr v-for="c in costs" :key="c.id" class="hover:bg-gray-50">
                <td class="px-4 py-3">
                  <div class="text-sm font-semibold text-gray-900">
                    {{ truncate(c.title || c.item || 'Cost', 60) }}
                  </div>
                  <div v-if="c.vendor" class="mt-0.5 text-xs text-gray-500">
                    {{ truncate(c.vendor, 60) }}
                  </div>
                  <div v-if="c.attachments_count" class="mt-1 text-xs text-gray-500">
                    {{ c.attachments_count }} attachment{{ c.attachments_count === 1 ? '' : 's' }}
                  </div>
                </td>

                <td class="px-4 py-3 text-sm text-gray-700">
                  {{ formatEnum(c.category || 'other') }}
                </td>

                <td class="px-4 py-3 text-sm">
                  <Badge
                    :text="c.is_paid ? 'Paid' : 'Unpaid'"
                    :tone="paidTone(!!c.is_paid) as any"
                  />
                </td>

                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                  {{ formatMoneyKobo(amountField(c), currencyField(c)) }}
                </td>

                <td class="px-4 py-3 text-sm text-gray-700">
                  {{ formatDate(c.incurred_on || c.date || c.created_at) }}
                </td>

                <td class="px-4 py-3 text-right text-sm">
                  <div class="flex justify-end gap-3">
                    <Link
                      v-if="project?.id && c.id"
                      :href="`/app/projects/${project.id}/costs/${c.id}`"
                      class="text-indigo-600 hover:underline"
                    >
                      View
                    </Link>
                    <Link
                      v-if="project?.id && c.id"
                      :href="`/app/projects/${project.id}/costs/${c.id}/edit`"
                      class="text-gray-700 hover:underline"
                    >
                      Edit
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-5" v-if="links">
          <Pagination :links="links" />
        </div>
      </SectionCard>
    </div>
  </AppLayout>
</template>
