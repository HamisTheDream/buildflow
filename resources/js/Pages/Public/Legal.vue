<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

defineProps<{
    title: string
}>()
</script>

<template>
  <PublicLayout>
    <Head :title="title" />

    <section class="py-20 lg:py-32">
        <div class="mx-auto max-w-4xl px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-white mb-10">{{ title }}</h1>
            
            <div class="prose prose-invert prose-lg max-w-none text-gray-400">
                <slot />
            </div>
        </div>
    </section>
  </PublicLayout>
</template>
