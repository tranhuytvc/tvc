@extends('layouts.app')

@section('title', 'Quản lý khách - CMS')

@push('styles')
<style>
    .qr-thumb { width: 60px; height: 60px; object-fit: contain; border-radius: 6px; border: 1px solid #eee; cursor: pointer; }
    .media-thumb { width: 80px; height: 60px; object-fit: cover; border-radius: 6px; }
    .guest-name { font-weight: 600; color: #333; }
    .guest-email { font-size: 0.8rem; color: #888; }
    .action-btns { display: flex; gap: 6px; flex-wrap: wrap; }
    .search-bar { display: flex; gap: 12px; margin-bottom: 20px; align-items: center; flex-wrap: wrap; }
    .search-bar input { flex: 1; min-width: 200px; }
    .bulk-actions { display: flex; gap: 10px; align-items: center; padding: 16px 0; border-bottom: 1px solid #f0f0f0; margin-bottom: 16px; flex-wrap: wrap; }
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; }
    .modal-overlay.show { display: flex; }
    .modal-box { background: white; border-radius: 16px; padding: 30px; max-width: 400px; width: 90%; text-align: center; }
    .modal-box img { width: 100%; max-width: 300px; border-radius: 8px; margin-bottom: 16px; }
    .modal-box h3 { font-size: 1.1rem; margin-bottom: 6px; }
</style>
@endpush

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-users"></i> Danh sách khách mời</h2>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('cms.download-all-qr') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-download"></i> Tải tất cả QR
                </a>
                <a href="{{ route('cms.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Thêm khách
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="search-bar">
                <input type="text" class="form-control" id="searchInput" placeholder="Tìm kiếm theo tên, email...">
                <span style="color:#888; font-size:0.85rem;">Tổng: <strong>{{ $guests->total() }}</strong> khách</span>
            </div>

            <div style="overflow-x:auto;">
                <table class="table" id="guestTable">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Tên khách</th>
                            <th>Media</th>
                            <th>QR Code</th>
                            <th>Check-in</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guests as $guest)
                        <tr class="guest-row" data-name="{{ strtolower($guest->name) }}" data-email="{{ strtolower($guest->email) }}">
                            <td>{{ $guest->id }}</td>
                            <td>
                                <div class="guest-name">{{ $guest->name }}</div>
                                @if($guest->email)<div class="guest-email">{{ $guest->email }}</div>@endif
                            </td>
                            <td>
                                @if($guest->media_path)
                                    @if($guest->media_type === 'image')
                                        <img src="{{ asset('storage/' . $guest->media_path) }}" class="media-thumb" alt="{{ $guest->name }}">
                                    @else
                                        <video src="{{ asset('storage/' . $guest->media_path) }}" class="media-thumb" muted></video>
                                    @endif
                                @else
                                    <span style="color:#ccc">—</span>
                                @endif
                            </td>
                            <td>
                                @if($guest->qr_code_path)
                                    <img src="{{ asset('storage/' . $guest->qr_code_path) }}" class="qr-thumb"
                                         onclick="showQrModal('{{ asset('storage/' . $guest->qr_code_path) }}', '{{ $guest->name }}')"
                                         title="Click để phóng to">
                                @else
                                    <span style="color:#ccc">Chưa có</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $guest->checkins_count }} lần</span>
                            </td>
                            <td>
                                @if($guest->is_active)
                                    <span class="badge badge-success">Hoạt động</span>
                                @else
                                    <span class="badge badge-danger">Tắt</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-btns">
                                    @if($guest->qr_code_path)
                                    <a href="{{ route('cms.download-qr', $guest) }}" class="btn btn-info btn-sm" title="Tải QR">
                                        <i class="fas fa-qrcode"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('cms.edit', $guest) }}" class="btn btn-warning btn-sm" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('cms.destroy', $guest) }}" onsubmit="return confirm('Xác nhận xóa khách này?')">
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
                            <td colspan="7" style="text-align:center; padding:40px; color:#999">
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
        <h3 id="qrModalName"></h3>
        <p style="color:#888; font-size:0.85rem; margin-bottom:16px;">Click vào QR để đóng</p>
        <button class="btn btn-secondary" onclick="document.getElementById('qrModal').classList.remove('show')">
            <i class="fas fa-times"></i> Đóng
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showQrModal(src, name) {
        document.getElementById('qrModalImg').src = src;
        document.getElementById('qrModalName').textContent = name;
        document.getElementById('qrModal').classList.add('show');
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.guest-row').forEach(row => {
            const name = row.dataset.name || '';
            const email = row.dataset.email || '';
            row.style.display = (name.includes(q) || email.includes(q)) ? '' : 'none';
        });
    });
</script>
@endpush
