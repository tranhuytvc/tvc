@extends('layouts.app')

@section('title', 'Upload ảnh/video khách hàng')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('cms.import.form') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h4 class="mb-0 fw-bold">Upload ảnh/video khách hàng</h4>
            <small class="text-muted">Bước 2/2 — Upload file media, hệ thống tự ghép theo tên file</small>
        </div>
    </div>

    {{-- Progress --}}
    <div class="d-flex align-items-center gap-0 mb-4" style="max-width:500px;">
        <div class="d-flex align-items-center gap-2 text-muted">
            <div style="width:32px;height:32px;border-radius:50%;background:#28a745;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.85rem;"><i class="fas fa-check"></i></div>
            <span>Upload Excel</span>
        </div>
        <div style="flex:1;height:2px;background:#7c3aed;margin:0 12px;"></div>
        <div class="d-flex align-items-center gap-2">
            <div style="width:32px;height:32px;border-radius:50%;background:#7c3aed;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;">2</div>
            <span class="fw-bold" style="color:#7c3aed;">Upload ảnh/video</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('import_errors') && count(session('import_errors')))
        <div class="alert alert-warning">
            <strong>Lỗi khi import một số dòng:</strong>
            @foreach(session('import_errors') as $e)<div class="small">{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="row g-4">
        {{-- Upload form --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-1"><i class="fas fa-images text-warning me-2"></i>Upload ảnh &amp; video</h6>
                    <p class="text-muted small mb-3">Chọn tất cả file media cùng lúc. Hệ thống tự ghép với khách theo tên file.</p>

                    <form method="POST" action="{{ route('cms.import.media.upload') }}" enctype="multipart/form-data" id="mediaForm">
                        @csrf
                        <div id="dropzoneMedia"
                             onclick="document.getElementById('mediaInput').click()"
                             style="border:2px dashed #f59e0b;border-radius:12px;padding:40px;text-align:center;cursor:pointer;background:rgba(245,158,11,0.03);transition:all .2s;">
                            <i class="fas fa-photo-video fa-2x mb-2" style="color:#f59e0b;"></i>
                            <div class="fw-bold">Kéo thả hoặc click để chọn</div>
                            <div class="text-muted small mt-1">Chọn nhiều file cùng lúc (ảnh: jpg, png, gif | video: mp4, webm, mov)</div>
                            <div class="text-muted small">Tối đa <strong>1 GB</strong> mỗi lần upload</div>
                            <div id="mediaCount" class="mt-2 fw-bold text-warning" style="display:none;"></div>
                        </div>
                        <input type="file" id="mediaInput" name="files[]" accept="image/*,video/*" multiple class="d-none">

                        {{-- File preview list --}}
                        <div id="fileList" class="mt-3" style="display:none;max-height:260px;overflow-y:auto;">
                            <div class="fw-bold small text-muted mb-2">File đã chọn:</div>
                            <div id="fileListItems"></div>
                        </div>

                        {{-- Progress bar --}}
                        <div id="progressWrap" class="mt-3" style="display:none;">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Đang upload...</span>
                                <span id="progressPct">0%</span>
                            </div>
                            <div class="progress" style="height:10px;border-radius:6px;">
                                <div id="progressBar" class="progress-bar bg-warning" style="width:0%;transition:width .3s;"></div>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" class="btn w-100 mt-3 fw-bold" style="background:#f59e0b;color:#fff;border:0;padding:12px;border-radius:10px;" disabled>
                            <i class="fas fa-upload me-2"></i>Upload &amp; ghép media
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Pending list --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0 pt-3 px-4">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-list-check me-2 text-muted"></i>
                        Khách chờ ghép media
                        <span class="badge rounded-pill ms-2" style="background:#7c3aed;">{{ $pending->count() }}</span>
                    </h6>
                </div>
                <div class="card-body px-4 py-3" style="max-height:480px;overflow-y:auto;">
                    @if($pending->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                            Tất cả khách đã có media!
                        </div>
                    @else
                        <div class="d-flex flex-column gap-2">
                        @foreach($pending as $g)
                            <div class="d-flex align-items-center gap-3 p-2 rounded" style="background:#f8f9fa;">
                                <div style="width:34px;height:34px;border-radius:50%;background:rgba(124,58,237,0.1);color:#7c3aed;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;">
                                    {{ strtoupper(mb_substr($g->name, 0, 1)) }}
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div class="fw-bold small text-truncate">{{ $g->name }}</div>
                                    <div class="text-muted" style="font-size:.75rem;">
                                        <i class="fas fa-file me-1"></i>{{ $g->import_media_name ?? '(chưa có tên file)' }}
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark" style="font-size:.7rem;">Chờ media</span>
                            </div>
                        @endforeach
                        </div>
                    @endif
                </div>
                @if($pending->isNotEmpty())
                <div class="card-footer bg-white border-0 px-4 pb-3">
                    <div class="alert alert-info py-2 px-3 mb-0" style="font-size:.78rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        Tên file upload phải <strong>khớp chính xác</strong> với cột <code>file_media</code> trong Excel.
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('cms.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-check me-2"></i>Hoàn tất, về danh sách khách
        </a>
    </div>
</div>

<script>
const input   = document.getElementById('mediaInput');
const zone    = document.getElementById('dropzoneMedia');
const count   = document.getElementById('mediaCount');
const listWrap= document.getElementById('fileList');
const listEl  = document.getElementById('fileListItems');
const submitBtn = document.getElementById('submitBtn');
const form    = document.getElementById('mediaForm');

function updateFileList(files) {
    if (!files.length) return;
    count.textContent = `✓ Đã chọn ${files.length} file`;
    count.style.display = 'block';
    submitBtn.disabled = false;

    listEl.innerHTML = '';
    Array.from(files).forEach(f => {
        const isVideo = f.type.startsWith('video/');
        const icon = isVideo ? '🎬' : '🖼️';
        const size = (f.size / 1024 / 1024).toFixed(1) + ' MB';
        listEl.innerHTML += `<div class="d-flex align-items-center gap-2 py-1 border-bottom" style="font-size:.8rem;">
            <span>${icon}</span>
            <span class="text-truncate flex-1" style="flex:1;">${f.name}</span>
            <span class="text-muted">${size}</span>
        </div>`;
    });
    listWrap.style.display = 'block';
}

input.addEventListener('change', () => updateFileList(input.files));

zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.background = 'rgba(245,158,11,0.08)'; });
zone.addEventListener('dragleave', () => { zone.style.background = 'rgba(245,158,11,0.03)'; });
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.style.background = 'rgba(245,158,11,0.03)';
    if (e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        updateFileList(e.dataTransfer.files);
    }
});

// Progress via XHR
form.addEventListener('submit', function(e) {
    e.preventDefault();
    if (!input.files.length) return;

    const data = new FormData(form);
    const xhr  = new XMLHttpRequest();
    document.getElementById('progressWrap').style.display = 'block';
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang upload...';

    xhr.upload.addEventListener('progress', ev => {
        if (ev.lengthComputable) {
            const pct = Math.round(ev.loaded / ev.total * 100);
            document.getElementById('progressBar').style.width = pct + '%';
            document.getElementById('progressPct').textContent = pct + '%';
        }
    });

    xhr.addEventListener('load', () => {
        // Redirect to same page after upload (server redirects back)
        window.location.href = xhr.responseURL || window.location.href;
    });

    xhr.open('POST', form.action);
    xhr.send(data);
});
</script>
@endsection
