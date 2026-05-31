@extends('layouts.app')

@section('title', 'Thêm khách - CMS')

@push('styles')
<style>
    .form-wrapper { max-width: 600px; margin: 0 auto; }
    .media-preview { margin-top: 12px; border-radius: 12px; overflow: hidden; display: none; max-height: 300px; }
    .media-preview img, .media-preview video { width: 100%; max-height: 300px; object-fit: contain; background: #000; display: block; }
    .upload-area {
        border: 2px dashed #ccc; border-radius: 12px; padding: 30px;
        text-align: center; cursor: pointer; transition: all 0.2s; color: #888;
    }
    .upload-area:hover { border-color: #667eea; color: #667eea; background: #f8f9ff; }
    .upload-area i { font-size: 2.5rem; display: block; margin-bottom: 10px; }
    .type-selector { display: flex; gap: 12px; }
    .type-option { flex: 1; }
    .type-option input { display: none; }
    .type-option label {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px; border: 2px solid #e9ecef; border-radius: 10px;
        cursor: pointer; font-weight: 500; transition: all 0.2s;
    }
    .type-option input:checked + label { border-color: #667eea; background: #f0f2ff; color: #667eea; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="form-wrapper">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-user-plus"></i> Thêm khách mới</h2>
                <a href="{{ route('cms.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('cms.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Họ và tên <span style="color:red">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                               placeholder="Nhập tên khách mời..." required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                               placeholder="email@example.com">
                        @error('email')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Loại media <span style="color:red">*</span></label>
                        <div class="type-selector">
                            <div class="type-option">
                                <input type="radio" name="media_type" id="type_image" value="image"
                                       {{ old('media_type', 'image') === 'image' ? 'checked' : '' }} onchange="updateAccept()">
                                <label for="type_image"><i class="fas fa-image"></i> Hình ảnh</label>
                            </div>
                            <div class="type-option">
                                <input type="radio" name="media_type" id="type_video" value="video"
                                       {{ old('media_type') === 'video' ? 'checked' : '' }} onchange="updateAccept()">
                                <label for="type_video"><i class="fas fa-video"></i> Video</label>
                            </div>
                        </div>
                        @error('media_type')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">File media <span style="color:red">*</span></label>
                        <div class="upload-area" onclick="document.getElementById('mediaFile').click()">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <strong>Chọn file hoặc kéo thả vào đây</strong>
                            <p style="font-size:0.8rem; margin-top:6px;">Ảnh: JPG, PNG, GIF, WEBP | Video: MP4, MOV, AVI (tối đa 100MB)</p>
                        </div>
                        <input type="file" id="mediaFile" name="media" style="display:none"
                               accept="image/*" onchange="previewMedia(this)" required>
                        <div class="media-preview" id="mediaPreview">
                            <img id="previewImg" src="" alt="">
                            <video id="previewVideo" controls></video>
                        </div>
                        @error('media')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div style="display:flex; gap:12px; margin-top:24px;">
                        <button type="submit" class="btn btn-primary" style="flex:1">
                            <i class="fas fa-save"></i> Lưu & Tạo QR Code
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
    function updateAccept() {
        const isVideo = document.getElementById('type_video').checked;
        const input = document.getElementById('mediaFile');
        input.accept = isVideo ? 'video/*' : 'image/*';
        document.getElementById('mediaPreview').style.display = 'none';
        document.getElementById('previewImg').style.display = 'none';
        document.getElementById('previewVideo').style.display = 'none';
    }

    function previewMedia(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const preview = document.getElementById('mediaPreview');
        const img = document.getElementById('previewImg');
        const video = document.getElementById('previewVideo');
        const url = URL.createObjectURL(file);

        preview.style.display = 'block';
        if (file.type.startsWith('image/')) {
            img.src = url; img.style.display = 'block';
            video.style.display = 'none';
        } else {
            video.src = url; video.style.display = 'block';
            img.style.display = 'none';
        }
    }
</script>
@endpush
