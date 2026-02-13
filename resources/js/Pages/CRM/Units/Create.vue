<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps<{
    property: {
        id: number;
        name: string;
    };
}>();

const form = useForm({
    unit_number: '',
    type: 'apartment',
    status: 'available',
    floor_area: '',
    price: '',
    bedrooms: '',
    bathrooms: '',
});

    form.post(route('crm.properties.units.store', props.property.id), {
        onSuccess: () => form.reset(),
    });
</script>

<template>
    <Head title="Add Unit" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Add Unit to {{ property.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Unit Number -->
                            <div>
                                <label for="unit_number" class="block text-sm font-medium leading-6 text-gray-900">Unit Number</label>
                                <div class="mt-2">
                                    <input 
                                        v-model="form.unit_number"
                                        type="text" 
                                        name="unit_number" 
                                        id="unit_number" 
                                        required
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6" 
                                        placeholder="e.g. A-101"
                                    >
                                    <div v-if="form.errors.unit_number" class="text-red-500 text-xs mt-1">{{ form.errors.unit_number }}</div>
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
                                            <option value="apartment">Apartment</option>
                                            <option value="villa">Villa</option>
                                            <option value="shop">Shop / Retail</option>
                                            <option value="office">Office</option>
                                            <option value="plot">Plot</option>
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
                                            <option value="available">Available</option>
                                            <option value="reserved">Reserved</option>
                                            <option value="sold">Sold</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Specs -->
                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <div>
                                    <label for="floor_area" class="block text-sm font-medium leading-6 text-gray-900">Floor Area (m²)</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.floor_area"
                                            type="number" 
                                            step="0.01"
                                            name="floor_area" 
                                            id="floor_area" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                    </div>
                                </div>
                                <div>
                                    <label for="price" class="block text-sm font-medium leading-6 text-gray-900">Price ($)</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.price"
                                            type="number" 
                                            step="0.01"
                                            name="price" 
                                            id="price" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2" v-if="['apartment', 'villa'].includes(form.type)">
                                <div>
                                    <label for="bedrooms" class="block text-sm font-medium leading-6 text-gray-900">Bedrooms</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.bedrooms"
                                            type="number" 
                                            name="bedrooms" 
                                            id="bedrooms" 
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        >
                                    </div>
                                </div>
                                <div>
                                    <label for="bathrooms" class="block text-sm font-medium leading-6 text-gray-900">Bathrooms</label>
                                    <div class="mt-2">
                                        <input 
                                            v-model="form.bathrooms"
                                            type="number" 
                                            name="bathrooms" 
                                            id="bathrooms" 
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
                                    {{ form.processing ? 'Saving...' : 'Save Unit' }}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
