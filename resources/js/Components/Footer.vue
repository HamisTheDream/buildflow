<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

const currentYear = new Date().getFullYear()
</script>

<template>
  <footer class="border-t border-white/10 bg-neutral-900 pt-16 pb-8" aria-labelledby="footer-heading">
    <h2 id="footer-heading" class="sr-only">Footer</h2>
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
      <div class="xl:grid xl:grid-cols-3 xl:gap-8">
        <div class="space-y-8">
           <Link href="/" class="flex items-center gap-2 group">
              <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center">
                 <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
              </div>
              <span class="text-xl font-bold tracking-tight text-white">BuildFlow</span>
            </Link>
          <p class="text-sm leading-6 text-gray-400">
            The all-in-one ERP platform for real estate developers and construction firms. Manage projects, sales, finance, and teams from one dashboard.
          </p>
        </div>
        <div class="mt-16 grid grid-cols-2 gap-8 xl:col-span-2 xl:mt-0">
          <div class="md:grid md:grid-cols-2 md:gap-8">
            <div>
              <h3 class="text-sm font-semibold leading-6 text-white">Product</h3>
              <ul role="list" class="mt-6 space-y-4">
                <li><a href="/#features" class="text-sm leading-6 text-gray-400 hover:text-white">Features</a></li>
                <li><a href="/#pricing" class="text-sm leading-6 text-gray-400 hover:text-white">Pricing</a></li>
                <li><a href="/#faq" class="text-sm leading-6 text-gray-400 hover:text-white">FAQ</a></li>
              </ul>
            </div>
            <div class="mt-10 md:mt-0">
              <h3 class="text-sm font-semibold leading-6 text-white">Company</h3>
              <ul role="list" class="mt-6 space-y-4">
                <li><Link href="/blog" class="text-sm leading-6 text-gray-400 hover:text-white">Blog</Link></li>
                <li><Link href="/contact" class="text-sm leading-6 text-gray-400 hover:text-white">Contact</Link></li>
              </ul>
            </div>
          </div>
          <div class="md:grid md:grid-cols-2 md:gap-8">
            <div>
              <h3 class="text-sm font-semibold leading-6 text-white">Legal</h3>
              <ul role="list" class="mt-6 space-y-4">
                <li><Link href="/privacy" class="text-sm leading-6 text-gray-400 hover:text-white">Privacy Policy</Link></li>
                <li><Link href="/terms" class="text-sm leading-6 text-gray-400 hover:text-white">Terms of Service</Link></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-16 border-t border-white/10 pt-8 sm:mt-20 lg:mt-24">
        <p class="text-xs leading-5 text-gray-400">&copy; {{ currentYear }} BuildFlow. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>
