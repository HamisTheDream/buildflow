<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps<{
    stats: {
        revenue: number;
        expenses: number;
        profit: number;
        outstanding: number;
        overdue: number;
        pending_expenses: number;
    };
    invoices: Array<any>;
    expenses: Array<any>;
    budgets: Array<any>;
    recent_invoices: Array<any>;
    recent_expenses: Array<any>;
    projects: Array<any>;
}>();

// ── Tab State ──
const activeTab = ref('overview');
const tabs = [
    { id: 'overview', label: 'Overview', icon: '📊' },
    { id: 'invoices', label: 'Invoices', icon: '📄' },
    { id: 'expenses', label: 'Expenses', icon: '💸' },
    { id: 'budgets', label: 'Budgets', icon: '📋' },
];

onMounted(() => {
    const hash = window.location.hash.replace('#', '');
    if (hash && tabs.some(t => t.id === hash)) activeTab.value = hash;
});

const setTab = (id: string) => {
    activeTab.value = id;
    window.location.hash = id;
};

// ── Helpers ──
const formatMoney = (cents: number) => {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', minimumFractionDigits: 0 }).format(cents / 100);
};

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-NG', { day: 'numeric', month: 'short', year: 'numeric' });

const getClientName = (invoice: any) => {
    if (invoice.client_user) return invoice.client_user.name;
    if (invoice.client_lead) return invoice.client_lead.first_name + ' ' + (invoice.client_lead.last_name || '');
    return 'Unknown Client';
};

const invoiceStatusColors: Record<string, string> = {
    draft: 'bg-gray-50 text-gray-600 ring-gray-500/10',
    sent: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    paid: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    overdue: 'bg-red-50 text-red-700 ring-red-600/10',
    void: 'bg-gray-50 text-gray-500 ring-gray-500/10',
};

const expenseStatusColors: Record<string, string> = {
    pending: 'bg-amber-50 text-amber-800 ring-amber-600/20',
    approved: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    paid: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    rejected: 'bg-red-50 text-red-700 ring-red-600/10',
};

// ── Expense actions ──
const updateExpenseStatus = (expense: any, status: string) => {
    if (confirm(`Mark expense as ${status}?`)) {
        router.put(route('finance.expenses.update', expense.id), { status });
    }
};
const deleteExpense = (expense: any) => {
    if (confirm('Delete this expense?')) {
        router.delete(route('finance.expenses.destroy', expense.id));
    }
};

// ── Budget modal ──
const showBudgetModal = ref(false);
const budgetForm = useForm({ name: '', project_id: '', amount: '', start_date: '', end_date: '' });
const submitBudget = () => {
    budgetForm.post(route('finance.budgets.store'), {
        onSuccess: () => { showBudgetModal.value = false; budgetForm.reset(); },
    });
};
const deleteBudget = (id: number) => {
    if (confirm('Delete this budget?')) { router.delete(route('finance.budgets.destroy', id)); }
};
</script>

<template>
    <Head title="Finance" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Finance</h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex gap-x-6 overflow-x-auto" aria-label="Tabs">
                        <button
                            v-for="tab in tabs" :key="tab.id"
                            @click="setTab(tab.id)"
                            :class="[
                                activeTab === tab.id
                                    ? 'border-brand-500 text-brand-600'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                'group inline-flex items-center border-b-2 py-3 px-1 text-sm font-medium whitespace-nowrap transition-colors'
                            ]"
                        >
                            <span class="mr-2">{{ tab.icon }}</span>
                            {{ tab.label }}
                            <span v-if="tab.id === 'expenses' && stats.pending_expenses > 0"
                                class="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700"
                            >{{ stats.pending_expenses }}</span>
                        </button>
                    </nav>
                </div>

                <!-- ═══════════════════ OVERVIEW TAB ═══════════════════ -->
                <div v-if="activeTab === 'overview'" class="space-y-6">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-6 text-white shadow-lg">
                            <div class="text-sm font-medium text-emerald-100">Total Revenue</div>
                            <div class="mt-2 text-2xl font-semibold tracking-tight">{{ formatMoney(stats.revenue) }}</div>
                            <div class="absolute -right-3 -top-3 text-5xl opacity-20">💰</div>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-500 to-red-700 p-6 text-white shadow-lg">
                            <div class="text-sm font-medium text-red-100">Total Expenses</div>
                            <div class="mt-2 text-2xl font-semibold tracking-tight">{{ formatMoney(stats.expenses) }}</div>
                            <div class="absolute -right-3 -top-3 text-5xl opacity-20">💸</div>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg" :class="stats.profit >= 0 ? 'bg-gradient-to-br from-brand-500 to-brand-700' : 'bg-gradient-to-br from-orange-500 to-orange-700'">
                            <div class="text-sm font-medium" :class="stats.profit >= 0 ? 'text-brand-100' : 'text-orange-100'">Net Profit</div>
                            <div class="mt-2 text-2xl font-semibold tracking-tight">{{ formatMoney(stats.profit) }}</div>
                            <div class="absolute -right-3 -top-3 text-5xl opacity-20">📈</div>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 p-6 text-white shadow-lg">
                            <div class="text-sm font-medium text-amber-100">Overdue</div>
                            <div class="mt-2 text-2xl font-semibold tracking-tight">{{ formatMoney(stats.overdue) }}</div>
                            <div class="absolute -right-3 -top-3 text-5xl opacity-20">⚠️</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Recent Invoices -->
                        <div class="bg-white overflow-hidden shadow-sm rounded-xl">
                            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="font-semibold text-gray-900">Recent Invoices</h3>
                                <button type="button" @click="setTab('invoices')" class="text-sm text-brand-600 hover:text-brand-500">View All &rarr;</button>
                            </div>
                            <ul role="list" class="divide-y divide-gray-100">
                                <li v-for="invoice in recent_invoices" :key="invoice.id" class="flex justify-between gap-x-6 py-4 px-6 hover:bg-gray-50">
                                    <div class="flex min-w-0 gap-x-3">
                                        <div class="min-w-0 flex-auto">
                                            <p class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                                {{ invoice.number }}
                                                <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset capitalize" :class="invoiceStatusColors[invoice.status] || 'bg-gray-50 text-gray-600'">{{ invoice.status }}</span>
                                            </p>
                                            <p class="mt-0.5 text-xs text-gray-500">{{ getClientName(invoice) }}</p>
                                        </div>
                                    </div>
                                    <div class="hidden sm:flex sm:flex-col sm:items-end text-right">
                                        <p class="text-sm font-bold text-gray-900">{{ formatMoney(invoice.total_amount_cents) }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDate(invoice.issue_date) }}</p>
                                    </div>
                                </li>
                                <li v-if="recent_invoices.length === 0" class="py-8 text-center text-sm text-gray-400">No invoices yet.</li>
                            </ul>
                        </div>

                        <!-- Recent Expenses -->
                        <div class="bg-white overflow-hidden shadow-sm rounded-xl">
                            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="font-semibold text-gray-900">Recent Expenses</h3>
                                <button type="button" @click="setTab('expenses')" class="text-sm text-brand-600 hover:text-brand-500">View All &rarr;</button>
                            </div>
                            <ul role="list" class="divide-y divide-gray-100">
                                <li v-for="expense in recent_expenses" :key="expense.id" class="flex justify-between gap-x-6 py-4 px-6 hover:bg-gray-50">
                                    <div class="flex min-w-0 gap-x-3">
                                        <div class="min-w-0 flex-auto">
                                            <p class="text-sm font-semibold text-gray-900">{{ expense.description }}</p>
                                            <p class="mt-0.5 text-xs text-gray-500 capitalize">{{ expense.category }}</p>
                                        </div>
                                    </div>
                                    <div class="hidden sm:flex sm:flex-col sm:items-end text-right">
                                        <p class="text-sm font-bold text-gray-900">{{ formatMoney(expense.amount_cents) }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDate(expense.incurred_date) }}</p>
                                    </div>
                                </li>
                                <li v-if="recent_expenses.length === 0" class="py-8 text-center text-sm text-gray-400">No expenses yet.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════ INVOICES TAB ═══════════════════ -->
                <div v-if="activeTab === 'invoices'" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-500">{{ invoices.length }} invoices</p>
                        <Link :href="route('finance.invoices.create')" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 transition">
                            + Create Invoice
                        </Link>
                    </div>

                    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:pl-6">Number</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Client</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Project</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Amount</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">View</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-if="invoices.length === 0">
                                    <td colspan="7" class="py-12 text-center text-sm text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <p class="mb-3 text-gray-500">No invoices found.</p>
                                            <Link :href="route('finance.invoices.create')" class="text-brand-600 font-medium hover:text-brand-700">
                                                Create your first invoice &rarr;
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-brand-600 sm:pl-6">
                                        <Link :href="route('finance.invoices.show', invoice.id)">{{ invoice.number }}</Link>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">{{ getClientName(invoice) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ invoice.project ? invoice.project.name : '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ formatDate(invoice.issue_date) }}
                                        <span v-if="invoice.due_date && new Date(invoice.due_date) < new Date() && invoice.status !== 'paid'" class="text-red-600 text-xs block">
                                            Due: {{ formatDate(invoice.due_date) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-gray-900">{{ formatMoney(invoice.total_amount_cents) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize" :class="invoiceStatusColors[invoice.status] || 'bg-gray-50 text-gray-600'">{{ invoice.status }}</span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm sm:pr-6">
                                        <Link :href="route('finance.invoices.show', invoice.id)" class="text-brand-600 hover:text-brand-800 font-medium">View</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ═══════════════════ EXPENSES TAB ═══════════════════ -->
                <div v-if="activeTab === 'expenses'" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-500">{{ expenses.length }} expenses</p>
                        <Link :href="route('finance.expenses.create')" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 transition">
                            + Record Expense
                        </Link>
                    </div>

                    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:pl-6">Description</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Category</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Project</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Amount</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-if="expenses.length === 0">
                                    <td colspan="7" class="py-12 text-center text-sm text-gray-400">No expenses recorded yet.</td>
                                </tr>
                                <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ expense.description }}
                                        <span v-if="expense.reimbursable_to" class="block text-xs text-gray-500 font-normal">Reimbursable to: {{ expense.reimbursable_to.name }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 capitalize">{{ expense.category }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ expense.project ? expense.project.name : '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ formatDate(expense.incurred_date) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-gray-900">{{ formatMoney(expense.amount_cents) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize" :class="expenseStatusColors[expense.status] || 'bg-gray-50 text-gray-600'">{{ expense.status }}</span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                                        <a v-if="expense.receipt_path" :href="'/storage/' + expense.receipt_path" target="_blank" class="text-brand-600 hover:text-brand-800">Receipt</a>
                                        <template v-if="expense.status === 'pending'">
                                            <button type="button" @click="updateExpenseStatus(expense, 'approved')" class="text-emerald-600 hover:text-emerald-800">Approve</button>
                                            <button type="button" @click="updateExpenseStatus(expense, 'rejected')" class="text-red-600 hover:text-red-800">Reject</button>
                                        </template>
                                        <button type="button" @click="deleteExpense(expense)" class="text-gray-400 hover:text-red-600">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ═══════════════════ BUDGETS TAB ═══════════════════ -->
                <div v-if="activeTab === 'budgets'" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-500">{{ budgets.length }} budgets</p>
                        <button type="button" @click="showBudgetModal = true" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 transition">
                            + Create Budget
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="budget in budgets" :key="budget.id" class="bg-white overflow-hidden shadow-sm rounded-xl p-6 relative group border border-gray-100 hover:border-brand-200 transition-colors">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-semibold text-lg text-gray-900">{{ budget.name }}</h3>
                                    <p class="text-sm text-gray-500 mt-0.5">{{ budget.project.name }}</p>
                                </div>
                                <button type="button" @click="deleteBudget(budget.id)" class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 opacity-0 group-hover:opacity-100 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Duration</span>
                                    <span class="font-medium text-gray-700">{{ formatDate(budget.start_date) }} — {{ formatDate(budget.end_date) }}</span>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1.5">
                                        <span class="font-medium text-gray-700">{{ budget.progress }}% Used</span>
                                        <span class="text-gray-500">{{ formatMoney(budget.spent_amount_cents) }} / {{ formatMoney(budget.total_amount_cents) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div
                                            class="h-2.5 rounded-full transition-all"
                                            :class="budget.progress > 100 ? 'bg-red-600' : (budget.progress > 80 ? 'bg-amber-500' : 'bg-brand-600')"
                                            :style="{ width: Math.min(budget.progress, 100) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="budgets.length === 0" class="col-span-full py-12 text-center text-gray-400 bg-white rounded-xl shadow-sm border border-dashed border-gray-300">
                            No budgets created yet. Click "Create Budget" to get started.
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ═══════════════════ BUDGET MODAL ═══════════════════ -->
        <div v-if="showBudgetModal" class="relative z-50">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showBudgetModal = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-5">Create Budget</h3>
                    <form @submit.prevent="submitBudget" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Budget Name</label>
                            <input v-model="budgetForm.name" type="text" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required placeholder="e.g. Q1 Project Materials">
                            <p v-if="budgetForm.errors.name" class="mt-1 text-xs text-red-600">{{ budgetForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Project</label>
                            <select v-model="budgetForm.project_id" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                                <option value="">Select Project</option>
                                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                            </select>
                            <p v-if="budgetForm.errors.project_id" class="mt-1 text-xs text-red-600">{{ budgetForm.errors.project_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total Budget (₦)</label>
                            <input v-model="budgetForm.amount" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                            <p v-if="budgetForm.errors.amount" class="mt-1 text-xs text-red-600">{{ budgetForm.errors.amount }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input v-model="budgetForm.start_date" type="date" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input v-model="budgetForm.end_date" type="date" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showBudgetModal = false" class="text-gray-700 text-sm font-medium hover:text-gray-900 px-3 py-2">Cancel</button>
                            <button type="submit" :disabled="budgetForm.processing" class="bg-brand-600 text-white text-sm font-semibold rounded-lg px-4 py-2 hover:bg-brand-500 disabled:opacity-50 transition">
                                {{ budgetForm.processing ? 'Creating...' : 'Create Budget' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
