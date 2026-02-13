<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    leads: Array<{
        id: number;
        first_name: string;
        last_name: string;
        email: string;
        phone: string;
        status: string;
        source: string;
        created_at: string;
        assigned_to: {
            id: number;
            name: string;
        } | null;
    }>;
}>();

const statusColors: Record<string, string> = {
    new: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    contacted: 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
    qualified: 'bg-purple-50 text-purple-700 ring-purple-600/20',
    lost: 'bg-red-50 text-red-700 ring-red-600/10',
    converted: 'bg-green-50 text-green-700 ring-green-600/20',
};

const form = useForm({
    status: '',
});

const updateStatus = (lead: any, newStatus: string) => {
    form.status = newStatus;
    form.put(route('crm.leads.update', lead.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Leads" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Leads</h2>
                <Link
                    :href="route('crm.leads.create')"
                    class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                >
                    Add Lead
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Contact</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Source</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Assigned To</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-if="leads.length === 0">
                                <td colspan="6" class="py-10 text-center text-sm text-gray-500">No leads found. Add one to get started.</td>
                            </tr>
                            <tr v-for="lead in leads" :key="lead.id">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    {{ lead.first_name }} {{ lead.last_name }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <div class="flex flex-col">
                                        <span>{{ lead.email }}</span>
                                        <span class="text-xs text-gray-400">{{ lead.phone }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ lead.source }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <div v-if="lead.assigned_to" class="flex items-center gap-2">
                                        <div class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-600">
                                            {{ lead.assigned_to.name.charAt(0) }}
                                        </div>
                                        {{ lead.assigned_to.name }}
                                    </div>
                                    <span v-else class="text-gray-400 italic">Unassigned</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <select 
                                        :value="lead.status"
                                        @change="updateStatus(lead, ($event.target as HTMLSelectElement).value)"
                                        class="block z-10 w-32 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-600 sm:text-sm sm:leading-6"
                                        :class="statusColors[lead.status] || 'bg-white'"
                                    >
                                        <option value="new">New</option>
                                        <option value="contacted">Contacted</option>
                                        <option value="qualified">Qualified</option>
                                        <option value="lost">Lost</option>
                                        <option value="converted">Converted</option>
                                    </select>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <a href="#" class="text-brand-600 hover:text-brand-900">Edit</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
