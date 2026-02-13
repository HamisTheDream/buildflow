<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    employees: Array<{
        id: number;
        name: string;
        base_salary: number;
        payment_frequency: string;
        amount: number;
        bonus: number;
        deductions: number;
    }>;
}>();

const form = useForm({
    run_date: new Date().toISOString().split('T')[0],
    start_date: '',
    end_date: '',
    employees: props.employees.map(e => ({
        id: e.id,
        name: e.name, // Local only
        amount: e.base_salary,
        bonus: 0,
        deductions: 0,
    })),
});

const totalPayroll = computed(() => {
    return form.employees.reduce((acc, curr) => {
        return acc + curr.amount + curr.bonus - curr.deductions;
    }, 0);
});

const formatMoney = (amount: number) => {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 0,
    }).format(amount);
};

const submit = () => {
    form.post(route('hr.payroll.store'), {
        onSuccess: () => window.location.href = route('hr.dashboard') + '#payroll',
    });
};
</script>

<template>
    <Head title="Run Payroll" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Run Payroll</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
                        <!-- Settings -->
                        <div class="lg:col-span-1 space-y-6">
                            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
                                <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">Run Details</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Run Date</label>
                                        <input v-model="form.run_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                                        <p v-if="form.errors.run_date" class="mt-1 text-xs text-red-600">{{ form.errors.run_date }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Period Start</label>
                                        <input v-model="form.start_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                                        <p v-if="form.errors.start_date" class="mt-1 text-xs text-red-600">{{ form.errors.start_date }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Period End</label>
                                        <input v-model="form.end_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                                        <p v-if="form.errors.end_date" class="mt-1 text-xs text-red-600">{{ form.errors.end_date }}</p>
                                    </div>
                                </div>
                                
                                <div class="mt-6 pt-6 border-t border-gray-100">
                                    <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                                        <span>Total:</span>
                                        <span>{{ formatMoney(totalPayroll) }}</span>
                                    </div>
                                    <button 
                                        type="submit" 
                                        :disabled="form.processing"
                                        class="mt-4 w-full rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:opacity-50"
                                    >
                                        {{ form.processing ? 'Processing...' : 'Submit Payroll' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Employees -->
                        <div class="lg:col-span-2">
                             <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                                <div class="p-6">
                                    <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">Employee Breakdown</h3>
                                    <div class="space-y-4">
                                        <div v-for="(emp, index) in form.employees" :key="emp.id" class="grid grid-cols-1 sm:grid-cols-12 gap-4 p-4 border rounded-lg bg-gray-50 items-center">
                                            <div class="sm:col-span-3">
                                                <div class="font-medium text-gray-900">{{ emp.name }}</div>
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500">Base Pay</label>
                                                <input v-model="emp.amount" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm h-8">
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500">Bonus</label>
                                                <input v-model="emp.bonus" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm h-8">
                                            </div>
                                             <div class="sm:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500">Deductions</label>
                                                <input v-model="emp.deductions" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm h-8">
                                            </div>
                                        </div>
                                    </div>
                                    <p v-if="form.employees.length === 0" class="text-sm text-gray-500 text-center py-4">No active employees to pay.</p>
                                </div>
                             </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
