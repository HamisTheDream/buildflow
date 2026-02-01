<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import Pagination from '@/Components/Pagination.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps<{
  filters: { q:string }
  admins: { data:any[]; links:any[] }
  me: { id:number; is_super:boolean }
}>()

const form = useForm({
  q: props.filters.q || '',
})

function apply() {
  form.get('/owner/admins', { preserveScroll:true, preserveState:true })
}

const reasonForm = useForm({ audit_reason: '' })

function toggleSuper(a:any) {
  reasonForm.patch(`/owner/admins/${a.id}/toggle-super`, { preserveScroll:true })
}

function toggleActive(a:any) {
  reasonForm.patch(`/owner/admins/${a.id}/toggle-active`, { preserveScroll:true })
}
</script>

<template>
  <OwnerLayout>
    <Head title="Admins" />

    <SectionCard title="Admins" subtitle="Manage owner admins securely.">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex gap-2 w-full md:w-2/3">
          <input v-model="form.q" class="w-full rounded-lg border p-2 text-sm" placeholder="Search by name/email..." />
          <button @click="apply" class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Go</button>
        </div>

        <Link v-if="me.is_super" href="/owner/admins/create" class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-black">
          New admin
        </Link>
      </div>

      <div class="mt-4 rounded-xl border p-4 bg-gray-50">
        <div class="text-sm font-semibold text-gray-900">Audit reason (optional)</div>
        <div class="mt-1 text-xs text-gray-500">Used for any toggle action you perform below.</div>
        <input v-model="reasonForm.audit_reason" class="mt-2 w-full rounded-lg border p-2 text-sm" placeholder="e.g. Promote to super to handle billing ops" />
      </div>

      <div class="mt-6 overflow-x-auto">
        <table class="min-w-full border rounded-lg overflow-hidden">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Admin</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Super</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Active</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y">
            <tr v-for="a in admins.data" :key="a.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <div class="text-sm font-semibold text-gray-900">{{ a.name }}</div>
                <div class="text-xs text-gray-500">{{ a.email }}</div>
              </td>

              <td class="px-4 py-3 text-sm">
                <Badge :text="a.is_super ? 'Yes' : 'No'" :tone="a.is_super ? 'green' : 'gray'" />
              </td>

              <td class="px-4 py-3 text-sm">
                <Badge :text="a.is_active ? 'Active' : 'Inactive'" :tone="a.is_active ? 'blue' : 'red'" />
              </td>

              <td class="px-4 py-3 text-right text-sm">
                <div class="flex justify-end gap-2">
                  <button
                    class="rounded-lg border px-3 py-2 text-xs font-semibold hover:bg-gray-50 disabled:opacity-50"
                    :disabled="!me.is_super || a.id === me.id"
                    @click="toggleSuper(a)"
                  >
                    Toggle super
                  </button>

                  <button
                    class="rounded-lg border px-3 py-2 text-xs font-semibold hover:bg-gray-50 disabled:opacity-50"
                    :disabled="!me.is_super || a.id === me.id"
                    @click="toggleActive(a)"
                  >
                    Toggle active
                  </button>
                </div>

                <div v-if="a.id === me.id" class="mt-1 text-xs text-gray-500">You</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="admins.links" />
    </SectionCard>
  </OwnerLayout>
</template>
