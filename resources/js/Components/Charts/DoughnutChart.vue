<script setup>
import { computed, onMounted, ref } from 'vue'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
    labels: { type: Array, required: true },
    values: { type: Array, required: true },
    colors: { type: Array, default: () => ['#f97316', '#3b82f6', '#10b981', '#eab308', '#6366f1', '#ec4899'] },
    title: { type: String, default: '' },
    height: { type: Number, default: 260 },
})

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [{
        data: props.values,
        backgroundColor: props.colors.slice(0, props.values.length),
        borderWidth: 0,
        hoverOffset: 6,
    }]
}))

const options = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '65%',
    plugins: {
        legend: {
            position: 'bottom',
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
    }
}
</script>

<template>
    <div>
        <h3 v-if="title" class="mb-3 text-sm font-semibold text-gray-700">{{ title }}</h3>
        <div :style="{ height: height + 'px' }">
            <Doughnut :data="chartData" :options="options" />
        </div>
    </div>
</template>
