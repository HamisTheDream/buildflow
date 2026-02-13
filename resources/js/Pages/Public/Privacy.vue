<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
</script>

<template>
    <PublicLayout>
        <Head title="Privacy Policy — BuildFlow" />

        <div class="relative py-20 px-6 lg:px-8">
            <div class="mx-auto max-w-3xl rounded-2xl bg-neutral-800/40 border border-white/10 p-8 shadow-xl backdrop-blur-sm">
                <h1 class="text-3xl font-bold text-white mb-8 border-b border-white/10 pb-4">Privacy Policy</h1>

                <div class="prose prose-invert max-w-none text-gray-300 prose-headings:text-white prose-h3:text-lg prose-h3:font-semibold prose-p:leading-7">
                    <p class="text-sm text-gray-500">Last updated: February 2026</p>

                    <h3>1. Information We Collect</h3>
                    <p>We collect information you provide when creating an account, setting up your organization, importing project data, or contacting our support team. This includes your name, email address, organization details, and any data you upload to the platform (project logs, financial records, employee information, CRM data).</p>

                    <h3>2. How We Use Your Information</h3>
                    <p>We use collected information to: operate and improve the BuildFlow platform, process billing transactions via Paystack, send important service notifications, provide customer support, and generate anonymized usage analytics to improve our product.</p>

                    <h3>3. Data Storage & Security</h3>
                    <p>Your data is stored on secure, encrypted servers. We implement industry-standard security measures including encryption at rest and in transit, regular security audits, and access controls. All financial transactions are processed through Paystack's PCI-DSS compliant infrastructure.</p>

                    <h3>4. Data Sharing</h3>
                    <p>We do not sell, rent, or trade your personal information. We may share data with: payment processors (Paystack) to complete transactions, hosting infrastructure providers to operate the platform, and law enforcement when legally required.</p>

                    <h3>5. Your Rights</h3>
                    <p>In accordance with the Nigeria Data Protection Regulation (NDPR), you have the right to: access your personal data, request corrections to inaccurate data, request deletion of your data, and export your data in standard formats. To exercise these rights, contact us at the email below.</p>

                    <h3>6. Cookies</h3>
                    <p>BuildFlow uses essential cookies for authentication and session management. We do not use third-party advertising cookies. You can configure your browser to reject cookies, though this may affect platform functionality.</p>

                    <h3>7. Changes to This Policy</h3>
                    <p>We may update this Privacy Policy from time to time. We will notify active users of any material changes via email or in-app notification.</p>

                    <h3>8. Contact Us</h3>
                    <p>For privacy-related inquiries, please contact us at <a href="mailto:hello@buildflow.app" class="text-brand-400 hover:text-brand-300">hello@buildflow.app</a>.</p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
