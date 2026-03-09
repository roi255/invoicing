<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ROI Invoicing') }} — Professional Invoicing</title>
    <meta name="description" content="Modern invoicing software for professionals. Create invoices, track payments, and grow your business.">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent: #6366f1;
            --accent-2: #8b5cf6;
            --accent-3: #06b6d4;
            --glow: rgba(99,102,241,.35);
            --glass-bg: rgba(255,255,255,.04);
            --glass-border: rgba(255,255,255,.09);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --surface: #0f0f1a;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: #050510;
            color: var(--text-primary);
            overflow-x: hidden;
        }

        /* ── Three.js canvas ──────────────────────────────────────── */
        #bg {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        /* ── Layout wrappers ────────────────────────────────────────── */
        .relative { position: relative; z-index: 1; }

        /* ── Navigation ─────────────────────────────────────────────── */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(5,5,16,.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: -.02em;
            text-decoration: none;
            color: var(--text-primary);
        }

        .nav-logo svg { width: 28px; height: 28px; }

        .nav-logo span { color: var(--accent); }

        .nav-actions { display: flex; align-items: center; gap: .75rem; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1.25rem;
            border-radius: 8px;
            font-size: .875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all .2s ease;
            border: 1px solid transparent;
        }

        .btn-ghost {
            color: var(--text-secondary);
            border-color: var(--glass-border);
            background: transparent;
        }

        .btn-ghost:hover {
            color: var(--text-primary);
            border-color: rgba(255,255,255,.2);
            background: var(--glass-bg);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #fff;
            box-shadow: 0 0 24px var(--glow);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 0 36px var(--glow);
        }

        /* ── Hero ────────────────────────────────────────────────────── */
        #hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 8rem 3rem 4rem;
            max-width: 1280px;
            margin: 0 auto;
            width: 100%;
        }

        .hero-inner {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            width: 100%;
        }

        .hero-left {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .hero-right {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-features {
            display: flex;
            flex-direction: column;
            gap: .65rem;
            margin: 1.5rem 0 2rem;
        }

        .hero-feature-item {
            display: flex;
            align-items: center;
            gap: .65rem;
            font-size: .875rem;
            color: var(--text-secondary);
        }

        .hero-feature-item svg {
            flex-shrink: 0;
            width: 16px;
            height: 16px;
            color: #34d399;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(99,102,241,.12);
            border: 1px solid rgba(99,102,241,.3);
            border-radius: 100px;
            padding: .35rem .9rem;
            font-size: .78rem;
            font-weight: 600;
            color: #a5b4fc;
            letter-spacing: .04em;
            text-transform: uppercase;
            margin-bottom: 1.75rem;
        }

        .badge-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #818cf8;
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .5; transform: scale(1.4); }
        }

        .hero-title {
            font-size: clamp(2.4rem, 5vw, 4rem);
            font-weight: 900;
            letter-spacing: -.04em;
            line-height: 1.05;
            margin-bottom: 1.25rem;
        }

        .hero-title .line-1 { display: block; color: var(--text-primary); }

        .hero-title .line-2 {
            display: block;
            background: linear-gradient(135deg, #818cf8 0%, #c084fc 45%, #67e8f9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-sub {
            font-size: 1.05rem;
            color: var(--text-secondary);
            line-height: 1.75;
            font-weight: 400;
        }

        .hero-cta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-lg {
            padding: .8rem 2rem;
            font-size: 1rem;
            border-radius: 10px;
        }

        /* ── Stats bar ──────────────────────────────────────────────── */
        .stats-bar {
            display: flex;
            gap: 3rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 4rem;
            padding-top: 3rem;
            border-top: 1px solid var(--glass-border);
            width: 100%;
        }

        .stat { text-align: center; }

        .stat-num {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -.03em;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: .82rem;
            color: var(--text-secondary);
            font-weight: 500;
            margin-top: .25rem;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        /* ── Floating invoice mock-up ────────────────────────────────── */
        .hero-visual {
            position: relative;
            width: 100%;
        }

        .invoice-card {
            background: rgba(15,15,30,.85);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2rem;
            backdrop-filter: blur(20px);
            box-shadow:
                0 0 0 1px rgba(99,102,241,.15),
                0 40px 80px rgba(0,0,0,.6),
                0 0 60px rgba(99,102,241,.1);
            animation: float 6s ease-in-out infinite;
            text-align: left;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotateX(2deg); }
            50% { transform: translateY(-12px) rotateX(-1deg); }
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .invoice-brand {
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--accent);
        }

        .invoice-num {
            font-size: .75rem;
            color: var(--text-secondary);
        }

        .invoice-badge {
            background: rgba(16,185,129,.15);
            color: #34d399;
            border: 1px solid rgba(16,185,129,.3);
            border-radius: 6px;
            padding: .25rem .7rem;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .invoice-row {
            display: flex;
            justify-content: space-between;
            padding: .6rem 0;
            border-bottom: 1px solid var(--glass-border);
            font-size: .85rem;
        }

        .invoice-row:last-of-type { border-bottom: none; }

        .invoice-row .label { color: var(--text-secondary); }

        .invoice-row .value { font-weight: 600; }

        .invoice-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, rgba(99,102,241,.15), rgba(139,92,246,.1));
            border: 1px solid rgba(99,102,241,.25);
            border-radius: 10px;
        }

        .invoice-total .total-label {
            font-size: .8rem;
            color: var(--text-secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .invoice-total .total-amount {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -.03em;
            color: var(--text-primary);
        }

        /* ── Section container ──────────────────────────────────────── */
        .section {
            padding: 6rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-label {
            display: inline-block;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: .75rem;
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            letter-spacing: -.04em;
            line-height: 1.1;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .section-sub {
            font-size: 1rem;
            color: var(--text-secondary);
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* ── Feature grid ────────────────────────────────────────────── */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
        }

        .feature-card {
            position: relative;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.75rem;
            overflow: hidden;
            transition: all .3s ease;
            cursor: default;
            transform-style: preserve-3d;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at var(--mx, 50%) var(--my, 50%), rgba(99,102,241,.12), transparent 60%);
            opacity: 0;
            transition: opacity .3s ease;
            pointer-events: none;
        }

        .feature-card:hover {
            border-color: rgba(99,102,241,.35);
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,.4), 0 0 30px rgba(99,102,241,.08);
        }

        .feature-card:hover::before { opacity: 1; }

        .feature-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.2rem;
        }

        .feature-icon svg { width: 22px; height: 22px; }

        .icon-indigo { background: rgba(99,102,241,.15); color: #818cf8; }
        .icon-violet { background: rgba(139,92,246,.15); color: #c084fc; }
        .icon-cyan   { background: rgba(6,182,212,.15); color: #67e8f9; }
        .icon-emerald{ background: rgba(16,185,129,.15); color: #34d399; }
        .icon-amber  { background: rgba(245,158,11,.15); color: #fbbf24; }
        .icon-pink   { background: rgba(236,72,153,.15); color: #f472b6; }
        .icon-sky    { background: rgba(14,165,233,.15); color: #38bdf8; }
        .icon-rose   { background: rgba(244,63,94,.15); color: #fb7185; }

        .feature-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: .5rem;
            letter-spacing: -.01em;
        }

        .feature-desc {
            font-size: .875rem;
            color: var(--text-secondary);
            line-height: 1.65;
        }

        .feature-tag {
            display: inline-flex;
            margin-top: 1rem;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--accent);
            background: rgba(99,102,241,.1);
            border: 1px solid rgba(99,102,241,.2);
            padding: .2rem .6rem;
            border-radius: 4px;
        }

        /* ── Workflow section ────────────────────────────────────────── */
        .workflow {
            background: rgba(255,255,255,.02);
            border-top: 1px solid var(--glass-border);
            border-bottom: 1px solid var(--glass-border);
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0;
            counter-reset: steps;
        }

        .step {
            position: relative;
            padding: 2.5rem 2rem;
            text-align: center;
            border-right: 1px solid var(--glass-border);
            counter-increment: steps;
        }

        .step:last-child { border-right: none; }

        .step::before {
            content: counter(steps, decimal-leading-zero);
            display: block;
            font-size: 3rem;
            font-weight: 900;
            color: rgba(99,102,241,.2);
            letter-spacing: -.05em;
            line-height: 1;
            margin-bottom: 1rem;
        }

        .step-title {
            font-size: .95rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: .5rem;
        }

        .step-desc {
            font-size: .82rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* ── CTA section ─────────────────────────────────────────────── */
        #cta {
            text-align: center;
            padding: 8rem 2rem;
        }

        .cta-glow {
            position: relative;
            display: inline-block;
        }

        .cta-glow::before {
            content: '';
            position: absolute;
            inset: -60px;
            background: radial-gradient(ellipse, rgba(99,102,241,.25) 0%, transparent 70%);
            pointer-events: none;
        }

        .cta-title {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 900;
            letter-spacing: -.05em;
            line-height: 1.05;
            margin-bottom: 1.25rem;
        }

        .cta-title span {
            background: linear-gradient(135deg, #818cf8, #c084fc, #67e8f9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .cta-sub {
            font-size: 1.05rem;
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
        }

        /* ── Footer ──────────────────────────────────────────────────── */
        footer {
            border-top: 1px solid var(--glass-border);
            padding: 2rem;
            text-align: center;
            font-size: .82rem;
            color: var(--text-secondary);
        }

        footer strong { color: var(--text-primary); }

        /* ── Divider with gradient ───────────────────────────────────── */
        .gradient-line {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,.5), rgba(139,92,246,.5), transparent);
            margin: 0 auto;
            max-width: 600px;
        }

        /* ── System stats section ───────────────────────────────────── */
        #system-stats { padding: 5rem 2rem; }

        .stats-table-wrap {
            max-width: 820px;
            margin: 0 auto;
            background: rgba(15,15,30,.85);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            backdrop-filter: blur(24px);
            box-shadow: 0 0 0 1px rgba(99,102,241,.1), 0 40px 80px rgba(0,0,0,.5);
        }

        .stats-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .stats-table-title {
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--text-secondary);
        }

        .live-dot {
            display: flex;
            align-items: center;
            gap: .45rem;
            font-size: .72rem;
            font-weight: 600;
            color: #34d399;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .live-dot::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #34d399;
            animation: pulse-dot 2s ease-in-out infinite;
        }

        .stat-row {
            display: grid;
            grid-template-columns: 2.5rem 1fr auto;
            align-items: center;
            gap: 1.25rem;
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid var(--glass-border);
            transition: background .2s ease;
        }

        .stat-row:last-child { border-bottom: none; }

        .stat-row:hover { background: rgba(99,102,241,.05); }

        .stat-row-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-row-icon svg { width: 18px; height: 18px; }

        .stat-row-info { min-width: 0; }

        .stat-row-label {
            font-size: .9rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: .15rem;
        }

        .stat-row-desc {
            font-size: .76rem;
            color: var(--text-secondary);
        }

        .stat-row-value {
            text-align: right;
            flex-shrink: 0;
        }

        .stat-row-num {
            font-size: 2rem;
            font-weight: 900;
            letter-spacing: -.04em;
            line-height: 1;
        }

        .stat-row-unit {
            font-size: .7rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-top: .2rem;
        }

        /* ── Auth section ────────────────────────────────────────────── */
        #auth { padding: 5rem 2rem 6rem; }

        .auth-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            max-width: 900px;
            margin: 0 auto;
        }

        .auth-card {
            background: rgba(15,15,30,.9);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2.25rem 2rem;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 0 0 1px rgba(99,102,241,.1), 0 30px 60px rgba(0,0,0,.5);
            transition: border-color .3s ease;
        }

        .auth-card:hover { border-color: rgba(99,102,241,.3); }

        .auth-card-header {
            margin-bottom: 1.75rem;
        }

        .auth-card-title {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -.03em;
            color: var(--text-primary);
            margin-bottom: .35rem;
        }

        .auth-card-sub {
            font-size: .82rem;
            color: var(--text-secondary);
        }

        .form-group { margin-bottom: 1.1rem; }

        .form-label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: .45rem;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .form-input {
            width: 100%;
            background: rgba(255,255,255,.05);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            padding: .65rem .9rem;
            font-size: .9rem;
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .form-input::placeholder { color: rgba(148,163,184,.45); }

        .form-input:focus {
            border-color: rgba(99,102,241,.6);
            box-shadow: 0 0 0 3px rgba(99,102,241,.12);
        }

        .form-input.is-error { border-color: rgba(248,113,113,.6); }

        .form-error {
            display: block;
            font-size: .76rem;
            color: #fca5a5;
            margin-top: .35rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .82rem;
            color: var(--text-secondary);
            margin-bottom: 1.25rem;
            cursor: pointer;
        }

        .form-check input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .btn-form {
            width: 100%;
            padding: .75rem 1rem;
            font-size: .9rem;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all .2s ease;
        }

        .btn-form-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #fff;
            box-shadow: 0 0 24px var(--glow);
        }

        .btn-form-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 0 36px var(--glow);
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin: 1.25rem 0;
            color: var(--text-secondary);
            font-size: .75rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--glass-border);
        }

        /* Password strength bar */
        .pw-strength {
            height: 3px;
            border-radius: 2px;
            background: var(--glass-border);
            margin-top: .5rem;
            overflow: hidden;
        }

        .pw-strength-bar {
            height: 100%;
            width: 0;
            border-radius: 2px;
            transition: width .3s ease, background .3s ease;
        }

        /* Logged-in auth card */
        .auth-logged-in {
            max-width: 420px;
            margin: 0 auto;
            text-align: center;
        }

        .auth-logged-in .check-circle {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: rgba(16,185,129,.15);
            border: 1px solid rgba(16,185,129,.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .auth-logged-in .check-circle svg { color: #34d399; }

        /* ── Responsive ─────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .auth-grid { grid-template-columns: 1fr; }
            .form-row  { grid-template-columns: 1fr; }
        }

        @media (max-width: 960px) {
            .hero-inner {
                grid-template-columns: 1fr;
                gap: 3rem;
                text-align: center;
            }
            .hero-left { align-items: center; }
            .hero-cta  { justify-content: center; }
            .hero-feature-item { justify-content: center; }
            #hero { padding: 7rem 2rem 3rem; }
        }

        @media (max-width: 640px) {
            .stats-bar { gap: 2rem; }
            .step { border-right: none; border-bottom: 1px solid var(--glass-border); }
            .step:last-child { border-bottom: none; }
            nav { padding: .75rem 1rem; }
            .nav-logo span.logo-full { display: none; }
        }
    </style>
</head>
<body>

<canvas id="bg"></canvas>

<div class="relative">

    <!-- ── Navigation ─────────────────────────────────────────────── -->
    <nav>
        <a href="/" class="nav-logo">
            <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="32" height="32" rx="8" fill="url(#logo-grad)"/>
                <path d="M8 10h12M8 14h8M8 18h10M8 22h6" stroke="white" stroke-width="2" stroke-linecap="round"/>
                <path d="M22 18l3 3-3 3" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <defs>
                    <linearGradient id="logo-grad" x1="0" y1="0" x2="32" y2="32">
                        <stop stop-color="#6366f1"/>
                        <stop offset="1" stop-color="#8b5cf6"/>
                    </linearGradient>
                </defs>
            </svg>
            <span class="logo-full">{{ config('app.name', 'ROI Invoicing') }}</span>
            <span style="display:none" class="logo-short">ROI</span>
        </a>
        <div class="nav-actions">
            @auth
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-ghost">Sign Out</button>
                </form>
                <a href="{{ url('/admin') }}" class="btn btn-primary">Open Dashboard</a>
            @else
                <a href="{{ url('/admin/login') }}" class="btn btn-ghost">Sign In</a>
                <a href="#auth" class="btn btn-primary">Get Started</a>
            @endauth
        </div>
    </nav>

    <!-- ── Hero ───────────────────────────────────────────────────── -->
    <section id="hero" style="position:relative;">

        <div class="hero-inner">

            <!-- Left — description ──────────────────────────────── -->
            <div class="hero-left">
                <div class="badge">
                    <span class="badge-dot"></span>
                    ROI Invoicing Dashboard
                </div>

                <h1 class="hero-title">
                    <span class="line-1">Your business,</span>
                    <span class="line-2">beautifully billed</span>
                </h1>

                <p class="hero-sub">
                    A purpose-built invoicing dashboard for professionals who care about
                    the details. From first quote to final payment — every step is
                    handled with precision, speed, and clarity.
                </p>

                <div class="hero-features">
                    <div class="hero-feature-item">
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Create &amp; send professional PDF invoices in seconds
                    </div>
                    <div class="hero-feature-item">
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Manage individual clients and company contacts in one place
                    </div>
                    <div class="hero-feature-item">
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Track payments, flag overdue invoices, and automate reminders
                    </div>
                    <div class="hero-feature-item">
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Real-time revenue dashboard — always know where you stand
                    </div>
                </div>

                <div class="hero-cta">
                    @auth
                        <a href="{{ url('/admin') }}" class="btn btn-primary btn-lg">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                            Open Dashboard
                        </a>
                    @else
                        <a href="#auth" class="btn btn-primary btn-lg">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Get Started Free
                        </a>
                        <a href="{{ url('/admin/login') }}" class="btn btn-ghost btn-lg">Sign In</a>
                    @endauth
                </div>
            </div>

            <!-- Right — invoice card ─────────────────────────────── -->
            <div class="hero-right">
                <div class="hero-visual">
                    <div class="invoice-card">
                        <div class="invoice-header">
                            <div>
                                <div class="invoice-brand">INVOICE</div>
                                <div class="invoice-num">#INV-2025-0042</div>
                            </div>
                            <span class="invoice-badge">PAID</span>
                        </div>

                        <div class="invoice-row">
                            <span class="label">Client</span>
                            <span class="value">Acme Corporation</span>
                        </div>
                        <div class="invoice-row">
                            <span class="label">Web Design &amp; Development</span>
                            <span class="value">$3,200.00</span>
                        </div>
                        <div class="invoice-row">
                            <span class="label">Monthly Retainer — March</span>
                            <span class="value">$1,500.00</span>
                        </div>
                        <div class="invoice-row">
                            <span class="label">Tax (10%)</span>
                            <span class="value">$470.00</span>
                        </div>

                        <div class="invoice-total">
                            <span class="total-label">Total Due</span>
                            <span class="total-amount">$5,170.00</span>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.hero-inner -->

        <div class="stats-bar">
            <div class="stat">
                <div class="stat-num" data-target="{{ $stats['invoices'] }}">0</div>
                <div class="stat-label">Invoices Created</div>
            </div>
            <div class="stat">
                <div class="stat-num" data-target="{{ $stats['customers'] }}">0</div>
                <div class="stat-label">Customers</div>
            </div>
            <div class="stat">
                <div class="stat-num" data-target="{{ $stats['clients'] }}">0</div>
                <div class="stat-label">Clients on Platform</div>
            </div>
        </div>

    </section>

    <!-- ── System Stats ────────────────────────────────────────────── -->
    <section id="system-stats">
        <div class="section-header">
            <span class="section-label">Platform overview</span>
            <h2 class="section-title">Live system stats</h2>
            <p class="section-sub">A real-time snapshot of the ROI Invoicing platform activity.</p>
        </div>

        <div class="stats-table-wrap">
            <div class="stats-table-header">
                <span class="stats-table-title">ROI Invoicing — System</span>
                <span class="live-dot">Live</span>
            </div>

            {{-- Total Invoices ──────────────────────────────────── --}}
            <div class="stat-row">
                <div class="stat-row-icon icon-indigo">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div class="stat-row-info">
                    <div class="stat-row-label">Total Invoices</div>
                    <div class="stat-row-desc">All invoices ever created in the system</div>
                </div>
                <div class="stat-row-value">
                    <div class="stat-row-num" style="color:#818cf8;" data-target="{{ $stats['invoices'] }}">0</div>
                    <div class="stat-row-unit">invoices</div>
                </div>
            </div>

            {{-- Total Customers ─────────────────────────────────── --}}
            <div class="stat-row">
                <div class="stat-row-icon icon-violet">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="stat-row-info">
                    <div class="stat-row-label">Total Customers</div>
                    <div class="stat-row-desc">Individuals and companies billed through the platform</div>
                </div>
                <div class="stat-row-value">
                    <div class="stat-row-num" style="color:#c084fc;" data-target="{{ $stats['customers'] }}">0</div>
                    <div class="stat-row-unit">customers</div>
                </div>
            </div>

            {{-- Total Clients ───────────────────────────────────── --}}
            <div class="stat-row">
                <div class="stat-row-icon icon-emerald">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                </div>
                <div class="stat-row-info">
                    <div class="stat-row-label">Total Clients</div>
                    <div class="stat-row-desc">Registered users operating the ROI Invoicing dashboard</div>
                </div>
                <div class="stat-row-value">
                    <div class="stat-row-num" style="color:#34d399;" data-target="{{ $stats['clients'] }}">0</div>
                    <div class="stat-row-unit">clients</div>
                </div>
            </div>

        </div>
    </section>

    <div class="gradient-line"></div>

    <!-- ── Auth ────────────────────────────────────────────────────── -->
    <section id="auth">
        <div style="max-width:1200px;margin:0 auto;">
            <div class="section-header">
                <span class="section-label">Your account</span>
                @auth
                    <h2 class="section-title">Welcome back</h2>
                @else
                    <h2 class="section-title">Create your account</h2>
                    <p class="section-sub">Already have an account?
                        <a href="{{ url('/admin/login') }}" style="color:#818cf8;font-weight:600;text-decoration:none;">Sign in here →</a>
                    </p>
                @endauth
            </div>

            @auth
                {{-- Already logged in ──────────────────────────── --}}
                <div class="auth-card auth-logged-in">
                    <div class="check-circle">
                        <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div class="auth-card-title">You're signed in</div>
                    <p class="auth-card-sub" style="margin-bottom:1.5rem;">
                        Signed in as <strong style="color:var(--text-primary);">{{ auth()->user()->email }}</strong>
                    </p>
                    <a href="{{ url('/admin') }}" class="btn btn-primary btn-lg" style="display:inline-flex;gap:.5rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        Go to Dashboard
                    </a>
                </div>
            @else
                {{-- Register ─────────────────────────────────────── --}}
                <div class="auth-card" style="max-width:480px;margin:0 auto;">
                    <div class="auth-card-header">
                        <div class="auth-card-title">Create account</div>
                        <div class="auth-card-sub">Free forever · No credit card required</div>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="form-group">
                            <label class="form-label" for="reg-name">Full name</label>
                            <input
                                id="reg-name"
                                type="text"
                                name="name"
                                class="form-input {{ $errors->has('name') ? 'is-error' : '' }}"
                                value="{{ old('name') }}"
                                placeholder="Jane Smith"
                                autocomplete="name"
                                required
                            >
                            @error('name')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="reg-email">Email address</label>
                            <input
                                id="reg-email"
                                type="email"
                                name="email"
                                class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                                value="{{ old('email') }}"
                                placeholder="jane@company.com"
                                autocomplete="email"
                                required
                            >
                            @error('email')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="reg-password">Password</label>
                            <input
                                id="reg-password"
                                type="password"
                                name="password"
                                class="form-input {{ $errors->has('password') ? 'is-error' : '' }}"
                                placeholder="Minimum 8 characters"
                                autocomplete="new-password"
                                required
                                oninput="checkStrength(this.value)"
                            >
                            <div class="pw-strength">
                                <div class="pw-strength-bar" id="pw-bar"></div>
                            </div>
                            @error('password')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="reg-password-confirm">Confirm password</label>
                            <input
                                id="reg-password-confirm"
                                type="password"
                                name="password_confirmation"
                                class="form-input"
                                placeholder="Repeat password"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <button type="submit" class="btn-form btn-form-primary">
                            Create Account →
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </section>

    <div class="gradient-line"></div>

    <!-- ── Features ───────────────────────────────────────────────── -->
    <section id="features">
        <div class="section">
            <div class="section-header">
                <span class="section-label">Everything you need</span>
                <h2 class="section-title">Packed with powerful features</h2>
                <p class="section-sub">From first invoice to final payment — every step handled with precision and elegance.</p>
            </div>

            <div class="features-grid">

                <div class="feature-card">
                    <div class="feature-icon icon-indigo">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                    </div>
                    <div class="feature-title">Smart Invoice Builder</div>
                    <div class="feature-desc">Create professional invoices in seconds. Add line items, apply taxes, set due dates, and send — all in one fluid workflow.</div>
                    <span class="feature-tag">Core</span>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-violet">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="feature-title">Customer Management</div>
                    <div class="feature-desc">Manage individuals and companies. Companies get a dedicated contact person with their own email — perfect for large organisations.</div>
                    <span class="feature-tag">Customers</span>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-emerald">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </div>
                    <div class="feature-title">PDF Generation</div>
                    <div class="feature-desc">Beautiful, print-ready PDF invoices generated instantly. Consistent branding every time — ready to attach or download.</div>
                    <span class="feature-tag">Export</span>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-cyan">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <div class="feature-title">Multi-Recipient Email</div>
                    <div class="feature-desc">Send to the company address, a contact person, or both simultaneously. Flexible delivery routing built right in.</div>
                    <span class="feature-tag">Email</span>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-amber">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </div>
                    <div class="feature-title">Payment Tracking</div>
                    <div class="feature-desc">Record payments against invoices, track outstanding balances, and see at a glance what's been paid and what's overdue.</div>
                    <span class="feature-tag">Finance</span>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-pink">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </div>
                    <div class="feature-title">Automated Reminders</div>
                    <div class="feature-desc">Set it and forget it. Overdue invoices trigger polite payment reminders automatically, keeping cash flow healthy without awkward chasing.</div>
                    <span class="feature-tag">Automation</span>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-sky">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    </div>
                    <div class="feature-title">Product Catalog</div>
                    <div class="feature-desc">Store your recurring products and services. One click to add them to any invoice — no retyping, no mistakes, just speed.</div>
                    <span class="feature-tag">Catalog</span>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-rose">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                    <div class="feature-title">Dashboard & Analytics</div>
                    <div class="feature-desc">Real-time financial overview. Total revenue, outstanding amounts, recent invoices — everything at a glance from a clean dashboard.</div>
                    <span class="feature-tag">Insights</span>
                </div>

            </div>
        </div>
    </section>

    <!-- ── Workflow ────────────────────────────────────────────────── -->
    <div class="gradient-line"></div>
    <section class="workflow">
        <div class="section">
            <div class="section-header">
                <span class="section-label">How it works</span>
                <h2 class="section-title">From setup to paid in minutes</h2>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-title">Add your customers</div>
                    <div class="step-desc">Import or create clients — individuals or companies — with all their contact details in one place.</div>
                </div>
                <div class="step">
                    <div class="step-title">Build your invoice</div>
                    <div class="step-desc">Pick products from your catalog, set quantities, apply tax, and add any notes. The total calculates live.</div>
                </div>
                <div class="step">
                    <div class="step-title">Send &amp; get paid</div>
                    <div class="step-desc">Send a PDF by email to the right people. Record payments when they arrive and mark invoices as settled.</div>
                </div>
                <div class="step">
                    <div class="step-title">Stay on top</div>
                    <div class="step-desc">Let automated reminders chase overdue invoices while you focus on the work that matters.</div>
                </div>
            </div>
        </div>
    </section>
    <div class="gradient-line"></div>

    <!-- ── CTA ────────────────────────────────────────────────────── -->
    <section id="cta">
        <div class="cta-glow">
            <h2 class="cta-title">Ready to get <span>paid faster?</span></h2>
            <p class="cta-sub">Your invoicing dashboard is one click away.</p>
            <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
                @auth
                    <a href="{{ url('/admin') }}" class="btn btn-primary btn-lg">Open Dashboard →</a>
                @else
                    <a href="#auth" class="btn btn-primary btn-lg">Get Started Free →</a>
                    <a href="{{ url('/admin/login') }}" class="btn btn-ghost btn-lg">Sign In</a>
                @endauth
            </div>
        </div>
    </section>

    <!-- ── Footer ─────────────────────────────────────────────────── -->
    <footer>
        <strong>{{ config('app.name', 'ROI Invoicing') }}</strong>
        &nbsp;·&nbsp;
        <span style="color:#4f46e5;">♥</span>
    </footer>

</div><!-- /.relative -->

<!-- ── Three.js (CDN) ──────────────────────────────────────────── -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function () {
    'use strict';

    /* ── Scene setup ──────────────────────────────────────────────── */
    const canvas   = document.getElementById('bg');
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setClearColor(0x050510, 1);

    const scene  = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 200);
    camera.position.set(0, 0, 30);

    /* ── Particle field ──────────────────────────────────────────── */
    const particleCount = 2200;
    const positions     = new Float32Array(particleCount * 3);
    const colors        = new Float32Array(particleCount * 3);

    const palette = [
        new THREE.Color('#6366f1'),
        new THREE.Color('#8b5cf6'),
        new THREE.Color('#06b6d4'),
        new THREE.Color('#c084fc'),
        new THREE.Color('#38bdf8'),
    ];

    for (let i = 0; i < particleCount; i++) {
        const r = 45 + Math.random() * 35;
        const theta = Math.random() * Math.PI * 2;
        const phi   = Math.acos(2 * Math.random() - 1);
        positions[i * 3]     = r * Math.sin(phi) * Math.cos(theta);
        positions[i * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
        positions[i * 3 + 2] = r * Math.cos(phi);

        const c = palette[Math.floor(Math.random() * palette.length)];
        colors[i * 3]     = c.r;
        colors[i * 3 + 1] = c.g;
        colors[i * 3 + 2] = c.b;
    }

    const particleGeo = new THREE.BufferGeometry();
    particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    particleGeo.setAttribute('color',    new THREE.BufferAttribute(colors, 3));

    const particleMat = new THREE.PointsMaterial({
        size: 0.18,
        vertexColors: true,
        transparent: true,
        opacity: 0.7,
        sizeAttenuation: true,
    });

    const particles = new THREE.Points(particleGeo, particleMat);
    scene.add(particles);

    /* ── Floating wireframe shapes ───────────────────────────────── */
    const shapeDefs = [
        { geo: new THREE.IcosahedronGeometry(3.5, 0),  pos: [-18,  8, -10], speed: 0.004 },
        { geo: new THREE.OctahedronGeometry(2.5, 0),   pos: [ 16, -6, -12], speed: 0.006 },
        { geo: new THREE.TetrahedronGeometry(3, 0),    pos: [  0, 14, -18], speed: 0.003 },
        { geo: new THREE.IcosahedronGeometry(2, 0),    pos: [ 20, 12,  -8], speed: 0.007 },
        { geo: new THREE.OctahedronGeometry(1.8, 0),   pos: [-20, -12, -6], speed: 0.005 },
        { geo: new THREE.TorusGeometry(3, 0.4, 6, 12), pos: [ 10,  0, -15], speed: 0.008 },
        { geo: new THREE.TetrahedronGeometry(2, 0),    pos: [-10, -16, -10], speed: 0.004 },
    ];

    const shapeObjects = shapeDefs.map(({ geo, pos, speed }) => {
        const mat = new THREE.MeshBasicMaterial({
            color: palette[Math.floor(Math.random() * palette.length)],
            wireframe: true,
            transparent: true,
            opacity: 0.18,
        });
        const mesh = new THREE.Mesh(geo, mat);
        mesh.position.set(...pos);
        mesh.userData.speed = speed;
        mesh.userData.floatOffset = Math.random() * Math.PI * 2;
        scene.add(mesh);
        return mesh;
    });

    /* ── Nebula / depth fog blobs ─────────────────────────────────── */
    function makeFogBlob(color, x, y, z, size) {
        const geo = new THREE.SphereGeometry(size, 6, 6);
        const mat = new THREE.MeshBasicMaterial({
            color,
            transparent: true,
            opacity: 0.03,
            depthWrite: false,
        });
        const mesh = new THREE.Mesh(geo, mat);
        mesh.position.set(x, y, z);
        scene.add(mesh);
    }
    makeFogBlob(0x6366f1, -12, 6, -25, 16);
    makeFogBlob(0x8b5cf6,  14, -8, -30, 18);
    makeFogBlob(0x06b6d4,   0, 10, -28, 12);

    /* ── Mouse parallax ──────────────────────────────────────────── */
    const mouse     = { x: 0, y: 0 };
    const targetCam = { x: 0, y: 0 };
    document.addEventListener('mousemove', e => {
        mouse.x = (e.clientX / window.innerWidth  - 0.5) * 2;
        mouse.y = (e.clientY / window.innerHeight - 0.5) * 2;
    });

    /* ── Feature card spotlight effect ──────────────────────────── */
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width)  * 100;
            const y = ((e.clientY - rect.top)  / rect.height) * 100;
            card.style.setProperty('--mx', x + '%');
            card.style.setProperty('--my', y + '%');
        });
    });

    /* ── Stats counter animation ─────────────────────────────────── */
    function animateCounters() {
        const counters = document.querySelectorAll('[data-target]');
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const el     = entry.target;
                const target = parseInt(el.dataset.target, 10);
                const suffix = el.dataset.suffix || '';
                let start = 0;
                const duration = 1800;
                const step = timestamp => {
                    if (!start) start = timestamp;
                    const progress = Math.min((timestamp - start) / duration, 1);
                    const ease = 1 - Math.pow(1 - progress, 3);
                    el.textContent = Math.round(ease * target) + suffix;
                    if (progress < 1) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
                observer.unobserve(el);
            });
        }, { threshold: 0.3 });
        counters.forEach(c => observer.observe(c));
    }
    animateCounters();

    /* ── Password strength indicator ─────────────────────────── */
    window.checkStrength = function (val) {
        const bar = document.getElementById('pw-bar');
        if (!bar) return;
        let score = 0;
        if (val.length >= 8)  score++;
        if (val.length >= 12) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        const pct   = (score / 5) * 100;
        const color = score <= 1 ? '#f87171'
                    : score <= 3 ? '#fbbf24'
                    :              '#34d399';
        bar.style.width      = pct + '%';
        bar.style.background = color;
    };

    /* ── Scroll #auth into view smoothly when # link clicked ─── */
    document.querySelectorAll('a[href="#auth"]').forEach(a => {
        a.addEventListener('click', e => {
            e.preventDefault();
            document.getElementById('auth').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    /* ── Resize ──────────────────────────────────────────────────── */
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });

    /* ── Render loop ─────────────────────────────────────────────── */
    let clock = 0;
    function animate() {
        requestAnimationFrame(animate);
        clock += 0.01;

        /* Particle gentle rotation */
        particles.rotation.y += 0.0008;
        particles.rotation.x += 0.0003;

        /* Shape animations */
        shapeObjects.forEach(mesh => {
            mesh.rotation.x += mesh.userData.speed;
            mesh.rotation.y += mesh.userData.speed * 0.7;
            mesh.position.y += Math.sin(clock + mesh.userData.floatOffset) * 0.008;
        });

        /* Smooth camera parallax */
        targetCam.x += (mouse.x * 2.5 - targetCam.x) * 0.03;
        targetCam.y += (-mouse.y * 2.5 - targetCam.y) * 0.03;
        camera.position.x = targetCam.x;
        camera.position.y = targetCam.y;
        camera.lookAt(0, 0, 0);

        renderer.render(scene, camera);
    }

    animate();
})();
</script>

</body>
</html>
