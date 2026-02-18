<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import { ref } from 'vue'

const props = defineProps<{
    plans: Array<{
        id: number
        name: string
        price_monthly_cents: number
        max_projects: number
    }>
    settings: Record<string, string>
}>()

const form = useForm({
    plans: props.plans.map(p => ({
        id: p.id,
        price_monthly_cents: p.price_monthly_cents
    })),
    settings: {
        support_email: props.settings.support_email || '',
        seo_logo: null as File | null,
        site_icon: null as File | null,
        site_title: props.settings.site_title || '',
        site_description: props.settings.site_description || '',
        site_keywords: props.settings.site_keywords || '',
    }
})

const submit = () => {
    form.post('/owner/settings', {
        preserveScroll: true,
    })
}

const handleFileUpload = (event: Event, field: 'seo_logo' | 'site_icon') => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.settings[field] = target.files[0];
    }
}

// Helper to format cents to currency for display (but input is raw cents for now, maybe multiply by 100 or convert)
// Wait, price_monthly_cents IS cents (or kobo). So input 1000 = 10.00
// I'll add a helper to display in human readable but edit in cents? Or edit in major unit and convert.
// Let's edit in major unit (Naira/Dollars) and convert on save? Or just edit raw cents.
// For simplicity, let's edit raw cents but show label.
</script>

<template>
    <OwnerLayout>
        <Head title="Global Settings" />

        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Global Settings</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage pricing and system configuration.</p>
                </div>
                <button 
                    @click="submit" 
                    :disabled="form.processing"
                    class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition disabled:opacity-50"
                >
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>
            </div>

            <!-- Global Configuration -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">System Configuration</h2>
                <div class="grid grid-cols-1 gap-6 max-w-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Support Email</label>
                        <p class="text-xs text-gray-400 mb-2">Used for contact forms and system notifications.</p>
                         <input v-model="form.settings.support_email" type="email" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900" />
                    </div>

                    <!-- SEO & Branding -->
                    <div class="border-t pt-4 mt-2">
                        <h3 class="text-sm font-medium text-gray-900 mb-4">SEO & Branding</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Site Title</label>
                                <input v-model="form.settings.site_title" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900" placeholder="BuildFlow" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Site Description</label>
                                <textarea v-model="form.settings.site_description" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900" placeholder="The operating system for modern construction..."></textarea>
                            </div>

                             <div>
                                <label class="block text-sm font-medium text-gray-700">Keywords (Comma separated)</label>
                                <input v-model="form.settings.site_keywords" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900" placeholder="construction, erp, real estate" />
                            </div>

                            <!-- Site Icon (Favicon) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Site Icon (Favicon)</label>
                                <p class="text-xs text-gray-400 mb-2">Small icon for browser tabs (32x32 or 512x512). Max 1MB.</p>
                                
                                <div v-if="props.settings.site_icon" class="mb-2">
                                     <img :src="'/storage/' + props.settings.site_icon" alt="Current Site Icon" class="h-8 w-8 object-contain border rounded p-1">
                                </div>

                                <input 
                                    type="file" 
                                    @input="(e) => handleFileUpload(e, 'site_icon')"
                                    class="block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-full file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-brand-50 file:text-brand-700
                                        hover:file:bg-brand-100"
                                />
                            </div>

                            <!-- SEO Logo (OG Image) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">SEO Social Image</label>
                                <p class="text-xs text-gray-400 mb-2">Image used for social sharing (1200x630px). Max 2MB.</p>
                                
                                <div v-if="props.settings.seo_logo" class="mb-2">
                                     <img :src="'/storage/' + props.settings.seo_logo" alt="Current SEO Image" class="h-16 w-auto object-contain border rounded p-1">
                                </div>

                                <input 
                                    type="file" 
                                    @input="(e) => handleFileUpload(e, 'seo_logo')"
                                    class="block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-full file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-brand-50 file:text-brand-700
                                        hover:file:bg-brand-100"
                                />
                            </div>
                        </div>
                    </div>

                    <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full h-2 mt-2">
                        {{ form.progress.percentage }}%
                    </progress>
                </div>
            </div>

            <!-- Pricing Plans -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Subscription Plans</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan Name</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price (Cents/Kobo)</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Calculated Display</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(plan, index) in props.plans" :key="plan.id">
                                <td class="px-3 py-4 text-sm font-medium text-gray-900">{{ plan.name }}</td>
                                <td class="px-3 py-4">
                                     <input
                                        v-model.number="form.plans[index].price_monthly_cents" 
                                        type="number" 
                                        class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900" 
                                    />
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-500">
                                    {{ (form.plans[index].price_monthly_cents / 100).toLocaleString('en-US', { style: 'currency', currency: 'NGN' }) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </OwnerLayout>
</template>
