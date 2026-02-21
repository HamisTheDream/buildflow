<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{
  organization: { id: number; name: string; type: 'individual' | 'company' }
  myRole: string
  members: { id: number; name: string; email: string; phone: string | null; role: string }[]
  invites: { id: number; email: string; role: string; expires_at: string | null; accepted_at: string | null; invite_url: string }[]
}>()

const page = usePage()
const flash = computed(() => (page.props.flash as any) ?? {})

const canInvite = computed(() => ['owner', 'admin'].includes(props.myRole))

const form = useForm({
  email: '',
  role: 'member',
  expires_days: 7,
})

function submitInvite() {
  form.post('/app/organization/invites', { preserveScroll: true })
}

function copy(text: string) {
  navigator.clipboard.writeText(text)
}

function revoke(inviteId: number) {
  const f = useForm({})
  f.delete(`/app/organization/invites/${inviteId}`, { preserveScroll: true })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Organization Members" />

    <div class="p-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Members</h1>
          <p class="mt-1 text-sm text-gray-600">
            Manage users in <span class="font-semibold">{{ organization.name }}</span>.
          </p>
        </div>

        <div class="text-sm text-gray-600">
          Your role: <span class="font-semibold text-gray-900">{{ myRole }}</span>
        </div>
      </div>

      <div v-if="flash.success" class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">
        {{ flash.success }}
      </div>
      <div v-if="flash.error" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">
        {{ flash.error }}
      </div>

      <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Invite card -->
        <div class="rounded-xl bg-white p-6 shadow">
          <h2 class="text-lg font-semibold text-gray-900">Invite a teammate</h2>
          <p class="mt-1 text-sm text-gray-600">They will join this organization via a secure link.</p>

          <div v-if="!canInvite" class="mt-4 rounded-lg border border-dashed p-4 text-sm text-gray-600">
            Only owners/admins can invite members.
          </div>

          <form v-else class="mt-4 space-y-3" @submit.prevent="submitInvite">
            <div>
              <label class="text-sm text-gray-700">Email</label>
              <input v-model="form.email" class="mt-1 w-full rounded-lg border p-2" placeholder="person@company.com" />
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div>
                <label class="text-sm text-gray-700">Role</label>
                <select v-model="form.role" class="mt-1 w-full rounded-lg border p-2">
                  <option value="viewer">Viewer (read-only)</option>
                  <option value="member">Member</option>
                  <option value="admin">Admin</option>
                </select>
              </div>

              <div>
                <label class="text-sm text-gray-700">Expires</label>
                <select v-model="form.expires_days" class="mt-1 w-full rounded-lg border p-2">
                  <option :value="3">3 days</option>
                  <option :value="7">7 days</option>
                  <option :value="14">14 days</option>
                  <option :value="30">30 days</option>
                </select>
              </div>
            </div>

            <button
              class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              :disabled="form.processing"
            >
              Create invite link
            </button>
          </form>
        </div>

        <!-- Members list -->
        <div class="lg:col-span-2 space-y-6">
          <div class="rounded-xl bg-white p-6 shadow">
            <h2 class="text-lg font-semibold text-gray-900">Current members</h2>

            <div class="mt-4 space-y-2">
              <div v-for="m in members" :key="m.id" class="flex items-center justify-between rounded-lg border p-3">
                <div class="min-w-0">
                  <div class="truncate text-sm font-semibold text-gray-900">{{ m.name }}</div>
                  <div class="truncate text-xs text-gray-500">{{ m.email }} <span v-if="m.phone">• {{ m.phone }}</span></div>
                </div>
                <div class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700">{{ m.role }}</div>
              </div>

              <div v-if="members.length === 0" class="rounded-lg border border-dashed p-8 text-center text-gray-600">
                No members yet.
              </div>
            </div>
          </div>

          <div class="rounded-xl bg-white p-6 shadow">
            <h2 class="text-lg font-semibold text-gray-900">Invites</h2>
            <p class="mt-1 text-sm text-gray-600">Copy the link and send it to the invited person.</p>

            <div class="mt-4 space-y-2">
              <div v-for="i in invites" :key="i.id" class="rounded-lg border p-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                  <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-gray-900">{{ i.email }}</div>
                    <div class="text-xs text-gray-500">
                      Role: {{ i.role }} • Expires: {{ i.expires_at ?? '—' }} • Accepted: {{ i.accepted_at ?? '—' }}
                    </div>
                  </div>

                  <div class="flex flex-wrap gap-2">
                    <button type="button" class="rounded bg-gray-100 px-3 py-1 text-sm hover:bg-gray-200" @click="copy(i.invite_url)">
                      Copy link
                    </button>
                    <a class="rounded bg-gray-100 px-3 py-1 text-sm hover:bg-gray-200" :href="i.invite_url" target="_blank">
                      Open
                    </a>
                    <button
                      v-if="canInvite && !i.accepted_at"
                      class="rounded bg-red-50 px-3 py-1 text-sm text-red-700 hover:bg-red-100"
                      @click="revoke(i.id)"
                    >
                      Revoke
                    </button>
                  </div>
                </div>
              </div>

              <div v-if="invites.length === 0" class="rounded-lg border border-dashed p-8 text-center text-gray-600">
                No invites yet.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
