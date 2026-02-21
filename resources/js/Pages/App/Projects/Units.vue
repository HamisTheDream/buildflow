<script setup lang="ts">
import ProjectLayout from '@/Layouts/ProjectLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import EmptyState from '@/Components/EmptyState.vue'
import Badge from '@/Components/Badge.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps<{
    project: any;
    units: any[];
    canManage: boolean;
}>()

const showModal = ref(false)
const editingUnit = ref<any>(null)

const form = useForm({
    name: '',
    type: 'residential',
    status: 'active',
    description: '',
})

function openModal(unit: any = null) {
    editingUnit.value = unit
    if (unit) {
        form.name = unit.name
        form.type = unit.type
        form.status = unit.status
        form.description = unit.description || ''
    } else {
        form.reset()
        form.status = 'active'
        form.type = 'residential'
    }
    showModal.value = true
}

function submit() {
    if (editingUnit.value) {
        form.patch(`/app/projects/${props.project.id}/units/${editingUnit.value.id}`, {
            onSuccess: () => showModal.value = false
        })
    } else {
        form.post(`/app/projects/${props.project.id}/units`, {
            onSuccess: () => showModal.value = false
        })
    }
}

function deleteUnit(unit: any) {
    if (!confirm('Are you sure you want to delete this unit?')) return
    const f = useForm({})
    f.delete(`/app/projects/${props.project.id}/units/${unit.id}`)
}
</script>

<template>
  <ProjectLayout :project="project" active="units">
    <Head title="Project Units" />

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Project Units / Plots</h2>
            <p class="text-sm text-gray-500">Define the sub-units of your project (e.g. Plot 1, Block A, Apt 101) to track progress granularly.</p>
        </div>
        <button 
            v-if="canManage"
            @click="openModal()"
            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            Add Unit
        </button>
    </div>

    <div v-if="units.length === 0" class="mt-8">
        <EmptyState
            icon="home"
            title="No units defined"
            description="Create your first unit to start tracking work per plot or block."
            :actionLabel="canManage ? 'Add Unit' : undefined"
            :actionCallback="canManage ? () => openModal() : undefined"
        />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="unit in units" :key="unit.id" class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-2">
                <div>
                     <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800 uppercase tracking-wide">
                        {{ unit.type }}
                     </span>
                     <h3 class="mt-2 text-lg font-bold text-gray-900">{{ unit.name }}</h3>
                </div>
                <Badge :text="unit.status" :tone="unit.status === 'active' ? 'green' : 'gray'" size="sm" />
            </div>

            <p class="text-sm text-gray-600 mb-4 h-10 line-clamp-2">
                {{ unit.description || 'No description provided.' }}
            </p>

            <div class="flex items-center gap-4 py-3 border-t border-gray-50 text-xs font-medium text-gray-500">
                <div class="flex items-center gap-1" title="Daily Logs">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    {{ unit.logs_count }} Logs
                </div>
                 <div class="flex items-center gap-1" title="Tasks">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    {{ unit.tasks_count }} Tasks
                </div>
                <div class="flex items-center gap-1" title="Issues">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    {{ unit.issues_count }} Issues
                </div>
            </div>

            <div v-if="canManage" class="flex justify-end gap-2 mt-2 pt-2 border-t border-gray-100">
                <button type="button" @click="openModal(unit)" class="text-sm font-medium text-gray-600 hover:text-indigo-600">Edit</button>
                <button type="button" @click="deleteUnit(unit)" class="text-sm font-medium text-gray-400 hover:text-red-600">Delete</button>
            </div>
        </div>
    </div>

    <Modal :show="showModal" @close="showModal = false">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-6">
                {{ editingUnit ? 'Edit Unit' : 'Create New Unit' }}
            </h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel value="Unit Name" />
                    <TextInput v-model="form.name" class="mt-1 block w-full" placeholder="e.g. Plot 4, Block B" autofocus />
                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                 <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Type" />
                        <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="common_area">Common Area</option>
                            <option value="infrastructure">Infrastructure</option>
                        </select>
                    </div>
                     <div>
                        <InputLabel value="Status" />
                        <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="sold">Sold</option>
                            <option value="on_hold">On Hold</option>
                        </select>
                    </div>
                 </div>

                 <div>
                    <InputLabel value="Description (Optional)" />
                    <TextArea v-model="form.description" rows="3" class="mt-1 block w-full" placeholder="Details about this unit..." />
                 </div>

                 <div class="flex justify-end gap-3 pt-4">
                     <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                     <PrimaryButton type="submit" :disabled="form.processing">Save Unit</PrimaryButton>
                 </div>
            </form>
        </div>
    </Modal>
  </ProjectLayout>
</template>
