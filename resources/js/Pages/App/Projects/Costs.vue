<script setup lang="ts">
import { Head, Link, usePage, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import debounce from 'lodash-es/debounce'

import ProjectLayout from '@/Layouts/ProjectLayout.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { formatMoneyKobo, formatEnum, formatDate } from '@/utils/format'

const props = defineProps<{
    project: any;
    canManage: boolean;
    categories: string[];
    payments: string[];
    statuses: string[];
    units: any[];
    filters: Record<string, string>;
    costs: any;
    metrics: {
        pending_count: number;
        pending_amount: number;
        unpaid_count: number;
        unpaid_amount: number;
        total_spend: number;
        total_budget: number;
    };
    analysis: any;
}>()

const search = ref(props.filters.q || '')
const status = ref(props.filters.status || '')
const category = ref(props.filters.category || '')
const unit = ref(props.filters.unit || '')

const budgetProgress = computed(() => {
    if (props.metrics.total_budget === 0) return 0
    return Math.min(100, Math.round((props.metrics.total_spend / props.metrics.total_budget) * 100))
})

const budgetTone = computed(() => {
    if (budgetProgress.value > 90) return 'red'
    if (budgetProgress.value > 75) return 'amber'
    return 'green'
})

const updateFilters = debounce(() => {
    router.get(
        page.url, 
        { q: search.value, status: status.value, category: category.value, unit: unit.value }, 
        { preserveState: true, preserveScroll: true }
    )
}, 300)

watch([status, category, unit], updateFilters)
watch(search, updateFilters)

const page = usePage<any>()

// Create/Edit Modal
const showModal = ref(false)
const editingCost = ref<any>(null)

const form = useForm({
    cost_date: new Date().toISOString().split('T')[0],
    category: '',
    vendor: '',
    payment_method: 'transfer',
    amount: '',
    reference: '',
    description: '',
    project_unit_id: '',
})

function openCreate() {
    editingCost.value = null
    form.reset()
    
    // Check if new=1 present
    const params = new URLSearchParams(window.location.search)
    if (params.get('new')) {
         // window.history.replaceState({}, '', window.location.pathname)
    }
    
    showModal.value = true
}

function openEdit(cost: any) {
    editingCost.value = cost
    form.cost_date = cost.cost_date || ''
    form.category = cost.category
    form.vendor = cost.vendor || ''
    form.payment_method = cost.payment_method || 'transfer'
    form.amount = cost.amount
    form.reference = cost.reference || ''
    form.description = cost.description || ''
    form.project_unit_id = cost.unit?.id || ''
    showModal.value = true
}

function submit() {
    if (editingCost.value) {
        form.patch(`/app/projects/${props.project.id}/costs/${editingCost.value.id}`, {
            onSuccess: () => showModal.value = false
        })
    } else {
        form.post(`/app/projects/${props.project.id}/costs`, {
            onSuccess: () => showModal.value = false
        })
    }
}

function deleteCost(cost: any) {
     if(!confirm('Are you sure you want to delete this cost entry?')) return
    useForm({}).delete(`/app/projects/${props.project.id}/costs/${cost.id}`)
}

function statusTone(status: string) {
    switch(status) {
        case 'paid': return 'green'
        case 'approved': return 'blue'
        case 'pending': return 'amber'
        case 'rejected': return 'red'
        default: return 'gray'
    }
}
</script>

<template>
  <ProjectLayout :project="project" active="costs">
    <Head :title="project?.name ? `Costs — ${project.name}` : 'Costs'" />

    <div class="space-y-8">
        <!-- Financial Command Center -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- 1. Budget Card -->
            <div class="lg:col-span-2 rounded-2xl bg-slate-900 p-6 text-white shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-32 bg-brand-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                <div class="relative z-10">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Total Spend (Approved)</h3>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-4xl font-bold tracking-tight">{{ formatMoneyKobo(metrics.total_spend) }}</span>
                        <span class="text-lg text-slate-500 font-medium">/ {{ formatMoneyKobo(metrics.total_budget) }}</span>
                    </div>

                    <div class="mt-6">
                        <div class="flex justify-between text-xs font-semibold uppercase tracking-wider mb-2">
                            <span :class="{
                                'text-emerald-400': budgetTone === 'green',
                                'text-amber-400': budgetTone === 'amber',
                                'text-red-400': budgetTone === 'red',
                            }">{{ budgetProgress }}% Budget Used</span>
                            <span class="text-slate-500">Target: 100%</span>
                        </div>
                        <div class="h-3 w-full rounded-full bg-slate-800 overflow-hidden">
                            <div 
                                class="h-full rounded-full transition-all duration-1000 shadow-[0_0_10px_currentColor]"
                                :class="{
                                    'bg-emerald-500 text-emerald-500': budgetTone === 'green',
                                    'bg-amber-500 text-amber-500': budgetTone === 'amber',
                                    'bg-red-500 text-red-500': budgetTone === 'red',
                                }"
                                :style="{ width: `${budgetProgress}%` }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Action Queues -->
            <div class="space-y-4">
                <!-- Pending Approvals -->
                <div 
                    class="rounded-xl p-4 border transition-all cursor-pointer"
                    :class="metrics.pending_count > 0 ? 'bg-amber-50 border-amber-200 hover:bg-amber-100' : 'bg-white border-gray-200 opacity-60'"
                    @click="status = 'pending'"
                >
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wide" :class="metrics.pending_count > 0 ? 'text-amber-700' : 'text-gray-500'">Approvals Queue</span>
                        <span v-if="metrics.pending_count > 0" class="flex h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                    </div>
                    <div class="flex items-end justify-between">
                        <div>
                            <span class="text-2xl font-bold" :class="metrics.pending_count > 0 ? 'text-amber-900' : 'text-gray-400'">{{ metrics.pending_count }}</span>
                            <span class="text-xs ml-1" :class="metrics.pending_count > 0 ? 'text-amber-700' : 'text-gray-400'">Pending</span>
                        </div>
                        <span class="text-sm font-medium" :class="metrics.pending_count > 0 ? 'text-amber-800' : 'text-gray-400'">{{ formatMoneyKobo(metrics.pending_amount) }}</span>
                    </div>
                </div>

                <!-- Unpaid Invoices -->
                <div 
                    class="rounded-xl p-4 border transition-all cursor-pointer"
                    :class="metrics.unpaid_count > 0 ? 'bg-red-50 border-red-200 hover:bg-red-100' : 'bg-white border-gray-200 opacity-60'"
                    @click="status = 'approved'"
                >
                    <div class="flex items-center justify-between mb-2">
                         <span class="text-xs font-bold uppercase tracking-wide" :class="metrics.unpaid_count > 0 ? 'text-red-700' : 'text-gray-500'">Accounts Payable</span>
                    </div>
                     <div class="flex items-end justify-between">
                        <div>
                            <span class="text-2xl font-bold" :class="metrics.unpaid_count > 0 ? 'text-red-900' : 'text-gray-400'">{{ metrics.unpaid_count }}</span>
                            <span class="text-xs ml-1" :class="metrics.unpaid_count > 0 ? 'text-red-700' : 'text-gray-400'">Unpaid</span>
                        </div>
                        <span class="text-sm font-medium" :class="metrics.unpaid_count > 0 ? 'text-red-800' : 'text-gray-400'">{{ formatMoneyKobo(metrics.unpaid_amount) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Costs Manager -->
        <div class="rounded-xl bg-white shadow-sm border border-gray-200">
            <!-- Toolbar -->
            <div class="flex flex-col gap-4 p-5 md:flex-row md:items-center md:justify-between border-b border-gray-100">
                 <div class="flex items-center gap-4 flex-1">
                      <div class="relative w-full max-w-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input 
                            v-model="search"
                            type="text" 
                            class="block w-full rounded-lg border-gray-300 pl-10 focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                            placeholder="Search descriptions, vendors..."
                        >
                      </div>

                      <select v-model="status" class="rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All Statuses</option>
                        <option v-for="s in statuses" :key="s" :value="s">{{ formatEnum(s) }}</option>
                      </select>

                       <select v-model="category" class="rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All Categories</option>
                        <option v-for="c in categories" :key="c" :value="c">{{ formatEnum(c) }}</option>
                      </select>
                      
                      <select v-model="unit" class="rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All Units</option>
                        <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                      </select>
                 </div>

                 <button
                    v-if="project?.id && canManage"
                    @click="openCreate()"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    New Cost
                </button>
            </div>

            <!-- Table -->
             <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <tr v-for="cost in costs.data" :key="cost.id" class="group hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ cost.description || cost.category }}</div>
                                    <div class="text-xs text-gray-500 flex gap-2">
                                        <span>{{ cost.vendor || 'No Vendor' }}</span>
                                        <span v-if="cost.unit" class="text-brand-600 font-medium bg-brand-50 px-1 rounded">{{ cost.unit.name }}</span>
                                    </div>
                                </div>
                                <div v-if="cost.attachments.length > 0" class="flex items-center justify-center h-6 w-6 rounded bg-gray-100 text-gray-500">
                                     <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                </div>
                            </div>
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap">
                             <Badge :text="formatEnum(cost.status)" :tone="statusTone(cost.status)" size="sm" dot />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ formatMoneyKobo(cost.amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(cost.cost_date) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                             <button @click="openEdit(cost)" class="text-gray-600 font-medium hover:underline opacity-0 group-hover:opacity-100 transition-opacity mr-2">
                                Edit
                             </button>
                              <button @click="deleteCost(cost)" class="text-red-600 font-medium hover:underline opacity-0 group-hover:opacity-100 transition-opacity">
                                Delete
                             </button>
                        </td>
                    </tr>
                </tbody>
              </table>

               <div v-if="costs.data.length === 0" class="p-12 text-center text-gray-500">
                    No costs found matching your filters.
               </div>
             </div>
              <div v-if="costs.links" class="p-4 border-t border-gray-100">
                <Pagination :links="costs.links" />
            </div>
        </div>

    </div>
    
     <!-- Modal -->
    <Modal :show="showModal" @close="showModal = false">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-6">{{ editingCost ? 'Edit Cost' : 'New Cost' }}</h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                         <InputLabel value="Amount" />
                         <TextInput type="number" step="0.01" v-model="form.amount" class="mt-1 block w-full" required autofocus />
                    </div>
                     <div>
                        <InputLabel value="Date" />
                         <TextInput type="date" v-model="form.cost_date" class="mt-1 block w-full" required />
                    </div>
                </div>
                
                 <div>
                    <InputLabel value="Description" />
                    <TextArea v-model="form.description" class="mt-1 block w-full" rows="2" placeholder="What is this cost for?" />
                </div>
                
                 <div class="grid grid-cols-2 gap-4">
                     <div>
                        <InputLabel value="Category" />
                        <select v-model="form.category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            <option value="">Select...</option>
                            <option v-for="c in categories" :key="c" :value="c">{{ formatEnum(c) }}</option>
                        </select>
                     </div>
                     <div>
                         <InputLabel value="Unit (Optional)" />
                        <select v-model="form.project_unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Project General</option>
                            <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                     </div>
                 </div>
                 
                  <div class="grid grid-cols-2 gap-4">
                      <div>
                         <InputLabel value="Vendor (Optional)" />
                         <TextInput v-model="form.vendor" class="mt-1 block w-full" />
                      </div>
                      <div>
                          <InputLabel value="Payment Method" />
                        <select v-model="form.payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option v-for="p in payments" :key="p" :value="p">{{ formatEnum(p) }}</option>
                        </select>
                      </div>
                  </div>
                   <div>
                        <InputLabel value="Reference / Invoice No." />
                         <TextInput v-model="form.reference" class="mt-1 block w-full" />
                    </div>
                
                    <div class="flex justify-end gap-2 pt-4">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">Save Cost</PrimaryButton>
                    </div>
            </form>
        </div>
    </Modal>
  </ProjectLayout>
</template>
