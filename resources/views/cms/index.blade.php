@extends('layouts.app')

@section('title', 'Quản lý khách - CMS')

@push('styles')
<style>
    .qr-thumb { width: 56px; height: 56px; object-fit: contain; border-radius: 6px; border: 1px solid #eee; cursor: pointer; transition: transform 0.2s; }
    .qr-thumb:hover { transform: scale(1.1); }
    .media-thumb { width: 70px; height: 52px; object-fit: cover; border-radius: 6px; }
    .guest-name { font-weight: 600; color: #333; }
    .guest-email { font-size: 0.78rem; color: #aaa; }

    .scan-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; border: 1px solid;
    }
    .scan-available  { background: #d4edda; border-color: #28a745; color: #155724; }
    .scan-locked     { background: #f8d7da; border-color: #dc3545; color: #721c24; }
    .scan-exhausted  { background: #fff3cd; border-color: #ffc107; color: #856404; }
    .scan-inactive   { background: #e2e3e5; border-color: #adb5bd; color: #495057; }

    .scan-counter {
        display: flex; align-items: center; gap: 4px;
        font-size: 0.82rem; color: #555; white-space: nowrap;
    }
    .scan-bar-wrap { width: 60px; height: 4px; background: #e9ecef; border-radius: 2px; overflow: hidden; }
    .scan-bar { height: 100%; border-radius: 2px; transition: width 0.3s; }
    .bar-ok   { background: #28a745; }
    .bar-warn { background: #ffc107; }
    .bar-full { background: #dc3545; }

    .mode-tag {
        display: inline-block; padding: 2px 8px; border-radius: 4px;
        font-size: 0.72rem; font-weight: 600; background: #f0f2ff; color: #667eea;
    }

    .action-btns { display: flex; gap: 5px; flex-wrap: wrap; }
    .filter-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 20px; }
    .filter-form input, .filter-form select { height: 36px; }

    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; }
    .modal-overlay.show { display: flex; }
    .modal-box { background: white; border-radius: 16px; padding: 28px; max-width: 400px; width: 90%; text-align: center; }
    .modal-box img { width: 240px; height: 240px; border-radius: 8px; margin-bottom: 14px; }

    .bulk-bar {
        display: none; align-items: center; gap: 12px; padding: 12px 16px;
        background: #f0f2ff; border: 1px solid #c8d0ff; border-radius: 10px; margin-bottom: 16px; flex-wrap: wrap;
    }
    .bulk-bar.show { display: flex; }
    .bulk-count { font-weight: 600; color: #667eea; }

    th.sortable { cursor: pointer; user-select: none; }
    th.sortable:hover { color: #667eea; }
</style>
@endpush

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-users"></i> Danh sách khách mời <span style="opacity:0.7; font-size:0.85rem;">({{ $totalCount }} tổng)</span></h2>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('cms.download-all-qr') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-download"></i> Tải tất cả QR
                </a>
                <a href="{{ route('cms.import.form') }}" class="btn btn-sm" style="background:#7c3aed;color:#fff;">
                    <i class="fas fa-file-excel"></i> Import Excel
                </a>
                <a href="{{ route('cms.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Thêm khách
                </a>
                @if($totalCount > 0)
                <button class="btn btn-danger btn-sm" onclick="confirmDeleteAll()">
                    <i class="fas fa-trash-alt"></i> Xóa tất cả
                </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            {{-- Filter bar --}}
            <form method="GET" action="{{ route('cms.index') }}" class="filter-form">
                <input type="text" name="search" class="form-control" style="max-width:220px;"
                       placeholder="Tìm tên, email..." value="{{ request('search') }}">
                <select name="status" class="form-control" style="max-width:160px;" onchange="this.form.submit()">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="locked"   {{ request('status') === 'locked'   ? 'selected' : '' }}>Đã khóa</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                @if(request('search') || request('status'))
                    <a href="{{ route('cms.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Xóa lọc</a>
                @endif
            </form>

            {{-- Bulk action bar --}}
            <div class="bulk-bar" id="bulkBar">
                <span class="bulk-count"><span id="selectedCount">0</span> khách được chọn</span>
                <button class="btn btn-danger btn-sm" onclick="bulkDelete()">
                    <i class="fas fa-trash"></i> Xóa đã chọn
                </button>
                <button class="btn btn-secondary btn-sm" onclick="clearSelection()">
                    <i class="fas fa-times"></i> Bỏ chọn
                </button>
            </div>

            {{-- Hidden forms for bulk/all delete --}}
            <form id="deleteAllForm" method="POST" action="{{ route('cms.destroy-all') }}" style="display:none;">
                @csrf @method('DELETE')
            </form>
            <form id="deleteSelectedForm" method="POST" action="{{ route('cms.destroy-selected') }}" style="display:none;">
                @csrf @method('DELETE')
                <div id="selectedIdsContainer"></div>
            </form>

            <div style="overflow-x:auto;">
                <table class="table" id="guestTable">
                    <thead>
                        <tr>
                            <th width="36"><input type="checkbox" id="checkAll" onchange="toggleAll(this)" style="accent-color:#667eea;"></th>
                            <th width="44">ID</th>
                            <th>Tên khách</th>
                            <th>Media</th>
                            <th>QR Code</th>
                            <th>Chế độ quét</th>
                            <th>Số lần quét</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guests as $guest)
                        @php
                            $statusBadge = $guest->getScanStatusBadge();
                            $remaining = $guest->remainingScans();
                            $maxForBar = match($guest->scan_mode) {
                                'one_time' => 1,
                                'checkin_checkout' => 2,
                                'max_scans' => $guest->max_scan_count,
                                default => max($guest->scan_count, 1),
                            };
                            $barPct = $guest->scan_mode === 'unlimited' ? 0 : min(100, ($guest->scan_count / max($maxForBar, 1)) * 100);
                            $barClass = $barPct >= 100 ? 'bar-full' : ($barPct >= 60 ? 'bar-warn' : 'bar-ok');
                        @endphp
                        <tr id="row-{{ $guest->id }}" class="{{ $guest->is_locked ? 'table-row-locked' : '' }}">
                            <td>
                                <input type="checkbox" class="guest-check" value="{{ $guest->id }}"
                                       onchange="updateBulkBar()" style="accent-color:#667eea;">
                            </td>
                            <td style="font-size:0.8rem; color:#aaa; font-family:monospace;">{{ $guest->id }}</td>
                            <td>
                                <div class="guest-name">{{ $guest->name }}</div>
                                @if($guest->email)<div class="guest-email">{{ $guest->email }}</div>@endif
                            </td>
                            <td>
                                @if($guest->media_path)
                                    @if($guest->media_type === 'image')
                                        <img src="{{ asset('storage/' . $guest->media_path) }}" class="media-thumb" alt="">
                                    @else
                                        <video src="{{ asset('storage/' . $guest->media_path) }}" class="media-thumb" muted></video>
                                    @endif
                                @else
                                    <span style="color:#ddd">—</span>
                                @endif
                            </td>
                            <td>
                                @if($guest->qr_code_path)
                                    <img src="{{ asset('storage/' . $guest->qr_code_path) }}" class="qr-thumb"
                                         onclick="showQrModal('{{ asset('storage/' . $guest->qr_code_path) }}', '{{ $guest->name }}')"
                                         title="Click để xem to">
                                @else
                                    <span style="color:#ccc">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="mode-tag">{{ $guest->getScanModeLabel() }}</div>
                                @if($guest->scan_mode === 'max_scans')
                                    <div style="font-size:0.72rem; color:#888; margin-top:2px;">giới hạn {{ $guest->max_scan_count }} lần</div>
                                @endif
                            </td>
                            <td>
                                <div class="scan-counter">
                                    <strong>{{ $guest->scan_count }}</strong>
                                    @if($guest->scan_mode !== 'unlimited')
                                        / {{ $maxForBar }}
                                    @endif
                                </div>
                                @if($guest->scan_mode !== 'unlimited')
                                <div class="scan-bar-wrap" style="margin-top:4px;">
                                    <div class="scan-bar {{ $barClass }}" style="width:{{ $barPct }}%"></div>
                                </div>
                                @endif
                                @if($remaining !== null)
                                    <div style="font-size:0.7rem; color:#aaa; margin-top:2px;">còn {{ $remaining }} lần</div>
                                @endif
                            </td>
                            <td>
                                @if($statusBadge === 'available')
                                    <span class="scan-pill scan-available"><i class="fas fa-check"></i> Sẵn sàng</span>
                                @elseif($statusBadge === 'locked')
                                    <span class="scan-pill scan-locked"><i class="fas fa-lock"></i> Đã khóa</span>
                                @elseif($statusBadge === 'exhausted')
                                    <span class="scan-pill scan-exhausted"><i class="fas fa-ban"></i> Hết lượt</span>
                                @else
                                    <span class="scan-pill scan-inactive"><i class="fas fa-minus"></i> Tắt</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-btns">
                                    @if($guest->qr_code_path)
                                    <a href="{{ route('cms.download-qr', $guest) }}" class="btn btn-info btn-sm" title="Tải QR">
                                        <i class="fas fa-qrcode"></i>
                                    </a>
                                    @endif

                                    {{-- Lock/Unlock toggle --}}
                                    <form method="POST" action="{{ route('cms.toggle-lock', $guest) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $guest->is_locked ? 'btn-success' : 'btn-warning' }}"
                                                title="{{ $guest->is_locked ? 'Mở khóa' : 'Khóa QR' }}">
                                            <i class="fas fa-{{ $guest->is_locked ? 'lock-open' : 'lock' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Reset scans --}}
                                    @if($guest->scan_count > 0 || $guest->is_locked)
                                    <form method="POST" action="{{ route('cms.reset-scans', $guest) }}" style="display:inline;"
                                          onsubmit="return confirm('Đặt lại bộ đếm và mở khóa cho {{ $guest->name }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" title="Reset bộ đếm">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <a href="{{ route('cms.edit', $guest) }}" class="btn btn-warning btn-sm" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('cms.destroy', $guest) }}"
                                          onsubmit="return confirm('Xác nhận xóa {{ $guest->name }}?')" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:40px; color:#999;">
                                <i class="fas fa-users" style="font-size:3rem; display:block; margin-bottom:12px;"></i>
                                Chưa có khách nào. <a href="{{ route('cms.create') }}">Thêm ngay</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $guests->links('vendor.pagination.simple') }}
        </div>
    </div>
</div>

{{-- QR Modal --}}
<div class="modal-overlay" id="qrModal" onclick="this.classList.remove('show')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <img id="qrModalImg" src="" alt="QR Code">
        <h3 id="qrModalName" style="margin-bottom:14px;"></h3>
        <button class="btn btn-secondary btn-sm" onclick="document.getElementById('qrModal').classList.remove('show')">
            <i class="fas fa-times"></i> Đóng
        </button>
    </div>
</div>

@push('styles')
<style>
.table-row-locked td { opacity: 0.65; }
.table-row-locked .guest-name { text-decoration: line-through; color: #999; }
</style>
@endpush
@endsection

@push('scripts')
<script>
    function showQrModal(src, name) {
        document.getElementById('qrModalImg').src = src;
        document.getElementById('qrModalName').textContent = name;
        document.getElementById('qrModal').classList.add('show');
    }

    function toggleAll(cb) {
        document.querySelectorAll('.guest-check').forEach(c => c.checked = cb.checked);
        updateBulkBar();
    }

    function updateBulkBar() {
        const checked = document.querySelectorAll('.guest-check:checked').length;
        const bar = document.getElementById('bulkBar');
        document.getElementById('selectedCount').textContent = checked;
        bar.classList.toggle('show', checked > 0);
        document.getElementById('checkAll').indeterminate =
            checked > 0 && checked < document.querySelectorAll('.guest-check').length;
        document.getElementById('checkAll').checked =
            checked === document.querySelectorAll('.guest-check').length;
    }

    function clearSelection() {
        document.querySelectorAll('.guest-check').forEach(c => c.checked = false);
        document.getElementById('checkAll').checked = false;
        updateBulkBar();
    }

    function confirmDeleteAll() {
        if (!confirm('Xóa TẤT CẢ khách mời và toàn bộ file liên quan? Hành động này không thể hoàn tác!')) return;
        document.getElementById('deleteAllForm').submit();
    }

    function bulkDelete() {
        const ids = [...document.querySelectorAll('.guest-check:checked')].map(c => c.value);
        if (!ids.length) return;
        if (!confirm(`Xóa ${ids.length} khách đã chọn và toàn bộ file liên quan?`)) return;
        const container = document.getElementById('selectedIdsContainer');
        container.innerHTML = ids.map(id => `<input type="hidden" name="ids[]" value="${id}">`).join('');
        document.getElementById('deleteSelectedForm').submit();
    }
</script>
@endpush
