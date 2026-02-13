<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

defineProps<{
    posts: {
        data: Array<{
            id: number
            title: string
            slug: string
            excerpt: string
            published_at: string
            created_at: string
            author?: { name: string }
        }>
        links: any[]
    }
}>()

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>

<template>
    <PublicLayout>
        <Head title="Blog - BuildFlow" />

        <div class="relative bg-neutral-900 py-24 sm:py-32">
             <!-- Background -->
            <div class="absolute inset-x-0 top-0 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>

            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">From the blog</h2>
                    <p class="mt-2 text-lg leading-8 text-gray-400">Learn how to grow your business with our expert advice.</p>
                </div>
                <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                    <article v-for="post in posts.data" :key="post.id" class="flex flex-col items-start justify-between">
                        <div class="relative w-full">
                            <!-- Placeholder Image if no featured image -->
                             <div class="aspect-[16/9] w-full rounded-2xl bg-gray-100 object-cover sm:aspect-[2/1] lg:aspect-[3/2] flex items-center justify-center bg-gradient-to-br from-gray-800 to-gray-900 border border-white/10">
                                <span class="text-gray-600 font-bold text-2xl">BuildFlow</span>
                             </div>
                            
                            <div class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-gray-900/10"></div>
                        </div>
                        <div class="max-w-xl">
                            <div class="mt-8 flex items-center gap-x-4 text-xs">
                                <time :datetime="post.published_at" class="text-gray-400">{{ formatDate(post.published_at) }}</time>
                            </div>
                            <div class="group relative">
                                <h3 class="mt-3 text-lg font-semibold leading-6 text-white group-hover:text-gray-300">
                                    <Link :href="route('blog.show', post.slug)">
                                        <span class="absolute inset-0"></span>
                                        {{ post.title }}
                                    </Link>
                                </h3>
                                <p class="mt-5 line-clamp-3 text-sm leading-6 text-gray-400">{{ post.excerpt }}</p>
                            </div>
                        </div>
                    </article>
                    
                     <div v-if="posts.data.length === 0" class="col-span-3 text-center text-gray-500 py-12">
                        No posts published yet.
                    </div>
                </div>

                 <!-- Pagination -->
                 <div class="mt-12 flex justify-center" v-if="posts.links.length > 3">
                    <!-- Simple pagination buttons handling -->
                    <Component 
                        v-for="(link, i) in posts.links" 
                        :key="i"
                        :is="link.url ? Link : 'span'"
                        :href="link.url"
                        v-html="link.label"
                        class="px-4 py-2 border rounded mx-1 text-sm font-medium"
                        :class="{
                            'bg-brand-600 text-white border-brand-600': link.active,
                            'bg-neutral-800 text-gray-300 border-white/10 hover:bg-neutral-700': !link.active && link.url,
                            'text-gray-500 border-transparent': !link.url
                        }"
                    />
                 </div>
            </div>
        </div>
    </PublicLayout>
</template>
