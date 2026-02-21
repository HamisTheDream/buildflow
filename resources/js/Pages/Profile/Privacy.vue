<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const exportForm = useForm({});

const downloadData = () => {
    // We do a standard GET/POST request that returns a download response
    // Using simple window location to trigger the download directly instead of XHR
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('settings.privacy.export');
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.head.querySelector('meta[name="csrf-token"]').content;
    
    form.appendChild(csrfToken);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};

</script>

<template>
    <Head title="Privacy & Data" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Privacy & Data
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                
                <!-- Data Export -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <section class="max-w-xl space-y-6">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                GDPR Right to Data Portability
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                Request a comprehensive export of your personal information, organization details, and analytical tracking data securely stored by BuildFlow. 
                                The file will download as a JSON package.
                            </p>
                        </header>

                        <div>
                            <PrimaryButton @click="downloadData">
                                Export My Data
                            </PrimaryButton>
                        </div>
                    </section>
                </div>

                <!-- Account Erasure -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <DeleteUserForm class="max-w-xl" />
                </div>
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>
