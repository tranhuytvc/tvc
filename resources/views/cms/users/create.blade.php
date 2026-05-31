@extends('layouts.app')

@section('title', 'Thêm người dùng')

@section('content')
<div class="container" style="max-width:700px;">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-user-plus"></i> Thêm người dùng</h2>
            <a href="{{ route('cms.users.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('cms.users.store') }}">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Họ tên <span style="color:red">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span style="color:red">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mật khẩu <span style="color:red">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Xác nhận mật khẩu <span style="color:red">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <div class="form-group" style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f8f9fa;border-radius:8px;">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} style="accent-color:#667eea;width:16px;height:16px;">
                    <label for="is_active" style="cursor:pointer;font-weight:500;">Kích hoạt tài khoản</label>
                </div>

                @if($roles->isNotEmpty())
                <div class="form-group" style="margin-top:6px;">
                    <label class="form-label"><i class="fas fa-shield-alt"></i> Gán vai trò</label>
                    <div style="display:flex;flex-wrap:wrap;gap:10px;padding:16px;background:#f8f9fa;border-radius:8px;">
                        @foreach($roles as $role)
                        <label style="display:flex;align-items:center;gap:7px;cursor:pointer;background:white;border:2px solid #e9ecef;border-radius:8px;padding:8px 14px;transition:all 0.15s;"
                               onmouseover="this.style.borderColor='#667eea'" onmouseout="this.querySelector('input').checked ? null : this.style.borderColor='#e9ecef'">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                   {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                   style="accent-color:#667eea;"
                                   onchange="this.closest('label').style.borderColor=this.checked?'#667eea':'#e9ecef'; this.closest('label').style.background=this.checked?'#f0f2ff':'white'">
                            <span style="font-size:0.88rem;font-weight:500;">{{ $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    <div style="margin-top:8px;font-size:0.8rem;color:#888;">
                        <i class="fas fa-info-circle"></i> Chưa có vai trò nào?
                        <a href="{{ route('cms.roles.create') }}">Tạo vai trò mới</a>
                    </div>
                </div>
                @else
                <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px 16px;font-size:0.85rem;color:#856404;margin-bottom:16px;">
                    <i class="fas fa-exclamation-triangle"></i> Chưa có vai trò nào.
                    <a href="{{ route('cms.roles.create') }}">Tạo vai trò trước</a> để phân quyền cho người dùng này.
                </div>
                @endif

                <div style="display:flex;gap:10px;margin-top:8px;">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Tạo người dùng</button>
                    <a href="{{ route('cms.users.index') }}" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
