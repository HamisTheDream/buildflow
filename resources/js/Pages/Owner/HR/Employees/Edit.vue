<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'

const props = defineProps<{
    employee?: {
        id: number
        name: string
        email: string
        role_type: string
        department_id: number | null
        job_title: string
        status: string
    }
    departments: Array<{ id: number, name: string }>
}>

const isEdit = !!props.employee

const form = useForm({
    name: props.employee?.name || '',
    email: props.employee?.email || '',
    password: '',
    role_type: props.employee?.role_type || 'content_developer',
    department_id: props.employee?.department_id || '',
    job_title: props.employee?.job_title || '',
    status: props.employee?.status || 'active',
})

const submit = () => {
    if (isEdit) {
        form.put(route('owner.hr.employees.update', props.employee!.id))
    } else {
        form.post(route('owner.hr.employees.store'))
    }
}
</script>

<template>
    <OwnerLayout>
        <Head :title="isEdit ? 'Edit Employee' : 'Add Employee'" />

        <div class="mb-6">
            <Link :href="route('owner.hr.employees.index')" class="text-brand-600 hover:text-brand-800 text-sm font-medium">&larr; Back to Employees</Link>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ isEdit ? 'Edit Employee' : 'Add New Employee' }}</h1>
        </div>

        <div class="max-w-2xl bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password {{ isEdit ? '(Leave blank to keep current)' : '' }}</label>
                    <input v-model="form.password" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" :required="!isEdit">
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <select v-model="form.role_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            <option value="admin">Administrator</option>
                            <option value="content_developer">Content Developer</option>
                            <option value="sales_rep">Sales Rep</option>
                            <option value="support_agent">Support Agent</option>
                        </select>
                        <p v-if="form.errors.role_type" class="mt-1 text-xs text-red-600">{{ form.errors.role_type }}</p>
                    </div>

                    <!-- Department -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <select v-model="form.department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            <option value="">None</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                        </select>
                        <p v-if="form.errors.department_id" class="mt-1 text-xs text-red-600">{{ form.errors.department_id }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Job Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Job Title</label>
                        <input v-model="form.job_title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        <p v-if="form.errors.job_title" class="mt-1 text-xs text-red-600">{{ form.errors.job_title }}</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        <p v-if="form.errors.status" class="mt-1 text-xs text-red-600">{{ form.errors.status }}</p>
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-100 pt-6">
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 disabled:opacity-50 transition-all">
                        {{ form.processing ? 'Saving...' : (isEdit ? 'Update Employee' : 'Create Employee') }}
                    </button>
                </div>
            </form>
        </div>
    </OwnerLayout>
</template>
