@extends('layouts.app')
@section('title', '403 - Không có quyền truy cập')
@section('content')
<div class="container" style="text-align:center;padding:80px 20px;">
    <div style="font-size:5rem;color:#dc3545;margin-bottom:16px;"><i class="fas fa-ban"></i></div>
    <h1 style="font-size:2rem;color:#333;margin-bottom:8px;">Không có quyền truy cập</h1>
    <p style="color:#888;margin-bottom:24px;">{{ $exception->getMessage() ?: 'Bạn không có quyền thực hiện thao tác này.' }}</p>
    <a href="{{ url()->previous() }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>
@endsection
