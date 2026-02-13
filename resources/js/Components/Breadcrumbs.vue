<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

defineProps<{
  items: Array<{
    label: string
    href?: string | null
  }>
}>()
</script>

<template>
  <nav class="flex" aria-label="Breadcrumb">
    <ol role="list" class="flex items-center space-x-2">
      <li v-for="(item, index) in items" :key="index">
        <div class="flex items-center">
          <svg
            v-if="index > 0"
            class="h-5 w-5 flex-shrink-0 text-gray-300 mr-2"
            fill="currentColor"
            viewBox="0 0 20 20"
            aria-hidden="true"
          >
            <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
          </svg>
          
          <Link
            v-if="item.href"
            :href="item.href"
            class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors"
          >
            {{ item.label }}
          </Link>
          <span
            v-else
            class="text-sm font-medium text-gray-900"
            aria-current="page"
          >
            {{ item.label }}
          </span>
        </div>
      </li>
    </ol>
  </nav>
</template>
