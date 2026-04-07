<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="pc-label">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>

<style scoped>
.pc-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 6px;
    font-family: 'Inter', system-ui, sans-serif;
}
</style>
