<script setup lang="ts">
import ProjectLayout from '@/Layouts/ProjectLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  project: { id:number; name:string; status:string }
  active: string
  title: string
}>()
</script>

<template>
  <ProjectLayout :project="project" :active="active">
    <Head :title="`${title} — ${project.name}`" />
    <div class="rounded-xl bg-white p-6 shadow">
      <h2 class="text-lg font-semibold text-gray-900">{{ title }}</h2>
      <p class="mt-2 text-sm text-gray-600">
        Coming next. This tab is wired correctly and uses the shared Project UI shell.
      </p>
    </div>
  </ProjectLayout>
</template>
