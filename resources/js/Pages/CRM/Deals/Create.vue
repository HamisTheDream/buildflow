<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps<{
    leads: Array<{ id: number; name: string }>;
    units: Array<{ id: number; name: string }>;
}>();

const form = useForm({
    title: '',
    lead_id: '',
    property_unit_id: '',
    amount: '',
    stage: 'prospecting',
    expected_close_date: '',
});

const submit = () => {
    form.post(route('crm.deals.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create Deal" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Deal</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium leading-6 text-gray-900">Deal Title</label>
                                <div class="mt-2">
                                    <input 
                                        v-model="form.title"
                                        type="text" 
                                        name="title" 
                                        id="title" 
                                        required
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6" 
                                        placeholder="e.g. Unit A-101 Sale"
                                    >
                                    <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <!-- Lead -->
                                <div>
                                    <label for="lead" class="block text-sm font-medium leading-6 text-gray-900">Lead</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.lead_id"
                                            id="lead" 
                                            name="lead" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="">Select Lead...</option>
                                            <option v-for="lead in leads" :key="lead.id" :value="lead.id">{{ lead.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Unit -->
                                <div>
                                    <label for="unit" class="block text-sm font-medium leading-6 text-gray-900">Property Unit</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.property_unit_id"
                                            id="unit" 
                                            name="unit" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="">Select Unit...</option>
                                            <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <!-- Amount -->
                                <div>
                                    <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Amount ($)</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.amount"
                                            type="number" 
                                            step="0.01"
                                            name="amount" 
                                            id="amount" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                    </div>
                                </div>

                                <!-- Stage -->
                                <div>
                                    <label for="stage" class="block text-sm font-medium leading-6 text-gray-900">Stage</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.stage"
                                            id="stage" 
                                            name="stage" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="prospecting">Prospecting</option>
                                            <option value="qualification">Qualification</option>
                                            <option value="proposal">Proposal</option>
                                            <option value="negotiation">Negotiation</option>
                                            <option value="closed_won">Closed Won</option>
                                            <option value="closed_lost">Closed Lost</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Close Date -->
                            <div>
                                <label for="close_date" class="block text-sm font-medium leading-6 text-gray-900">Expected Close Date</label>
                                <div class="mt-2">
                                    <input 
                                        v-model="form.expected_close_date"
                                        type="date" 
                                        name="close_date" 
                                        id="close_date" 
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-x-6">
                                <Link :href="route('crm.dashboard', { tab: 'sales' })" class="text-sm font-semibold leading-6 text-gray-900">Cancel</Link>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Creating...' : 'Create Deal' }}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
