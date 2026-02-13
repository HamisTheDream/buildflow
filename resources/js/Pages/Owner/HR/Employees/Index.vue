<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import Pagination from '@/Components/Pagination.vue' // Assuming this exists or will use simple links
import { debounce } from 'lodash'

const props = defineProps<{
    employees: {
        data: Array<{
            id: number
            name: string
            email: string
            role_type: string
            status: string
            department: { name: string } | null
            job_title: string
            avatar_path: string
            created_at: string
        }>
        links: any[]
    }
    departments: Array<{ id: number, name: string }>
    filters: {
        search: string
        role: string
        department_id: string
    }
}>

const search = ref(props.filters.search || '')
const role = ref(props.filters.role || '')
const department_id = ref(props.filters.department_id || '')

watch([search, role, department_id], debounce(() => {
    router.get(route('owner.hr.employees.index'), {
        search: search.value,
        role: role.value,
        department_id: department_id.value
    }, { preserveState: true, replace: true })
}, 300))

const roleLabels: Record<string, string> = {
    admin: 'Administrator',
    content_developer: 'Content Dev',
    sales_rep: 'Sales Rep',
    support_agent: 'Support Agent',
}
</script>

<template>
    <OwnerLayout>
        <Head title="Employees" />

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
                <p class="text-sm text-gray-500">Manage your team members and their roles</p>
            </div>
            <div class="flex gap-2">
                <Link :href="route('owner.hr.departments.index')" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    Manage Departments
                </Link>
                <Link :href="route('owner.hr.employees.create')" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">
                    Add Employee
                </Link>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
            <input 
                v-model="search" 
                type="text" 
                placeholder="Search name or email..." 
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
            >
            <select v-model="role" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">All Roles</option>
                <option value="admin">Administrator</option>
                <option value="content_developer">Content Developer</option>
                <option value="sales_rep">Sales Rep</option>
            </select>
            <select v-model="department_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">All Departments</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
            </select>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="employees.data.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">No employees found.</td>
                    </tr>
                    <tr v-for="employee in employees.data" :key="employee.id" class="hover:bg-gray-50 text-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full object-cover" :src="employee.avatar_path ? '/storage/' + employee.avatar_path : `https://ui-avatars.com/api/?name=${employee.name}&background=random`" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ employee.name }}</div>
                                    <div class="text-sm text-gray-500">{{ employee.email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize">
                                {{ roleLabels[employee.role_type] || employee.role_type }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">{{ employee.job_title }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ employee.department?.name || '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize" 
                                :class="employee.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                {{ employee.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link :href="route('owner.hr.employees.edit', employee.id)" class="text-brand-600 hover:text-brand-900 mr-4">Edit</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="border-t border-gray-200 px-4 py-3 sm:px-6" v-if="employees.links.length > 3">
               <!-- Simple pagination implementation or use component -->
               <div class="flex justify-between sm:hidden">
                    <Link v-if="employees.links[0].url" :href="employees.links[0].url" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</Link>
                    <Link v-if="employees.links[employees.links.length - 1].url" :href="employees.links[employees.links.length - 1].url" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</Link>
               </div>
               <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                   <div>
                       <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                           <template v-for="(link, key) in employees.links" :key="key">
                                <Link 
                                    v-if="link.url" 
                                    :href="link.url" 
                                    v-html="link.label"
                                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20 focus:outline-offset-0"
                                    :class="link.active ? 'z-10 bg-brand-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0'"
                                />
                                <span v-else v-html="link.label" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0"></span>
                           </template>
                       </nav>
                   </div>
               </div>
            </div>
        </div>
    </OwnerLayout>
</template>
