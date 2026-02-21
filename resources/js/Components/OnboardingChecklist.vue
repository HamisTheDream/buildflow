<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'

const page = usePage<any>()
const isOpen = ref(true)
const dismissed = ref(false)

onMounted(() => {
  dismissed.value = localStorage.getItem('buildflow_onboarding_dismissed') === 'true'
})

function dismiss() {
  dismissed.value = true
  localStorage.setItem('buildflow_onboarding_dismissed', 'true')
}

const steps = computed(() => {
  const stats = page.props.onboarding_stats || page.props.stats || page.props.dashboardStats || {}
  return [
    {
      key: 'create_project',
      title: 'Create your first project',
      description: 'Set up a construction project to start tracking',
      href: '/app/projects/create',
      completed: (stats.projects_count ?? 0) > 0,
      emoji: '🏗️',
    },
    {
      key: 'log_activity',
      title: 'Log site activity',
      description: 'Record your first daily progress report',
      href: null,
      completed: (stats.recent_activity_count ?? 0) > 0,
      emoji: '📝',
    },
    {
      key: 'add_task',
      title: 'Add a task',
      description: 'Break down work into trackable items',
      href: null,
      completed: (stats.tasks_count ?? 0) > 0,
      emoji: '✅',
    },
    {
      key: 'invite_member',
      title: 'Invite a team member',
      description: 'Collaborate with your crew on site',
      href: '/app/organization/members',
      completed: (stats.members_count ?? 1) > 1,
      emoji: '👥',
    },
    {
      key: 'generate_report',
      title: 'Generate a report',
      description: 'Share progress updates with stakeholders',
      href: null,
      completed: (stats.reports_count ?? 0) > 0,
      emoji: '📊',
    },
    {
      key: 'explore_billing',
      title: 'Explore your plan',
      description: 'See features included in your plan',
      href: '/app/billing',
      completed: !!stats.has_plan,
      emoji: '💎',
    },
  ]
})

const completedCount = computed(() => steps.value.filter(s => s.completed).length)
const totalSteps = computed(() => steps.value.length)
const progressPercent = computed(() => Math.round((completedCount.value / totalSteps.value) * 100))
const allComplete = computed(() => completedCount.value === totalSteps.value)

const orgRole = computed(() => page.props.auth?.orgRole || null)
const isOrgManager = computed(() => ['owner', 'admin'].includes(orgRole.value))

const shouldShow = computed(() => isOrgManager.value && !dismissed.value && !allComplete.value)

// SVG progress ring
const radius = 28
const circumference = 2 * Math.PI * radius
const dashOffset = computed(() => circumference - (progressPercent.value / 100) * circumference)
</script>

<template>
  <div v-if="shouldShow" class="fixed bottom-4 right-4 z-40 w-[320px] sm:bottom-6 sm:right-6 sm:w-80">
    <!-- Collapsed State -->
    <button
      v-if="!isOpen"
      @click="isOpen = true"
      class="flex w-full items-center gap-3 rounded-2xl bg-white p-4 shadow-xl ring-1 ring-gray-900/5 hover:shadow-2xl transition"
    >
      <!-- Progress Ring -->
      <div class="relative h-12 w-12 shrink-0">
        <svg class="h-12 w-12 -rotate-90" viewBox="0 0 64 64">
          <circle cx="32" cy="32" :r="radius" fill="none" stroke="#e5e7eb" stroke-width="4" />
          <circle 
            cx="32" cy="32" :r="radius" fill="none" stroke="#f97316" stroke-width="4"
            stroke-linecap="round"
            :stroke-dasharray="circumference" :stroke-dashoffset="dashOffset"
            class="transition-all duration-700"
          />
        </svg>
        <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-900">{{ progressPercent }}%</span>
      </div>
      <div class="flex-1 text-left">
        <div class="text-sm font-semibold text-gray-900">Getting Started</div>
        <div class="text-xs text-gray-500">{{ completedCount }}/{{ totalSteps }} steps done</div>
      </div>
    </button>

    <!-- Expanded State -->
    <div v-else class="rounded-2xl bg-white shadow-2xl ring-1 ring-gray-900/5 overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-4 text-white">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <!-- Progress Ring (small) -->
            <div class="relative h-10 w-10 shrink-0">
              <svg class="h-10 w-10 -rotate-90" viewBox="0 0 64 64">
                <circle cx="32" cy="32" :r="radius" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="4" />
                <circle 
                  cx="32" cy="32" :r="radius" fill="none" stroke="white" stroke-width="4"
                  stroke-linecap="round"
                  :stroke-dasharray="circumference" :stroke-dashoffset="dashOffset"
                  class="transition-all duration-700"
                />
              </svg>
              <span class="absolute inset-0 flex items-center justify-center text-[10px] font-bold text-white">{{ progressPercent }}%</span>
            </div>
            <div>
              <h3 class="text-sm font-bold">Getting Started</h3>
              <p class="text-xs text-brand-100">{{ completedCount }} of {{ totalSteps }} steps</p>
            </div>
          </div>
          <button type="button" @click="isOpen = false" class="rounded-lg p-1 hover:bg-white/20 transition">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Steps -->
      <div class="divide-y divide-gray-100">
        <component
          :is="step.href && !step.completed ? Link : 'div'"
          v-for="(step, i) in steps"
          :key="step.key"
          :href="step.completed ? undefined : step.href"
          class="flex items-center gap-3 px-5 py-3.5 transition"
          :class="step.completed ? 'bg-gray-50/50' : step.href ? 'hover:bg-gray-50 cursor-pointer' : ''"
        >
          <div 
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-lg"
            :class="step.completed ? 'bg-emerald-100' : 'bg-gray-100'"
          >
            <svg v-if="step.completed" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span v-else class="text-base leading-none">{{ step.emoji }}</span>
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-sm font-medium" :class="step.completed ? 'text-gray-400 line-through' : 'text-gray-900'">
              {{ step.title }}
            </div>
            <div class="text-xs text-gray-500">{{ step.description }}</div>
          </div>
          <svg v-if="!step.completed && step.href" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </component>
      </div>

      <!-- Footer -->
      <div class="border-t border-gray-100 px-5 py-3">
        <button type="button" @click="dismiss" class="text-xs text-gray-500 hover:text-gray-700 transition">
          Dismiss checklist
        </button>
      </div>
    </div>
  </div>
</template>
