<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, usePage, Head, useForm } from '@inertiajs/vue3'
import ProjectLayout from '@/Layouts/ProjectLayout.vue'
import EmptyState from '@/Components/EmptyState.vue'
import Pagination from '@/Components/Pagination.vue'
import Badge from '@/Components/Badge.vue'
import PermissionNotice from '@/Components/PermissionNotice.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

import { formatDateTime, formatEnum } from '@/utils/format'

const page = usePage<any>()
const project = computed(() => page.props.project ?? page.props.data?.project ?? null)
const canManage = computed(() => page.props.canManage ?? true)

const paginator = computed(() => page.props.media ?? page.props.rows ?? null)

const media = computed<any[]>(() => {
  const p = paginator.value
  if (!p) return page.props.items ?? []
  if (Array.isArray(p)) return p
  if (Array.isArray(p.data)) return p.data
  return []
})

const links = computed(() => {
  const p = paginator.value
  return p && !Array.isArray(p) ? p.links : null
})

const showModal = ref(false)
const form = useForm({
    files: [] as File[],
    caption: '',
})

function openUpload() {
    form.reset()
    showModal.value = true
}

function handleFileChange(e: Event) {
    const target = e.target as HTMLInputElement
    if (target.files) {
        form.files = Array.from(target.files)
    }
}

function submit() {
    form.post(`/app/projects/${project.value.id}/media`, {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false
            form.reset()
        },
    })
}

// Laravel reports per-file failures as `files.0`, `files.1`, ... — collect
// every files-related error so rejections are never silently swallowed.
const fileErrors = computed(() => {
    return Object.entries(form.errors)
        .filter(([key]) => key === 'files' || key.startsWith('files.'))
        .map(([, msg]) => msg as string)
})

function kindTone(k: string) {
  const v = (k || '').toLowerCase()
  if (v.includes('image')) return 'blue'
  if (v.includes('video')) return 'amber'
  if (v.includes('pdf') || v.includes('doc')) return 'gray'
  return 'green'
}

function fileKind(m:any) {
  return m.type || m.mime || m.kind || 'file'
}

function thumbUrl(m:any) {
  return m.thumb_url || m.thumbnail_url || null
}

function openUrl(m:any) {
  return m.url || m.download_url || m.public_url || '#'
}

const deleteForm = useForm({})

function deleteFile(m: any) {
    if (!confirm('Are you sure you want to delete this file? This cannot be undone.')) return
    deleteForm.delete(`/app/projects/${project.value.id}/media/${m.id}`, {
        preserveScroll: true,
    })
}
</script>

<template>
  <ProjectLayout :project="project" active="media">
    <Head :title="project?.name ? `Media — ${project.name}` : 'Media'" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
               <h2 class="text-lg font-medium text-gray-900">Media</h2>
               <p class="mt-1 text-sm text-gray-500">Upload evidence, photos, videos and documents.</p>
            </div>
            
             <button
              v-if="project?.id && canManage"
              @click="openUpload()"
              class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-black transition"
            >
              Upload
            </button>
        </div>

        <PermissionNotice v-if="!canManage" />

        <div v-if="!media || media.length === 0" class="rounded-xl border-2 border-dashed border-gray-200 p-12">
          <EmptyState
            icon="media"
            title="No photos or files uploaded"
            description="Capture progress by uploading images and documents."
            :actionText="canManage ? 'Upload' : undefined"
            :actionCallback="canManage ? openUpload : undefined"
          />
        </div>

        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="m in media"
            :key="m.id"
            class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white transition-all hover:shadow-lg"
          >
            <!-- Thumbnail (click to view) -->
             <a :href="openUrl(m)" target="_blank" class="block aspect-video w-full bg-gray-100 relative overflow-hidden">
                 <img
                    v-if="thumbUrl(m)"
                    :src="thumbUrl(m)"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                    alt=""
                  />
                  <div v-else class="flex h-full items-center justify-center text-gray-400">
                       <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                  </div>

                  <!-- Overlay Type Badge -->
                  <div class="absolute top-2 right-2">
                       <Badge :text="formatEnum(fileKind(m))" :tone="kindTone(fileKind(m)) as any" size="sm" class="shadow-sm border-0" />
                  </div>
             </a>

             <!-- Info -->
            <div class="flex flex-1 flex-col p-4">
               <h3 class="text-sm font-medium text-gray-900 truncate" :title="m.name || m.filename">
                  {{ m.name || m.filename || 'File' }}
               </h3>
               <p v-if="m.caption" class="mt-1 text-sm text-gray-600 line-clamp-2">
                   {{ m.caption }}
               </p>
               <p class="mt-1 text-xs text-gray-500">{{ formatDateTime(m.created_at) }}</p>
            </div>

            <div class="flex items-center justify-between bg-gray-50 px-4 py-2 transition-colors group-hover:bg-brand-50">
                <a :href="openUrl(m)" target="_blank" class="text-xs font-medium text-brand-600">View File &rarr;</a>
                <button
                  v-if="canManage"
                  @click="deleteFile(m)"
                  :disabled="deleteForm.processing"
                  class="text-xs font-medium text-red-600 hover:text-red-800 disabled:opacity-50"
                >
                  Delete
                </button>
            </div>
          </div>
        </div>

        <div v-if="links" class="mt-6">
          <Pagination :links="links" />
        </div>
    </div>
    
    <!-- Upload Modal -->
    <Modal :show="showModal" @close="showModal = false">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-6">Upload Media</h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel value="Files" />
                    <input 
                        type="file" 
                        multiple 
                        @change="handleFileChange" 
                        class="mt-1 block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-brand-50 file:text-brand-700
                        hover:file:bg-brand-100"
                    />
                    <div v-if="fileErrors.length" class="mt-1.5 space-y-1">
                        <div v-for="(err, i) in fileErrors" :key="i" class="text-sm text-red-600">{{ err }}</div>
                    </div>
                </div>

                <div>
                    <InputLabel value="Caption (Optional)" />
                    <TextInput v-model="form.caption" class="mt-1 block w-full" placeholder="Describe these files..." />
                    <p class="mt-1 text-xs text-gray-500">This caption will be applied to all uploaded files.</p>
                     <div v-if="form.errors.caption" class="mt-1.5 text-sm text-red-600">{{ form.errors.caption }}</div>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                    <PrimaryButton type="submit" :disabled="form.processing">Upload</PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
  </ProjectLayout>
</template>
