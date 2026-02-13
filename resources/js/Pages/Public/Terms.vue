<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
</script>

<template>
    <PublicLayout>
        <Head title="Terms of Service — BuildFlow" />

        <div class="relative py-20 px-6 lg:px-8">
            <div class="mx-auto max-w-3xl rounded-2xl bg-neutral-800/40 border border-white/10 p-8 shadow-xl backdrop-blur-sm">
                <h1 class="text-3xl font-bold text-white mb-8 border-b border-white/10 pb-4">Terms of Service</h1>

                <div class="prose prose-invert max-w-none text-gray-300 prose-headings:text-white prose-h3:text-lg prose-h3:font-semibold prose-p:leading-7">
                    <p class="text-sm text-gray-500">Last updated: February 2026</p>

                    <h3>1. Introduction</h3>
                    <p>Welcome to BuildFlow ("we", "our", "us"). By accessing our website at buildflow.app and using our platform, you agree to be bound by these Terms of Service. BuildFlow provides cloud-based construction and real estate project management software for organizations operating in Nigeria and across Africa.</p>

                    <h3>2. Account Terms</h3>
                    <p>You are responsible for maintaining the security of your account credentials. Each organization account is managed by an account owner. BuildFlow cannot and will not be liable for any loss or damage resulting from your failure to maintain account security.</p>

                    <h3>3. Billing & Payments</h3>
                    <p>Paid plans are billed in Nigerian Naira (₦) on a monthly or annual basis via Paystack. Your subscription begins immediatley upon successful payment. You may cancel your subscription at any time from your organization's billing settings. Refunds are handled on a case-by-case basis.</p>

                    <h3>4. Your Data</h3>
                    <p>You retain full ownership of all data you upload to BuildFlow, including project logs, financial records, CRM data, and HR information. We do not sell, share, or monetize your data. Upon account a deletion request, we will remove your data within 30 days.</p>

                    <h3>5. Acceptable Use</h3>
                    <p>You agree not to misuse the platform, including but not limited to: uploading malicious content, attempting to gain unauthorized access, or using the service for any illegal purpose. We reserve the right to suspend accounts that violate these terms.</p>

                    <h3>6. Service Modifications</h3>
                    <p>BuildFlow reserves the right to modify, suspend, or discontinue the Service (or any part thereof) at any time. We will provide reasonable notice for any significant changes that may affect your use of the platform.</p>

                    <h3>7. Limitation of Liability</h3>
                    <p>The Service is provided on an "as is" and "as available" basis. BuildFlow shall not be liable for any indirect, incidental, or consequential damages arising from your use of the platform. Our total liability shall not exceed the amount paid by you in the 12 months preceding the claim.</p>

                    <h3>8. Contact</h3>
                    <p>If you have questions about these Terms, please contact us at <a href="mailto:hello@buildflow.app" class="text-brand-400 hover:text-brand-300">hello@buildflow.app</a>.</p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
