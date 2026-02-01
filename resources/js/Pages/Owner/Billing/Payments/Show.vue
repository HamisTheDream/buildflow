<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import { formatDateTime, formatEnum, formatMoneyKobo } from '@/utils/format'
import Badge from '@/Components/Badge.vue'

const page = usePage<any>()
const admin = page.props.ownerAuth?.admin

const props = defineProps<{
  payment: any
  notes: any[]
}>()

function tone(s:string) {
  if (s === 'success') return 'green'
  if (s === 'failed') return 'red'
  if (s === 'pending') return 'amber'
  return 'gray'
}

const verifyForm = useForm({ reference: props.payment.reference, reason: '' })
const noteForm = useForm({ note: '' })
</script>

<template>
  <OwnerLayout>
    <Head :title="`Payment — ${payment.reference}`" />

    <div class="space-y-6">
      <SectionCard title="Payment" subtitle="Details and reconciliation tools.">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Reference</div>
            <div class="mt-2 text-sm font-bold text-gray-900">{{ payment.reference }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Status</div>
            <div class="mt-2">
              <Badge :text="formatEnum(payment.status)" :tone="tone(payment.status) as any" />
            </div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Amount</div>
            <div class="mt-2 text-lg font-bold text-gray-900">
              {{ formatMoneyKobo(payment.amount_kobo, payment.currency || 'NGN') }}
            </div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Org</div>
            <div class="mt-2 text-sm font-bold text-gray-900">{{ payment.organization?.name || '—' }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Created</div>
            <div class="mt-2 text-sm font-bold text-gray-900">{{ formatDateTime(payment.created_at) }}</div>
          </div>
        </div>

        <div class="mt-6 rounded-xl border p-4">
          <div class="text-sm font-semibold text-gray-900">Verify reference (super admin)</div>
          <div class="mt-1 text-xs text-gray-500">Use this if webhook was missed or payment is stuck.</div>

          <form class="mt-3 space-y-3" @submit.prevent="verifyForm.post('/owner/billing/payments/verify')">
            <div>
              <label class="text-xs font-semibold text-gray-700">Reference</label>
              <input v-model="verifyForm.reference" class="mt-1 w-full rounded-lg border p-2 text-sm" />
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-700">Reason (optional)</label>
              <input v-model="verifyForm.reason" class="mt-1 w-full rounded-lg border p-2 text-sm" />
            </div>

            <button
              class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-black disabled:opacity-50"
              :disabled="verifyForm.processing || !admin?.is_super"
            >
              Verify via Paystack
            </button>

            <div v-if="!admin?.is_super" class="mt-2 text-xs text-red-600">
              Super admin required.
            </div>
          </form>
        </div>
      </SectionCard>

      <SectionCard title="Notes" subtitle="Internal notes for ops.">
        <form class="space-y-3" @submit.prevent="noteForm.post(`/owner/billing/payments/${payment.id}/notes`, { preserveScroll:true, onSuccess: () => noteForm.note='' })">
          <textarea v-model="noteForm.note" class="w-full rounded-lg border p-2 text-sm" rows="3" placeholder="Add a note..."></textarea>
          <button class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700" :disabled="noteForm.processing">
            Add note
          </button>
        </form>

        <div class="mt-4 space-y-2">
          <div v-for="n in notes" :key="n.id" class="rounded-xl border p-4">
            <div class="text-sm text-gray-900">{{ n.note }}</div>
            <div class="mt-1 text-xs text-gray-500">{{ formatDateTime(n.created_at) }} • {{ n.admin?.name }} ({{ n.admin?.email }})</div>
          </div>
        </div>
      </SectionCard>

      <SectionCard title="Raw payload" subtitle="Stored gateway payload for debugging.">
        <pre class="whitespace-pre-wrap text-xs bg-gray-50 border rounded-xl p-4 overflow-x-auto">{{ JSON.stringify(payment.raw_payload, null, 2) }}</pre>
      </SectionCard>
    </div>
  </OwnerLayout>
</template>
