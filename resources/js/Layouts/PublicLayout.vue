<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import Footer from '@/Components/Footer.vue'

const page = usePage<any>()
</script>

<template>
  <div class="min-h-screen bg-neutral-900 text-white selection:bg-brand-500 selection:text-white flex flex-col font-sans">
    
    <!-- Glassmorphic Header -->
    <header class="fixed top-0 left-0 right-0 z-50 border-b border-white/10 bg-neutral-900/80 backdrop-blur-md">
      <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6 lg:px-8">
        
        <!-- Logo -->
        <Link href="/" class="flex items-center gap-2 group">
          <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-600 shadow-[0_0_15px_rgba(249,115,22,0.5)] transition-transform group-hover:scale-110 flex items-center justify-center overflow-hidden">
             <img v-if="page.props.site_settings?.seo_logo" :src="'/storage/' + page.props.site_settings.seo_logo" alt="BuildFlow" class="h-full w-full object-cover" />
             <svg v-else class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
          </div>
          <span class="text-xl font-bold tracking-tight text-white group-hover:text-brand-500 transition-colors">BuildFlow</span>
        </Link>

        <!-- Desktop Nav -->
        <nav class="hidden md:flex items-center gap-8">
          <a href="/#features" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Features</a>
          <a href="/#pricing" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Pricing</a>
          <Link href="/blog" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Blog</Link>
          <Link href="/contact" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Contact</Link>
        </nav>

        <!-- Auth Actions -->
        <div class="flex items-center gap-4">
          <template v-if="page.props.auth?.user">
            <Link 
                href="/dashboard" 
                class="hidden sm:block rounded-full px-5 py-2 text-sm font-semibold text-white transition-all hover:bg-white/10"
            >
                Dashboard
            </Link>
          </template>
          <template v-else>
            <Link 
                href="/login" 
                class="hidden sm:block text-sm font-semibold text-gray-300 hover:text-white transition-colors"
            >
                Log in
            </Link>
            <Link 
                href="/register" 
                class="rounded-full bg-brand-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition-transform hover:-translate-y-0.5 hover:shadow-brand-500/40"
            >
                Get Started
            </Link>
          </template>
        </div>

      </div>
    </header>

    <!-- Content Spacer -->
    <div class="h-20"></div>

    <main class="flex-1">
      <slot />
    </main>

    <Footer class="border-white/10 bg-neutral-900 text-gray-400" />
  </div>
</template>
