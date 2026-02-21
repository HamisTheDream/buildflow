<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import Modal from '@/Components/Modal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps<{
    departments: Array<{
        id: number
        name: string
        manager_id: number | null
        manager?: { name: string }
        members_count: number
    }>
    potentialManagers: Array<{ id: number, name: string }>
}>()

const showModal = ref(false)
const editingDepartment = ref<any>(null)

const form = useForm({
    name: '',
    manager_id: '',
})

const openCreateModal = () => {
    editingDepartment.value = null
    form.reset()
    showModal.value = true
}

const openEditModal = (department: any) => {
    editingDepartment.value = department
    form.name = department.name
    form.manager_id = department.manager_id || ''
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    form.reset()
    editingDepartment.value = null
}

const submit = () => {
    if (editingDepartment.value) {
        form.put(route('owner.hr.departments.update', editingDepartment.value.id), {
            onSuccess: () => closeModal(),
        })
    } else {
        form.post(route('owner.hr.departments.store'), {
            onSuccess: () => closeModal(),
        })
    }
}

const deleteDepartment = (department: any) => {
    if (department.members_count > 0) {
        alert('Cannot delete department with active members.')
        return
    }
    
    if (confirm('Are you sure you want to delete this department?')) {
        router.delete(route('owner.hr.departments.destroy', department.id))
    }
}
</script>

<template>
    <OwnerLayout>
        <Head title="Departments" />

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Departments</h1>
                <p class="text-sm text-gray-500">Manage organization units and structure</p>
            </div>
            <button type="button" @click="openCreateModal" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">
                Add Department
            </button>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Members</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="departments.length === 0">
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 text-sm">No departments found.</td>
                    </tr>
                    <tr v-for="department in departments" :key="department.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ department.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ department.manager?.name || '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ department.members_count }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button type="button" @click="openEditModal(department)" class="text-brand-600 hover:text-brand-900 mr-4">Edit</button>
                            <button type="button" @click="deleteDepartment(department)" class="text-red-600 hover:text-red-900" :disabled="department.members_count > 0" :class="{'opacity-50 cursor-not-allowed': department.members_count > 0}">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingDepartment ? 'Edit Department' : 'Create Department' }}
                </h2>
                
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="manager" value="Manager (Optional)" />
                        <select
                            id="manager"
                            v-model="form.manager_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                        >
                            <option value="">None</option>
                            <option v-for="admin in potentialManagers" :key="admin.id" :value="admin.id">
                                {{ admin.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.manager_id" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" class="flex items-center gap-2" :disabled="form.processing">
                            <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>{{ editingDepartment ? 'Update' : 'Create' }}</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </OwnerLayout>
</template>
