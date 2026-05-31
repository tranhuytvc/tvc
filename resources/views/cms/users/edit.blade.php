@extends('layouts.app')

@section('title', 'Sửa người dùng - ' . $user->name)

@section('content')
<div class="container" style="max-width:700px;">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-user-edit"></i> Sửa: {{ $user->name }}</h2>
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

            <form method="POST" action="{{ route('cms.users.update', $user) }}">
                @csrf @method('PUT')

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Họ tên <span style="color:red">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span style="color:red">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mật khẩu mới <small style="color:#aaa;">(để trống = giữ nguyên)</small></label>
                        <input type="password" name="password" class="form-control" minlength="6" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••">
                    </div>
                </div>

                <div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f8f9fa;border-radius:8px;flex:1;">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }} style="accent-color:#667eea;width:16px;height:16px;">
                        <label for="is_active" style="cursor:pointer;font-weight:500;">Kích hoạt tài khoản</label>
                    </div>
                    @if(auth()->user()->is_super_admin && !($user->id === auth()->id()))
                    <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f8f9fa;border-radius:8px;flex:1;">
                        <input type="checkbox" name="is_super_admin" id="is_super_admin" value="1"
                               {{ old('is_super_admin', $user->is_super_admin) ? 'checked' : '' }} style="accent-color:#764ba2;width:16px;height:16px;">
                        <label for="is_super_admin" style="cursor:pointer;font-weight:500;">Super Admin</label>
                    </div>
                    @endif
                </div>

                @if(!$user->is_super_admin && $roles->isNotEmpty())
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-shield-alt"></i> Vai trò</label>
                    <div style="display:flex;flex-wrap:wrap;gap:10px;padding:16px;background:#f8f9fa;border-radius:8px;">
                        @foreach($roles as $role)
                        @php $checked = in_array($role->id, old('roles', $userRoleIds)); @endphp
                        <label style="display:flex;align-items:center;gap:7px;cursor:pointer;background:{{ $checked ? '#f0f2ff' : 'white' }};border:2px solid {{ $checked ? '#667eea' : '#e9ecef' }};border-radius:8px;padding:8px 14px;transition:all 0.15s;"
                               onmouseover="this.style.borderColor='#667eea'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#e9ecef'">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                   {{ $checked ? 'checked' : '' }}
                                   style="accent-color:#667eea;"
                                   onchange="this.closest('label').style.borderColor=this.checked?'#667eea':'#e9ecef'; this.closest('label').style.background=this.checked?'#f0f2ff':'white'">
                            <div>
                                <div style="font-size:0.88rem;font-weight:500;">{{ $role->name }}</div>
                                @if($role->description)
                                <div style="font-size:0.75rem;color:#888;">{{ $role->description }}</div>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @elseif($user->is_super_admin)
                <div style="background:#f0f2ff;border:1px solid #c8d0ff;border-radius:8px;padding:12px 16px;font-size:0.85rem;color:#667eea;margin-bottom:16px;">
                    <i class="fas fa-info-circle"></i> Super Admin có toàn quyền — không cần phân vai trò.
                </div>
                @endif

                <div style="display:flex;gap:10px;margin-top:8px;">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Lưu thay đổi</button>
                    <a href="{{ route('cms.users.index') }}" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
