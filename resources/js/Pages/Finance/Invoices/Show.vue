<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    invoice: {
        id: number;
        number: string;
        issue_date: string;
        due_date: string;
        total_amount_cents: number;
        status: string;
        notes: string;
        meta: { items: Array<{ description: string; quantity: number; unit_price: number }> };
        client_user: { id: number; name: string; email: string; address: string } | null;
        client_lead: { id: number; first_name: string; last_name: string; email: string; address: string } | null;
        project: { id: number; name: string } | null;
    };
}>();

import { useForm } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

const emailForm = useForm({});
const confirmingEmail = ref(false);

const sendEmail = () => {
    confirmingEmail.value = true;
};

const submitEmail = () => {
    emailForm.post(route('finance.invoices.email', props.invoice.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingEmail.value = false;
        }
    });
};

const formatMoney = (cents: number) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 0,
    }).format(cents / 100);
};

const statusColors: Record<string, string> = {
    draft: 'bg-gray-50 text-gray-600 ring-gray-500/10',
    sent: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    paid: 'bg-green-50 text-green-700 ring-green-600/20',
    overdue: 'bg-red-50 text-red-700 ring-red-600/10',
    void: 'bg-gray-50 text-gray-500 ring-gray-500/10 decoration-line-through',
};

const client = computed(() => {
    if (props.invoice.client_user) {
        return {
            name: props.invoice.client_user.name,
            email: props.invoice.client_user.email,
            address: props.invoice.client_user.address || 'No address provided',
        };
    }
    if (props.invoice.client_lead) {
        return {
            name: props.invoice.client_lead.first_name + ' ' + (props.invoice.client_lead.last_name || ''),
            email: props.invoice.client_lead.email,
            address: 'No address provided',
        };
    }
    return { name: 'Unknown Client', email: '', address: '' };
});
</script>

<template>
    <Head :title="invoice.number" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('finance.dashboard') + '#invoices'" class="text-sm text-gray-500 hover:text-gray-700">Invoices</Link>
                    <span class="text-gray-400">/</span>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ invoice.number }}</h2>
                    <span 
                        class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                        :class="statusColors[invoice.status] || 'bg-gray-50 text-gray-600'"
                    >
                        {{ invoice.status.toUpperCase() }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        :href="route('finance.invoices.pdf', invoice.id)"
                        target="_blank"
                        class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 inline-flex items-center"
                    >
                        Download PDF
                    </a>
                    <button
                        v-if="invoice.status === 'draft'"
                        type="button"
                        @click="sendEmail"
                        :disabled="emailForm.processing"
                        class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:opacity-50"
                    >
                        {{ emailForm.processing ? 'Sending...' : 'Send Invoice' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-lg overflow-hidden">
                    
                    <!-- Invoice Header -->
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-base font-semibold leading-7 text-gray-900">Invoice</h3>
                                <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">
                                    Issue Date: {{ new Date(invoice.issue_date).toLocaleDateString() }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">Amount Due</p>
                                <p class="text-2xl font-bold tracking-tight text-gray-900">{{ formatMoney(invoice.total_amount_cents) }}</p>
                                <p v-if="invoice.due_date" class="text-sm text-gray-500 mt-1">
                                    Due: {{ new Date(invoice.due_date).toLocaleDateString() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-6 grid grid-cols-1 sm:grid-cols-2 gap-8">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Bill To</h4>
                            <div class="mt-3 text-sm text-gray-900">
                                <p class="font-semibold">{{ client.name }}</p>
                                <p>{{ client.email }}</p>
                                <p class="whitespace-pre-wrap text-gray-500 mt-1">{{ client.address }}</p>
                            </div>
                        </div>
                        <div v-if="invoice.project">
                            <h4 class="text-sm font-medium text-gray-500">Project</h4>
                            <div class="mt-3 text-sm text-gray-900">
                                <p class="font-semibold">{{ invoice.project.name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="px-6 py-6">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Description</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Qty</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Price</th>
                                    <th scope="col" class="py-3.5 pl-3 pr-4 text-right text-sm font-semibold text-gray-900 sm:pr-0">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="(item, index) in invoice.meta?.items || []" :key="index">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">{{ item.description }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">{{ item.quantity }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">{{ item.unit_price.toFixed(2) }}</td>
                                    <td class="whitespace-nowrap py-4 pl-3 pr-4 text-sm text-gray-900 text-right sm:pr-0">
                                        {{ ((item.quantity * item.unit_price)).toFixed(2) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th scope="row" colspan="3" class="hidden pl-4 pr-3 pt-6 text-right text-sm font-normal text-gray-500 sm:table-cell sm:pl-0">Total</th>
                                    <th scope="row" class="pl-3 pr-4 pt-6 text-right text-sm font-semibold text-gray-900 sm:pr-0">{{ formatMoney(invoice.total_amount_cents) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div v-if="invoice.notes" class="px-6 py-6 border-t border-gray-100">
                        <h4 class="text-sm font-medium text-gray-500">Notes</h4>
                        <p class="mt-2 text-sm text-gray-600">{{ invoice.notes }}</p>
                    </div>

                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="confirmingEmail"
            title="Send Invoice to Client"
            content="This will email the official PDF invoice to the client's registered email address. They will also receive a secure link to view and pay the invoice online. Are you sure you want to proceed?"
            confirm-text="Send Invoice"
            cancel-text="Cancel"
            :processing="emailForm.processing"
            @close="confirmingEmail = false"
            @confirm="submitEmail"
        />
    </AuthenticatedLayout>
</template>
