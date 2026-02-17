<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps<{
    projects: Array<{ id: number; name: string }>;
}>();

const form = useForm({
    name: '',
    type: 'residential',
    status: 'planning',
    address: '',
    project_id: '',
    total_units: 0,
});

const submit = () => {
    form.post(route('crm.properties.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create Property" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Property</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Property Name</label>
                                <div class="mt-2">
                                    <input 
                                        v-model="form.name"
                                        type="text" 
                                        name="name" 
                                        id="name" 
                                        required
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6" 
                                        placeholder="e.g. Sunrise Apartments"
                                    >
                                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                </div>
                            </div>

                            <!-- Type & Status -->
                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <div>
                                    <label for="type" class="block text-sm font-medium leading-6 text-gray-900">Type</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.type"
                                            id="type" 
                                            name="type" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="residential">Residential</option>
                                            <option value="commercial">Commercial</option>
                                            <option value="mixed">Mixed Use</option>
                                            <option value="land">Land / Plot</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Status</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.status"
                                            id="status" 
                                            name="status" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="planning">Planning</option>
                                            <option value="construction">Construction</option>
                                            <option value="ready">Ready / Completed</option>
                                            <option value="sold_out">Sold Out</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div>
                                <label for="address" class="block text-sm font-medium leading-6 text-gray-900">Address</label>
                                <div class="mt-2">
                                    <textarea 
                                        v-model="form.address"
                                        id="address" 
                                        name="address" 
                                        rows="3" 
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <!-- Project -->
                                <div>
                                    <label for="project" class="block text-sm font-medium leading-6 text-gray-900">Link to Construction Project (Optional)</label>
                                    <div class="mt-2">
                                        <select 
                                            v-model="form.project_id"
                                            id="project" 
                                            name="project" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="">None</option>
                                            <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                                        </select>
                                     </div>
                                </div>
                            
                                <!-- Total Units -->
                                <div>
                                    <label for="total_units" class="block text-sm font-medium leading-6 text-gray-900">Total Units Expected</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.total_units"
                                            type="number" 
                                            name="total_units" 
                                            id="total_units" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-x-6">
                                <Link :href="route('crm.dashboard', { tab: 'inventory' })" class="text-sm font-semibold leading-6 text-gray-900">Cancel</Link>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Creating...' : 'Create Property' }}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
