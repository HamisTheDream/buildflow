<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import SectionCard from '@/Components/SectionCard.vue'
import AnnouncementForm from './_Form.vue'

const page = usePage<any>()
const admin = page.props.ownerAuth?.admin

defineProps<{
  plans: any[]
  organizations: any[]
  announcement: any
}>()
</script>

<template>
  <OwnerLayout>
    <Head title="Edit Announcement" />

    <SectionCard title="Edit announcement" subtitle="Changes apply immediately (unless scheduled).">
      <AnnouncementForm
        mode="edit"
        :action="`/owner/announcements/${announcement.id}`"
        method="patch"
        :plans="plans"
        :organizations="organizations"
        :initial="announcement"
        :canDelete="!!admin?.is_super"
        :deleteAction="`/owner/announcements/${announcement.id}`"
      />
    </SectionCard>
  </OwnerLayout>
</template>
