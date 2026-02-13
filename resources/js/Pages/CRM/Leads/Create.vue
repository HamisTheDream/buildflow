<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps<{
    users: Array<{ id: number; name: string }>;
}>();

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    source: 'website',
    status: 'new',
    assigned_to: '',
});

    form.post(route('crm.leads.store'), {
        onSuccess: () => form.reset(),
    });
</script>

<template>
    <Head title="Create Lead" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Lead</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium leading-6 text-gray-900">First Name</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.first_name"
                                            type="text" 
                                            name="first_name" 
                                            id="first_name" 
                                            required
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                        <div v-if="form.errors.first_name" class="text-red-500 text-xs mt-1">{{ form.errors.first_name }}</div>
                                    </div>
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-medium leading-6 text-gray-900">Last Name</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.last_name"
                                            type="text" 
                                            name="last_name" 
                                            id="last_name" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <div>
                                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.email"
                                            type="email" 
                                            name="email" 
                                            id="email" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                        <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                                    </div>
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">Phone</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.phone"
                                            type="text" 
                                            name="phone" 
                                            id="phone" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                    </div>
                                </div>
                            </div>

                             <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <div>
                                    <label for="source" class="block text-sm font-medium leading-6 text-gray-900">Source</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.source"
                                            id="source" 
                                            name="source" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="website">Website</option>
                                            <option value="referral">Referral</option>
                                            <option value="walk_in">Walk-in</option>
                                            <option value="phone">Phone</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label for="assigned_to" class="block text-sm font-medium leading-6 text-gray-900">Assign To</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.assigned_to"
                                            id="assigned_to" 
                                            name="assigned_to" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="">Unassigned</option>
                                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="flex items-center justify-end gap-x-6">
                                <Link :href="route('crm.dashboard', { tab: 'leads' })" class="text-sm font-semibold leading-6 text-gray-900">Cancel</Link>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Creating...' : 'Create Lead' }}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
