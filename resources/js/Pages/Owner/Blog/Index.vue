<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import { ref } from 'vue'

defineProps<{
    posts: {
        data: Array<{
            id: number
            title: string
            slug: string
            published_at: string
            created_at: string
            author?: { name: string }
        }>
        links: any[]
    }
}>()

const confirmDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(`/owner/blog/${id}`)
    }
}
</script>

<template>
    <OwnerLayout>
        <Head title="Blog Management" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Blog Posts</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage your blog content and SEO.</p>
                </div>
                <Link href="/owner/blog/create" class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition">
                    Create New Post
                </Link>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Published</th>
                             <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="post in posts.data" :key="post.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ post.title }}</div>
                                <div class="text-sm text-gray-500">/{{ post.slug }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="post.published_at" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Published
                                </span>
                                <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Draft
                                </span>
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <Link :href="`/owner/blog/${post.id}/edit`" class="text-brand-600 hover:text-brand-900 mr-4">Edit</Link>
                                <button type="button" @click="confirmDelete(post.id)" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="posts.data.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                No posts found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </OwnerLayout>
</template>
