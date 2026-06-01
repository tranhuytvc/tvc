<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'QR Welcome System')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; color: #333; min-height: 100vh; display: flex; flex-direction: column; }

        .navbar {
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);
            color: white; padding: 0 24px;
            display: flex; align-items: center; justify-content: space-between;
            height: 60px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: sticky; top: 0; z-index: 100;
        }
        .navbar-brand { font-size: 1.2rem; font-weight: 700; color: white; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .navbar-brand .brand-logo { height: 36px; max-width: 140px; object-fit: contain; }
        .navbar-brand .brand-text { display: flex; flex-direction: column; line-height: 1.1; }
        .navbar-brand .brand-name { font-size: 1.15rem; font-weight: 800; letter-spacing: 1px; background: linear-gradient(135deg, #fff 30%, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .navbar-brand .brand-sub  { font-size: 0.6rem; font-weight: 500; color: rgba(255,255,255,0.45); letter-spacing: 2px; text-transform: uppercase; -webkit-text-fill-color: rgba(255,255,255,0.45); }

        .site-footer {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: rgba(255,255,255,0.4); text-align: center;
            padding: 16px 24px; font-size: 0.8rem; margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .site-footer span { color: rgba(255,255,255,0.65); font-weight: 600; }
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
        <a href="{{ route('cms.index') }}" class="navbar-brand">
            {{-- Nếu có file public/images/logo.png thì hiện ảnh, không thì hiện text --}}
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ asset('images/logo.png') }}" class="brand-logo" alt="TVTECH Logo">
            @else
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#667eea,#e94560);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-qrcode" style="font-size:1.1rem;color:white;"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-name">TVTECH</span>
                    <span class="brand-sub">QR Welcome</span>
                </div>
            @endif
        </a>
        <ul class="navbar-nav">
            <li><a href="{{ route('scan') }}" class="{{ request()->routeIs('scan') && !request()->route('slug') ? 'active' : '' }}"><i class="fas fa-camera"></i> Quét QR</a></li>
            <li><a href="{{ route('display') }}" class="{{ request()->routeIs('display') && !request()->route('slug') ? 'active' : '' }}"><i class="fas fa-tv"></i> Màn hình</a></li>
            @auth
                @if(auth()->user()->hasPermission('guests.view'))
                <li><a href="{{ route('cms.index') }}" class="{{ request()->routeIs('cms.index') || request()->routeIs('cms.create') || request()->routeIs('cms.edit') ? 'active' : '' }}"><i class="fas fa-users"></i> Khách mời</a></li>
                @endif
                @if(auth()->user()->hasPermission('stations.view'))
                <li><a href="{{ route('cms.stations.index') }}" class="{{ request()->routeIs('cms.stations.*') ? 'active' : '' }}"><i class="fas fa-door-open"></i> Stations</a></li>
                @endif
                @if(auth()->user()->hasPermission('stats.view'))
                <li><a href="{{ route('stats') }}" class="{{ request()->routeIs('stats') ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> Thống kê</a></li>
                @endif
                @if(auth()->user()->hasPermission('users.manage') || auth()->user()->hasPermission('roles.manage'))
                <li style="position:relative;" id="adminMenu">
                    <a href="#" onclick="toggleAdminMenu(event)" class="{{ request()->routeIs('cms.users.*') || request()->routeIs('cms.roles.*') ? 'active' : '' }}" style="gap:4px;">
                        <i class="fas fa-shield-alt"></i> Quản trị <i class="fas fa-chevron-down" style="font-size:0.65rem;"></i>
                    </a>
                    <div id="adminDropdown" style="display:none; position:absolute; top:calc(100% + 4px); right:0; background:#1a1a2e; border:1px solid rgba(255,255,255,0.12); border-radius:10px; min-width:180px; padding:6px; z-index:200; box-shadow:0 8px 24px rgba(0,0,0,0.4);">
                        @if(auth()->user()->hasPermission('users.manage'))
                        <a href="{{ route('cms.users.index') }}" style="display:flex; align-items:center; gap:8px; padding:9px 12px; color:rgba(255,255,255,0.85); text-decoration:none; border-radius:7px; font-size:0.88rem;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'">
                            <i class="fas fa-users-cog" style="width:16px;"></i> Người dùng
                        </a>
                        @endif
                        @if(auth()->user()->hasPermission('roles.manage'))
                        <a href="{{ route('cms.roles.index') }}" style="display:flex; align-items:center; gap:8px; padding:9px 12px; color:rgba(255,255,255,0.85); text-decoration:none; border-radius:7px; font-size:0.88rem;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'">
                            <i class="fas fa-key" style="width:16px;"></i> Vai trò & Quyền
                        </a>
                        @endif
                    </div>
                </li>
                @endif
                {{-- User avatar / logout --}}
                <li style="position:relative;" id="userMenu">
                    <a href="#" onclick="toggleUserMenu(event)" style="gap:6px;">
                        <span style="width:28px;height:28px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:white;flex-shrink:0;">
                            {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                        </span>
                        <span style="max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:0.85rem;">{{ auth()->user()->name }}</span>
                        <i class="fas fa-chevron-down" style="font-size:0.65rem;"></i>
                    </a>
                    <div id="userDropdown" style="display:none; position:absolute; top:calc(100% + 4px); right:0; background:#1a1a2e; border:1px solid rgba(255,255,255,0.12); border-radius:10px; min-width:180px; padding:6px; z-index:200; box-shadow:0 8px 24px rgba(0,0,0,0.4);">
                        <div style="padding:10px 12px 8px; border-bottom:1px solid rgba(255,255,255,0.08); margin-bottom:4px;">
                            <div style="color:white; font-weight:600; font-size:0.88rem;">{{ auth()->user()->name }}</div>
                            <div style="color:rgba(255,255,255,0.4); font-size:0.76rem;">{{ auth()->user()->email }}</div>
                            @if(auth()->user()->is_super_admin)
                            <div style="margin-top:4px;"><span style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;font-size:0.68rem;padding:2px 7px;border-radius:10px;font-weight:600;">Super Admin</span></div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" style="width:100%;display:flex;align-items:center;gap:8px;padding:9px 12px;color:rgba(255,100,100,0.9);background:transparent;border:none;cursor:pointer;border-radius:7px;font-size:0.88rem;text-align:left;" onmouseover="this.style.background='rgba(220,53,69,0.15)'" onmouseout="this.style.background='transparent'">
                                <i class="fas fa-sign-out-alt" style="width:16px;"></i> Đăng xuất
                            </button>
                        </form>
                    </div>
                </li>
            @else
                <li><a href="{{ route('cms.index') }}" class="{{ request()->routeIs('cms.*') ? 'active' : '' }}"><i class="fas fa-users"></i> Khách mời</a></li>
                <li><a href="{{ route('cms.stations.index') }}" class="{{ request()->routeIs('cms.stations.*') ? 'active' : '' }}"><i class="fas fa-door-open"></i> Stations</a></li>
                <li><a href="{{ route('stats') }}" class="{{ request()->routeIs('stats') ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> Thống kê</a></li>
                <li><a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a></li>
            @endauth
        </ul>
    </nav>
    <script>
        function toggleAdminMenu(e) {
            e.preventDefault(); e.stopPropagation();
            const d = document.getElementById('adminDropdown');
            document.getElementById('userDropdown') && (document.getElementById('userDropdown').style.display = 'none');
            d.style.display = d.style.display === 'none' ? 'block' : 'none';
        }
        function toggleUserMenu(e) {
            e.preventDefault(); e.stopPropagation();
            const d = document.getElementById('userDropdown');
            document.getElementById('adminDropdown') && (document.getElementById('adminDropdown').style.display = 'none');
            d.style.display = d.style.display === 'none' ? 'block' : 'none';
        }
        document.addEventListener('click', () => {
            document.getElementById('adminDropdown') && (document.getElementById('adminDropdown').style.display = 'none');
            document.getElementById('userDropdown') && (document.getElementById('userDropdown').style.display = 'none');
        });
    </script>
    <main style="flex:1;">
        @yield('content')
    </main>

    <footer class="site-footer">
        Quản lý bản quyền bởi <span>TVTECH</span> &copy; {{ date('Y') }}
    </footer>

    @stack('scripts')
</body>
</html>
