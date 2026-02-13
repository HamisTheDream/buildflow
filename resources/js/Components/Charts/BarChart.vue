<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)

const props = defineProps({
    labels: { type: Array, required: true },
    datasets: { type: Array, required: true },
    title: { type: String, default: '' },
    height: { type: Number, default: 280 },
    stacked: { type: Boolean, default: false },
    horizontal: { type: Boolean, default: false },
})

const chartData = computed(() => ({
    labels: props.labels,
    datasets: props.datasets.map((ds, i) => ({
        label: ds.label || `Series ${i + 1}`,
        data: ds.data,
        backgroundColor: ds.color || ['#f97316', '#3b82f6', '#10b981', '#eab308'][i % 4],
        borderRadius: 6,
        borderSkipped: false,
        maxBarThickness: 40,
        ...ds,
    }))
}))

const options = computed(() => ({
    indexAxis: props.horizontal ? 'y' : 'x',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: props.datasets.length > 1,
            position: 'top',
            labels: {
                padding: 16,
                usePointStyle: true,
                pointStyleWidth: 10,
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
            stacked: props.stacked,
            grid: { display: false },
            ticks: { font: { size: 11, family: "'Inter', sans-serif" }, color: '#9ca3af' },
        },
        y: {
            stacked: props.stacked,
            grid: { color: '#f3f4f6' },
            ticks: { font: { size: 11, family: "'Inter', sans-serif" }, color: '#9ca3af' },
            beginAtZero: true,
        }
    }
}))
</script>

<template>
    <div>
        <h3 v-if="title" class="mb-3 text-sm font-semibold text-gray-700">{{ title }}</h3>
        <div :style="{ height: height + 'px' }">
            <Bar :data="chartData" :options="options" />
        </div>
    </div>
</template>
