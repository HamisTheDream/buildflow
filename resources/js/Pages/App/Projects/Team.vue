<script setup lang="ts">
import ProjectLayout from '@/Layouts/ProjectLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{
  project: { id: number; name: string }
  canManage: boolean
  orgMembers: { id: number; name: string; email: string; org_role: string }[]
  projectMembers: { id: number; name: string; email: string; project_role: string }[]
  projectMemberIds: number[]
  roles: { value: string; label: string }[]
}>()

const page = usePage()
const flash = computed(() => (page.props.flash as any) ?? {})

const addForm = useForm({
  user_id: props.orgMembers.find(m => !props.projectMemberIds.includes(m.id))?.id ?? null,
  role: 'viewer',
})

function addMember() {
  addForm.post(`/app/projects/${props.project.id}/team`, { preserveScroll: true })
}

function updateRole(userId: number, role: string) {
  const f = useForm({ role })
  f.patch(`/app/projects/${props.project.id}/team/${userId}`, { preserveScroll: true })
}

function removeMember(userId: number) {
  const f = useForm({})
  f.delete(`/app/projects/${props.project.id}/team/${userId}`, { preserveScroll: true })
}

const availableOrgMembers = computed(() =>
  props.orgMembers.filter(m => !props.projectMemberIds.includes(m.id))
)

// Helper to get role label from value
function getRoleLabel(roleValue: string): string {
  const role = props.roles.find(r => r.value === roleValue)
  return role?.label ?? roleValue
}
</script>

<template>
  <ProjectLayout :project="project" active="team">
    <Head :title="`Team — ${project.name}`" />

    <div v-if="flash.success" class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">
      {{ flash.success }}
    </div>
    <div v-if="flash.error" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">
      {{ flash.error }}
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Add member - Only show to managers -->
        <div v-if="canManage" class="rounded-xl bg-white p-6 shadow">
          <h2 class="text-lg font-semibold text-gray-900">Add member</h2>
          <p class="mt-1 text-sm text-gray-600">Assign an organization member to this project.</p>

          <div v-if="availableOrgMembers.length === 0" class="mt-4 rounded-lg border border-dashed p-4 text-sm text-gray-600">
            Everyone in this organization is already on this project.
          </div>

          <form v-else class="mt-4 space-y-3" @submit.prevent="addMember">
            <div>
              <label class="text-sm text-gray-700">Member</label>
              <select v-model="addForm.user_id" class="mt-1 w-full rounded-lg border p-2">
                <option v-for="m in availableOrgMembers" :key="m.id" :value="m.id">
                  {{ m.name }} ({{ m.email }})
                </option>
              </select>
              <div v-if="addForm.errors.user_id" class="mt-1 text-sm text-red-600">{{ addForm.errors.user_id }}</div>
            </div>

            <div>
              <label class="text-sm text-gray-700">Project role</label>
              <select v-model="addForm.role" class="mt-1 w-full rounded-lg border p-2">
                <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
              </select>
              <div v-if="addForm.errors.role" class="mt-1 text-sm text-red-600">{{ addForm.errors.role }}</div>
            </div>

            <button
              class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              :disabled="addForm.processing"
            >
              Add to project
            </button>
          </form>
        </div>

        <!-- Current project members -->
        <div :class="canManage ? 'lg:col-span-2' : 'lg:col-span-3'" class="rounded-xl bg-white p-6 shadow">
          <h2 class="text-lg font-semibold text-gray-900">Members on this project</h2>
          <p class="mt-1 text-sm text-gray-600">
            <template v-if="canManage">Update roles or remove access.</template>
            <template v-else>View project team members.</template>
          </p>

          <div class="mt-4 space-y-2">
            <div
              v-for="m in projectMembers"
              :key="m.id"
              class="flex flex-col gap-3 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between"
            >
              <div class="min-w-0">
                <div class="truncate text-sm font-semibold text-gray-900">{{ m.name }}</div>
                <div class="truncate text-xs text-gray-500">{{ m.email }}</div>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                <!-- Manager view: show dropdown and remove button -->
                <template v-if="canManage">
                  <select
                    class="rounded-lg border p-2 text-sm"
                    :value="m.project_role"
                    @change="updateRole(m.id, ($event.target as HTMLSelectElement).value)"
                  >
                    <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                  </select>

                  <button
                    class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 hover:bg-red-100"
                    @click="removeMember(m.id)"
                  >
                    Remove
                  </button>
                </template>

                <!-- Non-manager view: show read-only role badge -->
                <template v-else>
                  <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                    {{ getRoleLabel(m.project_role) }}
                  </span>
                </template>
              </div>
            </div>

            <div v-if="projectMembers.length === 0" class="rounded-lg border border-dashed p-10 text-center text-gray-600">
              No members yet. Add someone from your organization.
            </div>
          </div>
      </div>
    </div>
  </ProjectLayout>
</template>

