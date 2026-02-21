<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    clients: Array<{ id: number; name: string }>;
    leads: Array<{ id: number; first_name: string; last_name: string }>;
    projects: Array<{ id: number; name: string }>;
    next_number: string;
}>();

const form = useForm({
    client_type: 'user', // user or lead
    client_user_id: '',
    client_lead_id: '',
    project_id: '',
    issue_date: new Date().toISOString().split('T')[0],
    due_date: '',
    notes: '',
    items: [
        { description: '', quantity: 1, unit_price: 0 }
    ]
});

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0 });
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
});

const submit = () => {
    form.post(route('finance.invoices.store'), {
        onSuccess: () => window.location.href = route('finance.dashboard') + '#invoices',
    });
};
</script>

<template>
    <Head title="Create Invoice" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Invoice {{ next_number }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Client Selection -->
                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium leading-6 text-gray-900">Client Type</label>
                                    <div class="mt-2 flex gap-4">
                                        <div class="flex items-center">
                                            <input id="type_user" name="client_type" type="radio" value="user" v-model="form.client_type" class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-600">
                                            <label for="type_user" class="ml-3 block text-sm font-medium leading-6 text-gray-900">Existing User</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input id="type_lead" name="client_type" type="radio" value="lead" v-model="form.client_type" class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-600">
                                            <label for="type_lead" class="ml-3 block text-sm font-medium leading-6 text-gray-900">Lead</label>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="form.client_type === 'user'">
                                    <label for="client_user" class="block text-sm font-medium leading-6 text-gray-900">Client (User)</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.client_user_id"
                                            id="client_user" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="">Select User...</option>
                                            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div v-else>
                                    <label for="client_lead" class="block text-sm font-medium leading-6 text-gray-900">Client (Lead)</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.client_lead_id"
                                            id="client_lead" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="">Select Lead...</option>
                                            <option v-for="lead in leads" :key="lead.id" :value="lead.id">{{ lead.first_name }} {{ lead.last_name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label for="project" class="block text-sm font-medium leading-6 text-gray-900">Project (Optional)</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.project_id"
                                            id="project" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="">None</option>
                                            <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Dates -->
                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <div>
                                    <label for="issue_date" class="block text-sm font-medium leading-6 text-gray-900">Issue Date</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.issue_date"
                                            type="date" 
                                            id="issue_date" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                    </div>
                                </div>
                                <div>
                                    <label for="due_date" class="block text-sm font-medium leading-6 text-gray-900">Due Date</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.due_date"
                                            type="date" 
                                            id="due_date" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Line Items -->
                            <div class="mt-8">
                                <h3 class="text-base font-semibold leading-7 text-gray-900">Items</h3>
                                <div class="mt-4 space-y-4">
                                    <div v-for="(item, index) in form.items" :key="index" class="flex gap-4 items-start">
                                        <div class="flex-1">
                                            <input 
                                                v-model="item.description"
                                                type="text" 
                                                placeholder="Description"
                                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                                required
                                            >
                                        </div>
                                        <div class="w-24">
                                            <input 
                                                v-model="item.quantity"
                                                type="number" 
                                                placeholder="Qty"
                                                min="1"
                                                step="0.01"
                                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                                required
                                            >
                                        </div>
                                        <div class="w-32">
                                            <input 
                                                v-model="item.unit_price"
                                                type="number" 
                                                placeholder="Price"
                                                min="0"
                                                step="0.01"
                                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                                required
                                            >
                                        </div>
                                        <div class="pt-2">
                                            <button 
                                                type="button" 
                                                @click="removeItem(index)"
                                                class="text-red-500 hover:text-red-700"
                                                :disabled="form.items.length === 1"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="button" @click="addItem" class="mt-4 text-sm text-brand-600 font-medium hover:text-brand-500 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Add Item
                                </button>
                            </div>

                            <!-- Totals -->
                            <div class="flex justify-end pt-6 border-t border-gray-200">
                                <div class="w-64 space-y-3">
                                    <div class="flex justify-between text-base font-semibold text-gray-900">
                                        <span>Total</span>
                                        <span>₦{{ subtotal.toLocaleString('en-NG', { minimumFractionDigits: 2 }) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Notes -->
                            <div>
                                <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Notes (Optional)</label>
                                <div class="mt-2">
                                    <textarea 
                                        v-model="form.notes"
                                        id="notes" 
                                        rows="3" 
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-x-6">
                                <Link :href="route('finance.dashboard') + '#invoices'" class="text-sm font-semibold leading-6 text-gray-900">Cancel</Link>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Creating...' : 'Create Invoice' }}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
