@extends('layouts.app')

@section('title', 'Sửa vai trò - ' . $role->name)

@section('content')
<div class="container" style="max-width:800px;">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-edit"></i> Sửa vai trò: {{ $role->name }}</h2>
            <a href="{{ route('cms.roles.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('cms.roles.update', $role) }}">
                @csrf @method('PUT')
                @include('cms.roles._form')
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Lưu thay đổi</button>
                    <a href="{{ route('cms.roles.index') }}" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
