<script setup>
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend)

const props = defineProps({
    labels: { type: Array, required: true },
    datasets: { type: Array, required: true },
    title: { type: String, default: '' },
    height: { type: Number, default: 260 },
    fill: { type: Boolean, default: true },
})

const chartData = computed(() => ({
    labels: props.labels,
    datasets: props.datasets.map((ds, i) => ({
        label: ds.label || `Series ${i + 1}`,
        data: ds.data,
        borderColor: ds.color || ['#f97316', '#3b82f6', '#10b981'][i % 3],
        backgroundColor: props.fill 
            ? (ds.color || ['#f97316', '#3b82f6', '#10b981'][i % 3]) + '15' 
            : 'transparent',
        fill: props.fill,
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: '#fff',
        pointBorderWidth: 2,
        pointBorderColor: ds.color || ['#f97316', '#3b82f6', '#10b981'][i % 3],
        borderWidth: 2,
        ...ds,
    }))
}))

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: props.datasets.length > 1,
            position: 'top',
            labels: {
                padding: 16,
                usePointStyle: true,
                font: { size: 12, family: "'Inter', sans-serif" },
                color: '#6b7280',
            }
        },
        tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { size: 13, family: "'Inter', sans-serif" },
            bodyFont: { size: 12, family: "'Inter', sans-serif" },
            padding: 10,
            cornerRadius: 8,
        }
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { font: { size: 11, family: "'Inter', sans-serif" }, color: '#9ca3af' },
        },
        y: {
            grid: { color: '#f3f4f6' },
            ticks: { font: { size: 11, family: "'Inter', sans-serif" }, color: '#9ca3af' },
            beginAtZero: true,
        }
    }
}
</script>

<template>
    <div>
        <h3 v-if="title" class="mb-3 text-sm font-semibold text-gray-700">{{ title }}</h3>
        <div :style="{ height: height + 'px' }">
            <Line :data="chartData" :options="options" />
        </div>
    </div>
</template>
