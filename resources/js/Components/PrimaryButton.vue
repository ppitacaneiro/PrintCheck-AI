<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type" class="pc-btn">
        <slot />
    </button>
</template>

<style scoped>
.pc-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 22px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    background: #16a34a;
    color: #fff;
    box-shadow: 0 4px 14px rgba(22, 163, 74, .25);
    transition: transform .15s, box-shadow .15s, background .15s, opacity .15s;
    font-family: 'Inter', system-ui, sans-serif;
    text-decoration: none;
    line-height: 1;
}

.pc-btn:hover:not(:disabled) {
    background: #15803d;
    box-shadow: 0 8px 24px rgba(22, 163, 74, .30);
    transform: translateY(-2px);
}

.pc-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}
</style>
