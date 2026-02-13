<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    deals: Array<{
        id: number;
        title: string;
        amount_cents: number;
        stage: string;
        lead: { id: number; names: string } | null;
        property_unit: { id: number; unit_number: string } | null;
        expected_close_date: string;
    }>;
    stages: Record<string, string>;
}>();

const formatMoney = (cents: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(cents / 100);
};

const dealsByStage = computed(() => {
    const grouped: Record<string, typeof props.deals> = {};
    for (const key in props.stages) {
        grouped[key] = [];
    }
    props.deals.forEach(deal => {
        if (grouped[deal.stage]) {
            grouped[deal.stage].push(deal);
        }
    });
    return grouped;
});

const form = useForm({
    stage: '',
});

const onDragStart = (event: DragEvent, dealId: number) => {
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('dealId', dealId.toString());
    }
};

const onDrop = (event: DragEvent, stage: string) => {
    const dealId = event.dataTransfer?.getData('dealId');
    if (dealId) {
        form.stage = stage;
        form.put(route('crm.deals.update', parseInt(dealId)), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Deals" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Deals Pipeline</h2>
                <Link
                    :href="route('crm.deals.create')"
                    class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                >
                    Create Deal
                </Link>
            </div>
        </template>

        <div class="h-[calc(100vh-10rem)] overflow-x-auto overflow-y-hidden p-6">
            <div class="flex h-full gap-6">
                
                <div 
                    v-for="(label, stageKey) in stages" 
                    :key="stageKey"
                    class="flex h-full w-80 flex-col rounded-xl bg-gray-100/50 border border-gray-200"
                    @dragover.prevent
                    @drop="onDrop($event, stageKey as string)"
                >
                    <!-- Column Header -->
                    <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-gray-50/50 rounded-t-xl">
                        <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wider">{{ label }}</h3>
                        <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ dealsByStage[stageKey]?.length || 0 }}
                        </span>
                    </div>

                    <!-- Cards Container -->
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <div 
                            v-for="deal in dealsByStage[stageKey]" 
                            :key="deal.id"
                            class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 cursor-move hover:shadow-md transition-shadow relative group"
                            draggable="true"
                            @dragstart="onDragStart($event, deal.id)"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-medium text-gray-500">#{{ deal.id }}</span>
                                <span class="font-bold text-gray-900">{{ formatMoney(deal.amount_cents) }}</span>
                            </div>
                            
                            <h4 class="font-medium text-gray-900 mb-1">{{ deal.title }}</h4>
                            
                            <div class="text-xs text-gray-500 space-y-1">
                                <p v-if="deal.lead">Lead: {{ deal.lead.names || 'Unknown' }}</p>
                                <p v-if="deal.property_unit">Unit: {{ deal.property_unit.unit_number }}</p>
                                <p v-if="deal.expected_close_date">Close: {{ deal.expected_close_date }}</p>
                            </div>

                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
