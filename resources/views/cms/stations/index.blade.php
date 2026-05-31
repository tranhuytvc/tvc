@extends('layouts.app')

@section('title', 'Quản lý Stations')

@push('styles')
<style>
    .station-card {
        background: white; border-radius: 12px; padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 16px;
        border-left: 4px solid #667eea;
        transition: transform 0.2s;
    }
    .station-card:hover { transform: translateY(-2px); }
    .station-card.inactive { border-left-color: #dee2e6; opacity: 0.7; }
    .station-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px; }
    .station-name { font-size: 1.1rem; font-weight: 700; color: #333; }
    .station-links { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
    .link-box {
        background: #f8f9ff; border: 1px solid #e0e4ff; border-radius: 8px; padding: 12px;
    }
    .link-box-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #888; margin-bottom: 4px; }
    .link-box-url { font-size: 0.8rem; color: #667eea; word-break: break-all; display: flex; align-items: center; gap: 6px; }
    .copy-btn { background: none; border: none; cursor: pointer; color: #999; padding: 2px; }
    .copy-btn:hover { color: #667eea; }
    .station-meta { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .preview-colors { display: flex; gap: 4px; }
    .color-dot { width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 1px rgba(0,0,0,0.1); }
    .action-btns { display: flex; gap: 8px; flex-wrap: wrap; }
    .open-link {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 500;
        text-decoration: none; border: 1px solid; transition: all 0.2s;
    }
    .open-scan { color: #667eea; border-color: #667eea; }
    .open-scan:hover { background: #667eea; color: white; }
    .open-display { color: #28a745; border-color: #28a745; }
    .open-display:hover { background: #28a745; color: white; }
    .empty-state { text-align: center; padding: 60px; color: #999; }
    .empty-state i { font-size: 4rem; display: block; margin-bottom: 16px; }
</style>
@endpush

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="font-size:1.3rem; font-weight:700;"><i class="fas fa-door-open" style="color:#667eea;"></i> Quản lý Stations (Cửa / Điểm quét)</h2>
            <p style="color:#888; font-size:0.9rem; margin-top:4px;">Mỗi station là 1 cặp link Quét QR + Màn hình hiển thị</p>
        </div>
        <a href="{{ route('cms.stations.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo Station mới
        </a>
    </div>

    @forelse($stations as $station)
    <div class="station-card {{ !$station->is_active ? 'inactive' : '' }}">
        <div class="station-header">
            <div>
                <div class="station-name">
                    <i class="fas fa-door-open" style="color:#667eea;"></i> {{ $station->name }}
                    @if(!$station->is_active) <span class="badge badge-danger" style="font-size:0.7rem;">Tắt</span> @endif
                </div>
                <div style="font-size:0.8rem; color:#888; margin-top:4px;">
                    {{ $station->checkins_count }} lượt check-in &bull;
                    Tạo: {{ $station->created_at->format('d/m/Y') }}
                </div>
            </div>
            <div class="action-btns">
                <a href="{{ route('cms.stations.edit', $station) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-cog"></i> Cài đặt
                </a>
                <form method="POST" action="{{ route('cms.stations.destroy', $station) }}" onsubmit="return confirm('Xóa station này?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>

        <div class="station-links">
            <div class="link-box">
                <div class="link-box-label"><i class="fas fa-camera"></i> Trang quét QR</div>
                <div class="link-box-url">
                    <span id="scan-{{ $station->id }}">/scan/{{ $station->scan_slug }}</span>
                    <button class="copy-btn" onclick="copyLink('scan-{{ $station->id }}', '{{ url('/scan/' . $station->scan_slug) }}')" title="Copy link">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <div style="margin-top:8px; display:flex; gap:6px;">
                    <a href="{{ url('/scan/' . $station->scan_slug) }}" target="_blank" class="open-link open-scan">
                        <i class="fas fa-external-link-alt"></i> Mở
                    </a>
                    <button class="btn btn-sm" style="padding:4px 10px; font-size:0.75rem; background:#f0f0f0; border:none; border-radius:6px; cursor:pointer;"
                            onclick="showQr('{{ url('/scan/' . $station->scan_slug) }}')">
                        <i class="fas fa-qrcode"></i> QR link
                    </button>
                </div>
            </div>
            <div class="link-box">
                <div class="link-box-label"><i class="fas fa-tv"></i> Màn hình hiển thị</div>
                <div class="link-box-url">
                    <span id="disp-{{ $station->id }}">/display/{{ $station->display_slug }}</span>
                    <button class="copy-btn" onclick="copyLink('disp-{{ $station->id }}', '{{ url('/display/' . $station->display_slug) }}')" title="Copy link">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <div style="margin-top:8px; display:flex; gap:6px;">
                    <a href="{{ url('/display/' . $station->display_slug) }}" target="_blank" class="open-link open-display">
                        <i class="fas fa-external-link-alt"></i> Mở
                    </a>
                    <button class="btn btn-sm" style="padding:4px 10px; font-size:0.75rem; background:#f0f0f0; border:none; border-radius:6px; cursor:pointer;"
                            onclick="showQr('{{ url('/display/' . $station->display_slug) }}')">
                        <i class="fas fa-qrcode"></i> QR link
                    </button>
                </div>
            </div>
        </div>

        <div class="station-meta">
            @php $s = $station->mergedSettings(); @endphp
            <div class="preview-colors">
                <div class="color-dot" style="background:{{ $s['bg_color_from'] ?? '#0f0c29' }}" title="Màu nền 1"></div>
                <div class="color-dot" style="background:{{ $s['bg_color_to'] ?? '#302b63' }}" title="Màu nền 2"></div>
                <div class="color-dot" style="background:{{ $s['accent_color'] ?? '#667eea' }}" title="Màu nhấn"></div>
            </div>
            @if($s['event_name'])
                <span style="font-size:0.85rem; color:#555;"><i class="fas fa-calendar-alt" style="color:#667eea;"></i> {{ $s['event_name'] }}</span>
            @endif
            @if($s['bg_type'] === 'image' && $s['bg_image'])
                <span class="badge badge-info"><i class="fas fa-image"></i> Ảnh nền</span>
            @elseif($s['bg_type'] === 'video' && $s['bg_video'])
                <span class="badge badge-info"><i class="fas fa-video"></i> Video nền</span>
            @else
                <span class="badge" style="background:#f0f2ff; color:#667eea;"><i class="fas fa-palette"></i> Gradient</span>
            @endif
            <span style="font-size:0.8rem; color:#888;">
                Chế độ: {{ $s['display_orientation'] === 'landscape' ? 'Ngang' : 'Dọc' }}
            </span>
        </div>
    </div>
    @empty
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-door-open"></i>
            <h3 style="margin-bottom:8px;">Chưa có station nào</h3>
            <p style="margin-bottom:20px;">Tạo station để có link Quét QR và Màn hình riêng cho từng cửa/điểm</p>
            <a href="{{ route('cms.stations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tạo Station đầu tiên
            </a>
        </div>
    </div>
    @endforelse
</div>

{{-- QR Link Modal --}}
<div class="modal-overlay" id="qrLinkModal" onclick="this.classList.remove('show')">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width:320px;">
        <div id="qrLinkImg" style="width:250px; height:250px; margin:0 auto; display:flex; align-items:center; justify-content:center; font-size:1rem; color:#999;">Đang tạo...</div>
        <p style="font-size:0.8rem; color:#888; margin:12px 0;">QR code trỏ đến link trên</p>
        <button class="btn btn-secondary btn-sm" onclick="document.getElementById('qrLinkModal').classList.remove('show')">Đóng</button>
    </div>
</div>

@push('styles')
<style>
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; }
    .modal-overlay.show { display: flex; }
    .modal-box { background: white; border-radius: 16px; padding: 24px; text-align: center; }
</style>
@endpush
@endsection

@push('scripts')
<script>
    function copyLink(spanId, url) {
        navigator.clipboard.writeText(url).then(() => {
            const span = document.getElementById(spanId);
            const orig = span.textContent;
            span.textContent = '✓ Đã copy!';
            span.style.color = '#28a745';
            setTimeout(() => { span.textContent = orig; span.style.color = ''; }, 2000);
        });
    }

    function showQr(url) {
        document.getElementById('qrLinkModal').classList.add('show');
        const container = document.getElementById('qrLinkImg');
        container.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(url)}" style="width:250px;height:250px;border-radius:8px;">`;
    }
</script>
@endpush
