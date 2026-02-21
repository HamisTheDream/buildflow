<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps<{
    stats: {
        employees: number;
        departments: number;
        payroll_ytd: number;
        on_leave: number;
        pending_leaves: number;
    };
    employees: Array<any>;
    departments: Array<any>;
    payrolls: Array<any>;
    leaves: Array<any>;
    recent_hires: Array<any>;
    upcoming_leaves: Array<any>;
    active_employees: Array<any>;
    org_users: Array<any>;
}>();

// ── Tab State ──
const activeTab = ref('overview');
const tabs = [
    { id: 'overview', label: 'Overview', icon: '📊' },
    { id: 'staff', label: 'Staff', icon: '👥' },
    { id: 'departments', label: 'Departments', icon: '🏢' },
    { id: 'payroll', label: 'Payroll', icon: '💰' },
    { id: 'leave', label: 'Leave', icon: '📅' },
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

const getEmployeeName = (emp: any) => `${emp.first_name} ${emp.last_name}`;

const statusColors: Record<string, string> = {
    active: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    terminated: 'bg-red-50 text-red-700 ring-red-600/10',
    on_leave: 'bg-amber-50 text-amber-700 ring-amber-600/20',
};

const leaveStatusColors: Record<string, string> = {
    pending: 'bg-amber-50 text-amber-800 ring-amber-600/20',
    approved: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    rejected: 'bg-red-50 text-red-700 ring-red-600/10',
};

// ── Department Modal ──
const showDeptModal = ref(false);
const editingDept = ref<any>(null);
const deptForm = useForm({ name: '', manager_id: '' });

const openCreateDept = () => { editingDept.value = null; deptForm.reset(); showDeptModal.value = true; };
const openEditDept = (dept: any) => { editingDept.value = dept; deptForm.name = dept.name; deptForm.manager_id = dept.manager?.id || ''; showDeptModal.value = true; };
const submitDept = () => {
    if (editingDept.value) {
        deptForm.put(route('hr.departments.update', editingDept.value.id), { onSuccess: () => showDeptModal.value = false });
    } else {
        deptForm.post(route('hr.departments.store'), { onSuccess: () => { showDeptModal.value = false; deptForm.reset(); } });
    }
};
const deleteDept = (id: number) => {
    if (confirm('Are you sure? Departments with assigned employees cannot be deleted.')) {
        router.delete(route('hr.departments.destroy', id), { onError: (e) => alert(e.error || 'Failed to delete') });
    }
};

// ── Leave Modal ──
const showLeaveModal = ref(false);
const leaveForm = useForm({ employee_id: '', type: 'vacation', start_date: '', end_date: '', reason: '' });
const submitLeave = () => {
    leaveForm.post(route('hr.leaves.store'), { onSuccess: () => { showLeaveModal.value = false; leaveForm.reset(); } });
};
const updateLeaveStatus = (leave: any, status: string) => {
    if (confirm(`Mark leave as ${status}?`)) {
        router.put(route('hr.leaves.update', leave.id), { status });
    }
};
const deleteLeave = (id: number) => {
    if (confirm('Delete this leave request?')) { router.delete(route('hr.leaves.destroy', id)); }
};
</script>

<template>
    <Head title="HR & Payroll" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">HR & Payroll</h2>
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
                            <span v-if="tab.id === 'leave' && stats.pending_leaves > 0"
                                class="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700"
                            >{{ stats.pending_leaves }}</span>
                        </button>
                    </nav>
                </div>

                <!-- ═══════════════════ OVERVIEW TAB ═══════════════════ -->
                <div v-if="activeTab === 'overview'" class="space-y-6">
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 p-6 text-white shadow-lg">
                            <div class="text-sm font-medium text-brand-100">Active Staff</div>
                            <div class="mt-2 text-3xl font-bold tracking-tight">{{ stats.employees }}</div>
                            <div class="absolute -right-3 -top-3 text-5xl opacity-20">👥</div>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 p-6 text-white shadow-lg">
                            <div class="text-sm font-medium text-blue-100">Departments</div>
                            <div class="mt-2 text-3xl font-bold tracking-tight">{{ stats.departments }}</div>
                            <div class="absolute -right-3 -top-3 text-5xl opacity-20">🏢</div>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-6 text-white shadow-lg">
                            <div class="text-sm font-medium text-emerald-100">Payroll YTD</div>
                            <div class="mt-2 text-3xl font-bold tracking-tight">{{ formatMoney(stats.payroll_ytd) }}</div>
                            <div class="absolute -right-3 -top-3 text-5xl opacity-20">💰</div>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 p-6 text-white shadow-lg">
                            <div class="text-sm font-medium text-amber-100">On Leave</div>
                            <div class="mt-2 text-3xl font-bold tracking-tight">{{ stats.on_leave }}</div>
                            <div class="absolute -right-3 -top-3 text-5xl opacity-20">📅</div>
                        </div>
                    </div>

                    <!-- Two-column: Recent Hires + Upcoming Leaves -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white overflow-hidden shadow-sm rounded-xl">
                            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="font-semibold text-gray-900">Recent Hires</h3>
                                <button type="button" @click="setTab('staff')" class="text-sm text-brand-600 hover:text-brand-500">View All &rarr;</button>
                            </div>
                            <ul role="list" class="divide-y divide-gray-100">
                                <li v-for="emp in recent_hires" :key="emp.id" class="flex justify-between gap-x-6 py-4 px-6 hover:bg-gray-50">
                                    <div class="flex min-w-0 gap-x-3">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-full bg-brand-100 flex items-center justify-center text-xs font-bold text-brand-700">
                                            {{ emp.first_name[0] }}{{ emp.last_name[0] }}
                                        </div>
                                        <div class="min-w-0 flex-auto">
                                            <p class="text-sm font-semibold text-gray-900">{{ emp.first_name }} {{ emp.last_name }}</p>
                                            <p class="mt-0.5 text-xs text-gray-500">{{ emp.job_title || 'No title' }}</p>
                                        </div>
                                    </div>
                                    <div class="hidden sm:flex sm:flex-col sm:items-end text-right">
                                        <p class="text-xs text-gray-500">Hired</p>
                                        <p class="text-sm text-gray-700">{{ formatDate(emp.hire_date) }}</p>
                                    </div>
                                </li>
                                <li v-if="recent_hires.length === 0" class="py-8 text-center text-sm text-gray-400">No recent hires.</li>
                            </ul>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm rounded-xl">
                            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="font-semibold text-gray-900">Upcoming Leave</h3>
                                <button type="button" @click="setTab('leave')" class="text-sm text-brand-600 hover:text-brand-500">View All &rarr;</button>
                            </div>
                            <ul role="list" class="divide-y divide-gray-100">
                                <li v-for="leave in upcoming_leaves" :key="leave.id" class="flex justify-between gap-x-6 py-4 px-6 hover:bg-gray-50">
                                    <div class="flex min-w-0 gap-x-3">
                                        <div class="min-w-0 flex-auto">
                                            <p class="text-sm font-semibold text-gray-900">{{ getEmployeeName(leave.employee) }}</p>
                                            <p class="mt-0.5 text-xs text-gray-500 capitalize">{{ leave.type }} Leave</p>
                                        </div>
                                    </div>
                                    <div class="hidden sm:flex sm:flex-col sm:items-end text-right">
                                        <p class="text-sm text-gray-700">{{ formatDate(leave.start_date) }}</p>
                                        <span
                                            :class="[leaveStatusColors[leave.status] || 'bg-gray-50 text-gray-600', 'mt-1 inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset capitalize']"
                                        >{{ leave.status }}</span>
                                    </div>
                                </li>
                                <li v-if="upcoming_leaves.length === 0" class="py-8 text-center text-sm text-gray-400">No upcoming leaves.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════ STAFF TAB ═══════════════════ -->
                <div v-if="activeTab === 'staff'" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-500">{{ employees.length }} staff members</p>
                        <Link :href="route('hr.employees.create')" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 transition">
                            + Add Employee
                        </Link>
                    </div>

                    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:pl-6">Employee</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Title</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Department</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Edit</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-if="employees.length === 0">
                                    <td colspan="5" class="py-12 text-center text-sm text-gray-400">No employees found. Add your first team member.</td>
                                </tr>
                                <tr v-for="emp in employees" :key="emp.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                        <div class="flex items-center gap-3">
                                            <div v-if="emp.user?.avatar_url" class="h-9 w-9 rounded-full overflow-hidden">
                                                <img :src="emp.user.avatar_url" alt="" class="h-full w-full object-cover" />
                                            </div>
                                            <div v-else class="h-9 w-9 rounded-full bg-brand-100 flex items-center justify-center text-xs font-bold text-brand-700">
                                                {{ emp.first_name[0] }}{{ emp.last_name[0] }}
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ emp.first_name }} {{ emp.last_name }}</div>
                                                <div class="text-gray-500 text-xs">{{ emp.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ emp.job_title || '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ emp.department?.name || '—' }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize"
                                            :class="statusColors[emp.status] || 'bg-gray-50 text-gray-600'"
                                        >{{ emp.status.replace('_', ' ') }}</span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm sm:pr-6">
                                        <Link :href="route('hr.employees.edit', emp.id)" class="text-brand-600 hover:text-brand-800 font-medium">Edit</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ═══════════════════ DEPARTMENTS TAB ═══════════════════ -->
                <div v-if="activeTab === 'departments'" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-500">{{ departments.length }} departments</p>
                        <button type="button" @click="openCreateDept" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 transition">
                            + Add Department
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="dept in departments" :key="dept.id" class="bg-white overflow-hidden shadow-sm rounded-xl p-6 relative group border border-gray-100 hover:border-brand-200 transition-colors">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-semibold text-lg text-gray-900">{{ dept.name }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">Manager: {{ dept.manager ? dept.manager.name : 'Unassigned' }}</p>
                                </div>
                                <div class="flex gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" @click="openEditDept(dept)" class="p-1.5 text-gray-400 hover:text-brand-600 rounded-lg hover:bg-brand-50 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button type="button" @click="deleteDept(dept.id)" class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                {{ dept.employees_count }} {{ dept.employees_count === 1 ? 'Employee' : 'Employees' }}
                            </div>
                        </div>

                        <div v-if="departments.length === 0" class="col-span-full py-12 text-center text-gray-400 bg-white rounded-xl shadow-sm border border-dashed border-gray-300">
                            No departments created yet. Click "Add Department" to get started.
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════ PAYROLL TAB ═══════════════════ -->
                <div v-if="activeTab === 'payroll'" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-500">{{ payrolls.length }} payroll runs</p>
                        <Link :href="route('hr.payroll.create')" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 transition">
                            Run Payroll
                        </Link>
                    </div>

                    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:pl-6">Run Date</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Period</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Total Amount</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">View</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-if="payrolls.length === 0">
                                    <td colspan="5" class="py-12 text-center text-sm text-gray-400">No payroll runs found. Run your first payroll.</td>
                                </tr>
                                <tr v-for="p in payrolls" :key="p.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ formatDate(p.run_date) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ formatDate(p.start_date) }} — {{ formatDate(p.end_date) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-gray-900">{{ formatMoney(p.total_amount_cents) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 capitalize">{{ p.status }}</span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm sm:pr-6">
                                        <Link :href="route('hr.payroll.show', p.id)" class="text-brand-600 hover:text-brand-800 font-medium">Details</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ═══════════════════ LEAVE TAB ═══════════════════ -->
                <div v-if="activeTab === 'leave'" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-500">{{ leaves.length }} leave requests</p>
                        <button type="button" @click="showLeaveModal = true" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 transition">
                            + New Request
                        </button>
                    </div>

                    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:pl-6">Employee</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Dates</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-if="leaves.length === 0">
                                    <td colspan="5" class="py-12 text-center text-sm text-gray-400">No leave requests found.</td>
                                </tr>
                                <tr v-for="leave in leaves" :key="leave.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ getEmployeeName(leave.employee) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600 capitalize">{{ leave.type }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-600">
                                        {{ formatDate(leave.start_date) }} — {{ formatDate(leave.end_date) }}
                                        <p v-if="leave.reason" class="text-xs text-gray-400 mt-0.5 max-w-xs truncate">{{ leave.reason }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize"
                                            :class="leaveStatusColors[leave.status] || 'bg-gray-50 text-gray-600'"
                                        >{{ leave.status }}</span>
                                        <div v-if="leave.approved_by" class="text-xs text-gray-400 mt-0.5">By: {{ leave.approved_by.name }}</div>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                                        <template v-if="leave.status === 'pending'">
                                            <button type="button" @click="updateLeaveStatus(leave, 'approved')" class="text-emerald-600 hover:text-emerald-800">Approve</button>
                                            <button type="button" @click="updateLeaveStatus(leave, 'rejected')" class="text-red-600 hover:text-red-800">Reject</button>
                                            <button type="button" @click="deleteLeave(leave.id)" class="text-gray-400 hover:text-gray-600">Delete</button>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- ═══════════════════ DEPARTMENT MODAL ═══════════════════ -->
        <div v-if="showDeptModal" class="relative z-50">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showDeptModal = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-5">{{ editingDept ? 'Edit' : 'Create' }} Department</h3>
                    <form @submit.prevent="submitDept" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input v-model="deptForm.name" type="text" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Manager</label>
                            <select v-model="deptForm.manager_id" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                                <option value="">None</option>
                                <option v-for="user in org_users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showDeptModal = false" class="text-gray-700 text-sm font-medium hover:text-gray-900 px-3 py-2">Cancel</button>
                            <button type="submit" :disabled="deptForm.processing" class="bg-brand-600 text-white text-sm font-semibold rounded-lg px-4 py-2 hover:bg-brand-500 disabled:opacity-50 transition">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ═══════════════════ LEAVE MODAL ═══════════════════ -->
        <div v-if="showLeaveModal" class="relative z-50">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showLeaveModal = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-5">New Leave Request</h3>
                    <form @submit.prevent="submitLeave" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Employee</label>
                            <select v-model="leaveForm.employee_id" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                                <option value="">Select Employee</option>
                                <option v-for="emp in active_employees" :key="emp.id" :value="emp.id">{{ getEmployeeName(emp) }}</option>
                            </select>
                            <p v-if="leaveForm.errors.employee_id" class="mt-1 text-xs text-red-600">{{ leaveForm.errors.employee_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select v-model="leaveForm.type" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                                <option value="vacation">Vacation</option>
                                <option value="sick">Sick Leave</option>
                                <option value="unpaid">Unpaid Leave</option>
                                <option value="maternity">Maternity</option>
                                <option value="paternity">Paternity</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input v-model="leaveForm.start_date" type="date" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input v-model="leaveForm.end_date" type="date" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reason (Optional)</label>
                            <textarea v-model="leaveForm.reason" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showLeaveModal = false" class="text-gray-700 text-sm font-medium hover:text-gray-900 px-3 py-2">Cancel</button>
                            <button type="submit" :disabled="leaveForm.processing" class="bg-brand-600 text-white text-sm font-semibold rounded-lg px-4 py-2 hover:bg-brand-500 disabled:opacity-50 transition">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
