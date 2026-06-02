@extends('layouts.app')

@section('title', 'Import khách hàng')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('cms.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h4 class="mb-0 fw-bold">Import khách hàng hàng loạt</h4>
            <small class="text-muted">Bước 1/2 — Tải lên file Excel danh sách khách</small>
        </div>
    </div>

    {{-- Progress --}}
    <div class="d-flex align-items-center gap-0 mb-4" style="max-width:500px;">
        <div class="d-flex align-items-center gap-2">
            <div style="width:32px;height:32px;border-radius:50%;background:#7c3aed;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;">1</div>
            <span class="fw-600 text-purple" style="color:#7c3aed;">Upload Excel</span>
        </div>
        <div style="flex:1;height:2px;background:#dee2e6;margin:0 12px;"></div>
        <div class="d-flex align-items-center gap-2 text-muted">
            <div style="width:32px;height:32px;border-radius:50%;background:#e9ecef;color:#adb5bd;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;">2</div>
            <span>Upload ảnh/video</span>
        </div>
    </div>

    <div class="row g-4">
        {{-- Upload form --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-file-excel text-success me-2"></i>Upload file Excel</h6>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('cms.import.excel') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <div id="dropzone" onclick="document.getElementById('fileInput').click()"
                                 style="border:2px dashed #7c3aed;border-radius:12px;padding:40px;text-align:center;cursor:pointer;background:rgba(124,58,237,0.03);transition:all .2s;">
                                <i class="fas fa-cloud-upload-alt fa-2x text-purple mb-2" style="color:#7c3aed;"></i>
                                <div class="fw-600">Kéo thả file hoặc click để chọn</div>
                                <div class="text-muted small mt-1">Hỗ trợ: .xlsx, .xls, .csv — Tối đa 10MB</div>
                                <div id="fileName" class="mt-2 text-success fw-bold" style="display:none;"></div>
                            </div>
                            <input type="file" id="fileInput" name="file" accept=".xlsx,.xls,.csv" class="d-none" required>
                        </div>
                        <button type="submit" class="btn btn-purple w-100" style="background:#7c3aed;color:#fff;border:0;padding:12px;border-radius:10px;font-weight:700;">
                            <i class="fas fa-upload me-2"></i>Nhập dữ liệu & tiếp tục
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Template guide --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-info-circle text-info me-2"></i>Hướng dẫn & mẫu file</h6>
                        <a href="{{ route('cms.import.template') }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-download me-1"></i>Tải mẫu
                        </a>
                    </div>

                    <p class="text-muted small mb-3">File Excel cần có các cột tiêu đề (dòng 1) như sau:</p>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" style="font-size:.82rem;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Cột</th><th>Tên cột</th><th>Mô tả</th><th>Bắt buộc</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>A</td><td><code>ten</code></td><td>Tên khách mời</td><td><span class="badge bg-danger">Có</span></td></tr>
                                <tr><td>B</td><td><code>email</code></td><td>Địa chỉ email</td><td><span class="badge bg-secondary">Không</span></td></tr>
                                <tr><td>C</td><td><code>file_media</code></td><td>Tên file ảnh/video (vd: <code>an.jpg</code>)</td><td><span class="badge bg-secondary">Không</span></td></tr>
                                <tr><td>D</td><td><code>che_do_quet</code></td><td>unlimited / one_time / checkin_checkout / max_scans</td><td><span class="badge bg-secondary">Không</span></td></tr>
                                <tr><td>E</td><td><code>so_lan_toi_da</code></td><td>Số lần (chỉ khi chế độ = max_scans)</td><td><span class="badge bg-secondary">Không</span></td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info py-2 px-3 mt-2" style="font-size:.8rem;">
                        <i class="fas fa-lightbulb me-1"></i>
                        <strong>Lưu ý cột file_media:</strong> Điền chính xác tên file (bao gồm phần mở rộng, ví dụ <code>nguyen-van-an.jpg</code>).
                        Ở bước 2 bạn sẽ upload các file ảnh/video đó — hệ thống tự động ghép theo tên file.
                    </div>

                    <div class="mt-3">
                        <div class="fw-bold small mb-2 text-muted">Chế độ quét hợp lệ:</div>
                        <div class="d-flex flex-wrap gap-2" style="font-size:.78rem;">
                            <span class="badge bg-light text-dark border">unlimited — Không giới hạn</span>
                            <span class="badge bg-light text-dark border">one_time — Một lần</span>
                            <span class="badge bg-light text-dark border">checkin_checkout — Vào &amp; Ra</span>
                            <span class="badge bg-light text-dark border">max_scans — Tối đa N lần</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const input = document.getElementById('fileInput');
const zone  = document.getElementById('dropzone');
const label = document.getElementById('fileName');

input.addEventListener('change', () => {
    if (input.files[0]) {
        label.textContent = '✓ ' + input.files[0].name;
        label.style.display = 'block';
    }
});

zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.background = 'rgba(124,58,237,0.08)'; });
zone.addEventListener('dragleave', () => { zone.style.background = 'rgba(124,58,237,0.03)'; });
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.style.background = 'rgba(124,58,237,0.03)';
    if (e.dataTransfer.files[0]) {
        input.files = e.dataTransfer.files;
        label.textContent = '✓ ' + e.dataTransfer.files[0].name;
        label.style.display = 'block';
    }
});
</script>
@endsection
