<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  organizationId: number
}>()

const form = useForm({
  content: '',
  assigned_to: null, 
  due_at: '',
})

const submit = () => {
  form.post(`/owner/organizations/${props.organizationId}/tasks`, {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
    <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-3">
      <h3 class="font-semibold text-gray-900">Tasks</h3>
    </div>
    
    <div class="p-4">
      <form @submit.prevent="submit" class="space-y-3">
        <div>
          <label class="sr-only">Task content</label>
          <input 
            v-model="form.content" 
            type="text" 
            placeholder="Add a task..." 
            class="w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500"
          />
        </div>
        <div class="flex gap-2">
          <input 
            v-model="form.due_at" 
            type="datetime-local" 
            class="w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500"
          />
          <button 
            type="submit" 
            :disabled="form.processing || !form.content"
            class="rounded-lg bg-brand-500 px-3 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50"
          >
            Add
          </button>
        </div>
      </form>

      <div class="mt-4 space-y-3">
        <slot />
      </div>
    </div>
  </div>
</template>
