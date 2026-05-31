@extends('layouts.app')

@section('title', 'Sửa khách - CMS')

@push('styles')
<style>
    .form-wrapper { max-width: 640px; margin: 0 auto; }
    .current-media { border-radius: 10px; overflow: hidden; margin-bottom: 12px; max-height: 250px; background: #000; }
    .current-media img, .current-media video { width: 100%; max-height: 250px; object-fit: contain; display: block; }
    .qr-section { display: flex; align-items: center; gap: 20px; padding: 16px; background: #f8f9ff; border-radius: 12px; margin-bottom: 20px; }
    .qr-section img { width: 100px; height: 100px; border-radius: 8px; border: 2px solid #e9ecef; }
    .type-selector { display: flex; gap: 12px; }
    .type-option { flex: 1; }
    .type-option input { display: none; }
    .type-option label {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px; border: 2px solid #e9ecef; border-radius: 10px;
        cursor: pointer; font-weight: 500; transition: all 0.2s;
    }
    .type-option input:checked + label { border-color: #667eea; background: #f0f2ff; color: #667eea; }
    .upload-area {
        border: 2px dashed #ccc; border-radius: 12px; padding: 24px;
        text-align: center; cursor: pointer; transition: all 0.2s; color: #888;
    }
    .upload-area:hover { border-color: #667eea; color: #667eea; background: #f8f9ff; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="form-wrapper">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-user-edit"></i> Sửa thông tin khách</h2>
                <a href="{{ route('cms.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
            <div class="card-body">
                {{-- QR Code Preview --}}
                @if($guest->qr_code_path)
                <div class="qr-section">
                    <img src="{{ asset('storage/' . $guest->qr_code_path) }}" alt="QR Code">
                    <div>
                        <div style="font-weight:600; margin-bottom:6px;">QR Code hiện tại</div>
                        <div style="font-size:0.8rem; color:#888; margin-bottom:10px;">Token: {{ $guest->qr_token }}</div>
                        <a href="{{ route('cms.download-qr', $guest) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-download"></i> Tải QR
                        </a>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('cms.update', $guest) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Họ và tên <span style="color:red">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $guest->name) }}" required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $guest->email) }}">
                        @error('email')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Loại media</label>
                        <div class="type-selector">
                            <div class="type-option">
                                <input type="radio" name="media_type" id="type_image" value="image"
                                       {{ old('media_type', $guest->media_type) === 'image' ? 'checked' : '' }}>
                                <label for="type_image"><i class="fas fa-image"></i> Hình ảnh</label>
                            </div>
                            <div class="type-option">
                                <input type="radio" name="media_type" id="type_video" value="video"
                                       {{ old('media_type', $guest->media_type) === 'video' ? 'checked' : '' }}>
                                <label for="type_video"><i class="fas fa-video"></i> Video</label>
                            </div>
                        </div>
                    </div>

                    @if($guest->media_path)
                    <div class="form-group">
                        <label class="form-label">Media hiện tại</label>
                        <div class="current-media">
                            @if($guest->media_type === 'image')
                                <img src="{{ asset('storage/' . $guest->media_path) }}" alt="{{ $guest->name }}">
                            @else
                                <video src="{{ asset('storage/' . $guest->media_path) }}" controls></video>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label">Thay đổi media (để trống nếu không đổi)</label>
                        <div class="upload-area" onclick="document.getElementById('mediaFile').click()">
                            <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem; display:block; margin-bottom:8px;"></i>
                            Chọn file mới...
                        </div>
                        <input type="file" id="mediaFile" name="media" style="display:none"
                               accept="image/*,video/*" onchange="showFileName(this)">
                        <div id="fileNameDisplay" style="margin-top:8px; font-size:0.85rem; color:#667eea; display:none;"></div>
                        @error('media')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1" {{ $guest->is_active ? 'checked' : '' }}
                                   style="width:18px; height:18px; accent-color:#667eea;">
                            <span class="form-label" style="margin:0;">Kích hoạt QR code</span>
                        </label>
                    </div>

                    {{-- Scan mode --}}
                    @include('cms._scan_mode_fields', ['guest' => $guest])

                    <div style="display:flex; gap:12px; margin-top:24px;">
                        <button type="submit" class="btn btn-primary" style="flex:1">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                        <a href="{{ route('cms.index') }}" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showFileName(input) {
        const div = document.getElementById('fileNameDisplay');
        if (input.files && input.files[0]) {
            div.textContent = '✓ ' + input.files[0].name;
            div.style.display = 'block';
        }
    }
</script>
@endpush
