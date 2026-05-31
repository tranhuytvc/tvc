@extends('layouts.app')

@section('title', 'Vai trò & Phân quyền')

@push('styles')
<style>
    .perm-pill {
        display:inline-block;padding:2px 9px;border-radius:10px;font-size:0.72rem;font-weight:600;
        background:#f0f2ff;color:#667eea;margin:2px;
    }
</style>
@endpush

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-key"></i> Vai trò & Phân quyền</h2>
            <a href="{{ route('cms.roles.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Tạo vai trò
            </a>
        </div>
        <div class="card-body">
            @forelse($roles as $role)
            <div style="border:1px solid #e9ecef;border-radius:12px;padding:20px;margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                    <div>
                        <h3 style="font-size:1.05rem;font-weight:700;color:#333;">{{ $role->name }}</h3>
                        @if($role->description)
                        <p style="color:#888;font-size:0.85rem;margin-top:3px;">{{ $role->description }}</p>
                        @endif
                        <div style="margin-top:4px;font-size:0.8rem;color:#aaa;">
                            <i class="fas fa-users"></i> {{ $role->users_count }} người dùng &nbsp;
                            <i class="fas fa-key"></i> {{ $role->permissions_count }} quyền
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;">
                        <a href="{{ route('cms.roles.edit', $role) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <form method="POST" action="{{ route('cms.roles.destroy', $role) }}"
                              onsubmit="return confirm('Xóa vai trò {{ $role->name }}? Người dùng mang vai trò này sẽ mất quyền tương ứng.')" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                @if($role->permissions->isNotEmpty())
                <div style="margin-top:12px;display:flex;flex-wrap:wrap;">
                    @foreach($role->permissions as $perm)
                        <span class="perm-pill"><i class="fas fa-check" style="font-size:0.6rem;"></i> {{ $perm->label }}</span>
                    @endforeach
                </div>
                @else
                <div style="margin-top:10px;color:#ccc;font-size:0.82rem;font-style:italic;">Chưa có quyền nào được gán.</div>
                @endif
            </div>
            @empty
            <div style="text-align:center;padding:50px;color:#999;">
                <i class="fas fa-key" style="font-size:3rem;display:block;margin-bottom:14px;opacity:0.3;"></i>
                Chưa có vai trò nào. <a href="{{ route('cms.roles.create') }}">Tạo ngay</a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Permission legend --}}
    <div class="card" style="margin-top:20px;">
        <div class="card-header" style="background:linear-gradient(135deg,#495057,#343a40);">
            <h2 style="font-size:0.95rem;"><i class="fas fa-list"></i> Danh sách tất cả quyền trong hệ thống</h2>
        </div>
        <div class="card-body">
            @foreach(\App\Models\Permission::$all as $group => $perms)
            <div style="margin-bottom:16px;">
                <div style="font-weight:700;font-size:0.82rem;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">{{ $group }}</div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    @foreach($perms as $slug => $label)
                    <div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:6px 12px;font-size:0.82rem;">
                        <span style="color:#888;font-family:monospace;font-size:0.75rem;">{{ $slug }}</span>
                        <span style="color:#555;margin-left:6px;">{{ $label }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
