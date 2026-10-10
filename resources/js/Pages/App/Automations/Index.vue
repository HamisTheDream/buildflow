<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    automations: Array<any>;
    recentLogs: Array<any>;
    schedules: Array<any>;
}>();

const activeTab = ref('automations');
const tabs = [
    { id: 'automations', label: 'Automations', icon: '⚙️' },
    { id: 'schedules', label: 'Payment Schedules', icon: '💳' },
    { id: 'activity', label: 'Activity Log', icon: '📋' },
];

const toggleAutomation = (key: string) => {
    router.post(route('automations.toggle', key), {}, { preserveScroll: true });
};

// ── Payment schedule form ──
const showScheduleModal = ref(false);
const scheduleForm = useForm({
    name: '',
    amount: '',
    frequency: 'monthly',
    interval: 1,
    start_date: new Date().toISOString().slice(0, 10),
    end_date: '',
    max_runs: '',
    description: '',
});
const submitSchedule = () => {
    scheduleForm.post(route('automations.schedules.store'), {
        onSuccess: () => { showScheduleModal.value = false; scheduleForm.reset(); },
        preserveScroll: true,
    });
};
const pauseSchedule = (id: number, status: string) => {
    router.patch(route('automations.schedules.update', id), { status }, { preserveScroll: true });
};
const deleteSchedule = (id: number) => {
    pendingDelete.value = id;
};
const pendingDelete = ref<number | null>(null);
const confirmDeleteSchedule = () => {
    if (pendingDelete.value) {
        router.delete(route('automations.schedules.destroy', pendingDelete.value), { preserveScroll: true });
        pendingDelete.value = null;
    }
};

const formatMoney = (cents: number) => '₦' + Number(cents / 100).toLocaleString('en-NG', { maximumFractionDigits: 0 });
const formatDateTime = (d: string) => d ? new Date(d).toLocaleString('en-NG', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '—';

const statusColors: Record<string, string> = {
    active: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    paused: 'bg-amber-50 text-amber-700 ring-amber-600/20',
    completed: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    cancelled: 'bg-gray-50 text-gray-600 ring-gray-500/10',
};

const automationIcons: Record<string, string> = {
    payment_schedules: '💳',
    payment_reminders: '🔔',
    budget_alerts: '⚠️',
    task_escalation: '📈',
    lead_followup: '🤝',
    maintenance_sla: '🔧',
    auto_payroll: '💰',
    daily_digest: '📰',
};
</script>

<template>
    <Head title="Automations" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Automations</h2>
                <p class="text-sm text-gray-500 hidden sm:block">BuildFlow works while you sleep — no tokens, no gimmicks.</p>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-6">
                    <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                        :class="[activeTab === tab.id ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                        {{ tab.icon }} {{ tab.label }}
                        <span v-if="tab.id === 'schedules'" class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs">{{ schedules.length }}</span>
                    </button>
                </nav>
            </div>

            <!-- ── Automations grid ── -->
            <div v-if="activeTab === 'automations'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                <div v-for="auto in automations" :key="auto.key"
                    class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-5 flex flex-col">
                    <div class="flex items-start justify-between">
                        <div class="text-3xl">{{ automationIcons[auto.key] || '⚙️' }}</div>
                        <button type="button" @click="toggleAutomation(auto.key)"
                            :class="auto.is_enabled ? 'bg-brand-600' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors">
                            <span :class="auto.is_enabled ? 'translate-x-5' : 'translate-x-0'"
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition m-0.5"></span>
                        </button>
                    </div>
                    <h3 class="mt-3 font-semibold text-gray-900">{{ auto.name }}</h3>
                    <p class="mt-1 text-sm text-gray-500 flex-1">{{ auto.description }}</p>
                    <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
                        <span>🕐 {{ auto.schedule }}</span>
                        <span v-if="auto.last_run_at">Last run: {{ formatDateTime(auto.last_run_at) }}</span>
                        <span v-else class="italic">Not run yet</span>
                    </div>
                    <div class="mt-1 text-xs">
                        <span :class="auto.is_enabled ? 'text-emerald-600' : 'text-gray-400'">
                            {{ auto.is_enabled ? '● Active' : '○ Disabled' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- ── Payment schedules ── -->
            <div v-if="activeTab === 'schedules'">
                <div class="flex justify-end mb-4">
                    <button @click="showScheduleModal = true"
                        class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">
                        + New Payment Schedule
                    </button>
                </div>

                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Schedule</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Next run</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="s in schedules" :key="s.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ s.name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ s.client_lead ? (s.client_lead.first_name + ' ' + (s.client_lead.last_name || '')) : (s.client_user?.name || '—') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ formatMoney(s.amount_cents) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 capitalize">{{ s.frequency }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ s.runs_completed }}{{ s.max_runs ? ' / ' + s.max_runs : '' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ s.next_run_date }}</td>
                                <td class="px-4 py-3">
                                    <span :class="statusColors[s.status]" class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize">{{ s.status }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-2 whitespace-nowrap">
                                    <template v-if="pendingDelete === s.id">
                                        <span class="text-xs text-gray-500">Delete?</span>
                                        <button @click="confirmDeleteSchedule" class="text-red-600 font-semibold">Yes</button>
                                        <button @click="pendingDelete = null" class="text-gray-400">No</button>
                                    </template>
                                    <template v-else>
                                        <button v-if="s.status === 'active'" @click="pauseSchedule(s.id, 'paused')" class="text-amber-600 hover:text-amber-800">Pause</button>
                                        <button v-if="s.status === 'paused'" @click="pauseSchedule(s.id, 'active')" class="text-emerald-600 hover:text-emerald-800">Resume</button>
                                        <button @click="deleteSchedule(s.id)" class="text-gray-400 hover:text-red-600">Delete</button>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="!schedules.length">
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-400">No payment schedules yet. Create one to auto-bill installments.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Activity log ── -->
            <div v-if="activeTab === 'activity'">
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Automation</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="log in recentLogs" :key="log.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ formatDateTime(log.created_at) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ automationIcons[log.automation_key] }} {{ log.automation_key.replace(/_/g, ' ') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 font-mono text-xs">{{ log.action }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    <span v-if="log.details?.schedule_name">{{ log.details.schedule_name }} — </span>
                                    <span v-if="log.details?.amount_cents">{{ formatMoney(log.details.amount_cents) }} — </span>
                                    <span v-if="log.details?.recipient">{{ log.details.recipient }} — </span>
                                    <span v-if="log.details?.days_overdue">{{ log.details.days_overdue }}d overdue — </span>
                                    <span class="text-xs text-gray-400">{{ log.entity_type }} #{{ log.entity_id }}</span>
                                </td>
                            </tr>
                            <tr v-if="!recentLogs.length">
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">No automation activity yet. Runs appear here once the scheduler fires.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ── New schedule modal ── -->
        <div v-if="showScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">New Payment Schedule</h3>
                <form @submit.prevent="submitSchedule" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input v-model="scheduleForm.name" required type="text" placeholder="e.g. 12-month plan — Plot A-101"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" />
                        <div v-if="scheduleForm.errors.name" class="text-red-500 text-xs mt-1">{{ scheduleForm.errors.name }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount per installment (₦)</label>
                            <input v-model="scheduleForm.amount" required type="number" min="1" step="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Frequency</label>
                            <select v-model="scheduleForm.frequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start date</label>
                            <input v-model="scheduleForm.start_date" required type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Number of installments</label>
                            <input v-model="scheduleForm.max_runs" type="number" min="1" max="360" placeholder="e.g. 12"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <input v-model="scheduleForm.description" type="text" placeholder="Appears on the generated invoice"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showScheduleModal = false" class="text-sm font-semibold text-gray-600">Cancel</button>
                        <button type="submit" :disabled="scheduleForm.processing"
                            class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 disabled:opacity-50">
                            {{ scheduleForm.processing ? 'Creating…' : 'Create Schedule' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
