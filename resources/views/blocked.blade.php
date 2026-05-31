@php
    $accentColor = $settings['accent_color'] ?? '#667eea';
    $fontColor = $settings['font_color'] ?? '#ffffff';
    $bgType = $settings['bg_type'] ?? 'gradient';
    $scanUrl = $station ? url('/scan/' . $station->scan_slug) : route('scan');
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã QR bị từ chối</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #1a0a0a, #2d1515);
            color: {{ $fontColor }}; font-family: 'Segoe UI', sans-serif; text-align: center; padding: 20px;
        }
        .card {
            max-width: 440px; width: 100%;
            background: rgba(220,53,69,0.12); border: 2px solid rgba(220,53,69,0.4);
            border-radius: 20px; padding: 40px 32px;
            animation: fadeIn 0.5s ease;
        }
        .icon { font-size: 4rem; margin-bottom: 16px; }
        h1 { font-size: 1.6rem; font-weight: 800; color: #ff6b7a; margin-bottom: 8px; }
        .reason { font-size: 1rem; color: rgba(255,255,255,0.6); margin-bottom: 24px; }
        .guest-name { font-size: 1.1rem; font-weight: 600; color: rgba(255,255,255,0.85); margin-bottom: 4px; }
        .scan-info { font-size: 0.85rem; color: rgba(255,255,255,0.4); margin-bottom: 28px; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 26px; border-radius: 50px; border: none;
            font-weight: 600; cursor: pointer; text-decoration: none;
            background: rgba(255,255,255,0.12); color: white; border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s; font-size: 0.9rem;
        }
        .btn:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🚫</div>
        <h1>Không thể quét</h1>
        <p class="reason">{{ $reason }}</p>

        <div class="guest-name"><i class="fas fa-user"></i> {{ $guest->name }}</div>
        <div class="scan-info">
            Đã quét: {{ $guest->scan_count }} lần &bull; Chế độ: {{ $guest->getScanModeLabel() }}
            @if($guest->scan_mode === 'max_scans') &bull; Giới hạn: {{ $guest->max_scan_count }} lần @endif
        </div>

        <a href="{{ $scanUrl }}" class="btn">
            <i class="fas fa-camera"></i> Quét mã khác
        </a>
    </div>
    <script>
        setTimeout(() => { location.href = '{{ $scanUrl }}'; }, 6000);
    </script>
</body>
</html>
