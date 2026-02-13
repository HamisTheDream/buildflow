<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    plans: {
        type: Array as () => Array<{
            id: number;
            key: string;
            name: string;
            description?: string;
            price_monthly_cents: number;
            features?: string[];
        }>,
        default: () => []
    }
});

const page = usePage();
const settings = page.props.site_settings as Record<string, string> || {};
const billingCycle = ref<'monthly' | 'annual'>('monthly')

function displayPrice(cents: number): string {
  if (!cents) return 'Free'
  if (billingCycle.value === 'annual') {
    const annual = cents * 10 // 2 months free
    const monthly = Math.round(annual / 12)
    return '₦' + (monthly / 100).toLocaleString('en-US')
  }
  return '₦' + (cents / 100).toLocaleString('en-US')
}
</script>

<template>
  <PublicLayout>
    <Head>
        <title>{{ settings.site_title || 'Construction & Real Estate Management Software — BuildFlow' }}</title>
        <meta name="description" :content="settings.site_description || 'The all-in-one ERP platform for real estate developers and construction firms. Track projects, manage sales, and run payroll.'" />
        <meta name="keywords" :content="settings.site_keywords || 'construction erp, real estate crm, construction management software'" />
    </Head>

    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-20 lg:pt-32 pb-24">
      <!-- Background Glows -->
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-full pointer-events-none">
         <div class="absolute top-20 left-10 w-[500px] h-[500px] bg-brand-500/10 rounded-full blur-[100px]"></div>
         <div class="absolute top-40 right-10 w-[400px] h-[400px] bg-indigo-500/10 rounded-full blur-[100px]"></div>
      </div>
      
      <div class="relative mx-auto max-w-7xl px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-sm font-medium text-brand-400 mb-8 backdrop-blur-sm">
          <span class="flex h-2 w-2 rounded-full bg-brand-500 animate-pulse"></span>
          For Real Estate Developers & Construction Teams
        </div>
        
        <h1 class="mx-auto max-w-4xl text-4xl font-bold tracking-tight text-white sm:text-6xl lg:text-7xl">
          Build Smarter.<br />
          <span class="bg-gradient-to-r from-brand-400 to-brand-600 bg-clip-text text-transparent">Grow Faster.</span>
        </h1>
        
        <p class="mx-auto mt-8 max-w-2xl text-lg text-gray-400 leading-relaxed">
           The complete platform to manage construction projects, track sales, and streamline finances. Built for real estate developers and modern construction companies.
        </p>

        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
          <Link href="/register" class="w-full sm:w-auto h-12 px-8 rounded-full bg-brand-500 font-bold text-white shadow-[0_0_20px_rgba(249,115,22,0.3)] hover:shadow-[0_0_30px_rgba(249,115,22,0.5)] hover:-translate-y-1 transition-all flex items-center justify-center">
             Start Free Trial
          </Link>
          <a href="#features" class="w-full sm:w-auto h-12 px-8 rounded-full border border-white/20 font-semibold text-white hover:bg-white/5 transition-colors flex items-center justify-center">
             See How It Works
          </a>
        </div>

        <!-- Dashboard Preview -->
        <div class="mt-20 relative mx-auto max-w-5xl rounded-2xl border border-white/10 bg-neutral-900/50 backdrop-blur-xl p-2 shadow-2xl">
           <div class="aspect-[16/9] rounded-xl bg-neutral-800/50 overflow-hidden relative group cursor-default">
              <img src="/images/dashboard-preview.png" alt="BuildFlow Dashboard" class="w-full h-full object-cover object-left-top rounded-xl shadow-2xl transition-transform group-hover:scale-[1.01] duration-500" />
           </div>
           
           <!-- Decorative glow behind -->
           <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-indigo-600 rounded-2xl opacity-10 blur-xl -z-10"></div>
        </div>
      </div>
    </section>

    <!-- Stats Bar -->
    <section class="py-12 border-y border-white/5 bg-neutral-900/50">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl sm:text-4xl font-bold text-white">6+</div>
                    <div class="mt-1 text-sm text-gray-500">Integrated Modules</div>
                </div>
                <div>
                    <div class="text-3xl sm:text-4xl font-bold text-white">₦0</div>
                    <div class="mt-1 text-sm text-gray-500">To Get Started</div>
                </div>
                <div>
                    <div class="text-3xl sm:text-4xl font-bold text-white">100%</div>
                    <div class="mt-1 text-sm text-gray-500">Secure Platform</div>
                </div>
                <div>
                    <div class="text-3xl sm:text-4xl font-bold text-white">24/7</div>
                    <div class="mt-1 text-sm text-gray-500">Platform Access</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Proof Section -->
    <section class="py-16">
       <div class="mx-auto max-w-7xl px-6 lg:px-8">
           <div class="mx-auto max-w-2xl text-center mb-12">
               <h2 class="text-2xl font-bold text-white sm:text-3xl">Built for how you actually work on site</h2>
               <p class="mt-4 text-gray-400">From site supervisors to project directors, BuildFlow fits your workflow.</p>
           </div>

           <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
               <div class="rounded-2xl border border-white/5 bg-neutral-800/30 p-6">
                   <div class="text-3xl mb-4">📱</div>
                   <h3 class="text-lg font-bold text-white">Log from the Field</h3>
                   <p class="mt-2 text-sm text-gray-400">Site engineers snap photos and log progress right from their phones. No paperwork, no delays.</p>
               </div>
               <div class="rounded-2xl border border-white/5 bg-neutral-800/30 p-6">
                   <div class="text-3xl mb-4">📊</div>
                   <h3 class="text-lg font-bold text-white">Instant Client Reports</h3>
                   <p class="mt-2 text-sm text-gray-400">Generate branded PDF reports and share via secure links. Clients see progress without needing a login.</p>
               </div>
               <div class="rounded-2xl border border-white/5 bg-neutral-800/30 p-6">
                   <div class="text-3xl mb-4">💰</div>
                   <h3 class="text-lg font-bold text-white">Know Where Every Penny Goes</h3>
                   <p class="mt-2 text-sm text-gray-400">Track costs per project, per unit, per week. Budget vs actual spend charts keep you profitable.</p>
               </div>
           </div>
       </div>
    </section>

    <!-- Features Bento Grid -->
    <section id="features" class="py-24 relative">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mb-16 max-w-3xl">
                <h2 class="text-base font-semibold leading-7 text-brand-500">Everything You Need</h2>
                <p class="mt-2 text-4xl font-bold tracking-tight text-white sm:text-5xl">One platform to run your entire real estate business.</p>
                <p class="mt-4 text-lg text-gray-400">From site supervision to client handover — BuildFlow replaces scattered tools with a single, powerful hub.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Feature 1 (Large): Project Tracking -->
                <div class="md:col-span-2 relative p-8 rounded-3xl bg-neutral-800/30 border border-white/5 hover:border-brand-500/30 transition-colors group overflow-hidden">
                    <div class="relative z-10">
                        <div class="h-12 w-12 rounded-xl bg-blue-500/20 flex items-center justify-center mb-6 text-blue-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-2">Project Tracking & Site Logs</h3>
                        <p class="text-gray-400 max-w-md">Monitor every construction project in real-time. Track daily logs, unit progress, tasks, and issues — all with photo uploads from the field.</p>
                    </div>
                    <div class="absolute right-0 bottom-0 w-1/2 h-full bg-gradient-to-t from-neutral-900 to-transparent z-0"></div>
                </div>

                 <!-- Feature 2: CRM & Sales -->
                <div class="relative p-8 rounded-3xl bg-neutral-800/30 border border-white/5 hover:border-brand-500/30 transition-colors group">
                    <div class="h-12 w-12 rounded-xl bg-indigo-500/20 flex items-center justify-center mb-6 text-indigo-400">
                         <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                     <h3 class="text-xl font-bold text-white mb-2">CRM & Sales Pipeline</h3>
                     <p class="text-gray-400 text-sm">Capture leads, manage property listings, track deals, and close sales faster with a pipeline built for real estate.</p>
                </div>

                 <!-- Feature 3: Finance -->
                <div class="relative p-8 rounded-3xl bg-neutral-800/30 border border-white/5 hover:border-brand-500/30 transition-colors group">
                    <div class="h-12 w-12 rounded-xl bg-green-500/20 flex items-center justify-center mb-6 text-green-400">
                         <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                     <h3 class="text-xl font-bold text-white mb-2">Finance & Invoicing</h3>
                     <p class="text-gray-400 text-sm">Create professional invoices, track expenses by project, set budgets, and monitor cash flow — all in one place.</p>
                </div>

                <!-- Feature 4: HR -->
                <div class="relative p-8 rounded-3xl bg-neutral-800/30 border border-white/5 hover:border-brand-500/30 transition-colors group">
                    <div class="h-12 w-12 rounded-xl bg-purple-500/20 flex items-center justify-center mb-6 text-purple-400">
                         <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                     <h3 class="text-xl font-bold text-white mb-2">HR & Payroll</h3>
                     <p class="text-gray-400 text-sm">Manage employees, departments, leave requests, and payroll processing — designed for construction workforces.</p>
                </div>

                <!-- Feature 5 (Large): Reporting -->
                <div class="md:col-span-2 relative p-8 rounded-3xl bg-neutral-800/30 border border-white/5 hover:border-brand-500/30 transition-colors group">
                     <div class="h-12 w-12 rounded-xl bg-brand-500/20 flex items-center justify-center mb-6 text-brand-400">
                         <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                     <h3 class="text-2xl font-bold text-white mb-2">Branded PDF Reports & Shareable Links</h3>
                     <p class="text-gray-400 max-w-md">Generate polished, branded reports for clients and investors. Share progress via secure public links — no login required for stakeholders.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-24 bg-neutral-800/20 border-y border-white/5">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center mb-12">
                <h2 class="text-base font-semibold leading-7 text-brand-500">Pricing</h2>
                <p class="mt-2 text-4xl font-bold tracking-tight text-white sm:text-5xl">Simple, transparent pricing.</p>
                <p class="mt-6 text-lg leading-8 text-gray-400">Start free. Upgrade when your team is ready. No hidden fees.</p>
                <!-- Billing Toggle -->
                <div class="mt-8 flex items-center justify-center">
                    <div class="flex items-center gap-3 rounded-full bg-white/5 border border-white/10 p-1">
                        <button
                            @click="billingCycle = 'monthly'"
                            :class="[
                                'rounded-full px-5 py-2 text-sm font-medium transition-all',
                                billingCycle === 'monthly' ? 'bg-brand-500 text-white shadow-sm' : 'text-gray-400 hover:text-white'
                            ]"
                        >Monthly</button>
                        <button
                            @click="billingCycle = 'annual'"
                            :class="[
                                'rounded-full px-5 py-2 text-sm font-medium transition-all',
                                billingCycle === 'annual' ? 'bg-brand-500 text-white shadow-sm' : 'text-gray-400 hover:text-white'
                            ]"
                        >
                            Annual
                            <span class="ml-1 inline-flex items-center rounded-full bg-green-500/20 px-2 py-0.5 text-[10px] font-bold text-green-400">2 mo free</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div 
                    v-for="plan in plans" 
                    :key="plan.id"
                    class="rounded-3xl border p-8 xl:p-10 relative flex flex-col"
                    :class="[
                        plan.key === 'pro' 
                            ? 'bg-brand-600/10 border-brand-500/50 ring-1 ring-brand-500' 
                            : 'bg-neutral-900 border-white/5 ring-1 ring-white/10'
                    ]"
                >
                    <div v-if="plan.key === 'pro'" class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-brand-500 px-3 py-1 text-sm font-semibold text-white shadow-sm">
                        Most Popular
                    </div>
                    
                    <h3 class="text-lg font-semibold leading-8 text-white">{{ plan.name }}</h3>
                    <p class="mt-4 text-sm leading-6" :class="plan.key === 'pro' ? 'text-gray-300' : 'text-gray-400'">
                        {{ plan.description || 'For growing teams.' }}
                    </p>
                    
                    <p class="mt-6 flex items-baseline gap-x-1">
                        <span class="text-4xl font-bold tracking-tight text-white">
                            {{ displayPrice(plan.price_monthly_cents) }}
                        </span>
                        <span v-if="plan.price_monthly_cents > 0" class="text-sm font-semibold leading-6" :class="plan.key === 'pro' ? 'text-gray-300' : 'text-gray-400'">/month</span>
                    </p>
                    <p v-if="billingCycle === 'annual' && plan.price_monthly_cents > 0" class="mt-1 text-xs text-green-400 font-medium">Billed annually · Save 2 months</p>

                    <Link 
                        :href="plan.key === 'enterprise' ? '/contact' : '/register'" 
                        class="mt-6 block rounded-full py-2.5 px-3 text-center text-sm font-semibold leading-6 shadow-sm hover:bg-opacity-90"
                        :class="[
                            plan.key === 'pro'
                                ? 'bg-brand-500 text-white hover:bg-brand-400'
                                : 'bg-white/10 text-white hover:bg-white/20'
                        ]"
                    >
                        {{ plan.key === 'enterprise' ? 'Contact sales' : (plan.price_monthly_cents === 0 ? 'Get started' : 'Start 14-day trial') }}
                    </Link>

                    <ul class="mt-8 space-y-3 text-sm leading-6" :class="plan.key === 'pro' ? 'text-gray-300' : 'text-gray-400'">
                        <li v-for="feature in (plan.features || [])" :key="feature" class="flex gap-x-3">
                            <span :class="plan.key === 'pro' ? 'text-brand-400' : 'text-brand-500'">✓</span> 
                            {{ feature }}
                        </li>
                         <!-- Fallback features if none in DB -->
                        <template v-if="!plan.features || plan.features.length === 0">
                            <!-- Free Plan -->
                            <template v-if="plan.key === 'free'">
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> 1 Project Only</li>
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> Up to 3 Team Members</li>
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> 200MB Storage</li>
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> Basic Reporting</li>
                                <li class="flex gap-x-3 text-gray-500"><span class="text-gray-500">✕</span> Password Protected Links</li>
                            </template>

                            <!-- Starter Plan -->
                            <template v-if="plan.key === 'starter'">
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> Up to 10 Projects</li>
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> Up to 20 Team Members</li>
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> 2GB Storage</li>
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> PDF Reports & Sharing</li>
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> Secure Password Links</li>
                            </template>
                            
                            <!-- Pro Plan -->
                            <template v-if="plan.key === 'pro'">
                                <li class="flex gap-x-3"><span class="text-brand-400">✓</span> Up to 50 Projects</li>
                                <li class="flex gap-x-3"><span class="text-brand-400">✓</span> Up to 100 Team Members</li>
                                <li class="flex gap-x-3"><span class="text-brand-400">✓</span> 10GB Storage</li>
                                <li class="flex gap-x-3"><span class="text-brand-400">✓</span> Advanced Financial Tools</li>
                                <li class="flex gap-x-3"><span class="text-brand-400">✓</span> Priority Support</li>
                            </template>
                            
                            <!-- Enterprise Plan (Fallback) -->
                            <template v-if="plan.key === 'enterprise'">
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> Unlimited Projects & Members</li>
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> Dedicated Account Manager</li>
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> Custom Integrations</li>
                                <li class="flex gap-x-3"><span class="text-brand-500">✓</span> SLA & Contracts</li>
                            </template>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
             <div class="mx-auto max-w-2xl text-center mb-16">
                <h2 class="text-base font-semibold leading-7 text-brand-500">Support</h2>
                <p class="mt-2 text-4xl font-bold tracking-tight text-white sm:text-5xl">Frequently Asked Questions</p>
            </div>
            
            <div class="mx-auto max-w-3xl divide-y divide-white/10">
                <div class="py-6">
                    <h3 class="flex items-center justify-between text-base font-semibold leading-7 text-white">
                        Is BuildFlow available globally?
                    </h3>
                    <p class="mt-2 text-base leading-7 text-gray-400">Yes! BuildFlow is used by construction teams and real estate developers around the world. Our platform supports multiple currencies and is designed to handle international project workflows.</p>
                </div>
                <div class="py-6">
                    <h3 class="flex items-center justify-between text-base font-semibold leading-7 text-white">
                        Can I upgrade or downgrade anytime?
                    </h3>
                    <p class="mt-2 text-base leading-7 text-gray-400">Absolutely. You can switch plans at any time from your billing settings. Upgrades take effect immediately, and you only pay the difference.</p>
                </div>
                <div class="py-6">
                    <h3 class="flex items-center justify-between text-base font-semibold leading-7 text-white">
                        Is there a free trial?
                    </h3>
                    <p class="mt-2 text-base leading-7 text-gray-400">Yes, all Pro features come with a 14-day free trial. No credit card required to start.</p>
                </div>
                 <div class="py-6">
                    <h3 class="flex items-center justify-between text-base font-semibold leading-7 text-white">
                        Can I export my data?
                    </h3>
                    <p class="mt-2 text-base leading-7 text-gray-400">Absolutely. You can export all your project logs, tasks, and financial data to PDF or CSV at any time.</p>
                </div>
                 <div class="py-6">
                    <h3 class="flex items-center justify-between text-base font-semibold leading-7 text-white">
                        Do you offer enterprise support?
                    </h3>
                    <p class="mt-2 text-base leading-7 text-gray-400">Yes, our Enterprise plan includes a dedicated account manager and priority 24/7 phone support.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-brand-600/10"></div>
        <div class="relative mx-auto max-w-7xl px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold tracking-tight text-white mb-6 sm:text-4xl">Your competitors are already using modern tools.</h2>
            <p class="text-lg text-gray-400 mb-10 max-w-2xl mx-auto sm:text-xl">Don't let disorganization cost you another project. Start managing your sites properly — for free.</p>
            <Link href="/register" class="inline-flex items-center justify-center h-14 px-10 rounded-full bg-white text-neutral-900 font-bold text-lg hover:bg-brand-50 transition-colors shadow-xl">
                Get Started for Free
            </Link>
        </div>
    </section>

  </PublicLayout>
</template>
