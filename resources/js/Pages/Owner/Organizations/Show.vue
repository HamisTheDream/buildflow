<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import Pagination from '@/Components/Pagination.vue'
import Badge from '@/Components/Badge.vue'
import { formatDateTime, formatEnum, formatMoneyKobo } from '@/utils/format'

const page = usePage<any>()
const admin = page.props.ownerAuth?.admin

const props = defineProps<{
  organization: any
  stats: any
  plans: { id:number; key:string; name:string }[]
  payments: { data:any[]; links:any[] }
  audit: { data:any[]; links:any[] }
}>()

function statusTone(s:string) {
  if (s === 'active') return 'green'
  if (s === 'trial') return 'blue'
  if (s === 'past_due') return 'amber'
  if (s === 'suspended') return 'red'
  return 'gray'
}

const extendTrialForm = useForm({ days: 7, reason: '' })
const compForm = useForm({ plan_key: 'starter', days: 30, reason: '' })
const downgradeForm = useForm({ plan_key: 'free', reason: '' })
const suspendForm = useForm({ reason: '' })
const reactivateForm = useForm({ mode: 'trial', days: 7, reason: '' })
</script>

<template>
  <OwnerLayout>
    <Head :title="`Organization — ${organization.name}`" />

    <div class="space-y-6">
      <SectionCard :title="organization.name" subtitle="Organization profile and subscription controls.">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Plan</div>
            <div class="mt-2 text-lg font-bold text-gray-900">{{ organization.plan?.name || '—' }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Status</div>
            <div class="mt-2">
              <Badge :text="formatEnum(organization.subscription_status)" :tone="statusTone(organization.subscription_status) as any" />
            </div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Created</div>
            <div class="mt-2 text-lg font-bold text-gray-900">{{ formatDateTime(organization.created_at) }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Trial ends</div>
            <div class="mt-2 text-lg font-bold text-gray-900">{{ formatDateTime(organization.trial_ends_at) }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Paid until</div>
            <div class="mt-2 text-lg font-bold text-gray-900">{{ formatDateTime(organization.paid_until) }}</div>
          </div>

          <div class="rounded-lg border p-4">
            <div class="text-xs text-gray-500">Members / Projects</div>
            <div class="mt-2 text-lg font-bold text-gray-900">{{ stats.members }} / {{ stats.projects }}</div>
          </div>
        </div>
      </SectionCard>

      <SectionCard title="Actions" subtitle="All actions are audited. Some require super admin.">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div class="rounded-xl border p-4">
            <div class="text-sm font-semibold text-gray-900">Extend trial</div>
            <div class="mt-1 text-xs text-gray-500">Set trial status and extend trial end date.</div>

            <form class="mt-3 space-y-3" @submit.prevent="extendTrialForm.post(`/owner/organizations/${organization.id}/extend-trial`)">
              <div>
                <label class="text-xs font-semibold text-gray-700">Days</label>
                <input v-model="extendTrialForm.days" type="number" class="mt-1 w-full rounded-lg border p-2 text-sm" />
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-700">Reason (optional)</label>
                <input v-model="extendTrialForm.reason" class="mt-1 w-full rounded-lg border p-2 text-sm" />
              </div>
              <button class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700" :disabled="extendTrialForm.processing">
                Extend
              </button>
            </form>
          </div>

          <div class="rounded-xl border p-4">
            <div class="text-sm font-semibold text-gray-900">Reactivate</div>
            <div class="mt-1 text-xs text-gray-500">Bring a suspended/past due org back.</div>

            <form class="mt-3 space-y-3" @submit.prevent="reactivateForm.post(`/owner/organizations/${organization.id}/reactivate`)">
              <div>
                <label class="text-xs font-semibold text-gray-700">Mode</label>
                <select v-model="reactivateForm.mode" class="mt-1 w-full rounded-lg border p-2 text-sm">
                  <option value="trial">Trial</option>
                  <option value="active">Active</option>
                  <option value="free">Free</option>
                </select>
              </div>

              <div v-if="reactivateForm.mode !== 'free'">
                <label class="text-xs font-semibold text-gray-700">Days</label>
                <input v-model="reactivateForm.days" type="number" class="mt-1 w-full rounded-lg border p-2 text-sm" />
              </div>

              <div>
                <label class="text-xs font-semibold text-gray-700">Reason (optional)</label>
                <input v-model="reactivateForm.reason" class="mt-1 w-full rounded-lg border p-2 text-sm" />
              </div>

              <button class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700" :disabled="reactivateForm.processing">
                Reactivate
              </button>

              <div v-if="!admin?.is_super" class="mt-2 text-xs text-gray-500">
                Some reactivation modes may require super admin depending on your settings.
              </div>
            </form>
          </div>

          <div class="rounded-xl border p-4">
            <div class="text-sm font-semibold text-gray-900">Comp plan (super admin)</div>
            <div class="mt-1 text-xs text-gray-500">Give an org paid access without payment.</div>

            <form class="mt-3 space-y-3" @submit.prevent="compForm.post(`/owner/organizations/${organization.id}/comp-plan`)">
              <div>
                <label class="text-xs font-semibold text-gray-700">Plan</label>
                <select v-model="compForm.plan_key" class="mt-1 w-full rounded-lg border p-2 text-sm">
                  <option v-for="p in plans" :key="p.key" :value="p.key">{{ p.name }}</option>
                </select>
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-700">Days</label>
                <input v-model="compForm.days" type="number" class="mt-1 w-full rounded-lg border p-2 text-sm" />
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-700">Reason (optional)</label>
                <input v-model="compForm.reason" class="mt-1 w-full rounded-lg border p-2 text-sm" />
              </div>
              <button class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-black disabled:opacity-50"
                      :disabled="compForm.processing || !admin?.is_super">
                Comp
              </button>
              <div v-if="!admin?.is_super" class="mt-2 text-xs text-red-600">
                Super admin required.
              </div>
            </form>
          </div>

          <div class="rounded-xl border p-4">
            <div class="text-sm font-semibold text-gray-900">Suspend / Downgrade (super admin)</div>
            <div class="mt-1 text-xs text-gray-500">Strong actions for abuse/non-payment.</div>

            <form class="mt-3 space-y-3" @submit.prevent="suspendForm.post(`/owner/organizations/${organization.id}/suspend`)">
              <div>
                <label class="text-xs font-semibold text-gray-700">Suspend reason</label>
                <input v-model="suspendForm.reason" class="mt-1 w-full rounded-lg border p-2 text-sm" placeholder="e.g. Chargeback / abuse" />
              </div>
              <button class="rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                      :disabled="suspendForm.processing || !admin?.is_super">
                Suspend
              </button>
            </form>

            <form class="mt-6 space-y-3" @submit.prevent="downgradeForm.post(`/owner/organizations/${organization.id}/downgrade`)">
              <div>
                <label class="text-xs font-semibold text-gray-700">Downgrade to plan</label>
                <select v-model="downgradeForm.plan_key" class="mt-1 w-full rounded-lg border p-2 text-sm">
                  <option v-for="p in plans" :key="p.key" :value="p.key">{{ p.name }}</option>
                </select>
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-700">Reason (optional)</label>
                <input v-model="downgradeForm.reason" class="mt-1 w-full rounded-lg border p-2 text-sm" />
              </div>
              <button class="rounded-lg bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-700 disabled:opacity-50"
                      :disabled="downgradeForm.processing || !admin?.is_super">
                Downgrade
              </button>
            </form>

            <div v-if="!admin?.is_super" class="mt-2 text-xs text-red-600">
              Super admin required for suspend/downgrade.
            </div>
          </div>
        </div>
      </SectionCard>

      <SectionCard title="Payments" subtitle="Recent payments for this organization.">
        <div class="overflow-x-auto">
          <table class="min-w-full border rounded-lg overflow-hidden">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Ref</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Amount</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">At</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="p in payments.data" :key="p.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ p.reference }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ formatEnum(p.status) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ formatMoneyKobo(p.amount_kobo, p.currency || 'NGN') }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ formatDateTime(p.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <Pagination :links="payments.links" />
      </SectionCard>

      <SectionCard title="Audit trail" subtitle="Owner actions taken on this organization.">
        <div class="space-y-2">
          <div v-for="l in audit.data" :key="l.id" class="rounded-xl border p-4">
            <div class="text-sm font-semibold text-gray-900">{{ formatEnum(l.action) }}</div>
            <div class="mt-1 text-xs text-gray-500">
              {{ formatDateTime(l.created_at) }} • {{ l.admin?.name }} ({{ l.admin?.email }})
            </div>
            <div v-if="l.reason" class="mt-2 text-sm text-gray-700">{{ l.reason }}</div>
          </div>
        </div>
        <Pagination :links="audit.links" />
      </SectionCard>
    </div>
  </OwnerLayout>
</template>
