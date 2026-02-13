<script setup lang="ts">
import { computed } from 'vue'
import { formatMoneyKobo, formatEnum } from '@/utils/format'

const props = defineProps<{
  byCategory: { category: string; total: number }[]
  byMonth?: { month: string; total: number }[]
  currency?: string
}>()

const currency = computed(() => props.currency || 'NGN')

// Category colors
const categoryColors: Record<string, string> = {
  material: 'bg-blue-500',
  labor: 'bg-green-500',
  equipment: 'bg-purple-500',
  transport: 'bg-amber-500',
  misc: 'bg-gray-500',
  service: 'bg-cyan-500',
  permit: 'bg-rose-500',
  fuel: 'bg-orange-500',
  security: 'bg-indigo-500',
}

const maxCategoryTotal = computed(() => Math.max(...props.byCategory.map(c => c.total), 1))
const maxMonthTotal = computed(() => Math.max(...(props.byMonth || []).map(m => m.total), 1))
const grandTotal = computed(() => props.byCategory.reduce((s, c) => s + c.total, 0))
</script>

<template>
  <div class="rounded-xl border bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900">Cost Analytics</h3>
      <div class="text-2xl font-bold text-gray-900">{{ formatMoneyKobo(grandTotal * 100, currency) }}</div>
    </div>

    <!-- Category Breakdown -->
    <div class="mt-6">
      <div class="text-sm font-medium text-gray-700 mb-3">Spending by Category</div>
      
      <div v-if="byCategory.length === 0" class="py-4 text-center text-sm text-gray-500">
        No cost data available
      </div>

      <div v-else class="space-y-3">
        <div 
          v-for="item in byCategory" 
          :key="item.category"
          class="flex items-center gap-3"
        >
          <div class="w-24 shrink-0 text-sm text-gray-600 truncate capitalize">
            {{ formatEnum(item.category) }}
          </div>
          <div class="flex-1 h-6 bg-gray-100 rounded-full overflow-hidden">
            <div 
              :class="categoryColors[item.category] || 'bg-gray-400'"
              class="h-full rounded-full transition-all"
              :style="{ width: `${(item.total / maxCategoryTotal) * 100}%` }"
            ></div>
          </div>
          <div class="w-28 text-right text-sm font-medium text-gray-900">
            {{ formatMoneyKobo(item.total * 100, currency) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Monthly Timeline (if provided) -->
    <div v-if="byMonth && byMonth.length > 0" class="mt-8">
      <div class="text-sm font-medium text-gray-700 mb-3">Spending Over Time</div>
      <div class="flex items-end gap-2 h-32">
        <div 
          v-for="item in byMonth" 
          :key="item.month" 
          class="flex-1 flex flex-col items-center"
        >
          <div 
            class="w-full bg-indigo-500 rounded-t transition-all"
            :style="{ height: `${(item.total / maxMonthTotal) * 100}%`, minHeight: item.total > 0 ? '4px' : '0' }"
          ></div>
          <div class="mt-2 text-xs text-gray-500">{{ item.month }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
