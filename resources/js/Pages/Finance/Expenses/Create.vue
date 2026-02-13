<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps<{
    projects: Array<{ id: number; name: string }>;
    users: Array<{ id: number; name: string }>;
}>();

const form = useForm({
    project_id: '',
    category: 'general',
    amount: '',
    incurred_date: new Date().toISOString().split('T')[0],
    description: '',
    reimbursable_to: '',
    receipt: null as File | null,
});

const submit = () => {
    form.post(route('finance.expenses.store'), {
        onSuccess: () => window.location.href = route('finance.dashboard') + '#expenses',
    });
};

const handleReceiptUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.receipt = target.files[0];
    }
};
</script>

<template>
    <Head title="Record Expense" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Record Expense</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div>
                                <label for="description" class="block text-sm font-medium leading-6 text-gray-900">Description</label>
                                <div class="mt-2">
                                    <input 
                                        v-model="form.description"
                                        type="text" 
                                        id="description" 
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        required
                                    >
                                    <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <div>
                                    <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Amount</label>
                                    <div class="mt-2 relative rounded-md shadow-sm">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <span class="text-gray-500 sm:text-sm">₦</span>
                                        </div>
                                        <input 
                                            v-model="form.amount"
                                            type="number" 
                                            id="amount" 
                                            min="0"
                                            step="0.01"
                                            class="block w-full rounded-md border-0 py-1.5 pl-7 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                            required
                                        >
                                    </div>
                                    <p v-if="form.errors.amount" class="mt-2 text-sm text-red-600">{{ form.errors.amount }}</p>
                                </div>

                                <div>
                                    <label for="incurred_date" class="block text-sm font-medium leading-6 text-gray-900">Date Incurred</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.incurred_date"
                                            type="date" 
                                            id="incurred_date" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                            required
                                        >
                                    </div>
                                    <p v-if="form.errors.incurred_date" class="mt-2 text-sm text-red-600">{{ form.errors.incurred_date }}</p>
                                </div>

                                <div>
                                    <label for="category" class="block text-sm font-medium leading-6 text-gray-900">Category</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.category"
                                            id="category" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="general">General</option>
                                            <option value="material">Material</option>
                                            <option value="labor">Labor</option>
                                            <option value="software">Software</option>
                                            <option value="office">Office</option>
                                            <option value="travel">Travel</option>
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

                                <div>
                                    <label for="reimbursable_to" class="block text-sm font-medium leading-6 text-gray-900">Reimbursable To (Optional)</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.reimbursable_to"
                                            id="reimbursable_to" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="">N/A</option>
                                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label for="receipt" class="block text-sm font-medium leading-6 text-gray-900">Receipt (Optional)</label>
                                    <div class="mt-2">
                                        <input 
                                            @change="handleReceiptUpload"
                                            type="file" 
                                            id="receipt" 
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100"
                                        >
                                    </div>
                                    <p v-if="form.errors.receipt" class="mt-2 text-sm text-red-600">{{ form.errors.receipt }}</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-x-6">
                                <Link :href="route('finance.dashboard') + '#expenses'" class="text-sm font-semibold leading-6 text-gray-900">Cancel</Link>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Saving...' : 'Save Expense' }}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
