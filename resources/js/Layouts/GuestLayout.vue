<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="flex min-h-screen">
    <!-- Left Panel: Branding -->
    <div class="hidden lg:flex lg:w-1/2 bg-slate-900 relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-10">
        <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
          <defs>
            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
              <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5" class="text-white"/>
            </pattern>
          </defs>
          <rect width="100" height="100" fill="url(#grid)" />
        </svg>
      </div>
      
      <!-- Orange Glow -->
      <div class="absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-brand-500/30 blur-3xl"></div>
      <div class="absolute -top-32 -right-32 h-64 w-64 rounded-full bg-brand-600/20 blur-3xl"></div>
      
      <!-- Content -->
      <div class="relative z-10 flex flex-col justify-between p-12 text-white">
        <div>
          <!-- Logo -->
          <Link href="/" class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-500 shadow-glow">
              <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <span class="text-xl font-bold">BuildFlow</span>
          </Link>
        </div>
        
        <div class="max-w-md">
          <h1 class="text-4xl font-bold leading-tight">
            Build better.<br/>
            <span class="text-brand-400">Track smarter.</span>
          </h1>
          <p class="mt-4 text-lg text-gray-400">
            The construction management platform that helps teams stay organized, on budget, and on schedule.
          </p>
          
          <div class="mt-8 flex items-center gap-6">
            <div class="flex -space-x-2">
              <div class="h-10 w-10 rounded-full border-2 border-slate-900 bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-sm font-bold">JK</div>
              <div class="h-10 w-10 rounded-full border-2 border-slate-900 bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-sm font-bold">AL</div>
              <div class="h-10 w-10 rounded-full border-2 border-slate-900 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-sm font-bold">MT</div>
            </div>
            <p class="text-sm text-gray-400">
              <span class="font-semibold text-white">2,500+</span> teams trust BuildFlow
            </p>
          </div>
        </div>
        
        <div class="text-sm text-gray-500">
          © 2024 BuildFlow. All rights reserved.
        </div>
      </div>
    </div>
    
    <!-- Right Panel: Form -->
    <div class="flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-16 bg-white">
      <!-- Mobile Logo -->
      <div class="mb-8 lg:hidden">
        <Link href="/" class="flex items-center gap-2">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <span class="text-lg font-bold text-gray-900">BuildFlow</span>
        </Link>
      </div>
      
      <div class="mx-auto w-full max-w-sm">
        <slot />
      </div>
    </div>
  </div>
</template>
