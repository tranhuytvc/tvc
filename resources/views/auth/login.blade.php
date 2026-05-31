<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - QR Welcome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }
        .login-card {
            background: rgba(255,255,255,0.05); backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 20px;
            padding: 44px 40px; width: 100%; max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .brand {
            text-align: center; margin-bottom: 32px;
        }
        .brand-icon {
            width: 64px; height: 64px; background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 16px; display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.8rem; color: white; margin-bottom: 14px;
            box-shadow: 0 8px 24px rgba(102,126,234,0.4);
        }
        .brand h1 { font-size: 1.5rem; font-weight: 700; color: white; }
        .brand p  { color: rgba(255,255,255,0.5); font-size: 0.88rem; margin-top: 4px; }

        .form-group { margin-bottom: 18px; }
        .form-label { display: block; color: rgba(255,255,255,0.75); font-size: 0.85rem; font-weight: 500; margin-bottom: 7px; }
        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.35); font-size: 0.9rem; }
        .form-control {
            width: 100%; padding: 12px 14px 12px 40px;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px; color: white; font-size: 0.9rem; outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .form-control::placeholder { color: rgba(255,255,255,0.3); }
        .form-control:focus { border-color: #667eea; background: rgba(255,255,255,0.12); }
        .form-error { color: #ff6b6b; font-size: 0.8rem; margin-top: 5px; }

        .remember-row { display: flex; align-items: center; gap: 8px; margin-bottom: 22px; }
        .remember-row label { color: rgba(255,255,255,0.6); font-size: 0.85rem; cursor: pointer; }
        .remember-row input[type=checkbox] { accent-color: #667eea; width: 15px; height: 15px; cursor: pointer; }

        .btn-login {
            width: 100%; padding: 13px; border-radius: 10px; border: none;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; font-size: 1rem; font-weight: 600; cursor: pointer;
            transition: all 0.2s; letter-spacing: 0.3px;
            box-shadow: 0 4px 16px rgba(102,126,234,0.4);
        }
        .btn-login:hover { filter: brightness(1.1); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(102,126,234,0.5); }
        .btn-login:active { transform: translateY(0); }

        .alert-error {
            background: rgba(220,53,69,0.2); border: 1px solid rgba(220,53,69,0.4);
            border-radius: 10px; padding: 12px 16px; color: #ff6b6b;
            font-size: 0.85rem; margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <div class="brand-icon"><i class="fas fa-qrcode"></i></div>
            <h1>QR Welcome</h1>
            <p>Đăng nhập để quản lý hệ thống</p>
        </div>

        @if($errors->any())
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control"
                           placeholder="admin@admin.com" value="{{ old('email') }}" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Mật khẩu</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>
            <div class="remember-row">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Ghi nhớ đăng nhập</label>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
            </button>
        </form>
    </div>
</body>
</html>
