<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Event – Giải pháp Check-in Sự kiện Thông minh | TVTECH</title>
    <meta name="description" content="Hệ thống check-in sự kiện bằng QR Code thông minh. Quản lý khách mời, màn hình chào mừng cá nhân hoá, thống kê real-time. Giải pháp của TVTECH.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --purple: #7c3aed;
            --purple-light: #a78bfa;
            --blue: #2563eb;
            --pink: #e94560;
            --dark: #0a0a1a;
            --dark2: #10102a;
            --card-bg: rgba(255,255,255,0.04);
            --border: rgba(255,255,255,0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--dark);
            color: #fff;
            overflow-x: hidden;
        }

        /* ── CANVAS PARTICLES ─────────────────────────────── */
        #particles { position: fixed; inset: 0; z-index: 0; pointer-events: none; }

        /* ── NAVBAR ───────────────────────────────────────── */
        .nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            padding: 16px 40px;
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(10,10,26,0.7);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            transition: all 0.3s;
        }
        .nav-brand {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none; color: white;
        }
        .nav-brand .logo-box {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
        }
        .nav-brand .brand-name { font-size: 1.1rem; font-weight: 800; letter-spacing: 1px; }
        .nav-brand .brand-sub  { font-size: 0.55rem; color: rgba(255,255,255,0.4); letter-spacing: 2px; display: block; }
        .nav-links { display: flex; gap: 8px; list-style: none; }
        .nav-links a {
            color: rgba(255,255,255,0.7); text-decoration: none; padding: 7px 16px;
            border-radius: 8px; font-size: 0.88rem; transition: all 0.2s;
        }
        .nav-links a:hover { background: var(--card-bg); color: white; }
        .nav-cta {
            background: linear-gradient(135deg, var(--purple), var(--blue));
            color: white !important; font-weight: 600;
            box-shadow: 0 4px 16px rgba(124,58,237,0.4);
        }
        .nav-cta:hover { filter: brightness(1.15); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(124,58,237,0.5) !important; }

        /* ── HERO ─────────────────────────────────────────── */
        .hero {
            min-height: 100vh;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 120px 40px 80px;
            position: relative; z-index: 1;
            text-align: center;
        }

        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(124,58,237,0.15);
            border: 1px solid rgba(124,58,237,0.4);
            border-radius: 20px; padding: 6px 18px;
            font-size: 0.8rem; color: var(--purple-light); font-weight: 600;
            margin-bottom: 28px;
            animation: fadeDown 0.8s ease both;
        }

        .hero-title {
            font-size: clamp(2.4rem, 6vw, 5rem);
            font-weight: 900; line-height: 1.1; letter-spacing: -1px;
            margin-bottom: 22px;
            animation: fadeDown 0.8s 0.1s ease both;
        }
        .hero-title .gradient {
            background: linear-gradient(135deg, #a78bfa, #60a5fa, #f472b6);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-sub {
            font-size: clamp(1rem, 2vw, 1.2rem);
            color: rgba(255,255,255,0.55); max-width: 620px; line-height: 1.7;
            margin-bottom: 40px;
            animation: fadeDown 0.8s 0.2s ease both;
        }

        .hero-btns {
            display: flex; gap: 14px; flex-wrap: wrap; justify-content: center;
            margin-bottom: 70px;
            animation: fadeDown 0.8s 0.3s ease both;
        }
        .btn-primary-hero {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 32px; border-radius: 12px; font-size: 1rem; font-weight: 700;
            background: linear-gradient(135deg, var(--purple), var(--blue));
            color: white; text-decoration: none; border: none; cursor: pointer;
            box-shadow: 0 8px 24px rgba(124,58,237,0.45);
            transition: all 0.25s;
        }
        .btn-primary-hero:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(124,58,237,0.6); filter: brightness(1.1); }
        .btn-outline-hero {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 32px; border-radius: 12px; font-size: 1rem; font-weight: 600;
            background: transparent; color: white; text-decoration: none;
            border: 1.5px solid rgba(255,255,255,0.2); transition: all 0.25s;
        }
        .btn-outline-hero:hover { background: rgba(255,255,255,0.07); border-color: rgba(255,255,255,0.4); transform: translateY(-2px); }

        /* 3D Event Scene */
        .scene-3d {
            perspective: 900px;
            width: 100%; max-width: 900px; margin: 0 auto;
            animation: fadeUp 1s 0.4s ease both;
        }
        .scene-inner {
            transform: rotateX(18deg) rotateY(-4deg);
            transform-style: preserve-3d;
            transition: transform 0.5s ease;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.6), 0 0 0 1px rgba(124,58,237,0.1);
            position: relative; overflow: hidden;
        }
        .scene-inner::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(124,58,237,0.06) 0%, rgba(37,99,235,0.06) 100%);
        }
        .scene-grid {
            display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;
            position: relative; z-index: 1;
        }
        .scene-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; padding: 20px;
            text-align: left; transition: all 0.3s;
        }
        .scene-card:hover { background: rgba(255,255,255,0.09); transform: translateZ(20px) translateY(-4px); }
        .scene-card .sc-icon {
            width: 42px; height: 42px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; margin-bottom: 12px;
        }
        .scene-card .sc-title { font-size: 0.82rem; font-weight: 700; color: rgba(255,255,255,0.9); margin-bottom: 4px; }
        .scene-card .sc-val  { font-size: 1.6rem; font-weight: 800; line-height: 1; }
        .scene-card .sc-sub  { font-size: 0.72rem; color: rgba(255,255,255,0.4); margin-top: 3px; }

        /* Floating QR */
        .qr-float {
            position: absolute; right: -10px; top: -10px; z-index: 10;
            width: 110px; height: 110px;
            animation: floatQr 4s ease-in-out infinite;
            filter: drop-shadow(0 8px 24px rgba(124,58,237,0.6));
        }
        @keyframes floatQr {
            0%,100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-14px) rotate(3deg); }
        }
        .qr-svg-wrap {
            width: 110px; height: 110px;
            background: white; border-radius: 16px; padding: 10px;
            display: grid; grid-template-columns: repeat(7,1fr); gap: 2px;
        }
        .qr-svg-wrap span { background: #0a0a1a; border-radius: 1px; }
        .qr-svg-wrap span.w { background: white; }

        /* ── STATS BAR ────────────────────────────────────── */
        .stats-bar {
            display: flex; justify-content: center; gap: 0;
            flex-wrap: wrap; position: relative; z-index: 1;
            margin: 0 auto; max-width: 900px;
            border-radius: 20px; overflow: hidden;
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.03);
        }
        .stat-item {
            flex: 1; min-width: 150px; padding: 28px 24px; text-align: center;
            border-right: 1px solid var(--border);
            transition: background 0.3s;
        }
        .stat-item:last-child { border-right: none; }
        .stat-item:hover { background: rgba(124,58,237,0.08); }
        .stat-num { font-size: 2rem; font-weight: 900; background: linear-gradient(135deg,#a78bfa,#60a5fa); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .stat-label { font-size: 0.78rem; color: rgba(255,255,255,0.45); margin-top: 4px; }

        /* ── SECTIONS ─────────────────────────────────────── */
        section { position: relative; z-index: 1; padding: 100px 40px; }
        .section-tag {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(124,58,237,0.12); border: 1px solid rgba(124,58,237,0.3);
            border-radius: 20px; padding: 5px 16px; font-size: 0.75rem;
            color: var(--purple-light); font-weight: 600; letter-spacing: 1px;
            text-transform: uppercase; margin-bottom: 16px;
        }
        .section-title {
            font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 800; line-height: 1.2;
            margin-bottom: 14px;
        }
        .section-sub {
            color: rgba(255,255,255,0.5); font-size: 1.05rem; max-width: 560px; line-height: 1.7;
        }
        .text-center { text-align: center; }
        .text-center .section-sub { margin: 0 auto; }

        /* ── FEATURES ─────────────────────────────────────── */
        .features-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px; margin-top: 60px; max-width: 1100px; margin-left: auto; margin-right: auto;
        }
        .feat-card {
            background: var(--card-bg); border: 1px solid var(--border);
            border-radius: 20px; padding: 32px;
            transition: all 0.35s cubic-bezier(.34,1.56,.64,1);
            cursor: default; position: relative; overflow: hidden;
        }
        .feat-card::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 50% 0%, rgba(124,58,237,0.12) 0%, transparent 70%);
            opacity: 0; transition: opacity 0.3s;
        }
        .feat-card:hover { transform: translateY(-8px) scale(1.01); border-color: rgba(124,58,237,0.35); box-shadow: 0 24px 60px rgba(0,0,0,0.4); }
        .feat-card:hover::before { opacity: 1; }
        .feat-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; margin-bottom: 20px; position: relative; z-index: 1;
        }
        .feat-title { font-size: 1.05rem; font-weight: 700; margin-bottom: 10px; position: relative; z-index: 1; }
        .feat-desc  { font-size: 0.88rem; color: rgba(255,255,255,0.5); line-height: 1.7; position: relative; z-index: 1; }
        .feat-tag {
            display: inline-block; margin-top: 14px; padding: 3px 10px;
            border-radius: 20px; font-size: 0.7rem; font-weight: 600;
            background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.5);
            position: relative; z-index: 1;
        }

        /* ── HOW IT WORKS ─────────────────────────────────── */
        .steps {
            display: flex; gap: 0; align-items: flex-start; flex-wrap: wrap;
            margin-top: 60px; max-width: 1000px; margin-left: auto; margin-right: auto; position: relative;
        }
        .steps::before {
            content: ''; position: absolute;
            top: 32px; left: calc(12.5% + 32px); right: calc(12.5% + 32px);
            height: 2px; background: linear-gradient(90deg, var(--purple), var(--blue));
            opacity: 0.3;
        }
        .step {
            flex: 1; min-width: 200px; text-align: center; padding: 0 16px;
            position: relative;
        }
        .step-num {
            width: 64px; height: 64px; border-radius: 50%;
            background: linear-gradient(135deg, var(--purple), var(--blue));
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; margin: 0 auto 20px;
            box-shadow: 0 8px 24px rgba(124,58,237,0.4);
            position: relative; z-index: 1;
            transition: transform 0.3s;
        }
        .step:hover .step-num { transform: scale(1.1); }
        .step-title { font-size: 1rem; font-weight: 700; margin-bottom: 8px; }
        .step-desc  { font-size: 0.83rem; color: rgba(255,255,255,0.45); line-height: 1.6; }

        /* ── BENEFITS ─────────────────────────────────────── */
        .benefits-wrap {
            display: grid; grid-template-columns: 1fr 1fr; gap: 40px;
            max-width: 1000px; margin: 60px auto 0; align-items: center;
        }
        .benefit-visual {
            perspective: 800px;
        }
        .benefit-phone {
            width: 260px; height: 480px; margin: 0 auto;
            background: linear-gradient(135deg, rgba(124,58,237,0.15), rgba(37,99,235,0.1));
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 40px; position: relative;
            transform: rotateY(-12deg) rotateX(4deg);
            box-shadow: 30px 30px 80px rgba(0,0,0,0.5), -10px -10px 40px rgba(124,58,237,0.08);
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; padding: 30px;
            overflow: hidden;
        }
        .benefit-phone::before {
            content: ''; position: absolute; top: 16px; left: 50%; transform: translateX(-50%);
            width: 60px; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px;
        }
        .phone-qr {
            width: 120px; height: 120px; background: white; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 3rem;
            animation: pulseQr 3s ease-in-out infinite;
        }
        @keyframes pulseQr { 0%,100%{transform:scale(1);} 50%{transform:scale(1.05);} }
        .phone-name { font-size: 1rem; font-weight: 700; text-align: center; }
        .phone-status {
            background: rgba(40,167,69,0.2); border: 1px solid #28a745;
            border-radius: 20px; padding: 6px 18px; font-size: 0.82rem; color: #7feba1; font-weight: 600;
        }
        .phone-scan {
            display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: rgba(255,255,255,0.4);
        }
        .scan-dot { width: 8px; height: 8px; border-radius: 50%; background: #28a745; animation: blink 1.2s infinite; }
        @keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.2;} }

        .benefits-list { display: flex; flex-direction: column; gap: 20px; }
        .benefit-item {
            display: flex; gap: 18px; align-items: flex-start;
            padding: 22px; border-radius: 16px;
            background: var(--card-bg); border: 1px solid var(--border);
            transition: all 0.3s;
        }
        .benefit-item:hover { border-color: rgba(124,58,237,0.3); background: rgba(124,58,237,0.06); transform: translateX(6px); }
        .benefit-icon {
            width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .benefit-item h4 { font-size: 0.95rem; font-weight: 700; margin-bottom: 4px; }
        .benefit-item p  { font-size: 0.82rem; color: rgba(255,255,255,0.45); line-height: 1.6; }

        /* ── CTA ──────────────────────────────────────────── */
        .cta-section {
            text-align: center; padding: 100px 40px;
            position: relative; z-index: 1;
        }
        .cta-box {
            max-width: 720px; margin: 0 auto;
            background: linear-gradient(135deg, rgba(124,58,237,0.12), rgba(37,99,235,0.08));
            border: 1px solid rgba(124,58,237,0.25);
            border-radius: 28px; padding: 60px 40px;
            position: relative; overflow: hidden;
        }
        .cta-box::before {
            content: ''; position: absolute; top: -60px; right: -60px;
            width: 200px; height: 200px; border-radius: 50%;
            background: radial-gradient(circle, rgba(124,58,237,0.2), transparent 70%);
        }
        .cta-box::after {
            content: ''; position: absolute; bottom: -60px; left: -60px;
            width: 200px; height: 200px; border-radius: 50%;
            background: radial-gradient(circle, rgba(37,99,235,0.2), transparent 70%);
        }
        .cta-title { font-size: clamp(1.6rem,4vw,2.5rem); font-weight: 800; margin-bottom: 14px; position: relative; z-index: 1; }
        .cta-sub { color: rgba(255,255,255,0.5); margin-bottom: 32px; font-size: 1rem; position: relative; z-index: 1; }

        /* ── FOOTER ───────────────────────────────────────── */
        footer {
            border-top: 1px solid var(--border);
            padding: 30px 40px; text-align: center;
            color: rgba(255,255,255,0.3); font-size: 0.82rem;
            position: relative; z-index: 1;
        }
        footer span { color: rgba(255,255,255,0.6); font-weight: 600; }

        /* ── ANIMATIONS ───────────────────────────────────── */
        @keyframes fadeDown { from { opacity:0; transform:translateY(-24px); } to { opacity:1; transform:none; } }
        @keyframes fadeUp   { from { opacity:0; transform:translateY(40px);  } to { opacity:1; transform:none; } }

        .reveal { opacity: 0; transform: translateY(40px); transition: opacity 0.7s ease, transform 0.7s ease; }
        .reveal.visible { opacity: 1; transform: none; }

        @media (max-width: 768px) {
            .nav { padding: 14px 20px; }
            .nav-links { display: none; }
            section { padding: 70px 20px; }
            .scene-grid { grid-template-columns: 1fr 1fr; }
            .steps::before { display: none; }
            .benefits-wrap { grid-template-columns: 1fr; }
            .benefit-visual { display: none; }
            .hero { padding: 100px 20px 60px; }
        }
    </style>
</head>
<body>

<canvas id="particles"></canvas>

<!-- NAVBAR -->
<nav class="nav" id="navbar">
    <a href="/" class="nav-brand">
        @if(file_exists(public_path('images/logo.png')))
            <img src="{{ asset('images/logo.png') }}" style="height:36px;object-fit:contain;" alt="TVTECH">
        @else
        <div class="logo-box"><i class="fas fa-qrcode"></i></div>
        <div>
            <span class="brand-name">TVTECH</span>
            <span class="brand-sub">QR EVENT</span>
        </div>
        @endif
    </a>
    <ul class="nav-links">
        <li><a href="#features">Tính năng</a></li>
        <li><a href="#how">Cách hoạt động</a></li>
        <li><a href="#benefits">Lợi ích</a></li>
        <li><a href="{{ route('login') }}" class="nav-cta"><i class="fas fa-sign-in-alt"></i> Đăng nhập CMS</a></li>
    </ul>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-badge">
        <i class="fas fa-bolt"></i> Giải pháp check-in thế hệ mới
    </div>
    <h1 class="hero-title">
        Trải nghiệm sự kiện<br>
        <span class="gradient">đẳng cấp hơn mỗi ngày</span>
    </h1>
    <p class="hero-sub">
        Hệ thống check-in thông minh bằng QR Code — chào mừng từng khách mời bằng hình ảnh &amp; video cá nhân hoá, quản lý đa cửa real-time, không cần giấy tờ.
    </p>
    <div class="hero-btns">
        <a href="{{ route('login') }}" class="btn-primary-hero">
            <i class="fas fa-rocket"></i> Dùng thử ngay
        </a>
        <a href="#features" class="btn-outline-hero">
            <i class="fas fa-play-circle"></i> Xem tính năng
        </a>
    </div>

    <!-- 3D Scene -->
    <div class="scene-3d" id="scene3d">
        <div class="scene-inner">
            <!-- Floating QR -->
            <div class="qr-float">
                <div style="width:110px;height:110px;background:white;border-radius:16px;padding:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-qrcode" style="font-size:4rem;color:#0a0a1a;"></i>
                </div>
            </div>

            <div class="scene-grid">
                <div class="scene-card">
                    <div class="sc-icon" style="background:rgba(124,58,237,0.15);color:#a78bfa;"><i class="fas fa-users"></i></div>
                    <div class="sc-title">Khách mời</div>
                    <div class="sc-val" style="color:#a78bfa;">1,240</div>
                    <div class="sc-sub">đã đăng ký</div>
                </div>
                <div class="scene-card">
                    <div class="sc-icon" style="background:rgba(40,167,69,0.15);color:#7feba1;"><i class="fas fa-check-circle"></i></div>
                    <div class="sc-title">Đã check-in</div>
                    <div class="sc-val" style="color:#7feba1;">987</div>
                    <div class="sc-sub">hôm nay · 79.6%</div>
                </div>
                <div class="scene-card">
                    <div class="sc-icon" style="background:rgba(37,99,235,0.15);color:#60a5fa;"><i class="fas fa-door-open"></i></div>
                    <div class="sc-title">Cửa hoạt động</div>
                    <div class="sc-val" style="color:#60a5fa;">4</div>
                    <div class="sc-sub">stations online</div>
                </div>
            </div>

            <!-- Live feed -->
            <div style="margin-top:20px;padding:16px;background:rgba(255,255,255,0.03);border-radius:12px;border:1px solid rgba(255,255,255,0.06);">
                <div style="font-size:0.75rem;color:rgba(255,255,255,0.3);margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                    <span style="width:6px;height:6px;border-radius:50%;background:#28a745;display:inline-block;animation:blink 1.2s infinite;"></span>
                    LIVE CHECK-IN
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach([
                        ['Nguyễn Văn A', 'Cửa 1', '14:32', '#a78bfa'],
                        ['Trần Thị B', 'Cửa 2', '14:31', '#60a5fa'],
                        ['Lê Minh C', 'Cửa 1', '14:29', '#f472b6'],
                    ] as $row)
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:28px;height:28px;border-radius:50%;background:{{ $row[3] }}22;border:1px solid {{ $row[3] }}44;display:flex;align-items:center;justify-content:center;font-size:0.7rem;color:{{ $row[3] }};font-weight:700;flex-shrink:0;">{{ strtoupper(substr($row[0],0,1)) }}</div>
                        <div style="flex:1;font-size:0.8rem;font-weight:600;">{{ $row[0] }}</div>
                        <div style="font-size:0.72rem;color:rgba(255,255,255,0.3);">{{ $row[1] }}</div>
                        <div style="font-size:0.7rem;color:rgba(255,255,255,0.25);">{{ $row[2] }}</div>
                        <div style="width:8px;height:8px;border-radius:50%;background:#28a745;"></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS BAR -->
<div style="padding:0 40px 80px;position:relative;z-index:1;">
    <div class="stats-bar reveal">
        <div class="stat-item"><div class="stat-num">∞</div><div class="stat-label">Khách mời không giới hạn</div></div>
        <div class="stat-item"><div class="stat-num">4</div><div class="stat-label">Chế độ quét linh hoạt</div></div>
        <div class="stat-item"><div class="stat-num">Real-time</div><div class="stat-label">Cập nhật tức thì</div></div>
        <div class="stat-item"><div class="stat-num">Multi</div><div class="stat-label">Đa cửa đa màn hình</div></div>
    </div>
</div>

<!-- FEATURES -->
<section id="features">
    <div class="text-center reveal">
        <div class="section-tag"><i class="fas fa-star"></i> Tính năng chính</div>
        <h2 class="section-title">Mọi thứ bạn cần<br>cho một sự kiện chuyên nghiệp</h2>
        <p class="section-sub">Từ quét QR đến màn hình chào mừng, từ quản lý khách đến phân tích thống kê — tất cả trong một hệ thống.</p>
    </div>

    <div class="features-grid">
        @foreach([
            ['fas fa-qrcode',        'Quét QR tức thì',          'Camera tự động nhận diện mã QR UUID không thể đoán. Hỗ trợ nhập thủ công và đa thiết bị đồng thời.', 'rgba(124,58,237,0.2)', '#a78bfa', 'Camera · UUID · Tức thì'],
            ['fas fa-photo-film',    'Chào mừng cá nhân hoá',    'Mỗi khách mời nhận màn hình chào mừng riêng với ảnh hoặc video được cài đặt sẵn. Hiệu ứng đẹp mắt, chuyên nghiệp.', 'rgba(233,69,96,0.2)', '#f472b6', 'Ảnh · Video · Hiệu ứng'],
            ['fas fa-users-cog',     'CMS quản lý khách',        'Thêm, sửa, xoá khách mời. Upload media, tải QR về máy, xoá hàng loạt. Tìm kiếm và lọc nhanh.', 'rgba(37,99,235,0.2)', '#60a5fa', 'CRUD · Bulk · Export ZIP'],
            ['fas fa-door-open',     'Đa cửa · Đa station',      'Mỗi cửa vào là một cặp link quét + màn hình độc lập. Khách quét cửa nào, màn hình cửa đó sáng lên.', 'rgba(16,185,129,0.2)', '#34d399', 'Station · Màn hình riêng'],
            ['fas fa-shield-check',  'Kiểm soát lượt quét',      '4 chế độ: không giới hạn, 1 lần, vào/ra (2 lần), tối đa N lần. Tự khoá khi hết lượt. Reset dễ dàng.', 'rgba(245,158,11,0.2)', '#fbbf24', '4 chế độ · Tự khoá · Reset'],
            ['fas fa-tv',            'Màn hình hiển thị',        'Màn hình idle với đồng hồ đẹp. Khi có khách quét, hiện ảnh/video + tên + trạng thái vào/ra tự động.', 'rgba(124,58,237,0.2)', '#c084fc', 'Polling 2s · Ngang/Dọc'],
            ['fas fa-chart-bar',     'Thống kê real-time',       'Tổng khách, đã vào, đã ra, đang trong hội trường, chưa đến. Lọc theo ngày và cửa cụ thể.', 'rgba(37,99,235,0.2)', '#818cf8', 'Ngày · Station · Export'],
            ['fas fa-user-shield',   'Phân quyền người dùng',    'Super admin, vai trò tuỳ chỉnh, 11 quyền hạn chi tiết. Mỗi nhân viên chỉ thấy và làm đúng phần việc của mình.', 'rgba(233,69,96,0.2)', '#fb7185', 'Role · Permission · Multi-user'],
        ] as $f)
        <div class="feat-card reveal">
            <div class="feat-icon" style="background:{{ $f[3] }};color:{{ $f[4] }};"><i class="{{ $f[0] }}"></i></div>
            <div class="feat-title">{{ $f[1] }}</div>
            <div class="feat-desc">{{ $f[2] }}</div>
            <div class="feat-tag">{{ $f[5] }}</div>
        </div>
        @endforeach
    </div>
</section>

<!-- HOW IT WORKS -->
<section id="how" style="background:rgba(255,255,255,0.01);">
    <div class="text-center reveal">
        <div class="section-tag"><i class="fas fa-map-signs"></i> Cách hoạt động</div>
        <h2 class="section-title">Triển khai trong<br><span style="color:#a78bfa;">4 bước đơn giản</span></h2>
    </div>

    <div class="steps">
        @foreach([
            ['fas fa-user-plus',   'Thêm khách mời',      'Nhập tên, email, upload ảnh/video chào mừng, chọn chế độ quét phù hợp'],
            ['fas fa-qrcode',      'Tạo QR tự động',      'Hệ thống sinh mã QR UUID duy nhất, tải về và gửi cho khách trước sự kiện'],
            ['fas fa-camera',      'Khách quét QR',        'Tại cửa vào, khách đưa mã QR vào camera — nhận diện tức thì dưới 1 giây'],
            ['fas fa-tv',          'Màn hình chào mừng',  'Ảnh/video cá nhân hoá hiện lên màn hình, ghi nhận check-in real-time'],
        ] as $i => $s)
        <div class="step reveal">
            <div class="step-num"><i class="{{ $s[0] }}"></i></div>
            <div class="step-title">{{ $s[1] }}</div>
            <div class="step-desc">{{ $s[2] }}</div>
        </div>
        @endforeach
    </div>
</section>

<!-- BENEFITS -->
<section id="benefits">
    <div class="benefits-wrap">
        <div class="benefit-visual reveal">
            <div class="benefit-phone">
                <div class="phone-qr">📱</div>
                <div class="phone-name">Nguyễn Văn An</div>
                <div class="phone-status"><i class="fas fa-check-circle"></i> Check-in thành công</div>
                <div class="phone-scan"><span class="scan-dot"></span> Cửa 1 · 14:35:22</div>
            </div>
        </div>

        <div>
            <div class="section-tag reveal"><i class="fas fa-gem"></i> Lợi ích</div>
            <h2 class="section-title reveal">Tại sao chọn<br><span style="color:#a78bfa;">QR Event?</span></h2>
            <div class="benefits-list">
                @foreach([
                    ['fas fa-bolt',          'rgba(245,158,11,0.15)', '#fbbf24', 'Check-in siêu tốc',        'Không xếp hàng, không tìm kiếm danh sách. Quét là xong trong chưa đầy 1 giây.'],
                    ['fas fa-leaf',          'rgba(16,185,129,0.15)', '#34d399', 'Không cần giấy tờ',        'Xanh & hiện đại. Toàn bộ danh sách khách, QR, thống kê đều số hoá 100%.'],
                    ['fas fa-heart',         'rgba(233,69,96,0.15)',  '#f472b6', 'Ấn tượng với khách mời',   'Mỗi người được chào đón bằng ảnh/video riêng. Tạo trải nghiệm đáng nhớ, sang trọng.'],
                    ['fas fa-chart-network', 'rgba(124,58,237,0.15)', '#a78bfa', 'Kiểm soát toàn diện',      'Biết chính xác ai đã đến, ai chưa, đang ở đâu — theo từng cửa, từng thời điểm.'],
                ] as $b)
                <div class="benefit-item reveal">
                    <div class="benefit-icon" style="background:{{ $b[1] }};color:{{ $b[2] }};"><i class="{{ $b[0] }}"></i></div>
                    <div>
                        <h4>{{ $b[3] }}</h4>
                        <p>{{ $b[4] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<div class="cta-section reveal">
    <div class="cta-box">
        <div class="cta-title">Sẵn sàng nâng tầm<br>sự kiện của bạn?</div>
        <p class="cta-sub">Bắt đầu ngay hôm nay — triển khai nhanh, dễ dùng, không cần kỹ thuật.</p>
        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;position:relative;z-index:1;">
            <a href="{{ route('login') }}" class="btn-primary-hero"><i class="fas fa-rocket"></i> Vào hệ thống CMS</a>
            <a href="{{ route('scan') }}" class="btn-outline-hero"><i class="fas fa-camera"></i> Thử quét QR</a>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer>
    Quản lý bản quyền bởi <span>TVTECH</span> &copy; {{ date('Y') }} &nbsp;·&nbsp;
    <a href="{{ route('login') }}" style="color:rgba(255,255,255,0.4);text-decoration:none;">Đăng nhập</a> &nbsp;·&nbsp;
    <a href="{{ route('scan') }}"  style="color:rgba(255,255,255,0.4);text-decoration:none;">Quét QR</a>
</footer>

<script>
// ── Particle canvas ────────────────────────────────────
const canvas = document.getElementById('particles');
const ctx = canvas.getContext('2d');
let W, H, particles = [];

function resize() {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
}
resize();
window.addEventListener('resize', resize);

for (let i = 0; i < 80; i++) {
    particles.push({
        x: Math.random() * 3000,
        y: Math.random() * 2000,
        r: Math.random() * 1.5 + 0.3,
        dx: (Math.random() - 0.5) * 0.3,
        dy: -Math.random() * 0.4 - 0.1,
        o: Math.random() * 0.5 + 0.1
    });
}

function drawParticles() {
    ctx.clearRect(0, 0, W, H);
    particles.forEach(p => {
        ctx.beginPath();
        ctx.arc(p.x % W, p.y % H, p.r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(167,139,250,${p.o})`;
        ctx.fill();
        p.x += p.dx; p.y += p.dy;
        if (p.y < 0) { p.y = H; p.x = Math.random() * W; }
    });
    requestAnimationFrame(drawParticles);
}
drawParticles();

// ── 3D scene mouse tilt ────────────────────────────────
const scene = document.querySelector('.scene-inner');
document.querySelector('.scene-3d').addEventListener('mousemove', e => {
    const r = e.currentTarget.getBoundingClientRect();
    const x = (e.clientX - r.left) / r.width  - 0.5;
    const y = (e.clientY - r.top)  / r.height - 0.5;
    scene.style.transform = `rotateX(${18 - y * 10}deg) rotateY(${-4 + x * 10}deg)`;
});
document.querySelector('.scene-3d').addEventListener('mouseleave', () => {
    scene.style.transform = 'rotateX(18deg) rotateY(-4deg)';
});

// ── Scroll reveal ──────────────────────────────────────
const io = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => io.observe(el));

// ── Navbar scroll effect ───────────────────────────────
window.addEventListener('scroll', () => {
    document.getElementById('navbar').style.background =
        window.scrollY > 50 ? 'rgba(10,10,26,0.95)' : 'rgba(10,10,26,0.7)';
});
</script>
</body>
</html>
