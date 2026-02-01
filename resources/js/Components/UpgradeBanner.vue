<script setup lang="ts">
import { useEntitlements } from '@/composables/useEntitlements'
import { Link } from '@inertiajs/vue3'

const { can, usage, limits } = useEntitlements()

function anyLimitHit() {
  if (!usage.value || !limits.value) return false
  return !can.value.create_project || !can.value.invite_member || !can.value.upload
}
</script>

<template>
  <div v-if="anyLimitHit()" class="rounded-xl bg-amber-50 p-4 text-amber-900">
    <div class="font-semibold">You’re hitting plan limits.</div>
    <div class="mt-1 text-sm">
      Projects: {{ usage?.projects }}/{{ limits?.max_projects }},
      Members: {{ usage?.members }}/{{ limits?.max_members }},
      Storage: {{ usage?.storage_mb }}MB/{{ limits?.max_storage_mb }}MB
    </div>
    <div class="mt-3">
      <Link href="/app/billing" class="inline-flex rounded-lg bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-700">
        View plans
      </Link>
    </div>
  </div>
</template>
