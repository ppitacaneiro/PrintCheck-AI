<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="dash-header">
                <div>
                    <p class="dash-eyebrow">Panel de control</p>
                    <h1 class="dash-title">Bienvenido, {{ $page.props.auth.user.name.split(' ')[0] }}</h1>
                </div>
                <button class="dash-upload-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Analizar archivo
                </button>
            </div>
        </template>

        <!-- Stats strip -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon stat-icon--green">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <p class="stat-value">0</p>
                    <p class="stat-label">Análisis realizados</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon--red">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div>
                    <p class="stat-value">0</p>
                    <p class="stat-label">Errores detectados</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon--blue">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div>
                    <p class="stat-value">0%</p>
                    <p class="stat-label">Tasa libre de errores</p>
                </div>
            </div>
        </div>

        <!-- Main grid -->
        <div class="dash-grid">

            <!-- Semáforo panel -->
            <div class="dash-card">
                <div class="card-header">
                    <div class="card-title-row">
                        <div class="card-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <p class="card-title">Análisis recientes</p>
                            <p class="card-sub">Últimas verificaciones de preimpresión</p>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </div>
                    <p class="empty-title">Aún no hay análisis</p>
                    <p class="empty-text">Sube tu primer archivo PDF o AI para comenzar la verificación automática.</p>
                    <button class="dash-upload-btn" style="margin-top:16px">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Subir archivo
                    </button>
                </div>
            </div>

            <!-- Right column -->
            <div class="dash-right">

                <!-- Quick checks card -->
                <div class="dash-card">
                    <div class="card-header">
                        <div class="card-title-row">
                            <div class="card-icon card-icon--green">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div>
                                <p class="card-title">Qué se verifica</p>
                                <p class="card-sub">Controles de preimpresión automáticos</p>
                            </div>
                        </div>
                    </div>
                    <div class="check-list">
                        <div class="check-item check-item--green">
                            <span class="check-dot check-dot--green"></span>
                            Resolución de imágenes (≥ 300 dpi)
                        </div>
                        <div class="check-item check-item--green">
                            <span class="check-dot check-dot--green"></span>
                            Perfil de color (CMYK / ICC)
                        </div>
                        <div class="check-item check-item--green">
                            <span class="check-dot check-dot--green"></span>
                            Fuentes incrustadas
                        </div>
                        <div class="check-item check-item--amber">
                            <span class="check-dot check-dot--amber"></span>
                            Área de sangrado (bleed)
                        </div>
                        <div class="check-item check-item--amber">
                            <span class="check-dot check-dot--amber"></span>
                            Márgenes de seguridad
                        </div>
                        <div class="check-item check-item--red">
                            <span class="check-dot check-dot--red"></span>
                            Transparencias sin aplanar
                        </div>
                    </div>
                </div>

                <!-- CTA card -->
                <div class="dash-card cta-card">
                    <div class="cta-eyebrow">Empieza ahora</div>
                    <h3 class="cta-title">Analiza tu primer archivo</h3>
                    <p class="cta-text">Arrastra un PDF, AI o EPS y recibe un informe detallado en segundos.</p>
                    <div class="cta-drop-zone">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:#16a34a;margin-bottom:10px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <p class="cta-drop-text">Arrastra archivos aquí</p>
                        <p class="cta-drop-sub">PDF · AI · EPS · hasta 500 MB</p>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Header ──────────────────────────────── */
.dash-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.dash-eyebrow {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #16a34a;
    margin: 0 0 2px;
}

.dash-title {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -.02em;
    margin: 0;
}

.dash-upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 20px;
    background: #16a34a;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Inter', system-ui, sans-serif;
    box-shadow: 0 4px 14px rgba(22, 163, 74, .25);
    transition: background .15s, transform .15s, box-shadow .15s;
}

.dash-upload-btn:hover {
    background: #15803d;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(22, 163, 74, .3);
}

/* ── Stats row ───────────────────────────── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

@media (max-width: 700px) {
    .stats-row { grid-template-columns: 1fr; }
}

.stat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
}

.stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon--green { background: #dcfce7; color: #16a34a; }
.stat-icon--red   { background: #fee2e2; color: #ef4444; }
.stat-icon--blue  { background: #eff6ff; color: #2563eb; }

.stat-value {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -.02em;
    line-height: 1;
    margin: 0 0 4px;
}

.stat-label {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

/* ── Main grid ───────────────────────────── */
.dash-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 20px;
    align-items: start;
}

@media (max-width: 900px) {
    .dash-grid { grid-template-columns: 1fr; }
}

.dash-right {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ── Cards ───────────────────────────────── */
.dash-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
    overflow: hidden;
}

.card-header {
    padding: 20px 20px 16px;
    border-bottom: 1px solid #f1f5f9;
}

.card-title-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.card-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: #f1f5f9;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.card-icon--green {
    background: #dcfce7;
    color: #16a34a;
}

.card-title {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 2px;
}

.card-sub {
    font-size: 12px;
    color: #94a3b8;
    margin: 0;
}

/* ── Empty state ─────────────────────────── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 56px 32px;
}

.empty-icon {
    width: 64px;
    height: 64px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    margin-bottom: 16px;
}

.empty-title {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 8px;
}

.empty-text {
    font-size: 14px;
    color: #64748b;
    max-width: 320px;
    line-height: 1.6;
    margin: 0;
}

/* ── Check list ──────────────────────────── */
.check-list {
    padding: 12px 20px 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.check-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #334155;
}

.check-item--green { background: #f0fdf4; }
.check-item--amber { background: #fffbeb; }
.check-item--red   { background: #fef2f2; }

.check-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
}

.check-dot--green { background: #16a34a; box-shadow: 0 0 5px rgba(22,163,74,.4); }
.check-dot--amber { background: #f59e0b; box-shadow: 0 0 5px rgba(245,158,11,.4); }
.check-dot--red   { background: #ef4444; box-shadow: 0 0 5px rgba(239,68,68,.4); }

/* ── CTA card ────────────────────────────── */
.cta-card {
    padding: 24px;
}

.cta-eyebrow {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #16a34a;
    margin-bottom: 6px;
}

.cta-title {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -.02em;
    margin: 0 0 6px;
}

.cta-text {
    font-size: 13px;
    color: #64748b;
    line-height: 1.6;
    margin: 0 0 16px;
}

.cta-drop-zone {
    border: 2px dashed #bbf7d0;
    border-radius: 10px;
    background: #f0fdf4;
    padding: 28px 16px;
    text-align: center;
    cursor: pointer;
    transition: border-color .15s, background .15s;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.cta-drop-zone:hover {
    border-color: #16a34a;
    background: #dcfce7;
}

.cta-drop-text {
    font-size: 14px;
    font-weight: 600;
    color: #15803d;
    margin: 0 0 4px;
}

.cta-drop-sub {
    font-size: 12px;
    color: #86efac;
    margin: 0;
}
</style>
