<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useEntitlements } from '@/composables/useEntitlements'

const { can } = useEntitlements()

const props = defineProps<{
  projectId: number
  attachableType: 'issue'|'task'|'log'|'today_log'|'cost'
  attachableId: number
  canManage: boolean
  attachments: any[]
}>()

const page = usePage()
const flash = computed(() => (page.props.flash as any) ?? {})

const form = useForm({
  attachable_type: props.attachableType,
  attachable_id: props.attachableId,
  files: [] as File[],
  caption: '',
})

function onFiles(e: Event) {
  const files = (e.target as HTMLInputElement).files
  form.files = files ? Array.from(files) : []
}

function upload() {
  form.post(`/app/projects/${props.projectId}/attachments`, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => form.reset('files','caption'),
  })
}

function remove(attId:number) {
  const f = useForm({})
  f.delete(`/app/projects/${props.projectId}/attachments/${attId}`, { preserveScroll: true })
}

function prettySize(bytes:number) {
  if (!bytes && bytes !== 0) return '—'
  const kb = bytes / 1024
  if (kb < 1024) return `${kb.toFixed(0)} KB`
  const mb = kb / 1024
  return `${mb.toFixed(1)} MB`
}
</script>

<template>
  <div class="rounded-lg border p-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="text-sm font-semibold text-gray-900">Attachments</div>
        <div class="mt-1 text-xs text-gray-600">Photos, documents, receipts, proof.</div>
      </div>
    </div>

    <form v-if="canUpload" class="mt-3 space-y-2" @submit.prevent="upload">
      <input type="file" multiple class="block w-full text-sm" @change="onFiles" />
      <input v-model="form.caption" class="w-full rounded-lg border p-2 text-sm" placeholder="Caption (optional)" />
      <button class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50" :disabled="form.processing">
        Add attachments
      </button>
      <div v-if="form.errors['files']" class="text-sm text-red-600">{{ form.errors['files'] }}</div>
      <div v-if="form.errors['files.0']" class="text-sm text-red-600">{{ form.errors['files.0'] }}</div>
    </form>
    
    <div v-else-if="canManage && !can.upload" class="mt-3 rounded bg-amber-50 p-3 text-sm text-amber-800">
      Upload disabled. Storage limit reached.
    </div>

    <div v-if="attachments.length === 0" class="mt-3 rounded bg-gray-50 p-4 text-sm text-gray-600">
      No attachments yet.
    </div>

    <div v-else class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
      <div v-for="a in attachments" :key="a.id" class="rounded-lg border p-3">
        <div v-if="a.is_image" class="overflow-hidden rounded bg-gray-50">
          <a :href="a.url" target="_blank" rel="noreferrer">
            <img :src="a.url" class="h-40 w-full object-cover" />
          </a>
        </div>
        <div v-else class="rounded bg-gray-50 p-3">
          <div class="truncate text-sm font-semibold text-gray-900">{{ a.original_name }}</div>
          <div class="mt-1 text-xs text-gray-500">{{ a.mime }} • {{ prettySize(a.size) }}</div>
          <a :href="a.url" target="_blank" rel="noreferrer" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">
            Open / Download
          </a>
        </div>

        <div class="mt-2 text-xs text-gray-500">
          {{ a.created_at }} • {{ a.uploader?.name || '—' }}
        </div>
        <div class="mt-1 text-sm text-gray-700">{{ a.caption || '—' }}</div>

        <button v-if="canManage || a.can_delete" class="mt-2 rounded bg-red-50 px-3 py-2 text-sm text-red-700 hover:bg-red-100" @click="remove(a.id)">
          Remove
        </button>
      </div>
    </div>
  </div>
</template>
