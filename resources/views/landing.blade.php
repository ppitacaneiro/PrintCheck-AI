<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>PrintCheck AI — Preimpresión automática para imprentas</title>
    <meta name="description" content="Detecta errores de pre-impresión, calcula presupuestos y notifica al jefe de producción automáticamente.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── Reset & tokens ─────────────────────────────────────── */
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --green:#16a34a; --green-light:#dcfce7; --green-dark:#15803d;
            --blue:#2563eb;  --blue-light:#eff6ff;
            --amber:#f59e0b; --amber-light:#fef3c7;
            --red:#ef4444;   --red-light:#fee2e2;
            --ink:#0f172a;   --ink-2:#334155; --muted:#64748b;
            --border:#e2e8f0;--bg:#f8fafc;    --white:#ffffff;
            --shadow-sm:0 1px 3px rgba(15,23,42,.08),0 1px 2px rgba(15,23,42,.06);
            --shadow:0 4px 16px rgba(15,23,42,.08),0 2px 6px rgba(15,23,42,.06);
            --shadow-lg:0 20px 48px rgba(15,23,42,.12),0 4px 16px rgba(15,23,42,.08);
            --radius:12px; --radius-sm:8px; --radius-full:9999px;
        }
        body{font-family:'Inter',system-ui,sans-serif;color:var(--ink);background:var(--bg);line-height:1.6;-webkit-font-smoothing:antialiased}
        img{display:block;max-width:100%}
        a{color:inherit;text-decoration:none}

        /* ── Layout ─────────────────────────────────────────────── */
        .wrap{max-width:1120px;margin:0 auto;padding:0 24px}
        section{padding:72px 0}
        section:first-child{padding-top:40px}

        /* ── Navbar ─────────────────────────────────────────────── */
        .navbar{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.92);backdrop-filter:blur(12px);border-bottom:1px solid var(--border)}
        .navbar-inner{display:flex;align-items:center;justify-content:space-between;height:64px}
        .navbar-logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:18px;color:var(--ink)}
        .navbar-logo svg{color:var(--green)}
        .navbar-links{display:flex;align-items:center;gap:8px}
        .nav-link{padding:8px 14px;border-radius:var(--radius-sm);font-size:14px;font-weight:500;color:var(--ink-2);transition:background .15s}
        .nav-link:hover{background:var(--bg)}

        /* ── Buttons ─────────────────────────────────────────────── */
        .btn{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:var(--radius-sm);font-size:15px;font-weight:600;cursor:pointer;border:none;transition:transform .15s,box-shadow .15s,background .15s;text-decoration:none}
        .btn:hover{transform:translateY(-2px)}
        .btn-primary{background:var(--green);color:#fff;box-shadow:0 4px 14px rgba(22,163,74,.25)}
        .btn-primary:hover{background:var(--green-dark);box-shadow:0 8px 24px rgba(22,163,74,.30)}
        .btn-outline{background:transparent;color:var(--ink);border:1.5px solid var(--border)}
        .btn-outline:hover{background:var(--white);box-shadow:var(--shadow-sm)}
        .btn-ghost{background:transparent;color:var(--blue);font-size:14px;font-weight:600;padding:8px 12px}
        .btn-white{background:#fff;color:var(--ink);box-shadow:var(--shadow)}
        .btn-white:hover{box-shadow:var(--shadow-lg)}
        .btn-sm{padding:8px 16px;font-size:13px}

        /* ── Hero ────────────────────────────────────────────────── */
        .hero{background:linear-gradient(135deg,#0f172a 0%,#1e293b 50%,#0f2d1a 100%);color:#fff;padding:80px 0 72px}
        .hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center}
        .hero-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(22,163,74,.15);border:1px solid rgba(22,163,74,.3);color:#4ade80;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:5px 12px;border-radius:var(--radius-full);margin-bottom:20px}
        .hero h1{font-size:clamp(28px,4vw,46px);font-weight:800;line-height:1.07;margin-bottom:20px;letter-spacing:-.02em;color:#fff}
        .hero h1 em{font-style:normal;color:#4ade80}
        .hero-lead{font-size:17px;color:#94a3b8;line-height:1.65;margin-bottom:28px}
        .hero-cta{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-bottom:36px}
        .hero-trust{display:flex;align-items:center;gap:12px;font-size:13px;color:#64748b}
        .hero-trust svg{color:#4ade80;flex-shrink:0}
        .hero-trust span{color:#94a3b8}

        /* ── Semáforo card ───────────────────────────────────────── */
        .sema-card{background:#fff;border-radius:16px;padding:28px;box-shadow:var(--shadow-lg);border:1px solid var(--border)}
        .sema-header{display:flex;align-items:center;gap:10px;margin-bottom:20px}
        .sema-icon{width:36px;height:36px;border-radius:var(--radius-sm);background:var(--green-light);display:flex;align-items:center;justify-content:center;color:var(--green);flex-shrink:0}
        .sema-title{font-size:14px;font-weight:700;color:var(--ink)}
        .sema-subtitle{font-size:12px;color:var(--muted)}
        .sema-file{display:flex;align-items:center;gap:10px;background:var(--bg);border:1px solid var(--border);border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:20px;font-size:13px;color:var(--ink-2)}
        .sema-file-icon{width:28px;height:28px;background:var(--red);border-radius:6px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:700;flex-shrink:0}
        .sema-progress{height:4px;background:#e2e8f0;border-radius:var(--radius-full);margin-bottom:20px;overflow:hidden}
        .sema-progress-bar{height:100%;background:linear-gradient(90deg,var(--green),#34d399);border-radius:var(--radius-full);width:78%;animation:scan 2s ease-in-out infinite alternate}
        @keyframes scan{from{width:60%}to{width:92%}}
        .sema-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)}
        .sema-row:last-child{border-bottom:none}
        .sema-row-left{display:flex;align-items:center;gap:10px;font-size:13px;font-weight:500;color:var(--ink-2)}
        .dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
        .dot-green{background:var(--green);box-shadow:0 0 6px rgba(22,163,74,.5)}
        .dot-yellow{background:var(--amber);box-shadow:0 0 6px rgba(245,158,11,.5)}
        .dot-red{background:var(--red);box-shadow:0 0 6px rgba(239,68,68,.5)}
        .sema-badge{font-size:11px;font-weight:700;padding:3px 8px;border-radius:var(--radius-full)}
        .sema-badge-green{background:var(--green-light);color:var(--green-dark)}
        .sema-badge-yellow{background:var(--amber-light);color:#92400e}
        .sema-badge-red{background:var(--red-light);color:#991b1b}
        .sema-footer{display:flex;align-items:center;justify-content:space-between;margin-top:18px;padding-top:14px;border-top:1px solid var(--border)}
        .sema-footer-text{font-size:12px;color:var(--muted)}
        .sema-footer-text strong{color:var(--ink)}

        /* ── Stat strip ──────────────────────────────────────────── */
        .stats-strip{background:#fff;border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
        .stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0}
        .stat-item{padding:32px 24px;text-align:center;border-right:1px solid var(--border)}
        .stat-item:last-child{border-right:none}
        .stat-value{font-size:36px;font-weight:800;color:var(--green);letter-spacing:-.02em;line-height:1}
        .stat-label{font-size:14px;color:var(--muted);margin-top:6px}

        /* ── Section titles ──────────────────────────────────────── */
        .section-label{font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--green);margin-bottom:10px}
        .section-title{font-size:clamp(22px,3.5vw,32px);font-weight:800;letter-spacing:-.02em;color:var(--ink);line-height:1.2;margin-bottom:16px}
        .section-sub{font-size:16px;color:var(--muted);max-width:520px}
        .section-head{margin-bottom:52px}

        /* ── Problema ────────────────────────────────────────────── */
        .problema-bg{background:#fff}
        .pain-grid{display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:center}
        .pain-list{display:flex;flex-direction:column;gap:16px;margin-top:28px}
        .pain-item{display:flex;align-items:flex-start;gap:16px;padding:18px;background:var(--bg);border:1px solid var(--border);border-radius:var(--radius)}
        .pain-icon{width:40px;height:40px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0;background:var(--red-light);color:var(--red)}
        .pain-text h4{font-size:14px;font-weight:700;margin-bottom:3px}
        .pain-text p{font-size:13px;color:var(--muted);margin:0}
        .pain-visual{background:linear-gradient(135deg,#fef2f2,#fee2e2);border-radius:16px;padding:32px;display:flex;flex-direction:column;gap:12px}
        .pv-row{display:flex;align-items:center;justify-content:space-between;background:#fff;border-radius:var(--radius-sm);padding:12px 16px;box-shadow:var(--shadow-sm)}
        .pv-file{display:flex;align-items:center;gap:10px;font-size:13px;font-weight:500}
        .pv-file-dot{width:8px;height:8px;border-radius:50%;background:var(--red);flex-shrink:0}
        .pv-status{font-size:12px;font-weight:700;color:var(--red);background:var(--red-light);padding:3px 8px;border-radius:var(--radius-full)}

        /* ── Cómo funciona ───────────────────────────────────────── */
        .steps-bg{background:var(--bg)}
        .steps-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:28px;position:relative}
        .steps-grid::before{content:'';position:absolute;top:28px;left:calc(16.67% + 14px);right:calc(16.67% + 14px);height:2px;background:linear-gradient(90deg,var(--green),var(--blue));border-radius:var(--radius-full)}
        .step-card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:28px;box-shadow:var(--shadow-sm);transition:transform .2s,box-shadow .2s}
        .step-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
        .step-num{width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;margin-bottom:20px;position:relative;z-index:1}
        .step-num-1{background:var(--blue-light);color:var(--blue)}
        .step-num-2{background:var(--amber-light);color:#92400e}
        .step-num-3{background:var(--green-light);color:var(--green-dark)}
        .step-card h3{font-size:16px;font-weight:700;margin-bottom:8px}
        .step-card p{font-size:14px;color:var(--muted);line-height:1.6}
        .step-detail{margin-top:16px;padding:12px;background:var(--bg);border-radius:var(--radius-sm);font-size:12px;color:var(--ink-2);display:flex;align-items:center;gap:8px}

        /* ── Audience ────────────────────────────────────────────── */
        .audience-bg{background:#fff}
        .roles-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
        .role-card{border:1px solid var(--border);border-radius:16px;padding:28px;background:var(--bg);text-align:center;transition:transform .2s,border-color .2s,box-shadow .2s}
        .role-card:hover{transform:translateY(-4px);border-color:var(--green);box-shadow:var(--shadow)}
        .role-avatar{width:60px;height:60px;border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:24px}
        .role-card h3{font-size:15px;font-weight:700;margin-bottom:8px}
        .role-card p{font-size:13px;color:var(--muted)}

        /* ── Resultados ──────────────────────────────────────────── */
        .results-bg{background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#0f2d1a 100%);color:#fff}
        .results-bg .section-title{color:#fff}
        .results-bg .section-sub{color:#94a3b8}
        .results-bg .section-label{color:#4ade80}
        .kpi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:48px}
        .kpi-card{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:28px;text-align:center}
        .kpi-value{font-size:42px;font-weight:800;color:#4ade80;letter-spacing:-.02em;line-height:1}
        .kpi-label{font-size:14px;color:#94a3b8;margin-top:8px}
        .testimonial{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:32px;max-width:680px;margin:0 auto}
        .quote-text{font-size:17px;line-height:1.7;color:#e2e8f0;font-style:italic;margin-bottom:20px}
        .quote-author{display:flex;align-items:center;gap:14px}
        .quote-avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--green),#34d399);display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff}
        .quote-info strong{display:block;font-size:14px;font-weight:700;color:#f1f5f9}
        .quote-info span{font-size:12px;color:#64748b}

        /* ── CTA Banner ──────────────────────────────────────────── */
        .cta-banner{background:var(--green);padding:64px 0;text-align:center}
        .cta-banner h2{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#fff;margin-bottom:12px}
        .cta-banner p{font-size:16px;color:rgba(255,255,255,.8);margin-bottom:32px}
        .cta-banner-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}

        /* ── Contact form ────────────────────────────────────────── */
        .contact-bg{background:#fff}
        .contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:start}
        .contact-info h3{font-size:20px;font-weight:700;margin-bottom:8px}
        .contact-info p{font-size:15px;color:var(--muted);margin-bottom:24px}
        .contact-detail{display:flex;align-items:center;gap:12px;font-size:14px;color:var(--ink-2);margin-bottom:14px}
        .contact-detail svg{color:var(--green);flex-shrink:0}
        .form-card{background:var(--bg);border:1px solid var(--border);border-radius:16px;padding:32px;box-shadow:var(--shadow)}
        .form-row{margin-bottom:16px}
        .form-label{display:block;font-size:13px;font-weight:600;color:var(--ink);margin-bottom:6px}
        .form-input{width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:14px;font-family:inherit;color:var(--ink);background:#fff;transition:border-color .15s,box-shadow .15s;outline:none}
        .form-input:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(22,163,74,.12)}
        .form-2col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .form-privacy{font-size:12px;color:var(--muted);margin-top:6px}
        .form-success{display:none;text-align:center;padding:24px;color:var(--green-dark)}
        .form-success svg{margin:0 auto 12px}

        /* ── Footer ──────────────────────────────────────────────── */
        .footer{background:#0f172a;color:#94a3b8;padding:40px 0 28px}
        .footer-inner{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px}
        .footer-logo{font-size:16px;font-weight:800;color:#fff}
        .footer-links{display:flex;gap:20px;font-size:13px}
        .footer-links a{color:#64748b;transition:color .15s}
        .footer-links a:hover{color:#fff}
        .footer-copy{font-size:12px;color:#475569;width:100%;text-align:center;margin-top:20px;padding-top:20px;border-top:1px solid #1e293b}

        /* ── Responsive ──────────────────────────────────────────── */
        @media(max-width:900px){
            .hero-grid,.pain-grid,.contact-grid{grid-template-columns:1fr}
            .steps-grid{grid-template-columns:1fr}
            .steps-grid::before{display:none}
            .kpi-grid,.roles-grid,.stats-grid{grid-template-columns:1fr 1fr}
            .stat-item{border-right:none;border-bottom:1px solid var(--border)}
            .stat-item:last-child{border-bottom:none}
            .form-2col{grid-template-columns:1fr}
            section{padding:48px 0}
        }
        @media(max-width:560px){
            .kpi-grid,.roles-grid,.stats-grid{grid-template-columns:1fr}
            .hero-cta{flex-direction:column;align-items:flex-start}
            .navbar-links .nav-link:not(.btn){display:none}
        }

        /* ── Scroll animations ───────────────────────────────────── */
        .fade-up{opacity:0;transform:translateY(24px);transition:opacity .5s ease,transform .5s ease}
        .fade-up.visible{opacity:1;transform:none}
    </style>
</head>
<body>

<!-- ══════════════════════ NAVBAR ══════════════════════ -->
<nav class="navbar">
    <div class="wrap navbar-inner">
        <a href="/" class="navbar-logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
            PrintCheck AI
        </a>
        <div class="navbar-links">
            <a href="#como" class="nav-link">Cómo funciona</a>
            <a href="#resultados" class="nav-link">Resultados</a>
            <a href="#contact" class="nav-link">Contacto</a>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-outline btn-sm" style="margin-left:4px">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm" style="margin-left:4px">Registro gratis</a>
                    @endif
                @endauth
            @endif
        </div>
    </div>
</nav>

<!-- ══════════════════════ HERO ══════════════════════════ -->
<section class="hero">
    <div class="wrap hero-grid">
        <div>
            <div class="hero-eyebrow">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor"><circle cx="6" cy="6" r="6"/></svg>
                MVP en acceso anticipado — prueba gratuita 14 días
            </div>
            <h1>Elimina errores de imprenta <em>antes</em> de llegar a prensa</h1>
            <p class="hero-lead">Sube un PDF y en 20 segundos tienes un reporte técnico, presupuesto estimado y notificación al jefe de producción. Menos retrabajo, más entregas a tiempo.</p>
            <div class="hero-cta">
                <a href="#contact" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    Empezar prueba gratuita
                </a>
                <a href="#como" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.2)">Ver cómo funciona →</a>
            </div>
            <div class="hero-trust">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Sin tarjeta de crédito</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:8px"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Sin integración compleja</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:8px"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Cancela cuando quieras</span>
            </div>
        </div>

        <!-- Semáforo panel -->
        <div>
            <div class="sema-card">
                <div class="sema-header">
                    <div class="sema-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div>
                        <div class="sema-title">Resultado del análisis</div>
                        <div class="sema-subtitle">Analizando en tiempo real…</div>
                    </div>
                </div>
                <div class="sema-file">
                    <div class="sema-file-icon">PDF</div>
                    <div>
                        <div style="font-weight:600;font-size:13px">catalogo_verano_2026.pdf</div>
                        <div style="font-size:11px;color:var(--muted)">24 páginas · 48.2 MB · CMYK</div>
                    </div>
                </div>
                <div class="sema-progress"><div class="sema-progress-bar"></div></div>
                <div class="sema-row">
                    <div class="sema-row-left"><span class="dot dot-green"></span> Sangrado y marcas de corte</div>
                    <span class="sema-badge sema-badge-green">OK</span>
                </div>
                <div class="sema-row">
                    <div class="sema-row-left"><span class="dot dot-green"></span> Tipografías incrustadas</div>
                    <span class="sema-badge sema-badge-green">OK</span>
                </div>
                <div class="sema-row">
                    <div class="sema-row-left"><span class="dot dot-yellow"></span> Resolución de imágenes</div>
                    <span class="sema-badge sema-badge-yellow">2 avisos</span>
                </div>
                <div class="sema-row">
                    <div class="sema-row-left"><span class="dot dot-red"></span> Perfil de color ICC</div>
                    <span class="sema-badge sema-badge-red">1 crítico</span>
                </div>
                <div class="sema-footer">
                    <div class="sema-footer-text">Análisis completado en <strong>18 s</strong> · Presupuesto: <strong>€ 1.240</strong></div>
                    <a href="#contact" class="btn btn-primary btn-sm">Aprobar →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════ STATS ══════════════════════════ -->
<div class="stats-strip">
    <div class="wrap">
        <div class="stats-grid">
            <div class="stat-item fade-up">
                <div class="stat-value">40%</div>
                <div class="stat-label">menos tiempo en revisión de archivos</div>
            </div>
            <div class="stat-item fade-up">
                <div class="stat-value">30%</div>
                <div class="stat-label">menos errores detectados en prensa</div>
            </div>
            <div class="stat-item fade-up">
                <div class="stat-value">20s</div>
                <div class="stat-label">para obtener presupuesto (antes 30 min)</div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════ PROBLEMA ═══════════════════════ -->
<section class="problema-bg" id="problema">
    <div class="wrap">
        <div class="pain-grid">
            <div class="fade-up">
                <p class="section-label">El problema</p>
                <h2 class="section-title">Cada error en prensa cuesta tiempo y dinero</h2>
                <p style="font-size:15px;color:var(--muted);line-height:1.7">Los errores de sangrado, resolución o tipografía se detectan demasiado tarde. El jefe de producción pierde horas validando manualmente cada archivo antes de dar luz verde.</p>
                <div class="pain-list">
                    <div class="pain-item">
                        <div class="pain-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </div>
                        <div class="pain-text">
                            <h4>Revisión manual lenta y propensa a errores</h4>
                            <p>Un técnico tarda entre 15 y 45 min por archivo. Imposible escalar.</p>
                        </div>
                    </div>
                    <div class="pain-item">
                        <div class="pain-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                        </div>
                        <div class="pain-text">
                            <h4>Errores en prensa = costes elevados</h4>
                            <p>Reimpresiones y retrabajo consumen hasta un 12% del margen.</p>
                        </div>
                    </div>
                    <div class="pain-item">
                        <div class="pain-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div class="pain-text">
                            <h4>Presupuestos lentos pierden ventas</h4>
                            <p>Calcular manualmente tarda 10–30 min. El cliente ya pasó al siguiente proveedor.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fade-up" style="animation-delay:.15s">
                <div class="pain-visual">
                    <div style="font-size:13px;font-weight:700;color:#991b1b;margin-bottom:4px">Cola de revisión — hoy</div>
                    <div class="pv-row"><div class="pv-file"><div class="pv-file-dot"></div>folleto_A4_final_v3.pdf</div><div class="pv-status">Error ICC</div></div>
                    <div class="pv-row"><div class="pv-file"><div class="pv-file-dot" style="background:var(--amber)"></div>etiqueta_batch_92.pdf</div><div class="pv-status" style="color:#92400e;background:var(--amber-light)">Res. baja</div></div>
                    <div class="pv-row"><div class="pv-file"><div class="pv-file-dot"></div>catalogo_primavera.pdf</div><div class="pv-status">Sin sangrado</div></div>
                    <div style="font-size:12px;color:#991b1b;text-align:center;margin-top:12px;font-weight:600">3 archivos bloqueados · ~2h de revisión manual</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════ CÓMO FUNCIONA ══════════════════ -->
<section class="steps-bg" id="como">
    <div class="wrap">
        <div class="section-head fade-up">
            <p class="section-label">Cómo funciona</p>
            <h2 class="section-title">De PDF a prensa en 3 pasos</h2>
            <p class="section-sub">Sin instalación, sin formación técnica. Empieza a analizar archivos en menos de 5 minutos.</p>
        </div>
        <div class="steps-grid">
            <div class="step-card fade-up">
                <div class="step-num step-num-1">1</div>
                <h3>Subir PDF</h3>
                <p>Drag &amp; drop o selección. Previsualización instantánea de portada, metadatos y número de páginas.</p>
                <div class="step-detail">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Barra de progreso + miniatura en tiempo real
                </div>
            </div>
            <div class="step-card fade-up" style="transition-delay:.1s">
                <div class="step-num step-num-2">2</div>
                <h3>Análisis automático</h3>
                <p>El motor detecta sangrados, imágenes de baja resolución, tipografías faltantes y perfiles de color ICC.</p>
                <div class="step-detail">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Semáforo verde/amarillo/rojo con detalle por error
                </div>
            </div>
            <div class="step-card fade-up" style="transition-delay:.2s">
                <div class="step-num step-num-3">3</div>
                <h3>Presupuesto y notificación</h3>
                <p>Precio estimado instantáneo según tirada, papel y acabados. Notificación al responsable para "Aprobar y enviar a prensa".</p>
                <div class="step-detail">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Email / SMS / Slack en segundos
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════ AUDIENCIA ══════════════════════ -->
<section class="audience-bg" id="audiencia">
    <div class="wrap">
        <div class="section-head fade-up" style="text-align:center">
            <p class="section-label">Para quién es</p>
            <h2 class="section-title">Diseñado para equipos de imprenta</h2>
            <p class="section-sub" style="margin:0 auto">Imprentas de cualquier tamaño que necesitan reducir errores, acelerar entregas y cerrar más presupuestos.</p>
        </div>
        <div class="roles-grid fade-up">
            <div class="role-card">
                <div class="role-avatar" style="background:#dbeafe;font-size:28px">🖨️</div>
                <h3>Jefe de producción</h3>
                <p>Aprueba archivos desde el móvil. Recibe solo los PDFs que han pasado todos los checks técnicos.</p>
            </div>
            <div class="role-card">
                <div class="role-avatar" style="background:#dcfce7;font-size:28px">🔍</div>
                <h3>Técnico de preimpresión</h3>
                <p>Elimina revisiones manuales repetitivas. El sistema detecta el 95% de los problemas habituales.</p>
            </div>
            <div class="role-card">
                <div class="role-avatar" style="background:#fef3c7;font-size:28px">💼</div>
                <h3>Comercial / Ventas</h3>
                <p>Genera presupuestos en 20 segundos mientras el cliente espera. Cierra más ventas, más rápido.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════ RESULTADOS ═════════════════════ -->
<section class="results-bg" id="resultados">
    <div class="wrap">
        <div class="section-head fade-up" style="text-align:center">
            <p class="section-label">Resultados</p>
            <h2 class="section-title">Números reales en imprentas piloto</h2>
            <p class="section-sub" style="margin:0 auto">Implementaciones tempranas con acceso anticipado muestran resultados desde la primera semana.</p>
        </div>
        <div class="kpi-grid fade-up">
            <div class="kpi-card">
                <div class="kpi-value">40%</div>
                <div class="kpi-label">menos tiempo en revisión de archivos</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value">30%</div>
                <div class="kpi-label">menos errores detectados en prensa</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value">20 s</div>
                <div class="kpi-label">de presupuesto (antes 10–30 minutos)</div>
            </div>
        </div>
        <div class="testimonial fade-up">
            <p class="quote-text">"PrintCheck AI redujo nuestro retrabajo a la mitad y el jefe de producción aprueba archivos desde el móvil. En dos semanas ya amortizamos la suscripción."</p>
            <div class="quote-author">
                <div class="quote-avatar">SG</div>
                <div class="quote-info">
                    <strong>Sara G., Directora de Producción</strong>
                    <span>Imprenta Soluciones Gráficas S.A.</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════ CTA BANNER ═════════════════════ -->
<section class="cta-banner">
    <div class="wrap fade-up">
        <h2>Empieza hoy — 14 días gratis, sin compromiso</h2>
        <p>Sin tarjeta de crédito. Sin integración compleja. Comienza a analizar PDFs en minutos.</p>
        <div class="cta-banner-btns">
            <a href="#contact" class="btn btn-white">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                Empezar prueba gratuita
            </a>
            <a href="#contact" class="btn" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3)">Solicitar demo personalizada</a>
        </div>
    </div>
</section>

<!-- ══════════════════════ CONTACTO ═══════════════════════ -->
<section class="contact-bg" id="contact">
    <div class="wrap">
        <div class="contact-grid">
            <div class="fade-up">
                <p class="section-label">Contacto</p>
                <h2 class="section-title" style="font-size:26px">¿Hablamos?<br>Te preparamos una demo</h2>
                <p class="contact-info" style="font-size:15px;color:var(--muted);margin-bottom:24px">Configura una prueba con tus propios archivos. Nuestro equipo te acompaña en la puesta en marcha.</p>
                <div class="contact-detail">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    ventas@printcheck.ai
                </div>
                <div class="contact-detail">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 11 19.79 19.79 0 01.22 2.39a2 2 0 012-2H5.5a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.91 9.4a16 16 0 006.72 6.72l1.28-1.28a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                    +34 600 000 000
                </div>
                <div style="margin-top:28px;padding:18px;background:var(--green-light);border-radius:var(--radius);border-left:4px solid var(--green)">
                    <div style="font-size:13px;font-weight:700;color:var(--green-dark);margin-bottom:4px">Acceso anticipado activo</div>
                    <div style="font-size:13px;color:var(--muted)">Primeras 20 imprentas con precio especial de lanzamiento y onboarding gratuito.</div>
                </div>
            </div>
            <div class="fade-up" style="transition-delay:.1s">
                <div class="form-card">
                    <form id="contactForm" novalidate>
                        <div class="form-2col">
                            <div class="form-row">
                                <label class="form-label" for="f_name">Nombre *</label>
                                <input class="form-input" id="f_name" name="name" type="text" placeholder="Tu nombre" required />
                            </div>
                            <div class="form-row">
                                <label class="form-label" for="f_company">Empresa *</label>
                                <input class="form-input" id="f_company" name="company" type="text" placeholder="Nombre de la imprenta" required />
                            </div>
                        </div>
                        <div class="form-row">
                            <label class="form-label" for="f_email">Email profesional *</label>
                            <input class="form-input" id="f_email" name="email" type="email" placeholder="jefe@toimprenta.com" required />
                        </div>
                        <div class="form-row">
                            <label class="form-label" for="f_phone">Teléfono (opcional)</label>
                            <input class="form-input" id="f_phone" name="phone" type="tel" placeholder="+34 600 000 000" />
                        </div>
                        <div class="form-row">
                            <label class="form-label" for="f_message">¿Qué quieres hacer con PrintCheck AI?</label>
                            <textarea class="form-input" id="f_message" name="message" rows="3" placeholder="Ej.: queremos automatizar la revisión de 50 PDFs al día…" style="resize:vertical"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            Solicitar demo gratuita
                        </button>
                        <p class="form-privacy">🔒 Tus datos están seguros. No compartimos tu información con terceros.</p>
                    </form>
                    <div class="form-success" id="formSuccess">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <div style="font-size:18px;font-weight:700;margin-bottom:8px">¡Solicitud recibida!</div>
                        <div style="font-size:14px;color:var(--muted)">Te contactaremos en menos de 24h para configurar tu demo personalizada.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════ FOOTER ══════════════════════════ -->
<footer class="footer">
    <div class="wrap">
        <div class="footer-inner">
            <div class="footer-logo">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline;margin-right:6px"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                PrintCheck AI
            </div>
            <div class="footer-links">
                <a href="#">Privacidad</a>
                <a href="#">Términos</a>
                <a href="#contact">Contacto</a>
            </div>
        </div>
        <div class="footer-copy">© {{ date('Y') }} PrintCheck AI — MVP para imprentas · Todos los derechos reservados</div>
    </div>
</footer>

<script>
    // Scroll fade-up animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } });
    }, { threshold: 0.12 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    // Contact form submit feedback
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) { this.reportValidity(); return; }
        this.style.display = 'none';
        document.getElementById('formSuccess').style.display = 'block';
    });
</script>
</body>
</html>
