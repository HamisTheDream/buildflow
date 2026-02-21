<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import { ref } from 'vue'

const props = defineProps<{
    expenses: { data: any[], links: any[] }
}>()

const isModalOpen = ref(false)
const editingExpense = ref<any>(null)

const form = useForm({
    title: '',
    amount: 0,
    date: new Date().toISOString().slice(0, 10),
    category: '',
    details: ''
})

const openCreateModal = () => {
    editingExpense.value = null
    form.reset()
    isModalOpen.value = true
}

const openEditModal = (expense: any) => {
    editingExpense.value = expense
    form.title = expense.title
    form.amount = expense.amount_cents / 100
    form.date = expense.date
    form.category = expense.category
    form.details = expense.details
    isModalOpen.value = true
}

const submit = () => {
    if (editingExpense.value) {
        form.put(`/owner/finance/expenses/${editingExpense.value.id}`, {
            onSuccess: () => isModalOpen.value = false
        })
    } else {
        form.post('/owner/finance/expenses', {
            onSuccess: () => isModalOpen.value = false
        })
    }
}

const deleteExpense = (id: number) => {
    if (confirm('Are you sure you want to delete this expense?')) {
        form.delete(`/owner/finance/expenses/${id}`)
    }
}

const formatCurrency = (cents: number) => {
    return (cents / 100).toLocaleString('en-NG', { style: 'currency', currency: 'NGN' })
}
</script>

<template>
    <OwnerLayout>
        <Head title="Manage Expenses" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Expenses</h1>
                    <p class="mt-1 text-sm text-gray-500">Track company spending.</p>
                </div>
                <button type="button" @click="openCreateModal" class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition">
                    New Expense
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="expense in expenses.data" :key="expense.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ expense.title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatCurrency(expense.amount_cents) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(expense.date).toLocaleDateString() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ expense.category || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button type="button" @click="openEditModal(expense)" class="text-brand-600 hover:text-brand-900 mr-4">Edit</button>
                                <button type="button" @click="deleteExpense(expense.id)" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                         <tr v-if="expenses.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No expenses recorded.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="isModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            {{ editingExpense ? 'Edit Expense' : 'New Expense' }}
                        </h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900">
                                <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700">Amount (NGN)</label>
                                <input v-model.number="form.amount" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900">
                                <div v-if="form.errors.amount" class="text-red-500 text-xs mt-1">{{ form.errors.amount }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <input v-model="form.date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700">Category</label>
                                <input v-model="form.category" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900" placeholder="e.g. Marketing, Server, Tools">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700">Details</label>
                                <textarea v-model="form.details" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button @click="submit" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-brand-600 text-base font-medium text-white hover:bg-brand-700 focus:outline-none sm:col-start-2 sm:text-sm">
                            Save
                        </button>
                        <button @click="isModalOpen = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </OwnerLayout>
</template>
