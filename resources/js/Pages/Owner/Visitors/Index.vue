<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import OwnerLayout from '@/Layouts/OwnerLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import Pagination from '@/Components/Pagination.vue'
import { ref, watch } from 'vue'
import { formatDateTime } from '@/utils/format'
import { debounce } from 'lodash'

const props = defineProps<{
    logs: { data: any[]; links: any[] }
    stats: {
        total_visits: number
        unique_visitors: number
        top_countries: { country: string; country_code: string; total: number }[]
        recent_visits: number
    }
    filters: { q: string; country: string }
}>

const search = ref(props.filters.q || '')
const countryFilter = ref(props.filters.country || '')

watch(search, debounce((value) => {
    router.get('/owner/visitors', { q: value, country: countryFilter.value }, { preserveState: true, replace: true })
}, 300))

watch(countryFilter, (value) => {
    router.get('/owner/visitors', { q: search.value, country: value }, { preserveState: true, replace: true })
})

const getFlagEmoji = (countryCode: string) => {
  if (!countryCode) return '🌍';
  const codePoints = countryCode
    .toUpperCase()
    .split('')
    .map(char => 127397 + char.charCodeAt(0));
  return String.fromCodePoint(...codePoints);
}
</script>

<template>
    <OwnerLayout>
        <Head title="Visitor Analytics" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Visitor Analytics</h1>
                    <p class="mt-1 text-sm text-gray-500">Monitor traffic and user locations.</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard title="Total Visits" :value="stats.total_visits" icon="eye" />
                <StatCard title="Unique Visitors" :value="stats.unique_visitors" icon="users" />
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">Top Countries</div>
                    <div class="mt-2 text-sm space-y-1">
                        <div v-for="c in stats.top_countries" :key="c.country_code" class="flex justify-between">
                            <span>{{ getFlagEmoji(c.country_code) }} {{ c.country }}</span>
                            <span class="font-bold">{{ c.total }}</span>
                        </div>
                        <div v-if="stats.top_countries.length === 0" class="text-gray-400 italic">No data yet</div>
                    </div>
                </div>
                <StatCard title="Visits (24h)" :value="stats.recent_visits" icon="clock" />
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4 bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <div class="flex-1">
                    <input v-model="search" type="text" placeholder="Search IP, URL, City..." class="w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500" />
                </div>
                <div class="w-full sm:w-48">
                    <select v-model="countryFilter" class="w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All Countries</option>
                        <option v-for="c in stats.top_countries" :key="c.country_code" :value="c.country_code">{{ c.country }}</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">{{ log.ip_address }}</div>
                                <div class="text-xs text-gray-500" v-if="log.session_id">Session: {{ log.session_id.substring(0, 8) }}...</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div v-if="log.country">
                                    {{ getFlagEmoji(log.country_code) }} {{ log.city }}, {{ log.country }}
                                </div>
                                <div v-else class="text-gray-400 italic">Unknown</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" :title="log.url">
                                {{ log.url }}
                                <div class="text-xs text-gray-400 truncate" v-if="log.referer">Ref: {{ log.referer }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDateTime(log.visit_time) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div v-if="log.user" class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-brand-100 flex items-center justify-center text-xs font-bold text-brand-700">
                                        {{ log.user.name.charAt(0) }}
                                    </div>
                                    <span>{{ log.user.name }}</span>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No visitor logs found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="logs.links" />
        </div>
    </OwnerLayout>
</template>
