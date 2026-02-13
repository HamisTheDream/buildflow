<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

</script>

<template>
  <PublicLayout>
    <Head title="Contact Us — BuildFlow" />

    <section class="py-20 lg:py-32 relative">
       <div class="mx-auto max-w-7xl px-6 lg:px-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
              
              <!-- Contact Info -->
              <div>
                  <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl mb-8">Get in touch</h1>
                  <p class="text-lg text-gray-400 mb-12">
                      Have questions about BuildFlow? Whether you're a real estate developer, construction firm, or property manager — our team is here to help you find the right plan.
                  </p>

                  <div class="space-y-8">
                      <div class="flex gap-4">
                          <div class="h-10 w-10 rounded-lg bg-neutral-800 flex items-center justify-center shrink-0 border border-white/5">
                              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                          </div>
                          <div>
                              <h3 class="text-base font-semibold text-white">Office</h3>
                              <p class="mt-2 text-gray-400">Lagos, Nigeria</p>
                          </div>
                      </div>

                       <div class="flex gap-4">
                          <div class="h-10 w-10 rounded-lg bg-neutral-800 flex items-center justify-center shrink-0 border border-white/5">
                              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                          </div>
                          <div>
                              <h3 class="text-base font-semibold text-white">Email</h3>
                              <p class="mt-2 text-gray-400">hello@buildflow.app</p>
                          </div>
                      </div>

                       <div class="flex gap-4">
                          <div class="h-10 w-10 rounded-lg bg-neutral-800 flex items-center justify-center shrink-0 border border-white/5">
                              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                          </div>
                          <div>
                              <h3 class="text-base font-semibold text-white">Working Hours</h3>
                              <p class="mt-2 text-gray-400">Mon – Fri, 9:00 AM – 6:00 PM (WAT)</p>
                          </div>
                      </div>
                  </div>
              </div>

              <!-- Form -->
              <div class="rounded-3xl bg-neutral-800/30 p-8 border border-white/5">
                  <form class="space-y-6">
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                          <div>
                              <label class="block text-sm font-medium text-gray-300 mb-2">First Name</label>
                              <input type="text" class="block w-full rounded-lg border-0 bg-neutral-900/50 py-2.5 text-white shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-brand-500 sm:text-sm sm:leading-6" />
                          </div>
                          <div>
                              <label class="block text-sm font-medium text-gray-300 mb-2">Last Name</label>
                              <input type="text" class="block w-full rounded-lg border-0 bg-neutral-900/50 py-2.5 text-white shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-brand-500 sm:text-sm sm:leading-6" />
                          </div>
                      </div>
                      <div>
                          <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                          <input type="email" class="block w-full rounded-lg border-0 bg-neutral-900/50 py-2.5 text-white shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-brand-500 sm:text-sm sm:leading-6" />
                      </div>
                      <div>
                          <label class="block text-sm font-medium text-gray-300 mb-2">How can we help?</label>
                          <textarea rows="4" placeholder="Tell us about your company and what you're looking for..." class="block w-full rounded-lg border-0 bg-neutral-900/50 py-2.5 text-white shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-brand-500 sm:text-sm sm:leading-6 placeholder-gray-600"></textarea>
                      </div>
                      <button type="submit" class="w-full rounded-lg bg-brand-500 px-3.5 py-2.5 text-center text-sm font-bold text-white shadow-sm hover:bg-brand-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 transition-colors">
                          Send Message
                      </button>
                  </form>
              </div>

          </div>
       </div>
    </section>
  </PublicLayout>
</template>
