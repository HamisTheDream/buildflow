<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    payroll: {
        id: number;
        run_date: string;
        start_date: string;
        end_date: string;
        total_amount_cents: number;
        status: string;
    };
    breakdown: Array<{
        employee_id: number;
        employee_name: string;
        base: number;
        bonus: number;
        deductions: number;
        net: number;
    }>;
}>();

const formatMoney = (amount: number) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatCents = (cents: number) => {
    return formatMoney(cents / 100);
};

const formatDate = (date: string) => {
     return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Payroll Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payroll Details</h2>
                <Link :href="route('hr.dashboard') + '#payroll'" class="text-sm font-semibold text-brand-600 hover:text-brand-500">
                    &larr; Back to History
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Summary -->
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-4">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Run Date</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ formatDate(payroll.run_date) }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Period</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ formatDate(payroll.start_date) }} - {{ formatDate(payroll.end_date) }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Total Paid</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ formatCents(payroll.total_amount_cents) }}</dd>
                        </div>
                         <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-lg font-semibold text-green-600 capitalize">{{ payroll.status }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Breakdown Table -->
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Payment Breakdown</h3>
                    </div>
                     <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Employee</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Base Pay</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Bonus</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Deductions</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Net Pay</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="item in breakdown" :key="item.employee_id">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    {{ item.employee_name }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-500">
                                    {{ formatMoney(item.base) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-500">
                                    {{ formatMoney(item.bonus) }}
                                </td>
                                 <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-red-500">
                                    -{{ formatMoney(item.deductions) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-bold text-gray-900">
                                    {{ formatMoney(item.net) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
