@php
    $bgStyle = '';
    $bgType = $settings['bg_type'] ?? 'gradient';
    if ($bgType === 'gradient') {
        $bgStyle = 'background: linear-gradient(135deg, ' . ($settings['bg_color_from'] ?? '#1a1a2e') . ', ' . ($settings['bg_color_to'] ?? '#16213e') . ');';
    } elseif ($bgType === 'color') {
        $bgStyle = 'background: ' . ($settings['bg_color_from'] ?? '#1a1a2e') . ';';
    }
    $accentColor = $settings['accent_color'] ?? '#667eea';
    $fontColor = $settings['font_color'] ?? '#ffffff';
    $hasBgMedia = in_array($bgType, ['image', 'video']) && !empty($settings[$bgType === 'image' ? 'bg_image' : 'bg_video']);
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['scan_title'] ?? 'Quét mã QR' }}{{ $station ? ' - ' . $station->name : '' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            {!! $hasBgMedia ? '' : $bgStyle !!}
            color: {{ $fontColor }}; font-family: 'Segoe UI', Tahoma, sans-serif;
            position: relative; overflow: hidden;
        }
        .bg-media { position: fixed; inset: 0; z-index: 0; }
        .bg-media img, .bg-media video { width: 100%; height: 100%; object-fit: cover; }
        .bg-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 1; }

        .scan-wrapper { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; width: 100%; }
        .scan-card {
            background: rgba(0,0,0,0.35); backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.12); border-radius: 24px;
            padding: 32px; max-width: 500px; width: 100%; text-align: center;
        }
        .station-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 600;
            background: {{ $accentColor }}33; border: 1px solid {{ $accentColor }};
            color: {{ $fontColor }}; margin-bottom: 16px; opacity: 0.85;
        }
        .logo-wrap { margin-bottom: 12px; }
        .logo-wrap img { max-height: 56px; max-width: 200px; object-fit: contain; }
        .scan-title { font-size: 1.4rem; font-weight: 700; margin-bottom: 5px; color: {{ $fontColor }}; }
        .scan-subtitle { color: {{ $fontColor }}; opacity: 0.65; font-size: 0.9rem; margin-bottom: 22px; }

        #reader { width: 100%; border-radius: 14px; overflow: hidden; border: 3px solid {{ $accentColor }}44; background: #000; }
        .scan-status {
            margin-top: 18px; padding: 10px 18px; border-radius: 10px;
            font-weight: 600; font-size: 0.9rem; display: none;
        }
        .scan-status.scanning { display: block; background: {{ $accentColor }}22; border: 1px solid {{ $accentColor }}; color: {{ $fontColor }}; }
        .scan-status.success { display: block; background: rgba(40,167,69,0.2); border: 1px solid #28a745; color: #7feba1; }
        .scan-status.error { display: block; background: rgba(220,53,69,0.2); border: 1px solid #dc3545; color: #f5c6cb; }
        .manual-divider { color: {{ $fontColor }}; opacity: 0.3; font-size: 0.8rem; margin: 14px 0; }
        .manual-input-wrap { display: flex; gap: 10px; }
        .manual-input {
            flex: 1; padding: 10px 14px; border-radius: 10px;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            color: {{ $fontColor }}; font-size: 0.9rem; outline: none;
        }
        .manual-input::placeholder { color: {{ $fontColor }}; opacity: 0.4; }
        .manual-input:focus { border-color: {{ $accentColor }}; }
        .submit-btn {
            padding: 10px 18px; border-radius: 10px; border: none;
            background: {{ $accentColor }}; color: white; cursor: pointer; font-size: 0.9rem;
            font-weight: 600; transition: all 0.2s;
        }
        .submit-btn:hover { filter: brightness(1.15); }
        .nav-links { display: flex; gap: 10px; margin-top: 18px; flex-wrap: wrap; justify-content: center; }
        .nav-link {
            display: flex; align-items: center; gap: 5px; padding: 7px 14px;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
            color: {{ $fontColor }}; text-decoration: none; border-radius: 8px;
            font-size: 0.82rem; opacity: 0.8; transition: all 0.2s;
        }
        .nav-link:hover { background: rgba(255,255,255,0.15); opacity: 1; }
        .corner-tl, .corner-tr, .corner-bl, .corner-br {
            position: absolute; width: 22px; height: 22px; border-color: {{ $accentColor }};
            border-style: solid; border-width: 0; pointer-events: none;
        }
        .corner-tl { top: 10px; left: 10px; border-top-width: 3px; border-left-width: 3px; border-radius: 4px 0 0 0; }
        .corner-tr { top: 10px; right: 10px; border-top-width: 3px; border-right-width: 3px; border-radius: 0 4px 0 0; }
        .corner-bl { bottom: 10px; left: 10px; border-bottom-width: 3px; border-left-width: 3px; border-radius: 0 0 0 4px; }
        .corner-br { bottom: 10px; right: 10px; border-bottom-width: 3px; border-right-width: 3px; border-radius: 0 0 4px 0; }
        .reader-wrap { position: relative; }
    </style>
</head>
<body>
    {{-- Background media --}}
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

    <div class="scan-wrapper">
        <div class="scan-card">
            @if(!empty($settings['logo_path']))
            <div class="logo-wrap">
                <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Logo">
            </div>
            @endif

            @if($station)
            <div class="station-badge">
                <i class="fas fa-door-open"></i> {{ $station->name }}
            </div>
            @endif

            <h1 class="scan-title">{{ $settings['scan_title'] ?? 'Quét mã QR Check-in' }}</h1>
            <p class="scan-subtitle">{{ $settings['scan_subtitle'] ?? 'Đưa mã QR vào khung hình để check-in / check-out' }}</p>

            <div class="reader-wrap">
                <div id="reader"></div>
                <div class="corner-tl"></div><div class="corner-tr"></div>
                <div class="corner-bl"></div><div class="corner-br"></div>
            </div>

            <div class="scan-status scanning" id="scanStatus">
                <i class="fas fa-camera"></i> Đang khởi động camera...
            </div>

            <div>
                <div class="manual-divider">— hoặc nhập mã thủ công —</div>
                <div class="manual-input-wrap">
                    <input type="text" class="manual-input" id="manualId" placeholder="Dán UUID hoặc URL QR...">
                    <button class="submit-btn" onclick="manualCheck()"><i class="fas fa-search"></i></button>
                </div>
            </div>

            <div class="nav-links">
                @if($station)
                <a href="{{ url('/display/' . $station->display_slug) }}" class="nav-link" target="_blank">
                    <i class="fas fa-tv"></i> Màn hình
                </a>
                @else
                <a href="{{ route('display') }}" class="nav-link"><i class="fas fa-tv"></i> Màn hình</a>
                @endif
                <a href="{{ route('cms.stations.index') }}" class="nav-link"><i class="fas fa-cog"></i> Stations</a>
                <a href="{{ route('stats') }}" class="nav-link"><i class="fas fa-chart-bar"></i> Thống kê</a>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        let isRedirecting = false;
        const status = document.getElementById('scanStatus');
        const stationSlug = @json($station?->scan_slug);

        function setStatus(msg, type) {
            status.className = 'scan-status ' + type;
            status.innerHTML = msg;
        }

        // UUID regex: 8-4-4-4-12 hex groups
        const UUID_RE = /[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i;

        function buildUrl(qrCode) {
            let url = '{{ url("/welcome") }}/' + qrCode;
            if (stationSlug) url += '?station=' + stationSlug;
            return url;
        }

        function handleQrResult(decodedText) {
            if (isRedirecting) return;
            isRedirecting = true;

            // Extract UUID from full URL or bare UUID
            const uuidMatch = decodedText.match(UUID_RE);
            if (uuidMatch) {
                setStatus('<i class="fas fa-check-circle"></i> Đã nhận diện!', 'success');
                setTimeout(() => { window.location.href = buildUrl(uuidMatch[0]); }, 400);
            } else {
                isRedirecting = false;
                setStatus('<i class="fas fa-exclamation-triangle"></i> Mã QR không hợp lệ', 'error');
                setTimeout(() => setStatus('<i class="fas fa-camera"></i> Đang quét...', 'scanning'), 2000);
            }
        }

        const scanner = new Html5QrcodeScanner("reader",
            { fps: 10, qrbox: { width: 250, height: 250 }, supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA] }, false);
        scanner.render(handleQrResult, () => {});
        setTimeout(() => { if (!isRedirecting) setStatus('<i class="fas fa-camera"></i> Đang quét... Đưa mã QR vào khung hình', 'scanning'); }, 1500);

        function manualCheck() {
            const raw = document.getElementById('manualId').value.trim();
            if (!raw) return;
            const m = raw.match(UUID_RE);
            window.location.href = buildUrl(m ? m[0] : raw);
        }
        document.getElementById('manualId').addEventListener('keypress', e => { if (e.key === 'Enter') manualCheck(); });
    </script>
</body>
</html>
