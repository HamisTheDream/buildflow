<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

defineProps<{
    post: {
        id: number
        title: string
        content: string
        published_at: string
        author?: { name: string }
        seo_title?: string
        seo_description?: string
    }
    related: Array<{
        title: string
        slug: string
        excerpt: string
    }>
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
        <Head :title="post.seo_title || post.title">
            <meta name="description" :content="post.seo_description || post.content.substring(0, 160)" />
        </Head>

        <article class="relative isolate overflow-hidden bg-neutral-900 px-6 py-24 sm:py-32 lg:overflow-visible lg:px-0">
            <div class="absolute inset-0 -z-10 overflow-hidden">
                <div class="absolute left-[max(50%,25rem)] top-0 h-[64rem] w-[128rem] -translate-x-1/2 stroke-gray-700 [mask-image:radial-gradient(64rem_64rem_at_top,white,transparent)]" aria-hidden="true">
                    <svg class="absolute inset-0 h-full w-full -z-10" aria-hidden="true">
                        <defs>
                            <pattern id="e813992c-7d03-4cc4-a2bd-151760b470a0" width="200" height="200" x="50%" y="-1" patternUnits="userSpaceOnUse">
                                <path d="M100 200V.5M.5 .5H200" fill="none" />
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" stroke-width="0" fill="url(#e813992c-7d03-4cc4-a2bd-151760b470a0)" />
                    </svg>
                </div>
            </div>
            
            <div class="mx-auto grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 lg:mx-0 lg:max-w-none lg:grid-cols-2 lg:items-start lg:gap-y-10">
                <div class="lg:col-span-2 lg:col-start-1 lg:row-start-1 lg:mx-auto lg:grid lg:w-full lg:max-w-7xl lg:grid-cols-2 lg:gap-x-8 lg:px-8">
                    <div class="lg:pr-4">
                        <div class="lg:max-w-lg">
                            <p class="text-base font-semibold leading-7 text-brand-500">Blog</p>
                            <h1 class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ post.title }}</h1>
                             <p class="mt-6 text-xl leading-8 text-gray-300">
                                Published on {{ formatDate(post.published_at) }} by {{ post.author?.name || 'BuildFlow Team' }}
                             </p>
                        </div>
                    </div>
                </div>
                <div class="-ml-12 -mt-12 p-12 lg:sticky lg:top-4 lg:col-start-2 lg:row-span-2 lg:row-start-1 lg:overflow-hidden">
                     <!-- Placeholder for dynamic image or just abstract art -->
                     <div class="aspect-[4/3] w-[48rem] max-w-none rounded-xl bg-gray-900 shadow-xl ring-1 ring-gray-400/10 sm:w-[57rem] flex items-center justify-center">
                         <div class="text-8xl">📰</div>
                     </div>
                </div>
                <div class="lg:col-span-2 lg:col-start-1 lg:row-start-2 lg:mx-auto lg:grid lg:w-full lg:max-w-7xl lg:grid-cols-2 lg:gap-x-8 lg:px-8">
                    <div class="lg:pr-4">
                        <div class="max-w-xl text-base leading-7 text-gray-300 lg:max-w-lg prose prose-invert">
                           <!-- Simple rendering. For markdown, we'd need a markdown parser. Assuming plain text/html for now or user will handle markdown parsing lib later -->
                           <div class="whitespace-pre-wrap">{{ post.content }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </PublicLayout>
</template>
