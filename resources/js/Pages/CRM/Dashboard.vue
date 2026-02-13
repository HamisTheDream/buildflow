<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    tab: string;
    stats: {
        totalProperties: number;
        totalUnits: number;
        availableUnits: number;
        soldUnits: number;
        reservedUnits: number;
        totalLeads: number;
        pipelineValue: number;
        totalRevenue: number;
    };
    properties: Array<{
        id: number;
        name: string;
        type: string;
        status: string;
        address: string;
        total_units: number;
        units_count: number;
        units: Array<{
            id: number;
            unit_number: string;
            type: string;
            status: string;
            price_cents: number;
            floor_area: number;
        }>;
        project: { id: number; name: string } | null;
    }>;
    leads: Array<{
        id: number;
        first_name: string;
        last_name: string;
        email: string;
        phone: string;
        status: string;
        source: string;
        created_at: string;
        assigned_to: { id: number; name: string } | null;
    }>;
    deals: Array<{
        id: number;
        title: string;
        amount_cents: number;
        stage: string;
        lead: { id: number; first_name: string; last_name: string } | null;
        property_unit: { id: number; unit_number: string } | null;
        expected_close_date: string;
    }>;
    recentLeads: Array<any>;
    recentDeals: Array<any>;
    stages: Record<string, string>;
}>();

const activeTab = ref(props.tab || 'overview');
const expandedProperty = ref<number | null>(null);

const tabs = [
    { key: 'overview', label: 'Overview', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { key: 'inventory', label: 'Inventory', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
    { key: 'leads', label: 'Leads', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
    { key: 'sales', label: 'Sales Pipeline', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
];

const switchTab = (key: string) => {
    activeTab.value = key;
    router.get(route('crm.dashboard'), { tab: key }, { preserveState: true, preserveScroll: true, replace: true });
};

const formatMoney = (cents: number) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 0,
    }).format(cents / 100);
};

const toggleProperty = (id: number) => {
    expandedProperty.value = expandedProperty.value === id ? null : id;
};

// Inventory helpers
const unitStatusColors: Record<string, string> = {
    available: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    reserved: 'bg-amber-50 text-amber-700 ring-amber-600/20',
    sold: 'bg-sky-50 text-sky-700 ring-sky-600/20',
    blocked: 'bg-red-50 text-red-700 ring-red-600/20',
};

const propertyStatusColors: Record<string, string> = {
    planning: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    construction: 'bg-amber-50 text-amber-800 ring-amber-600/20',
    ready: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    sold_out: 'bg-gray-50 text-gray-600 ring-gray-500/10',
};

const typeLabels: Record<string, string> = {
    residential: 'Residential',
    commercial: 'Commercial',
    mixed: 'Mixed Use',
    land: 'Land / Plot',
};

const leadStatusColors: Record<string, string> = {
    new: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    contacted: 'bg-amber-50 text-amber-800 ring-amber-600/20',
    qualified: 'bg-purple-50 text-purple-700 ring-purple-600/20',
    lost: 'bg-red-50 text-red-700 ring-red-600/10',
    converted: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
};

const sourceLabels: Record<string, string> = {
    website: 'Website',
    referral: 'Referral',
    walk_in: 'Walk-in',
    phone: 'Phone',
    other: 'Other',
};

// Sales pipeline
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

// Lead status update
const leadForm = useForm({ status: '' });
const updateLeadStatus = (lead: any, newStatus: string) => {
    leadForm.status = newStatus;
    leadForm.put(route('crm.leads.update', lead.id), { preserveScroll: true });
};

// Drag and drop for deals
const dragForm = useForm({ stage: '' });
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
        dragForm.stage = stage;
        dragForm.put(route('crm.deals.update', parseInt(dealId)), { preserveScroll: true });
    }
};

// Inventory stats
const inventoryRate = computed(() => {
    if (props.stats.totalUnits === 0) return 0;
    return Math.round((props.stats.soldUnits / props.stats.totalUnits) * 100);
});
</script>

<template>
    <Head title="Sales & CRM" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 leading-tight">Sales & CRM</h2>
                    <p class="mt-1 text-sm text-gray-500">Manage your inventory, leads, and sales pipeline</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('crm.properties.create')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Property
                    </Link>
                    <Link
                        :href="route('crm.leads.create')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        New Lead
                    </Link>
                    <Link
                        :href="route('crm.deals.create')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Sale
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Tab Navigation -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="switchTab(tab.key)"
                            :class="[
                                activeTab === tab.key
                                    ? 'border-brand-500 text-brand-600'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                'group inline-flex items-center border-b-2 py-4 px-1 text-sm font-medium transition-colors'
                            ]"
                        >
                            <svg
                                :class="[
                                    activeTab === tab.key ? 'text-brand-500' : 'text-gray-400 group-hover:text-gray-500',
                                    '-ml-0.5 mr-2 h-5 w-5'
                                ]"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon" />
                            </svg>
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- ===== OVERVIEW TAB ===== -->
                <div v-if="activeTab === 'overview'" class="space-y-6">
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                        <div class="relative overflow-hidden rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-50">
                                    <svg class="h-5 w-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Properties</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ stats.totalProperties }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="relative overflow-hidden rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50">
                                    <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Total Units</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ stats.totalUnits }}</p>
                                    <p class="text-xs text-emerald-600 font-medium">{{ stats.availableUnits }} available</p>
                                </div>
                            </div>
                        </div>
                        <div class="relative overflow-hidden rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50">
                                    <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Active Leads</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ stats.totalLeads }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="relative overflow-hidden rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50">
                                    <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Pipeline Value</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ formatMoney(stats.pipelineValue) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Summary + Sales Progress -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Unit Status Breakdown -->
                        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 p-6">
                            <h3 class="text-sm font-semibold text-gray-900 mb-4">Unit Status Breakdown</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded-full bg-emerald-500"></div>
                                        <span class="text-sm text-gray-600">Available</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ stats.availableUnits }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded-full bg-amber-500"></div>
                                        <span class="text-sm text-gray-600">Reserved</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ stats.reservedUnits }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded-full bg-sky-500"></div>
                                        <span class="text-sm text-gray-600">Sold</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ stats.soldUnits }}</span>
                                </div>
                            </div>
                            <!-- Progress Bar -->
                            <div class="mt-5">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Sales Progress</span>
                                    <span>{{ inventoryRate }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5">
                                    <div class="bg-gradient-to-r from-brand-500 to-emerald-500 h-2.5 rounded-full transition-all" :style="{ width: inventoryRate + '%' }"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Leads -->
                        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5">
                            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="text-sm font-semibold text-gray-900">Recent Leads</h3>
                                <button @click="switchTab('leads')" class="text-xs text-brand-600 hover:text-brand-500 font-medium">View All →</button>
                            </div>
                            <ul class="divide-y divide-gray-100">
                                <li v-for="lead in recentLeads" :key="lead.id" class="px-6 py-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ lead.first_name }} {{ lead.last_name }}</p>
                                        <p class="text-xs text-gray-500">{{ lead.email || lead.phone }}</p>
                                    </div>
                                    <span
                                        class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                                        :class="leadStatusColors[lead.status] || 'bg-gray-50 text-gray-600'"
                                    >{{ lead.status }}</span>
                                </li>
                                <li v-if="recentLeads.length === 0" class="px-6 py-6 text-center text-sm text-gray-400">No leads yet</li>
                            </ul>
                        </div>

                        <!-- Active Sales -->
                        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5">
                            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="text-sm font-semibold text-gray-900">Active Sales</h3>
                                <button @click="switchTab('sales')" class="text-xs text-brand-600 hover:text-brand-500 font-medium">View Pipeline →</button>
                            </div>
                            <ul class="divide-y divide-gray-100">
                                <li v-for="deal in recentDeals" :key="deal.id" class="px-6 py-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ deal.title }}</p>
                                        <p class="text-xs text-gray-500">{{ deal.lead ? deal.lead.first_name + ' ' + deal.lead.last_name : 'No buyer' }}</p>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">{{ formatMoney(deal.amount_cents) }}</span>
                                </li>
                                <li v-if="recentDeals.length === 0" class="px-6 py-6 text-center text-sm text-gray-400">No active sales</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ===== INVENTORY TAB ===== -->
                <div v-if="activeTab === 'inventory'" class="space-y-4">

                    <!-- Inventory Quick Stats -->
                    <div class="grid grid-cols-4 gap-4">
                        <div class="bg-white rounded-lg p-4 shadow-sm ring-1 ring-gray-900/5 text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ stats.totalProperties }}</p>
                            <p class="text-xs text-gray-500 mt-1">Properties</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm ring-1 ring-gray-900/5 text-center">
                            <p class="text-2xl font-bold text-emerald-600">{{ stats.availableUnits }}</p>
                            <p class="text-xs text-gray-500 mt-1">Available</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm ring-1 ring-gray-900/5 text-center">
                            <p class="text-2xl font-bold text-amber-600">{{ stats.reservedUnits }}</p>
                            <p class="text-xs text-gray-500 mt-1">Reserved</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm ring-1 ring-gray-900/5 text-center">
                            <p class="text-2xl font-bold text-sky-600">{{ stats.soldUnits }}</p>
                            <p class="text-xs text-gray-500 mt-1">Sold</p>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="properties.length === 0" class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <h3 class="mt-3 text-sm font-semibold text-gray-900">No properties yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Start by adding your first property or estate.</p>
                        <div class="mt-6">
                            <Link
                                :href="route('crm.properties.create')"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-500"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add Your First Property
                            </Link>
                        </div>
                    </div>

                    <!-- Property Cards with Expandable Unit Lists -->
                    <div v-for="property in properties" :key="property.id" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                        <!-- Property Header (clickable to expand) -->
                        <button
                            @click="toggleProperty(property.id)"
                            class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50/50 transition-colors"
                        >
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" :class="property.type === 'land' ? 'bg-amber-50' : 'bg-sky-50'">
                                    <svg v-if="property.type === 'land'" class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <svg v-else class="h-5 w-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div class="min-w-0 text-left">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate">{{ property.name }}</h3>
                                        <span
                                            class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset"
                                            :class="propertyStatusColors[property.status] || 'bg-gray-50 text-gray-600'"
                                        >{{ property.status.replace('_', ' ') }}</span>
                                        <span class="text-xs text-gray-400">{{ typeLabels[property.type] || property.type }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate">{{ property.address || 'No address' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6 shrink-0">
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">{{ property.units_count }} <span class="text-gray-400 font-normal">/ {{ property.total_units }}</span></p>
                                    <p class="text-xs text-gray-500">units tracked</p>
                                </div>
                                <svg
                                    class="h-5 w-5 text-gray-400 transition-transform"
                                    :class="expandedProperty === property.id ? 'rotate-180' : ''"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <!-- Expanded Unit Grid -->
                        <div v-if="expandedProperty === property.id" class="border-t border-gray-100">
                            <div class="px-6 py-3 bg-gray-50/50 flex items-center justify-between">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Units / Plots</p>
                                <Link
                                    :href="route('crm.properties.units.create', property.id)"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-brand-600 hover:text-brand-500"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Unit
                                </Link>
                            </div>

                            <div v-if="property.units.length === 0" class="px-6 py-8 text-center">
                                <p class="text-sm text-gray-400">No units added yet.</p>
                                <Link
                                    :href="route('crm.properties.units.create', property.id)"
                                    class="mt-2 inline-flex items-center text-sm text-brand-600 hover:text-brand-500 font-medium"
                                >Add first unit →</Link>
                            </div>

                            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 p-4">
                                <div
                                    v-for="unit in property.units"
                                    :key="unit.id"
                                    class="relative rounded-lg border p-3 text-center transition-all hover:shadow-md"
                                    :class="{
                                        'border-emerald-200 bg-emerald-50/50': unit.status === 'available',
                                        'border-amber-200 bg-amber-50/50': unit.status === 'reserved',
                                        'border-sky-200 bg-sky-50/50': unit.status === 'sold',
                                        'border-gray-200 bg-gray-50': !['available', 'reserved', 'sold'].includes(unit.status),
                                    }"
                                >
                                    <p class="text-sm font-bold text-gray-900">{{ unit.unit_number }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ unit.type }}</p>
                                    <p v-if="unit.floor_area" class="text-xs text-gray-400">{{ unit.floor_area }} m²</p>
                                    <p class="text-xs font-semibold mt-1" :class="{
                                        'text-emerald-700': unit.status === 'available',
                                        'text-amber-700': unit.status === 'reserved',
                                        'text-sky-700': unit.status === 'sold',
                                    }">{{ formatMoney(unit.price_cents) }}</p>
                                    <span
                                        class="mt-1.5 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider ring-1 ring-inset"
                                        :class="unitStatusColors[unit.status] || 'bg-gray-50 text-gray-600'"
                                    >{{ unit.status }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== LEADS TAB ===== -->
                <div v-if="activeTab === 'leads'" class="space-y-4">
                    <!-- Empty State -->
                    <div v-if="leads.length === 0" class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h3 class="mt-3 text-sm font-semibold text-gray-900">No leads yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Add potential buyers to track your sales pipeline.</p>
                        <div class="mt-6">
                            <Link
                                :href="route('crm.leads.create')"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-500"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                Add First Lead
                            </Link>
                        </div>
                    </div>

                    <!-- Leads Table -->
                    <div v-else class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3 pl-6 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Source</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="lead in leads" :key="lead.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="whitespace-nowrap py-3.5 pl-6 pr-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-brand-700 font-semibold text-xs">
                                                {{ lead.first_name.charAt(0) }}{{ lead.last_name?.charAt(0) || '' }}
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ lead.first_name }} {{ lead.last_name }}</span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-500">
                                        <div>{{ lead.email }}</div>
                                        <div class="text-xs text-gray-400">{{ lead.phone }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-500">
                                        {{ sourceLabels[lead.source] || lead.source }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-500">
                                        <div v-if="lead.assigned_to" class="flex items-center gap-1.5">
                                            <div class="h-5 w-5 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-medium text-gray-600">
                                                {{ lead.assigned_to.name.charAt(0) }}
                                            </div>
                                            {{ lead.assigned_to.name }}
                                        </div>
                                        <span v-else class="text-gray-400 italic text-xs">Unassigned</span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3.5">
                                        <select
                                            :value="lead.status"
                                            @change="updateLeadStatus(lead, ($event.target as HTMLSelectElement).value)"
                                            class="block w-28 rounded-md border-0 py-1 pl-2 pr-8 text-xs font-medium ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-600 bg-white text-gray-900"
                                        >
                                            <option value="new">New</option>
                                            <option value="contacted">Contacted</option>
                                            <option value="qualified">Qualified</option>
                                            <option value="lost">Lost</option>
                                            <option value="converted">Converted</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ===== SALES PIPELINE TAB ===== -->
                <div v-if="activeTab === 'sales'">
                    <div v-if="deals.length === 0" class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="mt-3 text-sm font-semibold text-gray-900">No sales yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Create a sale to start tracking your pipeline.</p>
                        <div class="mt-6">
                            <Link
                                :href="route('crm.deals.create')"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-500"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Create First Sale
                            </Link>
                        </div>
                    </div>

                    <!-- Kanban Pipeline -->
                    <div v-else class="overflow-x-auto -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8">
                        <div class="flex gap-4 min-w-max pb-4">
                            <div
                                v-for="(label, stageKey) in stages"
                                :key="stageKey"
                                class="flex w-72 flex-col rounded-xl bg-gray-50 ring-1 ring-gray-200"
                                style="min-height: 300px;"
                                @dragover.prevent
                                @drop="onDrop($event, stageKey as string)"
                            >
                                <!-- Column Header -->
                                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                                    <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ label }}</h3>
                                    <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-0.5 rounded-full">
                                        {{ dealsByStage[stageKey]?.length || 0 }}
                                    </span>
                                </div>

                                <!-- Deal Cards -->
                                <div class="flex-1 overflow-y-auto p-3 space-y-2.5">
                                    <div
                                        v-for="deal in dealsByStage[stageKey]"
                                        :key="deal.id"
                                        class="bg-white rounded-lg p-3.5 shadow-sm ring-1 ring-gray-200 cursor-move hover:shadow-md transition-shadow"
                                        draggable="true"
                                        @dragstart="onDragStart($event, deal.id)"
                                    >
                                        <div class="flex justify-between items-start mb-1.5">
                                            <span class="text-[10px] font-medium text-gray-400">#{{ deal.id }}</span>
                                            <span class="text-sm font-bold text-gray-900">{{ formatMoney(deal.amount_cents) }}</span>
                                        </div>
                                        <h4 class="text-sm font-medium text-gray-900 mb-1.5">{{ deal.title }}</h4>
                                        <div class="text-xs text-gray-500 space-y-0.5">
                                            <p v-if="deal.lead" class="flex items-center gap-1">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                {{ deal.lead.first_name }} {{ deal.lead.last_name }}
                                            </p>
                                            <p v-if="deal.property_unit" class="flex items-center gap-1">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                                Unit {{ deal.property_unit.unit_number }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
