<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
});

defineEmits(['update:modelValue']);

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
        ref="input"
        class="pc-input"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>

<style scoped>
.pc-input {
    width: 100%;
    padding: 11px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Inter', system-ui, sans-serif;
    color: #0f172a;
    background: #fff;
    transition: border-color .15s, box-shadow .15s;
    outline: none;
    line-height: 1.5;
}

.pc-input:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, .12);
}

.pc-input::placeholder {
    color: #94a3b8;
}
</style>
