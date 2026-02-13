<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

// Global route function from Ziggy
declare function route(name: string, params?: any): string;

const props = defineProps<{
    invoice: {
        id: number;
        number: string;
        issue_date: string;
        due_date: string;
        total_amount_cents: number;
        status: string;
        notes: string;
        currency: string;
        meta: { items: Array<{ description: string; quantity: number; unit_price: number }> };
        client_user: { name: string; email: string; address: string } | null;
        client_lead: { first_name: string; last_name: string; email: string; address: string } | null;
    };
    organization: {
        name: string;
        email: string;
        logo: string | null;
        color: string | null;
    };
    payUrl: string;
}>();

const formatMoney = (cents: number) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: props.invoice.currency || 'NGN',
        minimumFractionDigits: 2,
    }).format(cents / 100);
};

const client = computed(() => {
    if (props.invoice.client_user) {
        return {
            name: props.invoice.client_user.name,
            email: props.invoice.client_user.email,
            address: props.invoice.client_user.address || '',
        };
    }
    if (props.invoice.client_lead) {
        return {
            name: props.invoice.client_lead.first_name + ' ' + (props.invoice.client_lead.last_name || ''),
            email: props.invoice.client_lead.email,
            address: '',
        };
    }
    return { name: 'Valued Client', email: '', address: '' };
});
const paymentForm = useForm({});

const pay = () => {
    paymentForm.post(props.payUrl);
};
</script>

<template>
    <Head :title="`Invoice ${invoice.number}`" />

    <div class="min-h-screen bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <!-- Header -->
                <div class="px-8 py-6 text-white flex justify-between items-center" :style="{ backgroundColor: organization.color || '#ea580c' }">
                    <div class="flex items-center gap-4">
                        <img v-if="organization.logo" :src="organization.logo" alt="Logo" class="h-12 w-auto bg-white rounded p-1" />
                        <div>
                            <h1 class="text-2xl font-bold">{{ organization.name }}</h1>
                            <p class="text-white/80 text-sm mt-1">Invoice #{{ invoice.number }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm opacity-80">Amount Due</p>
                        <p class="text-3xl font-bold">{{ formatMoney(invoice.total_amount_cents) }}</p>
                    </div>
                </div>

                <!-- Body -->
                <div class="px-8 py-10">
                    <div class="flex flex-col sm:flex-row justify-between gap-8 mb-10">
                        <div>
                            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Billed To</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ client.name }}</p>
                            <p class="text-gray-600">{{ client.email }}</p>
                            <p class="text-gray-600 whitespace-pre-wrap">{{ client.address }}</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Invoice Details</h3>
                            <p class="text-gray-900"><span class="text-gray-500 w-24 inline-block">Issued:</span> {{ new Date(invoice.issue_date).toLocaleDateString() }}</p>
                            <p class="text-gray-900"><span class="text-gray-500 w-24 inline-block">Due:</span> {{ new Date(invoice.due_date).toLocaleDateString() }}</p>
                            <p class="mt-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                    :class="{
                                        'bg-green-100 text-green-800': invoice.status === 'paid',
                                        'bg-yellow-100 text-yellow-800': invoice.status === 'sent' || invoice.status === 'draft',
                                        'bg-red-100 text-red-800': invoice.status === 'overdue'
                                    }">
                                    {{ invoice.status }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Items -->
                    <table class="min-w-full divide-y divide-gray-200 mb-8">
                        <thead>
                            <tr>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 pl-0">Description</th>
                                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Qty</th>
                                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Price</th>
                                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 pr-0">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(item, index) in invoice.meta.items" :key="index">
                                <td class="whitespace-nowrap py-4 pl-0 pr-3 text-sm font-medium text-gray-900">{{ item.description }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">{{ item.quantity }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">{{ formatMoney(item.unit_price * 100) }}</td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-0 text-sm text-gray-900 text-right">{{ formatMoney(item.quantity * item.unit_price * 100) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="row" colspan="3" class="hidden pl-0 pr-3 pt-6 text-right text-sm font-normal text-gray-500 sm:table-cell">Total</th>
                                <th scope="row" class="pl-3 pr-0 pt-6 text-right text-base font-bold text-gray-900">{{ formatMoney(invoice.total_amount_cents) }}</th>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Payment Actions -->
                    <div v-if="invoice.status !== 'paid'" class="bg-gray-50 rounded-xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4 mt-8">
                        <div>
                            <h4 class="font-semibold text-gray-900">Ready to pay?</h4>
                            <p class="text-sm text-gray-500">Secure payment via Paystack or Bank Transfer.</p>
                        </div>
                        <div class="flex gap-3 w-full sm:w-auto">
                            <!-- Placeholder buttons for MVP -->
                            <button
                                v-if="invoice.status !== 'paid'"
                                @click="pay"
                                :disabled="paymentForm.processing"
                                class="inline-flex items-center justify-center w-full sm:w-auto rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 disabled:opacity-50"
                            >
                                {{ paymentForm.processing ? 'Processing...' : 'Pay Now' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-500">Sent via BuildFlow</p>
                </div>
            </div>
        </div>
    </div>
</template>
