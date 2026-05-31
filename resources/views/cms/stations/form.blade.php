@extends('layouts.app')

@section('title', isset($station) ? 'Cài đặt Station' : 'Tạo Station mới')

@push('styles')
<style>
    .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    @media(max-width:768px) { .settings-grid { grid-template-columns: 1fr; } }
    .section-title {
        font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
        color: #667eea; padding: 10px 0 8px; border-bottom: 2px solid #e9ecef; margin-bottom: 16px;
        display: flex; align-items: center; gap: 8px;
    }
    .color-input-wrap { display: flex; align-items: center; gap: 10px; }
    .color-input-wrap input[type=color] { width: 40px; height: 40px; border: none; border-radius: 8px; cursor: pointer; padding: 2px; }
    .color-input-wrap input[type=text] { flex: 1; }
    .bg-type-options { display: flex; gap: 8px; margin-bottom: 16px; }
    .bg-type-opt { flex: 1; }
    .bg-type-opt input { display: none; }
    .bg-type-opt label {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; cursor: pointer;
        font-size: 0.85rem; font-weight: 500; transition: all 0.2s; text-align: center;
    }
    .bg-type-opt input:checked + label { border-color: #667eea; background: #f0f2ff; color: #667eea; }

    .preview-box {
        position: sticky; top: 80px; border-radius: 12px; overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .preview-display {
        height: 220px; display: flex; flex-direction: column;
        align-items: center; justify-content: center; padding: 20px; text-align: center;
        transition: all 0.3s;
    }
    .preview-scan {
        padding: 20px; text-align: center;
        border-top: 1px solid rgba(255,255,255,0.1);
    }
    .preview-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; opacity: 0.6; }

    .upload-area {
        border: 2px dashed #ccc; border-radius: 8px; padding: 20px;
        text-align: center; cursor: pointer; color: #888; transition: all 0.2s;
        font-size: 0.85rem;
    }
    .upload-area:hover { border-color: #667eea; color: #667eea; background: #f8f9ff; }
    .existing-media { display: flex; align-items: center; gap: 10px; padding: 10px; background: #f8f9f8; border-radius: 8px; margin-bottom: 8px; }
    .existing-media img, .existing-media video { width: 50px; height: 40px; object-fit: cover; border-radius: 4px; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media(max-width:600px) { .form-row { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div style="max-width:1100px; margin:0 auto;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
            <h2 style="font-size:1.3rem; font-weight:700;">
                <i class="fas fa-{{ isset($station) ? 'cog' : 'plus-circle' }}" style="color:#667eea;"></i>
                {{ isset($station) ? 'Cài đặt: ' . $station->name : 'Tạo Station mới' }}
            </h2>
            <a href="{{ route('cms.stations.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <form method="POST" action="{{ isset($station) ? route('cms.stations.update', $station) : route('cms.stations.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if(isset($station)) @method('PUT') @endif

            <div class="settings-grid">
                {{-- LEFT: Settings Panel --}}
                <div>
                    {{-- Basic Info --}}
                    <div class="card" style="margin-bottom:16px;">
                        <div class="card-body">
                            <div class="section-title"><i class="fas fa-info-circle"></i> Thông tin cơ bản</div>

                            <div class="form-group">
                                <label class="form-label">Tên station <span style="color:red">*</span></label>
                                <input type="text" name="name" class="form-control"
                                       value="{{ old('name', $station->name ?? '') }}"
                                       placeholder="VD: Cửa chính, Cửa A1, Sảnh..." required
                                       oninput="updatePreviewName(this.value)">
                                @error('name')<div class="form-error">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        Slug trang Quét QR <span style="color:red">*</span>
                                        <small style="color:#888;">(chỉ a-z, 0-9, -)</small>
                                    </label>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <span style="color:#888; font-size:0.85rem; white-space:nowrap;">/scan/</span>
                                        <input type="text" name="scan_slug" class="form-control"
                                               value="{{ old('scan_slug', $station->scan_slug ?? '') }}"
                                               placeholder="cua-chinh" pattern="[a-z0-9\-]+"
                                               oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9-]/g,'')">
                                    </div>
                                    @error('scan_slug')<div class="form-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        Slug trang Màn hình <span style="color:red">*</span>
                                        <small style="color:#888;">(chỉ a-z, 0-9, -)</small>
                                    </label>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <span style="color:#888; font-size:0.85rem; white-space:nowrap;">/display/</span>
                                        <input type="text" name="display_slug" class="form-control"
                                               value="{{ old('display_slug', $station->display_slug ?? '') }}"
                                               placeholder="man-hinh-cua-chinh" pattern="[a-z0-9\-]+"
                                               oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9-]/g,'')">
                                    </div>
                                    @error('display_slug')<div class="form-error">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tên sự kiện</label>
                                <input type="text" name="event_name" class="form-control"
                                       value="{{ old('event_name', $settings['event_name'] ?? '') }}"
                                       placeholder="VD: Hội nghị thường niên 2025"
                                       oninput="updatePreviewEvent(this.value)">
                            </div>

                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer; margin-bottom:4px;">
                                <input type="checkbox" name="is_active" value="1"
                                       {{ old('is_active', $station->is_active ?? true) ? 'checked' : '' }}
                                       style="width:16px; height:16px; accent-color:#667eea;">
                                <span class="form-label" style="margin:0;">Kích hoạt station</span>
                            </label>
                        </div>
                    </div>

                    {{-- Background --}}
                    <div class="card" style="margin-bottom:16px;">
                        <div class="card-body">
                            <div class="section-title"><i class="fas fa-palette"></i> Nền trang</div>

                            <div class="form-group">
                                <label class="form-label">Loại nền</label>
                                <div class="bg-type-options">
                                    @foreach(['gradient' => ['Gradient', 'fa-fill-drip'], 'color' => ['Màu đơn', 'fa-square'], 'image' => ['Hình ảnh', 'fa-image'], 'video' => ['Video', 'fa-video']] as $val => [$label, $icon])
                                    <div class="bg-type-opt">
                                        <input type="radio" name="bg_type" id="bg_{{ $val }}" value="{{ $val }}"
                                               {{ old('bg_type', $settings['bg_type'] ?? 'gradient') === $val ? 'checked' : '' }}
                                               onchange="toggleBgType('{{ $val }}')">
                                        <label for="bg_{{ $val }}"><i class="fas {{ $icon }}"></i> {{ $label }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div id="bgGradient" class="{{ in_array(old('bg_type', $settings['bg_type'] ?? 'gradient'), ['gradient', 'color']) ? '' : 'hidden-section' }}">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Màu nền chính</label>
                                        <div class="color-input-wrap">
                                            <input type="color" id="colorFrom" value="{{ old('bg_color_from', $settings['bg_color_from'] ?? '#0f0c29') }}"
                                                   oninput="syncColor(this, 'textColorFrom'); updatePreviewBg()">
                                            <input type="text" id="textColorFrom" name="bg_color_from" class="form-control"
                                                   value="{{ old('bg_color_from', $settings['bg_color_from'] ?? '#0f0c29') }}"
                                                   oninput="syncText(this, 'colorFrom'); updatePreviewBg()" placeholder="#0f0c29">
                                        </div>
                                    </div>
                                    <div class="form-group" id="colorToGroup" style="{{ old('bg_type', $settings['bg_type'] ?? 'gradient') === 'color' ? 'display:none' : '' }}">
                                        <label class="form-label">Màu nền phụ (gradient)</label>
                                        <div class="color-input-wrap">
                                            <input type="color" id="colorTo" value="{{ old('bg_color_to', $settings['bg_color_to'] ?? '#302b63') }}"
                                                   oninput="syncColor(this, 'textColorTo'); updatePreviewBg()">
                                            <input type="text" id="textColorTo" name="bg_color_to" class="form-control"
                                                   value="{{ old('bg_color_to', $settings['bg_color_to'] ?? '#302b63') }}"
                                                   oninput="syncText(this, 'colorTo'); updatePreviewBg()" placeholder="#302b63">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="bgImage" class="{{ old('bg_type', $settings['bg_type'] ?? 'gradient') === 'image' ? '' : 'hidden-section' }}">
                                @if(isset($settings['bg_image']) && $settings['bg_image'])
                                <div class="existing-media">
                                    <img src="{{ asset('storage/' . $settings['bg_image']) }}" alt="Ảnh nền hiện tại">
                                    <span style="font-size:0.85rem; color:#555;">Ảnh nền hiện tại</span>
                                </div>
                                @endif
                                <div class="upload-area" onclick="document.getElementById('bgImageFile').click()">
                                    <i class="fas fa-image" style="font-size:1.5rem; display:block; margin-bottom:6px;"></i>
                                    Chọn ảnh nền (JPG, PNG, WEBP)
                                </div>
                                <input type="file" id="bgImageFile" name="bg_image" style="display:none" accept="image/*" onchange="showFileName(this, 'bgImageName')">
                                <div id="bgImageName" style="font-size:0.8rem; color:#667eea; margin-top:6px; display:none;"></div>
                            </div>

                            <div id="bgVideo" class="{{ old('bg_type', $settings['bg_type'] ?? 'gradient') === 'video' ? '' : 'hidden-section' }}">
                                @if(isset($settings['bg_video']) && $settings['bg_video'])
                                <div class="existing-media">
                                    <video src="{{ asset('storage/' . $settings['bg_video']) }}" style="width:50px;height:40px;object-fit:cover;border-radius:4px;"></video>
                                    <span style="font-size:0.85rem; color:#555;">Video nền hiện tại</span>
                                </div>
                                @endif
                                <div class="upload-area" onclick="document.getElementById('bgVideoFile').click()">
                                    <i class="fas fa-video" style="font-size:1.5rem; display:block; margin-bottom:6px;"></i>
                                    Chọn video nền (MP4, MOV)
                                </div>
                                <input type="file" id="bgVideoFile" name="bg_video" style="display:none" accept="video/*" onchange="showFileName(this, 'bgVideoName')">
                                <div id="bgVideoName" style="font-size:0.8rem; color:#667eea; margin-top:6px; display:none;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Colors & Logo --}}
                    <div class="card" style="margin-bottom:16px;">
                        <div class="card-body">
                            <div class="section-title"><i class="fas fa-paint-brush"></i> Màu sắc & Logo</div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Màu nhấn (accent)</label>
                                    <div class="color-input-wrap">
                                        <input type="color" id="colorAccent" value="{{ old('accent_color', $settings['accent_color'] ?? '#667eea') }}"
                                               oninput="syncColor(this, 'textAccent'); updatePreviewAccent()">
                                        <input type="text" id="textAccent" name="accent_color" class="form-control"
                                               value="{{ old('accent_color', $settings['accent_color'] ?? '#667eea') }}"
                                               oninput="syncText(this, 'colorAccent'); updatePreviewAccent()" placeholder="#667eea">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Màu chữ</label>
                                    <div class="color-input-wrap">
                                        <input type="color" id="colorFont" value="{{ old('font_color', $settings['font_color'] ?? '#ffffff') }}"
                                               oninput="syncColor(this, 'textFont'); updatePreviewFont()">
                                        <input type="text" id="textFont" name="font_color" class="form-control"
                                               value="{{ old('font_color', $settings['font_color'] ?? '#ffffff') }}"
                                               oninput="syncText(this, 'colorFont'); updatePreviewFont()" placeholder="#ffffff">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Logo sự kiện</label>
                                @if(isset($settings['logo_path']) && $settings['logo_path'])
                                <div class="existing-media">
                                    <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Logo">
                                    <span style="font-size:0.85rem; color:#555;">Logo hiện tại</span>
                                </div>
                                @endif
                                <div class="upload-area" onclick="document.getElementById('logoFile').click()">
                                    <i class="fas fa-image" style="font-size:1.5rem; display:block; margin-bottom:6px;"></i>
                                    Chọn logo (PNG có nền trong suốt)
                                </div>
                                <input type="file" id="logoFile" name="logo" style="display:none" accept="image/*" onchange="previewLogo(this)">
                                <div id="logoPreviewWrap" style="margin-top:8px; display:none;">
                                    <img id="logoPreviewImg" style="max-height:60px; border-radius:6px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Text Content --}}
                    <div class="card" style="margin-bottom:16px;">
                        <div class="card-body">
                            <div class="section-title"><i class="fas fa-font"></i> Nội dung văn bản</div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Tiêu đề khi Check-in</label>
                                    <input type="text" name="welcome_title_checkin" class="form-control"
                                           value="{{ old('welcome_title_checkin', $settings['welcome_title_checkin'] ?? 'Xin chào,') }}"
                                           placeholder="Xin chào,">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tiêu đề khi Check-out</label>
                                    <input type="text" name="welcome_title_checkout" class="form-control"
                                           value="{{ old('welcome_title_checkout', $settings['welcome_title_checkout'] ?? 'Tạm biệt,') }}"
                                           placeholder="Tạm biệt,">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nội dung tin nhắn Check-in</label>
                                <textarea name="welcome_msg_checkin" class="form-control" rows="2"
                                          placeholder="Chào mừng bạn đã đến...">{{ old('welcome_msg_checkin', $settings['welcome_msg_checkin'] ?? 'Chào mừng bạn đã đến. Chúc bạn có một buổi tuyệt vời!') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nội dung tin nhắn Check-out</label>
                                <textarea name="welcome_msg_checkout" class="form-control" rows="2"
                                          placeholder="Tạm biệt...">{{ old('welcome_msg_checkout', $settings['welcome_msg_checkout'] ?? 'Cảm ơn bạn đã tham dự. Hẹn gặp lại!') }}</textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Tiêu đề trang Quét QR</label>
                                    <input type="text" name="scan_title" class="form-control"
                                           value="{{ old('scan_title', $settings['scan_title'] ?? 'Quét mã QR Check-in') }}"
                                           placeholder="Quét mã QR Check-in"
                                           oninput="updatePreviewScan(this.value, null)">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Mô tả trang Quét QR</label>
                                    <input type="text" name="scan_subtitle" class="form-control"
                                           value="{{ old('scan_subtitle', $settings['scan_subtitle'] ?? 'Đưa mã QR vào khung hình') }}"
                                           placeholder="Đưa mã QR vào khung hình"
                                           oninput="updatePreviewScan(null, this.value)">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Text màn hình chờ</label>
                                <input type="text" name="idle_text" class="form-control"
                                       value="{{ old('idle_text', $settings['idle_text'] ?? 'Quét mã QR để check-in') }}"
                                       placeholder="Quét mã QR để check-in">
                            </div>
                        </div>
                    </div>

                    {{-- Display Settings --}}
                    <div class="card" style="margin-bottom:16px;">
                        <div class="card-body">
                            <div class="section-title"><i class="fas fa-tv"></i> Cài đặt màn hình</div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Chế độ hiển thị</label>
                                    <select name="display_orientation" class="form-control">
                                        <option value="landscape" {{ old('display_orientation', $settings['display_orientation'] ?? 'landscape') === 'landscape' ? 'selected' : '' }}>Ngang (Landscape)</option>
                                        <option value="portrait" {{ old('display_orientation', $settings['display_orientation'] ?? 'landscape') === 'portrait' ? 'selected' : '' }}>Dọc (Portrait)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Đếm ngược (giây)</label>
                                    <input type="number" name="countdown_seconds" class="form-control"
                                           value="{{ old('countdown_seconds', $settings['countdown_seconds'] ?? 10) }}"
                                           min="3" max="60" placeholder="10">
                                </div>
                            </div>
                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                <input type="checkbox" name="show_clock" value="1"
                                       {{ old('show_clock', $settings['show_clock'] ?? true) ? 'checked' : '' }}
                                       style="width:16px; height:16px; accent-color:#667eea;">
                                <span class="form-label" style="margin:0;">Hiển thị đồng hồ trên màn hình chờ</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; padding:14px; font-size:1rem;">
                        <i class="fas fa-save"></i> {{ isset($station) ? 'Lưu thay đổi' : 'Tạo Station' }}
                    </button>
                </div>

                {{-- RIGHT: Live Preview --}}
                <div>
                    <div style="font-size:0.85rem; font-weight:600; color:#555; margin-bottom:10px; text-transform:uppercase; letter-spacing:1px;">
                        <i class="fas fa-eye"></i> Xem trước
                    </div>

                    <div class="preview-box" id="previewBox">
                        {{-- Display preview --}}
                        <div class="preview-display" id="previewDisplay">
                            <div id="previewLogo" style="margin-bottom:10px; display:none;">
                                <img id="previewLogoImg" style="max-height:40px; max-width:150px; object-fit:contain;">
                            </div>
                            <div id="previewEvent" style="font-size:0.7rem; opacity:0.6; margin-bottom:8px; font-style:italic;"></div>
                            <div id="previewName" style="font-size:1.4rem; font-weight:800; margin-bottom:6px;">Nguyễn Văn A</div>
                            <div id="previewTitle" style="font-size:0.9rem; margin-bottom:4px; opacity:0.8;">Xin chào,</div>
                            <div id="previewMsg" style="font-size:0.75rem; opacity:0.6;">Chào mừng bạn đã đến!</div>
                        </div>
                        {{-- Scan preview --}}
                        <div class="preview-scan" id="previewScan">
                            <div id="previewScanTitle" style="font-size:0.9rem; font-weight:700; margin-bottom:4px;">Quét mã QR Check-in</div>
                            <div id="previewScanSub" style="font-size:0.75rem; opacity:0.6;">Đưa mã QR vào khung hình</div>
                            <div style="margin-top:10px; opacity:0.4; font-size:1.5rem;">📱</div>
                        </div>
                    </div>

                    <div style="margin-top:12px; padding:14px; background:#f8f9fa; border-radius:10px; font-size:0.8rem; color:#666; line-height:1.6;">
                        <strong>💡 Lưu ý:</strong><br>
                        - Scan URL và Display URL phải khác nhau<br>
                        - Khi quét tại <code style="background:#e9ecef; padding:1px 4px; border-radius:3px;">/scan/{{ old('scan_slug', $station->scan_slug ?? 'slug-cua') }}</code>,<br>
                        &nbsp;&nbsp;sẽ hiện trên <code style="background:#e9ecef; padding:1px 4px; border-radius:3px;">/display/{{ old('display_slug', $station->display_slug ?? 'slug-man-hinh') }}</code>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .hidden-section { display: none; }
</style>
@endpush

@push('scripts')
<script>
    function syncColor(colorInput, textId) {
        document.getElementById(textId).value = colorInput.value;
    }
    function syncText(textInput, colorId) {
        if (/^#[0-9a-fA-F]{6}$/.test(textInput.value)) {
            document.getElementById(colorId).value = textInput.value;
        }
    }

    function toggleBgType(type) {
        document.getElementById('bgGradient').classList.toggle('hidden-section', !['gradient','color'].includes(type));
        document.getElementById('bgImage').classList.toggle('hidden-section', type !== 'image');
        document.getElementById('bgVideo').classList.toggle('hidden-section', type !== 'video');
        document.getElementById('colorToGroup').style.display = type === 'color' ? 'none' : '';
        updatePreviewBg();
    }

    function updatePreviewBg() {
        const type = document.querySelector('[name=bg_type]:checked')?.value || 'gradient';
        const from = document.getElementById('textColorFrom')?.value || '#0f0c29';
        const to = document.getElementById('textColorTo')?.value || '#302b63';
        const preview = document.getElementById('previewDisplay');
        const previewScan = document.getElementById('previewScan');

        if (type === 'gradient') {
            const bg = `linear-gradient(135deg, ${from}, ${to})`;
            preview.style.background = bg;
            previewScan.style.background = bg;
        } else if (type === 'color') {
            preview.style.background = from;
            previewScan.style.background = from;
        }
    }

    function updatePreviewAccent() {
        const color = document.getElementById('textAccent').value;
        document.getElementById('previewName').style.color = color;
    }
    function updatePreviewFont() {
        const color = document.getElementById('textFont').value;
        const preview = document.getElementById('previewDisplay');
        const previewScan = document.getElementById('previewScan');
        preview.style.color = color;
        previewScan.style.color = color;
    }
    function updatePreviewName(val) {}
    function updatePreviewEvent(val) {
        document.getElementById('previewEvent').textContent = val;
    }
    function updatePreviewScan(title, sub) {
        if (title !== null) document.getElementById('previewScanTitle').textContent = title || 'Quét mã QR Check-in';
        if (sub !== null) document.getElementById('previewScanSub').textContent = sub || '';
    }

    function showFileName(input, id) {
        const div = document.getElementById(id);
        if (input.files && input.files[0]) {
            div.textContent = '✓ ' + input.files[0].name;
            div.style.display = 'block';
        }
    }

    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const wrap = document.getElementById('logoPreviewWrap');
            const img = document.getElementById('logoPreviewImg');
            const previewLogo = document.getElementById('previewLogo');
            const previewLogoImg = document.getElementById('previewLogoImg');
            const url = URL.createObjectURL(input.files[0]);
            img.src = url;
            wrap.style.display = 'block';
            previewLogoImg.src = url;
            previewLogo.style.display = 'block';
        }
    }

    // Init preview
    updatePreviewBg();
    updatePreviewFont();
    updatePreviewAccent();
    updatePreviewEvent(document.querySelector('[name=event_name]')?.value || '');
    updatePreviewScan(
        document.querySelector('[name=scan_title]')?.value,
        document.querySelector('[name=scan_subtitle]')?.value
    );
</script>
@endpush
