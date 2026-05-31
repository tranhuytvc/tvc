{{-- Shared form for create/edit role --}}
<div class="form-group">
    <label class="form-label">Tên vai trò <span style="color:red">*</span></label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $role->name ?? '') }}" required
           placeholder="Ví dụ: Nhân viên check-in, Quản lý sự kiện...">
</div>

<div class="form-group">
    <label class="form-label">Mô tả</label>
    <input type="text" name="description" class="form-control" value="{{ old('description', $role->description ?? '') }}"
           placeholder="Mô tả ngắn về vai trò này...">
</div>

<div class="form-group" style="margin-top:8px;">
    <label class="form-label"><i class="fas fa-key"></i> Quyền hạn</label>
    <p style="font-size:0.82rem;color:#888;margin-bottom:12px;">Chọn các quyền mà vai trò này được phép thực hiện.</p>

    @foreach($permissions as $group => $perms)
    <div style="margin-bottom:16px;">
        <div style="font-weight:700;font-size:0.78rem;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;display:flex;align-items:center;gap:8px;">
            @php $icons = ['guests'=>'users','stations'=>'door-open','stats'=>'chart-bar','users'=>'users-cog','general'=>'cog']; @endphp
            <i class="fas fa-{{ $icons[$group] ?? 'circle' }}"></i> {{ $group }}
            <label style="margin-left:auto;display:flex;align-items:center;gap:5px;cursor:pointer;font-size:0.75rem;font-weight:400;color:#667eea;text-transform:none;letter-spacing:0;">
                <input type="checkbox" class="group-all" data-group="{{ $group }}" style="accent-color:#667eea;"
                       onchange="toggleGroup('{{ $group }}', this.checked)"> Chọn tất cả
            </label>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:10px;padding:14px;background:#f8f9fa;border-radius:10px;">
            @foreach($perms as $slug => $label)
            @php $checked = isset($assigned) ? in_array($slug, $assigned) : in_array($slug, old('permissions', [])); @endphp
            <label class="perm-check-label" data-group="{{ $group }}"
                   style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;background:{{ $checked ? '#f0f2ff' : 'white' }};border:2px solid {{ $checked ? '#667eea' : '#e9ecef' }};border-radius:8px;padding:10px 14px;min-width:200px;flex:1;transition:all 0.15s;">
                <input type="checkbox" name="permissions[]" value="{{ $slug }}"
                       {{ $checked ? 'checked' : '' }} class="perm-check" data-group="{{ $group }}"
                       style="accent-color:#667eea;margin-top:1px;flex-shrink:0;"
                       onchange="onPermChange(this)">
                <div>
                    <div style="font-size:0.85rem;font-weight:500;color:#333;">{{ $label }}</div>
                    <div style="font-size:0.72rem;color:#aaa;font-family:monospace;margin-top:1px;">{{ $slug }}</div>
                </div>
            </label>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script>
    function onPermChange(cb) {
        const label = cb.closest('label');
        label.style.borderColor = cb.checked ? '#667eea' : '#e9ecef';
        label.style.background  = cb.checked ? '#f0f2ff' : 'white';
        syncGroupAll(cb.dataset.group);
    }
    function syncGroupAll(group) {
        const all  = document.querySelectorAll(`.perm-check[data-group="${group}"]`);
        const chk  = [...all].filter(c => c.checked).length;
        const ga   = document.querySelector(`.group-all[data-group="${group}"]`);
        ga.checked       = chk === all.length;
        ga.indeterminate = chk > 0 && chk < all.length;
    }
    function toggleGroup(group, checked) {
        document.querySelectorAll(`.perm-check[data-group="${group}"]`).forEach(cb => {
            cb.checked = checked;
            const label = cb.closest('label');
            label.style.borderColor = checked ? '#667eea' : '#e9ecef';
            label.style.background  = checked ? '#f0f2ff' : 'white';
        });
    }
    // Init group-all state
    document.querySelectorAll('.group-all').forEach(ga => syncGroupAll(ga.dataset.group));
</script>
@endpush
