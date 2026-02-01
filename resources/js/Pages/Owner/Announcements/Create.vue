<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import AnnouncementForm from './_Form.vue'

defineProps<{
  plans: any[]
  organizations: any[]
}>()
</script>

<template>
  <OwnerLayout>
    <Head title="New Announcement" />

    <SectionCard title="New announcement" subtitle="Create an in-app banner shown to users based on targeting.">
      <AnnouncementForm
        mode="create"
        action="/owner/announcements"
        method="post"
        :plans="plans"
        :organizations="organizations"
      />
    </SectionCard>
  </OwnerLayout>
</template>
