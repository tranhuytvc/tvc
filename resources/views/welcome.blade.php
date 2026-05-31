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
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: white; font-family: 'Segoe UI', Tahoma, sans-serif; overflow: hidden;
        }
        .particles { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .particle {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,0.1);
            animation: float linear infinite;
        }
        .welcome-card {
            position: relative; z-index: 1; text-align: center;
            max-width: 700px; width: 95%; padding: 0 20px;
            animation: fadeInUp 0.8s ease;
        }
        .status-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 20px; border-radius: 30px; font-size: 0.9rem; font-weight: 600;
            margin-bottom: 20px; animation: pulse 2s infinite;
        }
        .status-checkin { background: rgba(40,167,69,0.3); border: 2px solid #28a745; color: #7feba1; }
        .status-checkout { background: rgba(255,193,7,0.3); border: 2px solid #ffc107; color: #ffe599; }
        .media-container {
            border-radius: 20px; overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5), 0 0 0 2px rgba(255,255,255,0.1);
            margin-bottom: 28px; max-height: 55vh; background: #000;
        }
        .media-container img, .media-container video { width: 100%; max-height: 55vh; object-fit: contain; display: block; }
        .guest-name {
            font-size: clamp(1.8rem, 5vw, 3rem); font-weight: 800;
            text-shadow: 0 2px 20px rgba(0,0,0,0.5);
            background: linear-gradient(135deg, #fff, #c9d6ff);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        .welcome-text { font-size: 1.1rem; color: rgba(255,255,255,0.7); margin-bottom: 20px; }
        .time-display { font-size: 1rem; color: rgba(255,255,255,0.5); font-style: italic; }
        .back-btn {
            display: inline-flex; align-items: center; gap: 8px; margin-top: 24px;
            padding: 12px 28px; background: rgba(255,255,255,0.15); color: white;
            text-decoration: none; border-radius: 50px; font-weight: 500;
            transition: all 0.3s; border: 1px solid rgba(255,255,255,0.2);
        }
        .back-btn:hover { background: rgba(255,255,255,0.25); transform: translateY(-2px); }
        .countdown { font-size: 0.85rem; color: rgba(255,255,255,0.4); margin-top: 8px; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes float { 0% { transform: translateY(100vh); opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { transform: translateY(-100px); opacity: 0; } }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.7; } }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    <div class="welcome-card">
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

        <h1 class="guest-name">
            {{ $action === 'checkin' ? 'Xin chào,' : 'Tạm biệt,' }} {{ $guest->name }}!
        </h1>
        <p class="welcome-text">
            {{ $action === 'checkin' ? 'Chào mừng bạn đã đến. Chúc bạn có một buổi tuyệt vời!' : 'Cảm ơn bạn đã tham dự. Hẹn gặp lại!' }}
        </p>
        <div class="time-display"><i class="fas fa-clock"></i> {{ now()->format('H:i - d/m/Y') }}</div>

        <a href="{{ route('scan') }}" class="back-btn">
            <i class="fas fa-qrcode"></i> Quét mã tiếp theo
        </a>
        <div class="countdown">Tự động quay lại sau <span id="timer">10</span>s</div>
    </div>
    <script>
        const pc = document.getElementById('particles');
        for (let i = 0; i < 20; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            const s = Math.random() * 6 + 2;
            p.style.cssText = `width:${s}px;height:${s}px;left:${Math.random()*100}%;animation-duration:${Math.random()*10+8}s;animation-delay:${Math.random()*10}s;`;
            pc.appendChild(p);
        }
        let t = 10;
        const el = document.getElementById('timer');
        setInterval(() => { el.textContent = --t; if (t <= 0) location.href = '{{ route("scan") }}'; }, 1000);
    </script>
</body>
</html>
