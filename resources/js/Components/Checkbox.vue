<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked', 'update:modelValue']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: undefined,
    },
    modelValue: {
        type: [Array, Boolean],
        default: undefined,
    },
    value: {
        default: null,
    },
    id: {
        type: String,
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.modelValue !== undefined ? props.modelValue : props.checked;
    },

    set(val) {
        emit('update:checked', val);
        emit('update:modelValue', val);
    },
});
</script>

<template>
    <input
        type="checkbox"
        :id="id"
        :value="value"
        v-model="proxyChecked"
        class="h-4 w-4 rounded border-gray-300 text-brand-600 shadow-sm transition focus:ring-2 focus:ring-brand-500 focus:ring-offset-0"
        aria-describedby="checkbox-description"
    />
</template>
