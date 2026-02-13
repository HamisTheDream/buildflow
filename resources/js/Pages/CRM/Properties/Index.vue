<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    properties: Array<{
        id: number;
        name: string;
        type: string;
        status: string;
        address: string;
        total_units: number;
        units_count: number;
    }>;
}>();

const statusColors = {
    planning: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    construction: 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
    ready: 'bg-green-50 text-green-700 ring-green-600/20',
    sold_out: 'bg-gray-50 text-gray-600 ring-gray-500/10',
};

const typeLabels = {
    residential: 'Residential',
    commercial: 'Commercial',
    mixed: 'Mixed Use',
    land: 'Land / Plot',
};
</script>

<template>
    <Head title="Properties" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Properties</h2>
                <Link
                    :href="route('crm.properties.create')"
                    class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                >
                    Add Property
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="properties.length === 0" class="text-center py-12 bg-white rounded-lg border border-gray-200 shadow-sm">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No properties</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new property.</p>
                    <div class="mt-6">
                        <Link
                            :href="route('crm.properties.create')"
                            class="inline-flex items-center rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500"
                        >
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                            New Property
                        </Link>
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link 
                        v-for="property in properties" 
                        :key="property.id" 
                        :href="route('crm.properties.show', property.id)"
                        class="relative flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow"
                    >
                        <div class="p-6 flex-1">
                            <div class="flex items-center justify-between mb-4">
                                <span 
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="statusColors[property.status] || 'bg-gray-50 text-gray-600'"
                                >
                                    {{ property.status.replace('_', ' ').toUpperCase() }}
                                </span>
                                <span class="text-xs text-gray-500 font-medium">{{ typeLabels[property.type] }}</span>
                            </div>
                            
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 mb-2">{{ property.name }}</h3>
                            
                            <p class="text-sm text-gray-500 line-clamp-2 mb-4">
                                {{ property.address || 'No address provided' }}
                            </p>

                            <div class="grid grid-cols-2 gap-4 mt-auto pt-4 border-t border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-500">Total Units</p>
                                    <p class="font-medium text-gray-900">{{ property.total_units }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Tracked Units</p>
                                    <p class="font-medium text-gray-900">{{ property.units_count }}</p>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
