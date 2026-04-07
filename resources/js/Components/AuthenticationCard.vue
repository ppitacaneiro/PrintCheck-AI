<template>
    <div class="auth-bg">
        <div class="auth-logo-wrap">
            <slot name="logo" />
        </div>
        <div class="auth-card">
            <slot />
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.auth-bg {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px 24px;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f2d1a 100%);
    font-family: 'Inter', system-ui, sans-serif;
    -webkit-font-smoothing: antialiased;
}

.auth-logo-wrap {
    margin-bottom: 28px;
    text-align: center;
}

.auth-card {
    width: 100%;
    max-width: 440px;
    background: #fff;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 20px 48px rgba(15, 23, 42, .25), 0 4px 16px rgba(15, 23, 42, .15);
    border: 1px solid rgba(255, 255, 255, .1);
}
</style>
