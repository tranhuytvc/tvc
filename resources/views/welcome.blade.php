@php
    $bgType = $settings['bg_type'] ?? 'gradient';
    $hasBgMedia = in_array($bgType, ['image', 'video']) && !empty($settings[$bgType === 'image' ? 'bg_image' : 'bg_video']);
    $bgStyle = '';
    if (!$hasBgMedia) {
        if ($bgType === 'gradient') {
            $bgStyle = 'background: linear-gradient(135deg, ' . ($settings['bg_color_from'] ?? '#0f0c29') . ', ' . ($settings['bg_color_to'] ?? '#302b63') . ');';
        } else {
            $bgStyle = 'background: ' . ($settings['bg_color_from'] ?? '#0f0c29') . ';';
        }
    }
    $accentColor = $settings['accent_color'] ?? '#667eea';
    $fontColor = $settings['font_color'] ?? '#ffffff';
    $countdown = (int) ($settings['countdown_seconds'] ?? 10);
    $scanUrl = $station ? url('/scan/' . $station->scan_slug) : route('scan');
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng {{ $guest->name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            {!! $hasBgMedia ? '' : $bgStyle !!}
            color: {{ $fontColor }}; font-family: 'Segoe UI', Tahoma, sans-serif; overflow: hidden;
        }
        .bg-media { position: fixed; inset: 0; z-index: 0; }
        .bg-media img, .bg-media video { width: 100%; height: 100%; object-fit: cover; }
        .bg-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 1; }
        .particles { position: fixed; inset: 0; pointer-events: none; z-index: 2; }
        .particle { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.08); animation: float linear infinite; }

        .welcome-card {
            position: relative; z-index: 3; text-align: center;
            max-width: 700px; width: 95%; padding: 0 20px;
            animation: fadeInUp 0.7s ease;
        }
        .logo-wrap { margin-bottom: 16px; }
        .logo-wrap img { max-height: 60px; max-width: 220px; object-fit: contain; opacity: 0.9; }
        .station-info {
            font-size: 0.8rem; opacity: 0.5; margin-bottom: 10px;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .status-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 20px; border-radius: 30px; font-size: 0.9rem; font-weight: 600;
            margin-bottom: 20px; animation: pulse 2s infinite; border: 2px solid;
        }
        .status-checkin { background: rgba(40,167,69,0.25); border-color: #28a745; color: #7feba1; }
        .status-checkout { background: rgba(255,193,7,0.25); border-color: #ffc107; color: #ffe599; }

        .media-container {
            border-radius: 18px; overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5), 0 0 0 2px rgba(255,255,255,0.08);
            margin-bottom: 24px; max-height: 52vh; background: #000;
        }
        .media-container img, .media-container video { width: 100%; max-height: 52vh; object-fit: contain; display: block; }

        .guest-name {
            font-size: clamp(1.8rem, 5vw, 3rem); font-weight: 800; line-height: 1.1;
            text-shadow: 0 2px 20px rgba(0,0,0,0.4); color: {{ $accentColor }};
            margin-bottom: 8px;
        }
        .welcome-title { font-size: 1.2rem; opacity: 0.85; margin-bottom: 8px; color: {{ $fontColor }}; }
        .welcome-msg { font-size: 1rem; opacity: 0.65; margin-bottom: 16px; color: {{ $fontColor }}; }
        .time-display { font-size: 0.9rem; opacity: 0.45; font-style: italic; color: {{ $fontColor }}; }

        .back-btn {
            display: inline-flex; align-items: center; gap: 8px; margin-top: 22px;
            padding: 11px 26px; background: {{ $accentColor }}33;
            color: {{ $fontColor }}; text-decoration: none; border-radius: 50px; font-weight: 500;
            border: 1px solid {{ $accentColor }}66; transition: all 0.3s;
        }
        .back-btn:hover { background: {{ $accentColor }}55; transform: translateY(-2px); }
        .countdown { font-size: 0.8rem; opacity: 0.35; margin-top: 8px; color: {{ $fontColor }}; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes float { 0% { transform: translateY(100vh); opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { transform: translateY(-100px); opacity: 0; } }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.7; } }
    </style>
</head>
<body>
    @if($hasBgMedia)
    <div class="bg-media">
        @if($bgType === 'image')
            <img src="{{ asset('storage/' . $settings['bg_image']) }}" alt="">
        @else
            <video src="{{ asset('storage/' . $settings['bg_video']) }}" autoplay muted loop playsinline></video>
        @endif
    </div>
    <div class="bg-overlay"></div>
    @endif

    <div class="particles" id="particles"></div>

    <div class="welcome-card">
        @if(!empty($settings['logo_path']))
        <div class="logo-wrap">
            <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Logo">
        </div>
        @endif

        @if($station)
        <div class="station-info">
            <i class="fas fa-door-open"></i> {{ $station->name }}
        </div>
        @endif

        <div class="status-badge {{ $action === 'checkin' ? 'status-checkin' : 'status-checkout' }}">
            <i class="fas {{ $action === 'checkin' ? 'fa-sign-in-alt' : 'fa-sign-out-alt' }}"></i>
            {{ $action === 'checkin' ? 'CHECK-IN THÀNH CÔNG' : 'CHECK-OUT THÀNH CÔNG' }}
        </div>

        @if($guest->media_path)
        <div class="media-container">
            @if($guest->media_type === 'image')
                <img src="{{ asset('storage/' . $guest->media_path) }}" alt="{{ $guest->name }}">
            @else
                <video src="{{ asset('storage/' . $guest->media_path) }}" autoplay muted loop playsinline></video>
            @endif
        </div>
        @endif

        <div class="welcome-title">
            {{ $action === 'checkin' ? ($settings['welcome_title_checkin'] ?? 'Xin chào,') : ($settings['welcome_title_checkout'] ?? 'Tạm biệt,') }}
        </div>
        <h1 class="guest-name">{{ $guest->name }}!</h1>
        <p class="welcome-msg">
            {{ $action === 'checkin' ? ($settings['welcome_msg_checkin'] ?? 'Chào mừng bạn đã đến!') : ($settings['welcome_msg_checkout'] ?? 'Hẹn gặp lại!') }}
        </p>
        <div class="time-display"><i class="fas fa-clock"></i> {{ now()->format('H:i - d/m/Y') }}</div>

        <a href="{{ $scanUrl }}" class="back-btn">
            <i class="fas fa-qrcode"></i> Quét mã tiếp theo
        </a>
        <div class="countdown">Tự động quay lại sau <span id="timer">{{ $countdown }}</span>s</div>
    </div>

    <script>
        const pc = document.getElementById('particles');
        for (let i = 0; i < 18; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            const s = Math.random() * 5 + 2;
            p.style.cssText = `width:${s}px;height:${s}px;left:${Math.random()*100}%;animation-duration:${Math.random()*10+8}s;animation-delay:${Math.random()*8}s;`;
            pc.appendChild(p);
        }
        let t = {{ $countdown }};
        const el = document.getElementById('timer');
        const iv = setInterval(() => {
            el.textContent = --t;
            if (t <= 0) { clearInterval(iv); location.href = '{{ $scanUrl }}'; }
        }, 1000);
    </script>
</body>
</html>
