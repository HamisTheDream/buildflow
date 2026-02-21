<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import { ref } from 'vue'

const props = defineProps<{
    salaries: { data: any[], links: any[] }
}>()

const isModalOpen = ref(false)
const editingSalary = ref<any>(null)

const form = useForm({
    employee_name: '',
    role: '',
    amount: 0,
    frequency: 'monthly',
    next_payment_date: '',
    is_active: true
})

const openCreateModal = () => {
    editingSalary.value = null
    form.reset()
    isModalOpen.value = true
}

const openEditModal = (salary: any) => {
    editingSalary.value = salary
    form.employee_name = salary.employee_name
    form.role = salary.role
    form.amount = salary.amount_cents / 100
    form.frequency = salary.frequency
    form.next_payment_date = salary.next_payment_date
    form.is_active = !!salary.is_active
    isModalOpen.value = true
}

const submit = () => {
    if (editingSalary.value) {
        form.put(`/owner/finance/salaries/${editingSalary.value.id}`, {
            onSuccess: () => isModalOpen.value = false
        })
    } else {
        form.post('/owner/finance/salaries', {
            onSuccess: () => isModalOpen.value = false
        })
    }
}

const deleteSalary = (id: number) => {
    if (confirm('Are you sure you want to delete this salary entry?')) {
        form.delete(`/owner/finance/salaries/${id}`)
    }
}

const formatCurrency = (cents: number) => {
    return (cents / 100).toLocaleString('en-NG', { style: 'currency', currency: 'NGN' })
}
</script>

<template>
    <OwnerLayout>
        <Head title="Manage Salaries" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Salaries</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage team payroll.</p>
                </div>
                <button type="button" @click="openCreateModal" class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition">
                    New Salary Entry
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequency</th>
                             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="salary in salaries.data" :key="salary.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ salary.employee_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ salary.role }}</td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatCurrency(salary.amount_cents) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{{ salary.frequency }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="salary.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                    {{ salary.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button type="button" @click="openEditModal(salary)" class="text-brand-600 hover:text-brand-900 mr-4">Edit</button>
                                <button type="button" @click="deleteSalary(salary.id)" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                         <tr v-if="salaries.data.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No salaries found.</td>
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
                            {{ editingSalary ? 'Edit Salary' : 'New Salary' }}
                        </h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Employee Name</label>
                                <input v-model="form.employee_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900">
                                <div v-if="form.errors.employee_name" class="text-red-500 text-xs mt-1">{{ form.errors.employee_name }}</div>
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700">Role</label>
                                <input v-model="form.role" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700">Amount (NGN)</label>
                                <input v-model.number="form.amount" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900">
                                <div v-if="form.errors.amount" class="text-red-500 text-xs mt-1">{{ form.errors.amount }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Frequency</label>
                                <select v-model="form.frequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm bg-white text-gray-900">
                                    <option value="monthly">Monthly</option>
                                    <option value="weekly">Weekly</option>
                                </select>
                            </div>
                            <div class="flex items-center mt-4">
                                <input v-model="form.is_active" type="checkbox" class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded">
                                <label class="ml-2 block text-sm text-gray-900">Is Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="button" @click="submit" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-brand-600 text-base font-medium text-white hover:bg-brand-700 focus:outline-none sm:col-start-2 sm:text-sm">
                            Save
                        </button>
                        <button type="button" @click="isModalOpen = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </OwnerLayout>
</template>
