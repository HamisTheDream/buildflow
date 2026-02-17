<script setup lang="ts">
defineProps<{
  title: string
  value: string | number
  icon?: 'folder' | 'issue' | 'cost' | 'activity' | 'users' | 'ticket' | 'revenue'
  variant?: 'default' | 'warning' | 'success' | 'brand'
  hint?: string
  trend?: 'up' | 'down' | 'neutral'
  trendValue?: string
}>()

const icons: Record<string, string> = {
  folder: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
  issue: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
  cost: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  activity: 'M13 10V3L4 14h7v7l9-11h-7z',
  users: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
  ticket: 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z',
  revenue: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
}
</script>

<template>
  <div 
    :class="[
      'rounded-2xl border p-5 shadow-card transition-all hover:shadow-card-hover',
      {
        'border-gray-200 bg-white': !variant || variant === 'default',
        'border-amber-200 bg-gradient-to-br from-amber-50 to-amber-50/50': variant === 'warning',
        'border-emerald-200 bg-gradient-to-br from-emerald-50 to-emerald-50/50': variant === 'success',
        'border-brand-200 bg-gradient-to-br from-brand-50 to-brand-50/50': variant === 'brand',
      }
    ]"
  >
    <div class="flex items-start justify-between gap-4">
      <div class="min-w-0 flex-1">
        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">{{ title }}</div>
        <div 
          :class="[
            'mt-2 text-2xl font-semibold tracking-tight truncate',
            {
              'text-gray-900': !variant || variant === 'default',
              'text-amber-700': variant === 'warning',
              'text-emerald-700': variant === 'success',
              'text-brand-700': variant === 'brand',
            }
          ]"
        >
          {{ value }}
        </div>
        
        <!-- Trend indicator -->
        <div v-if="trend && trendValue" class="mt-2 flex items-center gap-1">
          <svg 
            v-if="trend === 'up'" 
            class="h-4 w-4 text-emerald-500" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
          </svg>
          <svg 
            v-else-if="trend === 'down'" 
            class="h-4 w-4 text-red-500" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
          <span 
            :class="[
              'text-xs font-medium',
              trend === 'up' ? 'text-emerald-600' : trend === 'down' ? 'text-red-600' : 'text-gray-500'
            ]"
          >
            {{ trendValue }}
          </span>
        </div>
        
        <div v-else-if="hint" class="mt-2 text-xs text-gray-500">{{ hint }}</div>
      </div>
      
      <div 
        v-if="icon" 
        :class="[
          'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl',
          {
            'bg-gray-100': !variant || variant === 'default',
            'bg-amber-100': variant === 'warning',
            'bg-emerald-100': variant === 'success',
            'bg-brand-100': variant === 'brand',
          }
        ]"
      >
        <svg 
          :class="[
            'h-6 w-6',
            {
              'text-gray-600': !variant || variant === 'default',
              'text-amber-600': variant === 'warning',
              'text-emerald-600': variant === 'success',
              'text-brand-600': variant === 'brand',
            }
          ]"
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor" 
          stroke-width="1.5"
        >
          <path stroke-linecap="round" stroke-linejoin="round" :d="icons[icon]" />
        </svg>
      </div>
    </div>
  </div>
</template>
