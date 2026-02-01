<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

defineProps<{
  title: string
  body?: string
  actionText?: string
  actionHref?: string
}>()
</script>

<template>
  <div class="rounded-xl border border-dashed bg-white p-10 text-center">
    <div class="mx-auto max-w-md">
      <div class="text-base font-semibold text-gray-900">{{ title }}</div>
      <div v-if="body" class="mt-2 text-sm text-gray-600">{{ body }}</div>
      <div v-if="actionText && actionHref" class="mt-5">
        <Link :href="actionHref" class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
          {{ actionText }}
        </Link>
      </div>
    </div>
  </div>
</template>
