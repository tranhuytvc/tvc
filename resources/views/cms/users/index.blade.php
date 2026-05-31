@extends('layouts.app')

@section('title', 'Quản lý người dùng')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-users-cog"></i> Người dùng <span style="opacity:0.7;font-size:0.85rem;">({{ $users->total() }})</span></h2>
            <a href="{{ route('cms.users.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Thêm người dùng
            </a>
        </div>
        <div class="card-body">
            <div style="overflow-x:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="44">ID</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td style="font-size:0.8rem;color:#aaa;font-family:monospace;">{{ $user->id }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <span style="width:34px;height:34px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:white;flex-shrink:0;">
                                        {{ strtoupper(substr($user->name,0,1)) }}
                                    </span>
                                    <div>
                                        <div style="font-weight:600;">{{ $user->name }}
                                            @if($user->id === auth()->id())
                                                <span style="background:#e8f4fd;color:#0c5460;font-size:0.68rem;padding:1px 6px;border-radius:8px;font-weight:600;">Bạn</span>
                                            @endif
                                        </div>
                                        @if($user->is_super_admin)
                                            <span style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;font-size:0.68rem;padding:2px 7px;border-radius:10px;font-weight:600;">Super Admin</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="color:#666;">{{ $user->email }}</td>
                            <td>
                                @if($user->is_super_admin)
                                    <span style="color:#888;font-size:0.82rem;font-style:italic;">Tất cả quyền</span>
                                @elseif($user->roles->isEmpty())
                                    <span style="color:#ccc;font-size:0.82rem;">—</span>
                                @else
                                    <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                        @foreach($user->roles as $role)
                                        <span style="background:#f0f2ff;color:#667eea;font-size:0.75rem;padding:2px 9px;border-radius:10px;font-weight:600;">{{ $role->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Hoạt động</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-ban"></i> Vô hiệu</span>
                                @endif
                            </td>
                            <td style="font-size:0.82rem;color:#888;">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div style="display:flex;gap:5px;">
                                    <a href="{{ route('cms.users.edit', $user) }}" class="btn btn-warning btn-sm" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('cms.users.destroy', $user) }}"
                                          onsubmit="return confirm('Xóa người dùng {{ $user->name }}?')" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:#999;">
                                <i class="fas fa-users" style="font-size:3rem;display:block;margin-bottom:12px;"></i>
                                Chưa có người dùng nào.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $users->links('vendor.pagination.simple') }}
        </div>
    </div>
</div>
@endsection
