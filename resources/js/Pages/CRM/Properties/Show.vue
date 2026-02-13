<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    property: {
        id: number;
        name: string;
        type: string;
        status: string;
        address: string;
        total_units: number;
        meta: any;
        project: {
            id: number;
            name: string;
        } | null;
        units: Array<{
            id: number;
            unit_number: string;
            type: string;
            status: string;
            price_cents: number;
            floor_area: number;
        }>;
    };
}>();

const formatMoney = (cents: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD', // TODO: Use Org currency
    }).format(cents / 100);
};

const statusColors: Record<string, string> = {
    planning: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    construction: 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
    ready: 'bg-green-50 text-green-700 ring-green-600/20',
    sold_out: 'bg-gray-50 text-gray-600 ring-gray-500/10',
};

const unitStatusColors: Record<string, string> = {
    available: 'bg-green-50 text-green-700 ring-green-600/20',
    reserved: 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
    sold: 'bg-blue-50 text-blue-700 ring-blue-600/20',
};
</script>

<template>
    <Head :title="property.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                     <div class="flex items-center gap-3">
                        <Link :href="route('crm.properties.index')" class="text-sm text-gray-500 hover:text-gray-700">Properties</Link>
                        <span class="text-gray-400">/</span>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ property.name }}</h2>
                         <span 
                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                            :class="statusColors[property.status] || 'bg-gray-50 text-gray-600'"
                        >
                            {{ property.status.toUpperCase() }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                     <Link
                        class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    >
                        Edit Property
                    </Link>
                    <Link
                        :href="route('crm.properties.units.create', property.id)"
                        class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                    >
                        Add Unit
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Overview Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Units</dt>
                                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ property.total_units }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 truncate">Project Link</dt>
                                <dd class="mt-1 text-lg font-medium tracking-tight text-gray-900">
                                    <Link v-if="property.project" :href="route('projects.show', property.project.id)" class="text-brand-600 hover:text-brand-500">
                                        {{ property.project.name }} &rarr;
                                    </Link>
                                    <span v-else class="text-gray-400">None</span>
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 truncate">Address</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ property.address || 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Units List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 px-4 py-5 sm:px-6 flex justify-between items-center">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Units / Lots</h3>
                    </div>
                    
                    <div v-if="property.units.length === 0" class="text-center py-12">
                         <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No units added</h3>
                        <p class="mt-1 text-sm text-gray-500">Add units to start tracking inventory.</p>
                        <div class="mt-6">
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500"
                            >
                                Add First Unit
                            </button>
                        </div>
                    </div>

                    <ul v-else role="list" class="divide-y divide-gray-100">
                        <li v-for="unit in property.units" :key="unit.id" class="flex items-center justify-between gap-x-6 py-5 px-6 hover:bg-gray-50">
                            <div class="min-w-0">
                                <div class="flex items-start gap-x-3">
                                    <p class="text-sm font-semibold leading-6 text-gray-900">{{ unit.unit_number }}</p>
                                    <p 
                                        class="rounded-md whitespace-nowrap mt-0.5 px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset"
                                        :class="unitStatusColors[unit.status] || 'bg-gray-50 text-gray-600'"
                                    >
                                        {{ unit.status }}
                                    </p>
                                </div>
                                <div class="mt-1 flex items-center gap-x-2 text-xs leading-5 text-gray-500">
                                    <p class="truncate">{{ unit.type }}</p>
                                    <svg viewBox="0 0 2 2" class="h-0.5 w-0.5 fill-current"><circle cx="1" cy="1" r="1" /></svg>
                                    <p class="truncate">{{ unit.floor_area }} m²</p>
                                </div>
                            </div>
                            <div class="flex flex-none items-center gap-x-4">
                                <p class="text-sm font-medium text-gray-900">{{ formatMoney(unit.price_cents) }}</p>
                                <Link 
                                    :href="route('crm.properties.units.destroy', [property.id, unit.id])"
                                    method="delete"
                                    as="button"
                                    class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-red-50 hover:text-red-700"
                                >
                                    Delete
                                </Link>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
