<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps<{
    post: {
        id: number
        title: string
        slug: string
        content: string
        excerpt: string
        published_at: string
        featured_image: string
        seo_title: string
        seo_description: string
    }
}>()

const form = useForm({
    title: props.post.title,
    content: props.post.content,
    excerpt: props.post.excerpt,
    published_at: props.post.published_at ? new Date(props.post.published_at).toISOString().slice(0, 16) : '',
    featured_image: props.post.featured_image,
    seo_title: props.post.seo_title,
    seo_description: props.post.seo_description
})

const submit = () => {
    form.put(`/owner/blog/${props.post.id}`)
}
</script>

<template>
    <OwnerLayout>
        <Head title="Edit Post" />

        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Edit Post: {{ post.title }}</h1>
                <Link href="/owner/blog" class="text-sm text-gray-600 hover:text-gray-900">Back to List</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900" required />
                    <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                </div>

                <!-- Content -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                    <div class="bg-white text-gray-900 rounded-md border border-gray-300 overflow-hidden">
                        <QuillEditor theme="snow" v-model:content="form.content" contentType="html" toolbar="full" style="height: 400px;" />
                    </div>
                    <div v-if="form.errors.content" class="text-red-500 text-xs mt-1">{{ form.errors.content }}</div>
                </div>

                <!-- Excerpt -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Excerpt</label>
                    <textarea v-model="form.excerpt" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900"></textarea>
                </div>
                
                 <!-- Publish Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Publish Date (Leave empty to save as draft)</label>
                    <input v-model="form.published_at" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900" />
                </div>

                <!-- SEO Section -->
                <div class="border-t pt-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">SEO Settings</h3>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                             <label class="block text-sm font-medium text-gray-700">SEO Title</label>
                             <input v-model="form.seo_title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" />
                        </div>
                        <div>
                             <label class="block text-sm font-medium text-gray-700">SEO Description</label>
                             <textarea v-model="form.seo_description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6">
                    <button type="submit" :disabled="form.processing" class="inline-flex justify-center rounded-md border border-transparent bg-brand-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Update Post' }}
                    </button>
                </div>
            </form>
        </div>
    </OwnerLayout>
</template>
