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
    $orientation = $settings['display_orientation'] ?? 'landscape';
    $showClock = $settings['show_clock'] ?? true;
    $idleText = $settings['idle_text'] ?? 'Quét mã QR để check-in';
    $apiUrl = $station ? url('/api/station/' . $station->display_slug . '/latest-checkin') : url('/api/latest-checkin');
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['event_name'] ?? 'Màn hình chào mừng' }}{{ $station ? ' - ' . $station->name : '' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #000; color: {{ $fontColor }}; overflow: hidden;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            width: 100vw; height: 100vh; display: flex; flex-direction: column;
        }
        .bg-media { position: fixed; inset: 0; z-index: 0; }
        .bg-media img, .bg-media video { width: 100%; height: 100%; object-fit: cover; }
        .bg-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1; }

        .orientation-toggle {
            position: fixed; top: 14px; right: 14px; z-index: 100;
            display: flex; gap: 6px;
        }
        .toggle-btn {
            padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2);
            background: rgba(0,0,0,0.4); color: {{ $fontColor }}; cursor: pointer;
            font-size: 0.8rem; transition: all 0.2s; display: flex; align-items: center; gap: 5px;
            backdrop-filter: blur(10px); text-decoration: none;
        }
        .toggle-btn:hover, .toggle-btn.active { background: {{ $accentColor }}99; border-color: {{ $accentColor }}; }

        .display-container { width: 100vw; height: 100vh; position: relative; z-index: 2; }

        /* Idle screen */
        .idle-screen {
            width: 100%; height: 100%; display: flex; flex-direction: column;
            align-items: center; justify-content: center; text-align: center;
            {!! $bgStyle !!}
            position: relative; z-index: 2;
        }
        .idle-clock { font-size: clamp(3rem, 8vw, 7rem); font-weight: 200; letter-spacing: 4px; margin-bottom: 6px; }
        .idle-date { font-size: 1rem; opacity: 0.5; margin-bottom: 40px; }
        .idle-logo { margin-bottom: 20px; }
        .idle-logo img { max-height: 70px; max-width: 250px; object-fit: contain; opacity: 0.8; }
        .idle-icon { font-size: clamp(4rem, 8vw, 7rem); opacity: 0.35; animation: pulse 2.5s infinite; margin-bottom: 16px; }
        .idle-title { font-size: clamp(1.2rem, 3vw, 2rem); font-weight: 600; opacity: 0.7; margin-bottom: 8px; }
        .idle-sub { font-size: 1rem; opacity: 0.4; }
        @if($station)
        .station-id {
            position: fixed; bottom: 16px; right: 16px; z-index: 100;
            font-size: 0.75rem; opacity: 0.35; padding: 4px 10px;
            background: rgba(0,0,0,0.4); border-radius: 6px;
        }
        @endif

        /* Landscape mode */
        .landscape .display-inner {
            display: grid; grid-template-columns: 1fr 1fr;
            width: 100%; height: 100%;
        }
        /* Portrait mode */
        .portrait .display-inner {
            display: flex; flex-direction: column; width: 100%; height: 100%;
        }
        .media-side { overflow: hidden; background: #000; }
        .landscape .media-side { height: 100vh; }
        .portrait .media-side { flex: 1; }
        .media-side img, .media-side video { width: 100%; height: 100%; object-fit: cover; }

        .info-side {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 40px; {!! $bgStyle !!} text-align: center;
        }
        .portrait .info-side { padding: 28px 20px; flex-shrink: 0; }

        .display-logo img { max-height: 50px; max-width: 180px; object-fit: contain; margin-bottom: 16px; }
        .badge-action {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;
            margin-bottom: 14px; border: 2px solid;
        }
        .badge-checkin { background: rgba(40,167,69,0.2); border-color: #28a745; color: #7feba1; }
        .badge-checkout { background: rgba(255,193,7,0.2); border-color: #ffc107; color: #ffe599; }
        .display-name {
            font-size: clamp(1.8rem, 4vw, 3.5rem); font-weight: 800; line-height: 1.1;
            margin-bottom: 10px; color: {{ $accentColor }};
        }
        .display-title { font-size: clamp(0.9rem, 1.8vw, 1.2rem); opacity: 0.75; margin-bottom: 8px; }
        .display-msg { font-size: clamp(0.8rem, 1.4vw, 1rem); opacity: 0.55; }
        .display-time { font-size: 0.85rem; opacity: 0.4; margin-top: 12px; }

        @keyframes pulse { 0%,100% { opacity: 0.35; } 50% { opacity: 0.15; } }
        @keyframes slideIn { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }
        .animate-in { animation: slideIn 0.4s ease; }
    </style>
</head>
<body>
    {{-- Background media (only for idle, if gradient is replaced) --}}
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

    <div class="orientation-toggle">
        <button class="toggle-btn {{ $orientation === 'landscape' ? 'active' : '' }}" id="btnLandscape" onclick="setOrientation('landscape')">
            <i class="fas fa-laptop"></i> Ngang
        </button>
        <button class="toggle-btn {{ $orientation === 'portrait' ? 'active' : '' }}" id="btnPortrait" onclick="setOrientation('portrait')">
            <i class="fas fa-mobile-alt"></i> Dọc
        </button>
        @if($station)
        <a href="{{ url('/scan/' . $station->scan_slug) }}" class="toggle-btn" target="_blank">
            <i class="fas fa-camera"></i>
        </a>
        @endif
        <a href="{{ route('cms.stations.index') }}" class="toggle-btn"><i class="fas fa-cog"></i></a>
    </div>

    <div class="display-container {{ $orientation }}" id="displayContainer">
        {{-- Idle Screen --}}
        <div class="idle-screen" id="idleScreen">
            @if($hasBgMedia)
            <div style="position:absolute;inset:0;z-index:-1;"></div>
            @endif

            @if(!empty($settings['logo_path']))
            <div class="idle-logo"><img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Logo"></div>
            @endif

            @if($showClock)
            <div class="idle-clock" id="clock">00:00:00</div>
            <div class="idle-date" id="dateDisplay"></div>
            @endif

            <div class="idle-icon">📱</div>
            <div class="idle-title">{{ $settings['event_name'] ?? '' }}</div>
            <div class="idle-sub">{{ $idleText }}</div>
        </div>

        {{-- Active guest display --}}
        <div class="display-inner {{ $orientation }}" id="displayInner" style="display:none;">
            <div class="media-side" id="mediaSide"></div>
            <div class="info-side" id="infoSide">
                @if(!empty($settings['logo_path']))
                <div class="display-logo"><img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Logo"></div>
                @endif
            </div>
        </div>
    </div>

    @if($station)
    <div class="station-id"><i class="fas fa-door-open"></i> {{ $station->name }}</div>
    @endif

    <script>
        let orientation = '{{ $orientation }}';
        let idleTimer = null;
        const accentColor = '{{ $accentColor }}';
        const fontColor = '{{ $fontColor }}';
        const bgStyle = @json($bgStyle);
        const logoHtml = @json(!empty($settings['logo_path']) ? '<div class="display-logo"><img src="' . asset('storage/' . $settings['logo_path']) . '" alt="Logo"></div>' : '');
        const titleCheckin = @json($settings['welcome_title_checkin'] ?? 'Xin chào,');
        const titleCheckout = @json($settings['welcome_title_checkout'] ?? 'Tạm biệt,');
        const msgCheckin = @json($settings['welcome_msg_checkin'] ?? 'Chào mừng bạn đã đến!');
        const msgCheckout = @json($settings['welcome_msg_checkout'] ?? 'Cảm ơn bạn đã tham dự!');
        const apiUrl = @json($apiUrl);

        function setOrientation(mode) {
            orientation = mode;
            const container = document.getElementById('displayContainer');
            container.className = 'display-container ' + mode;
            document.getElementById('displayInner').className = 'display-inner ' + mode;
            document.getElementById('btnLandscape').className = 'toggle-btn ' + (mode === 'landscape' ? 'active' : '');
            document.getElementById('btnPortrait').className = 'toggle-btn ' + (mode === 'portrait' ? 'active' : '');
        }

        function showGuest(guest) {
            document.getElementById('idleScreen').style.display = 'none';
            const inner = document.getElementById('displayInner');
            inner.style.display = orientation === 'landscape' ? 'grid' : 'flex';
            inner.className = 'display-inner ' + orientation + ' animate-in';

            const mediaSide = document.getElementById('mediaSide');
            const infoSide = document.getElementById('infoSide');

            if (guest.media_path) {
                const tag = guest.media_type === 'image' ? 'img' : 'video';
                const attrs = guest.media_type === 'video' ? ' autoplay muted loop playsinline' : '';
                mediaSide.innerHTML = `<${tag} src="/storage/${guest.media_path}"${attrs}></${tag}>`;
            } else {
                mediaSide.style.background = bgStyle.replace('background:', '').trim() || '#000';
                mediaSide.innerHTML = `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:8rem;opacity:0.4;">👤</div>`;
            }

            const isCheckin = guest.action === 'checkin';
            infoSide.style.cssText = bgStyle + ` display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px;text-align:center;color:${fontColor};`;
            infoSide.innerHTML = `
                ${logoHtml}
                <div class="badge-action ${isCheckin ? 'badge-checkin' : 'badge-checkout'}">
                    <i class="fas fa-${isCheckin ? 'sign-in-alt' : 'sign-out-alt'}"></i>
                    ${isCheckin ? 'CHECK-IN' : 'CHECK-OUT'}
                </div>
                <div class="display-name" style="color:${accentColor}">${guest.name}</div>
                <div class="display-title">${isCheckin ? titleCheckin : titleCheckout} ${guest.name}</div>
                <div class="display-msg">${isCheckin ? msgCheckin : msgCheckout}</div>
                <div class="display-time"><i class="fas fa-clock"></i> ${new Date().toLocaleTimeString('vi-VN')}</div>
            `;

            clearTimeout(idleTimer);
            idleTimer = setTimeout(showIdle, {{ $settings['countdown_seconds'] ?? 10 }} * 1000 + 2000);
        }

        function showIdle() {
            document.getElementById('idleScreen').style.display = 'flex';
            document.getElementById('displayInner').style.display = 'none';
        }

        @if($showClock)
        function updateClock() {
            const now = new Date();
            const el = document.getElementById('clock');
            const de = document.getElementById('dateDisplay');
            if (el) el.textContent = now.toLocaleTimeString('vi-VN');
            if (de) de.textContent = now.toLocaleDateString('vi-VN', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
        }
        setInterval(updateClock, 1000);
        updateClock();
        @endif

        let lastCheckinId = 0;
        async function pollCheckins() {
            try {
                const res = await fetch(apiUrl + '?after=' + lastCheckinId);
                const data = await res.json();
                if (data && data.id && data.id !== lastCheckinId) {
                    lastCheckinId = data.id;
                    showGuest(data);
                }
            } catch(e) {}
        }
        setInterval(pollCheckins, 2000);
    </script>
</body>
</html>
