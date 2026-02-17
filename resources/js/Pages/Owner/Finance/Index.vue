<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'

defineProps<{
    metrics: {
        total_revenue: number
        total_expenses: number
        monthly_burn: number
        pnl: number
    }
    recent_deals: Array<{ id: number, title: string, value_cents: number, status: string }>
    recent_expenses: Array<{ id: number, title: string, amount_cents: number, date: string }>
}>()

const formatCurrency = (cents: number) => {
    return (cents / 100).toLocaleString('en-NG', { style: 'currency', currency: 'NGN' })
}
</script>

<template>
    <OwnerLayout>
        <Head title="Finance Dashboard" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Finance Overview</h1>
                    <p class="mt-1 text-sm text-gray-500">Track revenue, expenses, and profitability.</p>
                </div>
                <div class="flex space-x-3">
                    <Link href="/owner/finance/deals" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Manage Deals
                    </Link>
                    <Link href="/owner/finance/expenses" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Manage Expenses
                    </Link>
                    <Link href="/owner/finance/salaries" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Manage Salaries
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                 <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-sm font-medium text-gray-500">Total Revenue (Won Deals)</h3>
                    <p class="mt-2 text-2xl lg:text-3xl font-semibold text-gray-900 truncate">{{ formatCurrency(metrics.total_revenue) }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-sm font-medium text-gray-500">Total Expenses</h3>
                    <p class="mt-2 text-2xl lg:text-3xl font-semibold text-gray-900 truncate">{{ formatCurrency(metrics.total_expenses) }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-sm font-medium text-gray-500">Est. Monthly Burn</h3>
                    <p class="mt-2 text-2xl lg:text-3xl font-semibold text-gray-900 truncate">{{ formatCurrency(metrics.monthly_burn) }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-sm font-medium text-gray-500">Net Profit (Approx)</h3>
                    <p class="mt-2 text-2xl lg:text-3xl font-semibold truncate" :class="metrics.pnl >= 0 ? 'text-green-600' : 'text-red-600'">
                        {{ formatCurrency(metrics.pnl) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Recent Deals -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-semibold text-gray-900">Recent Deals</h3>
                        <Link href="/owner/finance/deals" class="text-sm text-brand-600 hover:text-brand-700">View All</Link>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        <li v-for="deal in recent_deals" :key="deal.id" class="px-6 py-4 flex justify-between items-center">
                            <div>
                                <div class="font-medium text-gray-900">{{ deal.title }}</div>
                                <div class="text-xs text-gray-500 capitalize">{{ deal.status }}</div>
                            </div>
                            <div class="font-medium text-gray-900">{{ formatCurrency(deal.value_cents) }}</div>
                        </li>
                         <li v-if="recent_deals.length === 0" class="px-6 py-8 text-center text-sm text-gray-500">
                            No deals found.
                        </li>
                    </ul>
                </div>

                <!-- Recent Expenses -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-semibold text-gray-900">Recent Expenses</h3>
                        <Link href="/owner/finance/expenses" class="text-sm text-brand-600 hover:text-brand-700">View All</Link>
                    </div>
                    <ul class="divide-y divide-gray-100">
                         <li v-for="expense in recent_expenses" :key="expense.id" class="px-6 py-4 flex justify-between items-center">
                            <div>
                                <div class="font-medium text-gray-900">{{ expense.title }}</div>
                                <div class="text-xs text-gray-500">{{ new Date(expense.date).toLocaleDateString() }}</div>
                            </div>
                            <div class="font-medium text-gray-900">{{ formatCurrency(expense.amount_cents) }}</div>
                        </li>
                        <li v-if="recent_expenses.length === 0" class="px-6 py-8 text-center text-sm text-gray-500">
                            No expenses recorded.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </OwnerLayout>
</template>
