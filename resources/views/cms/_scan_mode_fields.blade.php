@php
    $currentMode = old('scan_mode', $guest?->scan_mode ?? 'unlimited');
    $currentMax  = old('max_scan_count', $guest?->max_scan_count ?? 2);
@endphp
<div class="form-group" style="background:#f8f9ff; border:1px solid #e0e4ff; border-radius:12px; padding:18px; margin-top:4px;">
    <label class="form-label" style="color:#667eea; font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:12px; display:block;">
        <i class="fas fa-shield-alt"></i> Chế độ quét QR
    </label>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
        @foreach(\App\Models\Guest::SCAN_MODES as $value => $label)
        @php
            $icons = ['unlimited' => 'fa-infinity', 'one_time' => 'fa-flag-checkered', 'checkin_checkout' => 'fa-exchange-alt', 'max_scans' => 'fa-sliders-h'];
            $descs = [
                'unlimited'        => 'Quét không giới hạn',
                'one_time'         => 'Khóa ngay sau lần quét đầu',
                'checkin_checkout' => 'Vào rồi ra, sau đó khóa',
                'max_scans'        => 'Đặt số lần quét tối đa',
            ];
        @endphp
        <label style="display:flex; gap:10px; cursor:pointer; padding:12px; border-radius:10px; border:2px solid {{ $currentMode === $value ? '#667eea' : '#e9ecef' }}; background:{{ $currentMode === $value ? '#f0f2ff' : 'white' }}; transition:all 0.2s;"
               id="label_{{ $value }}" onclick="selectMode('{{ $value }}')">
            <input type="radio" name="scan_mode" value="{{ $value }}"
                   {{ $currentMode === $value ? 'checked' : '' }} style="display:none;">
            <div>
                <div style="font-weight:600; font-size:0.88rem; color:{{ $currentMode === $value ? '#667eea' : '#555' }};">
                    <i class="fas {{ $icons[$value] }}"></i> {{ $label }}
                </div>
                <div style="font-size:0.76rem; color:#999; margin-top:2px;">{{ $descs[$value] }}</div>
            </div>
        </label>
        @endforeach
    </div>

    {{-- Max scan count input (only for max_scans) --}}
    <div id="maxScanGroup" style="{{ $currentMode === 'max_scans' ? '' : 'display:none;' }} margin-top:4px;">
        <label class="form-label">Số lần quét tối đa</label>
        <div style="display:flex; align-items:center; gap:10px;">
            <input type="number" name="max_scan_count" class="form-control" style="max-width:120px;"
                   value="{{ $currentMax }}" min="1" max="999" placeholder="VD: 3">
            <span style="font-size:0.85rem; color:#888;">lần (tính cả check-in và check-out)</span>
        </div>
        @error('max_scan_count')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    {{-- Current scan info (edit only) --}}
    @if($guest)
    <div style="margin-top:14px; padding:12px; background:white; border-radius:8px; border:1px solid #e9ecef; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
        <div>
            <div style="font-size:0.75rem; color:#888; margin-bottom:2px;">Đã quét</div>
            <div style="font-size:1.4rem; font-weight:700; color:#667eea;">{{ $guest->scan_count }}</div>
        </div>
        @if($guest->remainingScans() !== null)
        <div>
            <div style="font-size:0.75rem; color:#888; margin-bottom:2px;">Còn lại</div>
            <div style="font-size:1.4rem; font-weight:700; color:{{ $guest->remainingScans() > 0 ? '#28a745' : '#dc3545' }};">{{ $guest->remainingScans() }}</div>
        </div>
        @endif
        <div style="margin-left:auto; display:flex; gap:8px; flex-wrap:wrap;">
            @if($guest->is_locked)
                <form method="POST" action="{{ route('cms.toggle-lock', $guest) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-lock-open"></i> Mở khóa</button>
                </form>
            @else
                <form method="POST" action="{{ route('cms.toggle-lock', $guest) }}">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-lock"></i> Khóa ngay</button>
                </form>
            @endif
            @if($guest->scan_count > 0 || $guest->is_locked)
            <form method="POST" action="{{ route('cms.reset-scans', $guest) }}"
                  onsubmit="return confirm('Đặt lại bộ đếm về 0 và mở khóa?')">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-redo"></i> Reset</button>
            </form>
            @endif
        </div>
    </div>
    @endif
</div>

<script>
    function selectMode(val) {
        const modes = ['unlimited', 'one_time', 'checkin_checkout', 'max_scans'];
        modes.forEach(m => {
            const lbl = document.getElementById('label_' + m);
            const inp = lbl?.querySelector('input[type=radio]');
            if (lbl) {
                const active = m === val;
                lbl.style.borderColor = active ? '#667eea' : '#e9ecef';
                lbl.style.background  = active ? '#f0f2ff' : 'white';
                lbl.querySelector('div > div:first-child').style.color = active ? '#667eea' : '#555';
                if (inp) inp.checked = active;
            }
        });
        document.getElementById('maxScanGroup').style.display = val === 'max_scans' ? 'block' : 'none';
    }
</script>
