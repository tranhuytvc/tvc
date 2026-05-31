@extends('layouts.app')

@section('title', 'Quét mã QR')

@push('styles')
<style>
    .scan-wrapper {
        min-height: calc(100vh - 60px); display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        padding: 20px;
    }
    .scan-card {
        background: rgba(255,255,255,0.05); backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.1); border-radius: 24px;
        padding: 32px; max-width: 500px; width: 100%; text-align: center;
        color: white;
    }
    .scan-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 6px; }
    .scan-subtitle { color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 24px; }

    #reader {
        width: 100%; border-radius: 16px; overflow: hidden;
        border: 3px solid rgba(255,255,255,0.2); background: #000;
    }
    #reader video { border-radius: 13px; }

    .scan-status {
        margin-top: 20px; padding: 12px 20px; border-radius: 12px;
        font-weight: 600; font-size: 0.95rem; display: none;
    }
    .scan-status.scanning { display: block; background: rgba(102,126,234,0.2); border: 1px solid #667eea; color: #b3c0ff; }
    .scan-status.success { display: block; background: rgba(40,167,69,0.2); border: 1px solid #28a745; color: #7feba1; }
    .scan-status.error { display: block; background: rgba(220,53,69,0.2); border: 1px solid #dc3545; color: #f5c6cb; }

    .manual-form { margin-top: 20px; }
    .manual-divider { color: rgba(255,255,255,0.3); font-size: 0.8rem; margin: 16px 0; }
    .manual-input-wrap { display: flex; gap: 10px; }
    .manual-input {
        flex: 1; padding: 10px 14px; border-radius: 10px;
        background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
        color: white; font-size: 0.9rem; outline: none;
    }
    .manual-input::placeholder { color: rgba(255,255,255,0.4); }
    .manual-input:focus { border-color: #667eea; }

    .corner-tl, .corner-tr, .corner-bl, .corner-br {
        position: absolute; width: 24px; height: 24px; border-color: #667eea;
        border-style: solid; border-width: 0;
    }
    .corner-tl { top: 12px; left: 12px; border-top-width: 3px; border-left-width: 3px; border-radius: 4px 0 0 0; }
    .corner-tr { top: 12px; right: 12px; border-top-width: 3px; border-right-width: 3px; border-radius: 0 4px 0 0; }
    .corner-bl { bottom: 12px; left: 12px; border-bottom-width: 3px; border-left-width: 3px; border-radius: 0 0 0 4px; }
    .corner-br { bottom: 12px; right: 12px; border-bottom-width: 3px; border-right-width: 3px; border-radius: 0 0 4px 0; }
    .reader-wrap { position: relative; }

    .nav-links { display: flex; gap: 12px; margin-top: 20px; flex-wrap: wrap; justify-content: center; }
    .nav-link {
        display: flex; align-items: center; gap: 6px; padding: 8px 16px;
        background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
        color: rgba(255,255,255,0.8); text-decoration: none; border-radius: 8px;
        font-size: 0.85rem; transition: all 0.2s;
    }
    .nav-link:hover { background: rgba(255,255,255,0.15); color: white; }
</style>
@endpush

@section('content')
<div class="scan-wrapper">
    <div class="scan-card">
        <div style="font-size:3rem; margin-bottom:12px;">📱</div>
        <h1 class="scan-title">Quét mã QR Check-in</h1>
        <p class="scan-subtitle">Đưa mã QR vào khung hình để check-in / check-out tự động</p>

        <div class="reader-wrap">
            <div id="reader"></div>
            <div class="corner-tl"></div>
            <div class="corner-tr"></div>
            <div class="corner-bl"></div>
            <div class="corner-br"></div>
        </div>

        <div class="scan-status scanning" id="scanStatus">
            <i class="fas fa-camera"></i> Đang khởi động camera...
        </div>

        <div class="manual-form">
            <div class="manual-divider">— hoặc nhập mã thủ công —</div>
            <div class="manual-input-wrap">
                <input type="text" class="manual-input" id="manualToken" placeholder="Nhập token QR...">
                <button class="btn btn-primary" onclick="manualCheck()">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <div class="nav-links">
            <a href="{{ route('display') }}" class="nav-link"><i class="fas fa-tv"></i> Màn hình</a>
            <a href="{{ route('cms.index') }}" class="nav-link"><i class="fas fa-cog"></i> Quản lý</a>
            <a href="{{ route('stats') }}" class="nav-link"><i class="fas fa-chart-bar"></i> Thống kê</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let isRedirecting = false;
    const status = document.getElementById('scanStatus');

    function setStatus(msg, type) {
        status.className = 'scan-status ' + type;
        status.innerHTML = msg;
    }

    function handleQrResult(decodedText) {
        if (isRedirecting) return;

        // Check if it's a URL with our welcome route
        const base = window.location.origin;
        if (decodedText.startsWith(base + '/welcome/')) {
            isRedirecting = true;
            setStatus('<i class="fas fa-check-circle"></i> Đã nhận diện! Đang chuyển hướng...', 'success');
            setTimeout(() => { window.location.href = decodedText; }, 500);
        } else if (decodedText.match(/^[A-Za-z0-9]{16}$/)) {
            isRedirecting = true;
            setStatus('<i class="fas fa-check-circle"></i> Token nhận diện! Đang xử lý...', 'success');
            setTimeout(() => { window.location.href = base + '/welcome/' + decodedText; }, 500);
        } else {
            setStatus('<i class="fas fa-exclamation-triangle"></i> Mã QR không hợp lệ, thử lại...', 'error');
            setTimeout(() => { isRedirecting = false; setStatus('<i class="fas fa-camera"></i> Đang quét...', 'scanning'); }, 2000);
        }
    }

    const html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        { fps: 10, qrbox: { width: 250, height: 250 }, supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA] },
        false
    );

    html5QrcodeScanner.render(
        (decodedText) => handleQrResult(decodedText),
        (err) => {}
    );

    // Wait for scanner to start
    setTimeout(() => {
        if (!isRedirecting) setStatus('<i class="fas fa-camera"></i> Đang quét... Đưa mã QR vào khung hình', 'scanning');
    }, 2000);

    function manualCheck() {
        const token = document.getElementById('manualToken').value.trim();
        if (!token) return;
        window.location.href = '{{ url("/welcome") }}/' + token;
    }

    document.getElementById('manualToken').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') manualCheck();
    });
</script>
@endpush
