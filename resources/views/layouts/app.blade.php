<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'QR Welcome System')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; color: #333; }

        .navbar {
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);
            color: white; padding: 0 24px;
            display: flex; align-items: center; justify-content: space-between;
            height: 60px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: sticky; top: 0; z-index: 100;
        }
        .navbar-brand { font-size: 1.2rem; font-weight: 700; color: #e94560; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .navbar-nav { display: flex; gap: 6px; list-style: none; }
        .navbar-nav a {
            color: rgba(255,255,255,0.85); text-decoration: none; padding: 8px 14px;
            border-radius: 6px; font-size: 0.9rem; transition: all 0.2s; display: flex; align-items: center; gap: 6px;
        }
        .navbar-nav a:hover, .navbar-nav a.active { background: rgba(255,255,255,0.15); color: white; }

        .container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .container-fluid { padding: 24px; }

        .card {
            background: white; border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden;
        }
        .card-header {
            padding: 20px 24px; border-bottom: 1px solid #f0f0f0;
            display: flex; align-items: center; justify-content: space-between;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;
        }
        .card-header h2 { font-size: 1.1rem; font-weight: 600; }
        .card-body { padding: 24px; }

        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 18px; border-radius: 8px; border: none;
            font-size: 0.9rem; font-weight: 500; cursor: pointer; text-decoration: none;
            transition: all 0.2s; white-space: nowrap;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5a6fd6; transform: translateY(-1px); }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-info:hover { background: #138496; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #545b62; }
        .btn-sm { padding: 5px 12px; font-size: 0.8rem; }
        .btn-outline { background: transparent; border: 2px solid currentColor; }

        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #f8f9fa; padding: 12px 16px; text-align: left; font-weight: 600; font-size: 0.85rem; color: #555; border-bottom: 2px solid #e9ecef; }
        .table td { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; font-size: 0.9rem; vertical-align: middle; }
        .table tr:hover td { background: #fafbff; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-warning { background: #fff3cd; color: #856404; }

        .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-success { background: #d4edda; border-left: 4px solid #28a745; color: #155724; }
        .alert-danger { background: #f8d7da; border-left: 4px solid #dc3545; color: #721c24; }

        .form-group { margin-bottom: 18px; }
        .form-label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem; color: #555; }
        .form-control {
            width: 100%; padding: 10px 14px; border: 2px solid #e9ecef;
            border-radius: 8px; font-size: 0.9rem; transition: border-color 0.2s; outline: none;
        }
        .form-control:focus { border-color: #667eea; }
        select.form-control { cursor: pointer; }
        .form-error { color: #dc3545; font-size: 0.8rem; margin-top: 4px; }

        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px; margin-bottom: 24px;
        }
        .stat-card {
            background: white; border-radius: 12px; padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08); text-align: center;
            border-top: 4px solid;
        }
        .stat-card .value { font-size: 2.5rem; font-weight: 700; line-height: 1; }
        .stat-card .label { font-size: 0.85rem; color: #666; margin-top: 6px; }

        .pagination { display: flex; gap: 6px; justify-content: center; margin-top: 20px; flex-wrap: wrap; }
        .pagination a, .pagination span {
            display: inline-flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 8px; text-decoration: none;
            font-size: 0.85rem; font-weight: 500; border: 2px solid #e9ecef; color: #555;
        }
        .pagination a:hover { border-color: #667eea; color: #667eea; }
        .pagination .active { background: #667eea; border-color: #667eea; color: white; }

        @media (max-width: 768px) {
            .navbar-nav { display: none; }
            .container { padding: 12px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('scan') }}" class="navbar-brand">
            <i class="fas fa-qrcode"></i> QR Welcome
        </a>
        <ul class="navbar-nav">
            <li><a href="{{ route('scan') }}" class="{{ request()->routeIs('scan') ? 'active' : '' }}"><i class="fas fa-camera"></i> Quét QR</a></li>
            <li><a href="{{ route('display') }}" class="{{ request()->routeIs('display') ? 'active' : '' }}"><i class="fas fa-tv"></i> Màn hình</a></li>
            <li><a href="{{ route('cms.index') }}" class="{{ request()->routeIs('cms.*') ? 'active' : '' }}"><i class="fas fa-cog"></i> Quản lý</a></li>
            <li><a href="{{ route('stats') }}" class="{{ request()->routeIs('stats') ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> Thống kê</a></li>
        </ul>
    </nav>
    <main>
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
