<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  mode: 'create' | 'edit'
  action: string
  method?: 'post' | 'patch'
  plans: { id:number; key:string; name:string }[]
  organizations: { id:number; name:string }[]
  initial?: any
  canDelete?: boolean
  deleteAction?: string
}>()

const form = useForm({
  title: props.initial?.title || '',
  body: props.initial?.body || '',
  tone: props.initial?.tone || 'info',
  cta_text: props.initial?.cta_text || '',
  cta_url: props.initial?.cta_url || '',

  target: props.initial?.is_global ? 'global' : (props.initial?.plan_id ? 'plan' : 'organization'),
  plan_id: props.initial?.plan_id || null,
  organization_id: props.initial?.organization_id || null,

  is_active: props.initial?.is_active ?? true,
  starts_at: props.initial?.starts_at || '',
  ends_at: props.initial?.ends_at || '',

  audit_reason: '',
})

function submit() {
  const method = props.method || 'post'
  if (method === 'patch') form.patch(props.action)
  else form.post(props.action)
}

function onTargetChange() {
  if (form.target === 'global') {
    form.plan_id = null
    form.organization_id = null
  }
  if (form.target === 'plan') {
    form.organization_id = null
  }
  if (form.target === 'organization') {
    form.plan_id = null
  }
}
</script>

<template>
  <form class="space-y-5" @submit.prevent="submit">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <div>
        <label class="text-xs font-semibold text-gray-700">Title</label>
        <input v-model="form.title" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" maxlength="120" />
        <div v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</div>
      </div>

      <div>
        <label class="text-xs font-semibold text-gray-700">Tone</label>
        <select v-model="form.tone" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500">
          <option value="info">Info</option>
          <option value="success">Success</option>
          <option value="warning">Warning</option>
          <option value="danger">Danger</option>
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="text-xs font-semibold text-gray-700">Body (optional)</label>
        <textarea v-model="form.body" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" rows="4" maxlength="2000"></textarea>
      </div>

      <div>
        <label class="text-xs font-semibold text-gray-700">CTA text (optional)</label>
        <input v-model="form.cta_text" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" maxlength="40" placeholder="e.g. View update" />
      </div>

      <div>
        <label class="text-xs font-semibold text-gray-700">CTA URL (optional)</label>
        <input v-model="form.cta_url" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="https://..." />
        <div v-if="form.errors.cta_url" class="mt-1 text-xs text-red-600">{{ form.errors.cta_url }}</div>
      </div>
    </div>

    <div class="rounded-xl border p-4">
      <div class="text-sm font-semibold text-gray-900">Targeting</div>
      <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
          <label class="text-xs font-semibold text-gray-700">Target</label>
          <select v-model="form.target" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" @change="onTargetChange">
            <option value="global">Global (all orgs)</option>
            <option value="plan">Specific plan</option>
            <option value="organization">Specific organization</option>
          </select>
        </div>

        <div v-if="form.target === 'plan'">
          <label class="text-xs font-semibold text-gray-700">Plan</label>
          <select v-model="form.plan_id" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500">
            <option :value="null">Select plan</option>
            <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <div v-if="form.errors.plan_id" class="mt-1 text-xs text-red-600">{{ form.errors.plan_id }}</div>
        </div>

        <div v-if="form.target === 'organization'">
          <label class="text-xs font-semibold text-gray-700">Organization</label>
          <select v-model="form.organization_id" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500">
            <option :value="null">Select organization</option>
            <option v-for="o in organizations" :key="o.id" :value="o.id">{{ o.name }}</option>
          </select>
          <div v-if="form.errors.organization_id" class="mt-1 text-xs text-red-600">{{ form.errors.organization_id }}</div>
        </div>
      </div>
    </div>

    <div class="rounded-xl border p-4">
      <div class="text-sm font-semibold text-gray-900">Scheduling</div>
      <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
          <label class="text-xs font-semibold text-gray-700">Active</label>
          <select v-model="form.is_active" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500">
            <option :value="true">Yes</option>
            <option :value="false">No</option>
          </select>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-700">Starts at (optional)</label>
          <input v-model="form.starts_at" type="datetime-local" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" />
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-700">Ends at (optional)</label>
          <input v-model="form.ends_at" type="datetime-local" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" />
          <div v-if="form.errors.ends_at" class="mt-1 text-xs text-red-600">{{ form.errors.ends_at }}</div>
        </div>
      </div>
    </div>

    <div class="rounded-xl border p-4">
      <div class="text-sm font-semibold text-gray-900">Audit reason (optional)</div>
      <div class="mt-2 text-xs text-gray-500">Helps future you understand why changes were made.</div>
      <input v-model="form.audit_reason" class="mt-2 w-full rounded-lg border-gray-300 bg-white text-gray-900 p-2 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="e.g. notify all customers about maintenance" />
    </div>

    <div class="flex items-center justify-between">
      <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              :disabled="form.processing">
        {{ mode === 'create' ? 'Create' : 'Save changes' }}
      </button>

      <div v-if="canDelete && deleteAction" class="flex items-center gap-2">
        <form :action="deleteAction" method="post" @submit.prevent="form.delete(deleteAction)">
          <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
            Delete
          </button>
        </form>
      </div>
    </div>
  </form>
</template>
