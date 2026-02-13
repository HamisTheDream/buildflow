<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  organizationId: number
  notes: any[]
  systemEvents?: any[] // audit logs etc to merge
}>()

const form = useForm({
  content: '',
  type: 'note',
})

const submit = () => {
  form.post(`/owner/organizations/${props.organizationId}/notes`, {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}

const typeIcon = (type: string) => {
  switch (type) {
    case 'call': return 'phone'
    case 'email': return 'mail'
    case 'meeting': return 'users'
    default: return 'file-text'
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Input -->
    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
      <form @submit.prevent="submit">
        <div class="border-b border-gray-100 pb-3 mb-3 flex gap-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" v-model="form.type" value="note" class="text-brand-500 focus:ring-brand-500" />
            <span class="text-sm font-medium text-gray-700">Note</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" v-model="form.type" value="call" class="text-brand-500 focus:ring-brand-500" />
            <span class="text-sm font-medium text-gray-700">Call</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" v-model="form.type" value="email" class="text-brand-500 focus:ring-brand-500" />
            <span class="text-sm font-medium text-gray-700">Email</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" v-model="form.type" value="meeting" class="text-brand-500 focus:ring-brand-500" />
            <span class="text-sm font-medium text-gray-700">Meeting</span>
          </label>
        </div>
        
        <textarea 
          v-model="form.content"
          rows="3"
          placeholder="Log activity..."
          class="w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500 resize-none"
        ></textarea>
        
        <div class="mt-3 flex justify-end">
          <button 
            type="submit" 
            :disabled="form.processing || !form.content"
            class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-50"
          >
            Log Activity
          </button>
        </div>
      </form>
    </div>

    <!-- Feed -->
    <div class="relative pl-4 border-l-2 border-gray-100 space-y-8">
      <div v-for="item in notes" :key="'note-'+item.id" class="relative">
        <!-- Icon -->
        <div class="absolute -left-[25px] flex h-8 w-8 items-center justify-center rounded-full bg-white ring-2 ring-gray-100">
           <!-- Icons based on type -->
           <span v-if="item.type === 'call'" class="text-blue-500">📞</span>
           <span v-else-if="item.type === 'email'" class="text-purple-500">📧</span>
           <span v-else-if="item.type === 'meeting'" class="text-orange-500">👥</span>
           <span v-else-if="item.type === 'system' || item.type === 'payment'" class="text-gray-600">⚙️</span>
           <span v-else class="text-gray-400">📝</span>
        </div>

        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-100">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
              <span class="font-semibold text-gray-900 capitalize">{{ item.type }}</span>
              <span class="text-xs text-gray-500">by {{ item.admin.name }}</span>
            </div>
            <span class="text-xs text-gray-400">{{ item.created_at }}</span>
          </div>
          <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ item.content }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
