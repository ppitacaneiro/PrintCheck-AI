<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";

const analyses    = ref([]);
const stats       = ref({ total_analyses: 0, total_errors: 0, ok_rate: 0 });
const dragCounter = ref(0);
const isDragging  = computed(() => dragCounter.value > 0);
const isUploading = ref(false);
const uploadError = ref(null);
const pollingTimer = ref(null);
const fileInputRef = ref(null);

const CHECK_LABELS = {
    resolution:     "Resolución de imágenes (≥ 300 dpi)",
    color_profile:  "Perfil de color (CMYK / ICC)",
    embedded_fonts: "Fuentes incrustadas",
    bleed_area:     "Área de sangrado (bleed)",
    safety_margins: "Márgenes de seguridad",
    transparency:   "Transparencias sin aplanar",
};

const hasPending = computed(() =>
    analyses.value.some(a => a.status === "pending" || a.status === "processing")
);

function preventBrowserDrop(e) { e.preventDefault(); }

onMounted(() => {
    loadData();
    startPolling();
    window.addEventListener('dragover', preventBrowserDrop);
    window.addEventListener('drop', preventBrowserDrop);
});
onUnmounted(() => {
    stopPolling();
    window.removeEventListener('dragover', preventBrowserDrop);
    window.removeEventListener('drop', preventBrowserDrop);
});

async function loadData() {
    await Promise.all([loadAnalyses(), loadStats()]);
}
async function loadAnalyses() {
    try { const r = await axios.get("/api/print-analyses"); analyses.value = r.data.data ?? []; } catch {}
}
async function loadStats() {
    try { const r = await axios.get("/api/print-analyses/stats"); stats.value = r.data; } catch {}
}
function startPolling() {
    pollingTimer.value = setInterval(() => { if (hasPending.value) loadData(); }, 4000);
}
function stopPolling() { if (pollingTimer.value) clearInterval(pollingTimer.value); }

function onDragEnter(e) { e.preventDefault(); dragCounter.value++; }
function onDragLeave(e) { dragCounter.value = Math.max(0, dragCounter.value - 1); }
function onDragOver(e)  { e.preventDefault(); }
function onDrop(e)      { e.preventDefault(); dragCounter.value = 0; const f = e.dataTransfer?.files?.[0]; if (f) uploadFile(f); }
function onFileInput(e) { const f = e.target.files?.[0]; if (f) uploadFile(f); e.target.value = ""; }
function openFilePicker() { fileInputRef.value?.click(); }

async function uploadFile(file) {
    uploadError.value = null;
    if (file.type !== "application/pdf") { uploadError.value = "Solo se permiten archivos PDF."; return; }
    if (file.size > 50 * 1024 * 1024)    { uploadError.value = "El archivo supera el límite de 50 MB."; return; }
    isUploading.value = true;
    try {
        const form = new FormData();
        form.append("file", file);
        const res = await axios.post("/api/print-analyses", form, { headers: { "Content-Type": "multipart/form-data" } });
        analyses.value.unshift({ id: res.data.id, filename: res.data.filename, status: res.data.status, results: [], usage: null });
        await loadStats();
    } catch (err) {
        uploadError.value = err.response?.data?.errors?.file?.[0] ?? err.response?.data?.message ?? "Error al subir el archivo.";
    } finally {
        isUploading.value = false;
    }
}

function statusColor(s) {
    return { pass:"green", warn:"amber", fail:"red", pending:"gray", processing:"blue", completed:"green", failed:"red" }[s] ?? "gray";
}
function formatCost(u) { return u ? `$${parseFloat(u).toFixed(4)}` : "—"; }
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="dash-header">
                <div>
                    <p class="dash-eyebrow">Panel de control</p>
                    <h1 class="dash-title">Bienvenido, {{ $page.props.auth.user.name.split(" ")[0] }}</h1>
                </div>
                <button class="dash-upload-btn" @click="openFilePicker" :disabled="isUploading">
                    <svg v-if="!isUploading" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <svg v-else class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" opacity=".2"/><path d="M21 12a9 9 0 00-9-9"/></svg>
                    {{ isUploading ? "Analizando…" : "Analizar archivo" }}
                </button>
            </div>
        </template>

        <input ref="fileInputRef" type="file" accept="application/pdf" style="position:absolute;width:1px;height:1px;opacity:0;pointer-events:none;" @change="onFileInput" />

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon stat-icon--green"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
                <div><p class="stat-value">{{ stats.total_analyses }}</p><p class="stat-label">Análisis realizados</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon--red"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
                <div><p class="stat-value">{{ stats.total_errors }}</p><p class="stat-label">Errores detectados</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon--blue"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
                <div><p class="stat-value">{{ stats.ok_rate }}%</p><p class="stat-label">Tasa libre de errores</p></div>
            </div>
        </div>

        <div class="dash-grid">
            <!-- Panel izquierdo -->
            <div class="dash-card">
                <div class="card-header">
                    <div class="card-title-row">
                        <div class="card-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                        <div><p class="card-title">Análisis de preimpresión</p><p class="card-sub">Arrastra un PDF o haz clic para seleccionarlo</p></div>
                    </div>
                </div>

                <!-- Drop zone -->
                <div class="drop-zone" :class="{ active: isDragging, uploading: isUploading }"
                    @dragenter="onDragEnter" @dragleave="onDragLeave" @dragover="onDragOver" @drop="onDrop" @click="openFilePicker">
                    <div class="drop-zone__inner">
                        <div class="drop-zone__icon" :class="{ active: isDragging }">
                            <svg v-if="!isUploading" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <svg v-else class="spin" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" opacity=".2"/><path d="M21 12a9 9 0 00-9-9"/></svg>
                        </div>
                        <p class="drop-zone__title">{{ isDragging ? "¡Suelta el PDF aquí!" : isUploading ? "Subiendo y procesando…" : "Arrastra tu PDF aquí" }}</p>
                        <p class="drop-zone__sub">Solo archivos PDF · Máx. 50 MB</p>
                        <span v-if="!isUploading" class="drop-zone__btn">Seleccionar archivo</span>
                    </div>
                </div>

                <div v-if="uploadError" class="upload-error">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ uploadError }}
                </div>

                <!-- Historial -->
                <div v-if="analyses.length > 0" class="analysis-list">
                    <div v-for="item in analyses" :key="item.id" class="analysis-item">
                        <div class="analysis-item__header">
                            <div class="analysis-item__file">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <span class="analysis-item__name">{{ item.filename }}</span>
                            </div>
                            <span class="badge" :class="`badge--${statusColor(item.status)}`">
                                <span v-if="item.status === 'processing'" class="badge__dot pulse"></span>
                                {{ item.status }}
                            </span>
                        </div>
                        <div v-if="item.status === 'completed' && item.results?.length" class="check-grid">
                            <div v-for="r in item.results" :key="r.check_type" class="check-pill" :class="`check-pill--${statusColor(r.status)}`" :title="r.summary">
                                <span class="check-pill__dot" :class="`check-pill__dot--${statusColor(r.status)}`"></span>
                                {{ CHECK_LABELS[r.check_type] ?? r.check_type }}
                            </div>
                        </div>
                        <div v-else-if="item.status === 'pending' || item.status === 'processing'" class="item-processing">
                            <div class="progress-bar"><div class="progress-bar__fill progress-bar__fill--anim"></div></div>
                            <p class="item-processing__text">Analizando con IA…</p>
                        </div>
                        <div v-else-if="item.status === 'failed'" class="item-error">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            {{ item.error_message ?? "El análisis falló." }}
                        </div>
                        <div v-if="item.usage" class="item-usage">
                            <span>{{ item.usage.total_tokens.toLocaleString() }} tokens</span>
                            <span class="sep">·</span><span>{{ formatCost(item.usage.cost_usd) }}</span>
                            <span class="sep">·</span><span>{{ item.usage.model }}</span>
                        </div>
                    </div>
                </div>

                <div v-else-if="!isUploading" class="empty-state">
                    <div class="empty-icon"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
                    <p class="empty-title">Aún no hay análisis</p>
                    <p class="empty-text">Arrastra un PDF sobre la zona de arriba o usa el botón para comenzar la verificación automática.</p>
                </div>
            </div>

            <!-- Panel derecho -->
            <div class="dash-right">
                <div class="dash-card">
                    <div class="card-header">
                        <div class="card-title-row">
                            <div class="card-icon card-icon--green"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg></div>
                            <div><p class="card-title">Qué se verifica</p><p class="card-sub">Controles de preimpresión automáticos</p></div>
                        </div>
                    </div>
                    <div class="check-list">
                        <div class="check-item check-item--green"><span class="check-dot check-dot--green"></span>Resolución de imágenes (≥ 300 dpi)</div>
                        <div class="check-item check-item--green"><span class="check-dot check-dot--green"></span>Perfil de color (CMYK / ICC)</div>
                        <div class="check-item check-item--green"><span class="check-dot check-dot--green"></span>Fuentes incrustadas</div>
                        <div class="check-item check-item--amber"><span class="check-dot check-dot--amber"></span>Área de sangrado (bleed)</div>
                        <div class="check-item check-item--amber"><span class="check-dot check-dot--amber"></span>Márgenes de seguridad</div>
                        <div class="check-item check-item--red"><span class="check-dot check-dot--red"></span>Transparencias sin aplanar</div>
                    </div>
                </div>
                <div class="dash-card cta-card">
                    <div class="cta-eyebrow">Empieza ahora</div>
                    <h3 class="cta-title">Analiza tu primer archivo</h3>
                    <p class="cta-text">Arrastra un PDF y recibe un informe detallado en segundos gracias a IA.</p>
                    <div class="cta-drop-zone" @dragenter="onDragEnter" @dragleave="onDragLeave" @dragover="onDragOver" @drop="onDrop" @click="openFilePicker">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:#16a34a;margin-bottom:10px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <p class="cta-drop-text">Arrastra archivos aquí</p>
                        <p class="cta-drop-sub">Solo PDF · hasta 50 MB</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.dash-header{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.dash-eyebrow{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#16a34a;margin:0 0 2px}
.dash-title{font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin:0}
.dash-upload-btn{display:inline-flex;align-items:center;gap:7px;padding:10px 20px;background:#16a34a;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;box-shadow:0 4px 14px rgba(22,163,74,.25);transition:background .15s,transform .15s,box-shadow .15s}
.dash-upload-btn:disabled{opacity:.65;cursor:not-allowed;transform:none}
.dash-upload-btn:not(:disabled):hover{background:#15803d;transform:translateY(-2px);box-shadow:0 8px 24px rgba(22,163,74,.3)}
.stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
@media(max-width:700px){.stats-row{grid-template-columns:1fr}}
.stat-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;display:flex;align-items:center;gap:16px;box-shadow:0 1px 3px rgba(15,23,42,.06)}
.stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-icon--green{background:#dcfce7;color:#16a34a}.stat-icon--red{background:#fee2e2;color:#ef4444}.stat-icon--blue{background:#eff6ff;color:#2563eb}
.stat-value{font-size:28px;font-weight:800;color:#0f172a;letter-spacing:-.02em;line-height:1;margin:0 0 4px}.stat-label{font-size:13px;color:#64748b;margin:0}
.dash-grid{display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start}
@media(max-width:900px){.dash-grid{grid-template-columns:1fr}}
.dash-right{display:flex;flex-direction:column;gap:20px}
.dash-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(15,23,42,.06);overflow:hidden}
.card-header{padding:20px 20px 16px;border-bottom:1px solid #f1f5f9}
.card-title-row{display:flex;align-items:flex-start;gap:12px}
.card-icon{width:34px;height:34px;border-radius:8px;background:#f1f5f9;color:#64748b;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.card-icon--green{background:#dcfce7;color:#16a34a}
.card-title{font-size:14px;font-weight:700;color:#0f172a;margin:0 0 2px}.card-sub{font-size:12px;color:#94a3b8;margin:0}
/* Drop zone */
.drop-zone{margin:20px;border:2px dashed #e2e8f0;border-radius:12px;background:#f8fafc;cursor:pointer;transition:border-color .2s,background .2s}
.drop-zone:hover,.drop-zone.active{border-color:#16a34a;background:#f0fdf4}
.drop-zone.uploading{cursor:not-allowed;opacity:.7}
.drop-zone__inner{padding:36px 20px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:6px}
.drop-zone__icon{width:60px;height:60px;border-radius:14px;background:#f1f5f9;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;color:#94a3b8;margin-bottom:8px;transition:background .2s,color .2s,border-color .2s}
.drop-zone__icon.active,.drop-zone:hover .drop-zone__icon{background:#dcfce7;border-color:#bbf7d0;color:#16a34a}
.drop-zone__title{font-size:15px;font-weight:700;color:#0f172a;margin:0}
.drop-zone__sub{font-size:12px;color:#94a3b8;margin:0}
.drop-zone__btn{display:inline-block;margin-top:10px;padding:7px 18px;background:#16a34a;color:#fff;border-radius:8px;font-size:13px;font-weight:600;pointer-events:none}
/* Upload error */
.upload-error{display:flex;align-items:center;gap:6px;margin:0 20px 16px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:13px;color:#dc2626}
/* Analysis list */
.analysis-list{padding:12px 20px 20px;display:flex;flex-direction:column;gap:12px}
.analysis-item{border:1px solid #f1f5f9;border-radius:10px;padding:14px;background:#fafafa}
.analysis-item__header{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px}
.analysis-item__file{display:flex;align-items:center;gap:7px;color:#475569;min-width:0}
.analysis-item__name{font-size:13px;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;flex-shrink:0}
.badge--green{background:#dcfce7;color:#15803d}.badge--red{background:#fee2e2;color:#dc2626}.badge--blue{background:#eff6ff;color:#1d4ed8}.badge--gray{background:#f1f5f9;color:#64748b}.badge--amber{background:#fffbeb;color:#92400e}
.badge__dot{width:6px;height:6px;border-radius:50%;background:currentColor}
.pulse{animation:pulse-anim 1.4s ease-in-out infinite}
@keyframes pulse-anim{0%,100%{opacity:1}50%{opacity:.2}}
.check-grid{display:flex;flex-direction:column;gap:4px}
.check-pill{display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:7px;font-size:12px;font-weight:500;color:#334155}
.check-pill--green{background:#f0fdf4}.check-pill--amber{background:#fffbeb}.check-pill--red{background:#fef2f2}.check-pill--gray{background:#f8fafc}
.check-pill__dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.check-pill__dot--green{background:#16a34a}.check-pill__dot--amber{background:#f59e0b}.check-pill__dot--red{background:#ef4444}.check-pill__dot--gray{background:#94a3b8}
.item-processing{display:flex;flex-direction:column;gap:6px}
.item-processing__text{font-size:12px;color:#64748b;margin:0}
.progress-bar{height:4px;background:#e2e8f0;border-radius:2px;overflow:hidden}
.progress-bar__fill{height:100%;width:40%;background:#16a34a;border-radius:2px}
.progress-bar__fill--anim{animation:slide 1.5s ease-in-out infinite}
@keyframes slide{0%{transform:translateX(-100%)}100%{transform:translateX(300%)}}
.item-error{display:flex;align-items:flex-start;gap:6px;font-size:12px;color:#dc2626}
.item-usage{margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9;font-size:11px;color:#94a3b8;display:flex;gap:4px}
.sep{color:#e2e8f0}
.empty-state{display:flex;flex-direction:column;align-items:center;text-align:center;padding:40px 32px}
.empty-icon{width:64px;height:64px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#94a3b8;margin-bottom:16px}
.empty-title{font-size:16px;font-weight:700;color:#0f172a;margin:0 0 8px}
.empty-text{font-size:14px;color:#64748b;max-width:320px;line-height:1.6;margin:0}
/* Check list */
.check-list{padding:12px 20px 20px;display:flex;flex-direction:column;gap:8px}
.check-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;font-size:13px;font-weight:500;color:#334155}
.check-item--green{background:#f0fdf4}.check-item--amber{background:#fffbeb}.check-item--red{background:#fef2f2}
.check-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0}
.check-dot--green{background:#16a34a;box-shadow:0 0 5px rgba(22,163,74,.4)}.check-dot--amber{background:#f59e0b;box-shadow:0 0 5px rgba(245,158,11,.4)}.check-dot--red{background:#ef4444;box-shadow:0 0 5px rgba(239,68,68,.4)}
/* CTA */
.cta-card{padding:24px}
.cta-eyebrow{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#16a34a;margin-bottom:6px}
.cta-title{font-size:17px;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin:0 0 6px}
.cta-text{font-size:13px;color:#64748b;line-height:1.6;margin:0 0 16px}
.cta-drop-zone{border:2px dashed #bbf7d0;border-radius:10px;background:#f0fdf4;padding:28px 16px;text-align:center;cursor:pointer;transition:border-color .15s,background .15s;display:flex;flex-direction:column;align-items:center}
.cta-drop-zone:hover{border-color:#16a34a;background:#dcfce7}
.cta-drop-text{font-size:14px;font-weight:600;color:#15803d;margin:0 0 4px}
.cta-drop-sub{font-size:12px;color:#86efac;margin:0}
@keyframes spin{to{transform:rotate(360deg)}}
.spin{animation:spin .8s linear infinite;transform-origin:center}
</style>
