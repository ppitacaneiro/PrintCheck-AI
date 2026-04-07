<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link href="/" class="auth-brand">
        <div class="auth-brand-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
        </div>
        <span>PrintCheck AI</span>
    </Link>
</template>

<style scoped>
.auth-brand {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-weight: 800;
    font-size: 20px;
    color: #fff;
    text-decoration: none;
    font-family: 'Inter', system-ui, sans-serif;
}

.auth-brand-icon {
    background: rgba(22, 163, 74, .15);
    border: 1px solid rgba(22, 163, 74, .35);
    border-radius: 8px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4ade80;
    flex-shrink: 0;
}
</style>
