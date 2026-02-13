<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

defineProps({
    hasError: Boolean,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        v-model="model"
        ref="input"
        :class="[
            'block w-full rounded-lg border px-3.5 py-2.5 text-sm shadow-sm transition-colors',
            'placeholder:text-gray-400',
            'focus:outline-none focus:ring-2 focus:ring-offset-0',
            hasError 
                ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500/20' 
                : 'border-gray-300 text-gray-900 focus:border-brand-500 focus:ring-brand-500/20'
        ]"
    />
</template>
