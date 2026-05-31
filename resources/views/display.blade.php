<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Màn hình chào mừng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #000; color: white; overflow: hidden;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            width: 100vw; height: 100vh; display: flex;
            flex-direction: column; align-items: center; justify-content: center;
        }

        .orientation-toggle {
            position: fixed; top: 16px; right: 16px; z-index: 100;
            display: flex; gap: 8px;
        }
        .toggle-btn {
            padding: 8px 14px; border-radius: 8px; border: none;
            background: rgba(255,255,255,0.15); color: white; cursor: pointer;
            font-size: 0.85rem; transition: all 0.2s; display: flex; align-items: center; gap: 6px;
            backdrop-filter: blur(10px);
        }
        .toggle-btn:hover, .toggle-btn.active { background: rgba(102,126,234,0.7); }

        .display-container {
            width: 100vw; height: 100vh; position: relative;
            display: flex; align-items: center; justify-content: center;
        }

        /* Landscape mode */
        .landscape .display-inner {
            display: grid; grid-template-columns: 1fr 1fr;
            width: 100%; height: 100%; gap: 0;
        }
        .landscape .media-side {
            height: 100vh; overflow: hidden; background: #111;
        }
        .landscape .media-side img,
        .landscape .media-side video {
            width: 100%; height: 100%; object-fit: cover;
        }
        .landscape .info-side {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; padding: 40px;
            background: linear-gradient(135deg, #0f0c29, #302b63);
        }

        /* Portrait mode */
        .portrait .display-inner {
            display: flex; flex-direction: column;
            width: 100%; height: 100%;
        }
        .portrait .media-side {
            flex: 1; overflow: hidden; background: #111;
        }
        .portrait .media-side img,
        .portrait .media-side video {
            width: 100%; height: 100%; object-fit: cover;
        }
        .portrait .info-side {
            padding: 30px 20px; text-align: center;
            background: linear-gradient(135deg, #0f0c29, #302b63);
            flex-shrink: 0;
        }

        .guest-name {
            font-size: clamp(2rem, 5vw, 4rem); font-weight: 800;
            background: linear-gradient(135deg, #fff, #c9d6ff);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            margin-bottom: 12px; line-height: 1.1;
        }
        .welcome-msg { font-size: clamp(1rem, 2vw, 1.3rem); color: rgba(255,255,255,0.7); margin-bottom: 16px; }
        .checkin-time { font-size: 0.9rem; color: rgba(255,255,255,0.5); }
        .event-tag {
            display: inline-block; padding: 6px 16px; border-radius: 20px;
            background: rgba(102,126,234,0.3); border: 1px solid #667eea;
            color: #b3c0ff; font-size: 0.85rem; font-weight: 600; margin-bottom: 16px;
        }

        /* Idle screen */
        .idle-screen {
            width: 100%; height: 100%; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            text-align: center;
        }
        .idle-qr-icon { font-size: 8rem; margin-bottom: 20px; opacity: 0.6; animation: pulse 2s infinite; }
        .idle-title { font-size: clamp(1.5rem, 4vw, 2.5rem); font-weight: 700; margin-bottom: 10px; }
        .idle-sub { color: rgba(255,255,255,0.5); font-size: 1rem; }
        .clock { font-size: clamp(2rem, 6vw, 5rem); font-weight: 100; margin-bottom: 8px; letter-spacing: 4px; }
        .date-display { color: rgba(255,255,255,0.5); font-size: 1rem; }

        @keyframes pulse { 0%,100% { opacity: 0.6; } 50% { opacity: 0.3; } }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: slideIn 0.5s ease; }
    </style>
</head>
<body>
    <div class="orientation-toggle">
        <button class="toggle-btn active" id="btnLandscape" onclick="setOrientation('landscape')">
            <i class="fas fa-laptop"></i> Ngang
        </button>
        <button class="toggle-btn" id="btnPortrait" onclick="setOrientation('portrait')">
            <i class="fas fa-mobile-alt"></i> Dọc
        </button>
        <a href="{{ route('scan') }}" class="toggle-btn">
            <i class="fas fa-qrcode"></i>
        </a>
    </div>

    <div class="display-container landscape" id="displayContainer">
        <div class="idle-screen" id="idleScreen">
            <div class="clock" id="clock">00:00:00</div>
            <div class="date-display" id="dateDisplay"></div>
            <div style="margin: 30px 0;">
                <div class="idle-qr-icon">📱</div>
                <div class="idle-title">Hệ thống Chào mừng</div>
                <div class="idle-sub">Quét mã QR để check-in</div>
            </div>
        </div>

        <div class="display-inner" id="displayInner" style="display:none;">
            <div class="media-side" id="mediaSide"></div>
            <div class="info-side" id="infoSide"></div>
        </div>
    </div>

    <script>
        let orientation = 'landscape';
        let idleTimer = null;

        function setOrientation(mode) {
            orientation = mode;
            const container = document.getElementById('displayContainer');
            container.className = 'display-container ' + mode;
            document.getElementById('btnLandscape').className = 'toggle-btn ' + (mode === 'landscape' ? 'active' : '');
            document.getElementById('btnPortrait').className = 'toggle-btn ' + (mode === 'portrait' ? 'active' : '');
        }

        function showGuest(guest) {
            document.getElementById('idleScreen').style.display = 'none';
            const inner = document.getElementById('displayInner');
            inner.style.display = orientation === 'landscape' ? 'grid' : 'flex';
            inner.className = 'display-inner animate-in';

            const mediaSide = document.getElementById('mediaSide');
            const infoSide = document.getElementById('infoSide');

            if (guest.media_path) {
                if (guest.media_type === 'image') {
                    mediaSide.innerHTML = `<img src="/storage/${guest.media_path}" alt="${guest.name}">`;
                } else {
                    mediaSide.innerHTML = `<video src="/storage/${guest.media_path}" autoplay muted loop playsinline></video>`;
                }
            } else {
                mediaSide.innerHTML = `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:6rem;">👤</div>`;
            }

            const action = guest.action === 'checkin' ? 'CHECK-IN' : 'CHECK-OUT';
            const msg = guest.action === 'checkin' ? 'Chào mừng bạn đã đến!' : 'Tạm biệt! Hẹn gặp lại!';
            infoSide.innerHTML = `
                <div class="event-tag"><i class="fas fa-${guest.action === 'checkin' ? 'sign-in-alt' : 'sign-out-alt'}"></i> ${action}</div>
                <div class="guest-name">${guest.name}</div>
                <div class="welcome-msg">${msg}</div>
                <div class="checkin-time"><i class="fas fa-clock"></i> ${new Date().toLocaleTimeString('vi-VN')}</div>
            `;

            clearTimeout(idleTimer);
            idleTimer = setTimeout(showIdle, 8000);
        }

        function showIdle() {
            document.getElementById('idleScreen').style.display = 'flex';
            document.getElementById('displayInner').style.display = 'none';
        }

        // Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('vi-VN');
            document.getElementById('dateDisplay').textContent = now.toLocaleDateString('vi-VN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Listen for check-in events via polling
        let lastCheckinId = 0;
        async function pollCheckins() {
            try {
                const res = await fetch('/api/latest-checkin?after=' + lastCheckinId);
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
