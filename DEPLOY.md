# 🚀 Hướng dẫn Deploy QR Welcome System lên VPS

> **Stack:** Laravel 13 · PHP 8.4 · MySQL · Nginx · Ubuntu 20.04/22.04
>
> **Domain:** `chothuecongnghetuongtac.com/qrwelcome`
> **Đường dẫn VPS:** `/var/www/chothuecongnghetuongtac.com/public/qrwelcome`

---

## 📋 Mục lục

0. [Tải code về máy local](#0-tải-code-về-máy-local)
1. [Yêu cầu VPS](#1-yêu-cầu-vps)
2. [Cài đặt môi trường trên VPS](#2-cài-đặt-môi-trường-trên-vps)
3. [Tạo Database MySQL](#3-tạo-database-mysql)
4. [Upload code lên VPS (SCP)](#4-upload-code-lên-vps-scp)
5. [Cấu hình dự án](#5-cấu-hình-dự-án)
6. [Cấu hình Nginx](#6-cấu-hình-nginx)
7. [Chạy migration & seed dữ liệu ban đầu](#7-chạy-migration--seed-dữ-liệu-ban-đầu)
8. [Phân quyền thư mục & kiểm tra](#8-phân-quyền-thư-mục--kiểm-tra)
9. [Tài khoản đăng nhập mặc định](#9-tài-khoản-đăng-nhập-mặc-định)

---

## 0. Tải code về máy local

### Cách 1: Thư mục mới (khuyên dùng)

```bash
git clone -b claude/qr-code-welcome-system-44fHI https://github.com/tranhuytvc/tvc.git tvc
cd tvc
```

### Cách 2: Đang đứng trong thư mục đích (có file cũ)

```bash
# Xóa file xung đột nếu có
rm -f DEPLOY.md

git init
git remote add origin https://github.com/tranhuytvc/tvc.git
git pull origin claude/qr-code-welcome-system-44fHI
```

### Cách 3: Tải ZIP không cần Git

```
https://github.com/tranhuytvc/tvc/archive/refs/heads/claude/qr-code-welcome-system-44fHI.zip
```

---

## 1. Yêu cầu VPS

| Thành phần | Phiên bản tối thiểu |
|---|---|
| Ubuntu | 20.04 hoặc 22.04 |
| PHP | 8.2+ (khuyên dùng 8.4) |
| MySQL | 8.0+ |
| Nginx | 1.18+ |
| Composer | 2.x |
| RAM | 1 GB |
| Disk | 5 GB |

---

## 2. Cài đặt môi trường trên VPS

### 2.1 SSH vào VPS

```bash
ssh root@YOUR_VPS_IP
```

### 2.2 Cập nhật hệ thống

```bash
apt update && apt upgrade -y
```

### 2.3 Cài PHP 8.4 và các extension cần thiết

```bash
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

apt install -y \
  php8.4 \
  php8.4-fpm \
  php8.4-mysql \
  php8.4-mbstring \
  php8.4-xml \
  php8.4-curl \
  php8.4-zip \
  php8.4-gd \
  php8.4-bcmath \
  php8.4-intl \
  php8.4-tokenizer \
  php8.4-fileinfo \
  unzip curl git

php -v
```

### 2.4 Cài Composer

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
composer --version
```

### 2.5 Cài MySQL (nếu chưa có)

```bash
apt install -y mysql-server
systemctl start mysql
systemctl enable mysql
mysql_secure_installation
```

### 2.6 Cài Nginx (nếu chưa có)

```bash
apt install -y nginx
systemctl start nginx
systemctl enable nginx
```

---

## 3. Tạo Database MySQL

> ⚠️ Nếu đã tạo database rồi, bỏ qua phần này, chỉ cần ghi lại thông tin DB để điền vào `.env`.

```bash
mysql -u root -p
```

```sql
-- Tạo database
CREATE DATABASE qrwelcome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tạo user riêng
CREATE USER 'qruser'@'localhost' IDENTIFIED BY 'MatKhauManhNe';

-- Cấp quyền
GRANT ALL PRIVILEGES ON qrwelcome.* TO 'qruser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 4. Upload code lên VPS (SCP)

### 4.1 Tạo thư mục đích trên VPS

```bash
mkdir -p /var/www/chothuecongnghetuongtac.com/public/qrwelcome
```

### 4.2 Tạo file ZIP trên máy local

**Linux/Mac:**
```bash
cd /đường/dẫn/tới/tvc

zip -r qrwelcome.zip . \
  --exclude "*.git*" \
  --exclude "*/node_modules/*" \
  --exclude "*/vendor/*" \
  --exclude "*/storage/app/public/*" \
  --exclude "*/storage/logs/*" \
  --exclude "*/.env"
```

**Windows (PowerShell):**
```powershell
cd C:\đường\dẫn\tới\tvc
Compress-Archive -Path . -DestinationPath qrwelcome.zip
```

### 4.3 Upload lên VPS bằng SCP

```bash
# Chạy trên MÁY LOCAL
scp qrwelcome.zip root@YOUR_VPS_IP:/var/www/chothuecongnghetuongtac.com/public/qrwelcome/

# Nếu dùng port SSH khác (ví dụ 2222):
scp -P 2222 qrwelcome.zip root@YOUR_VPS_IP:/var/www/chothuecongnghetuongtac.com/public/qrwelcome/

# Nếu dùng file .pem:
scp -i ~/.ssh/your-key.pem qrwelcome.zip root@YOUR_VPS_IP:/var/www/chothuecongnghetuongtac.com/public/qrwelcome/
```

### 4.4 Giải nén trên VPS

```bash
cd /var/www/chothuecongnghetuongtac.com/public/qrwelcome
unzip qrwelcome.zip
ls -la
```

---

## 5. Cấu hình dự án

### 5.1 Cài dependencies PHP

```bash
cd /var/www/chothuecongnghetuongtac.com/public/qrwelcome
composer install --no-dev --optimize-autoloader --no-interaction
```

### 5.2 Tạo file .env

```bash
cp .env.example .env
```

### 5.3 Chỉnh sửa file .env

```bash
nano .env
```

Sửa các dòng sau (`Ctrl+O` lưu, `Ctrl+X` thoát):

```env
APP_NAME="QR Welcome"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://chothuecongnghetuongtac.com/qrwelcome

APP_LOCALE=vi

LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qrwelcome
DB_USERNAME=qruser
DB_PASSWORD=MatKhauManhNe

SESSION_DRIVER=file
CACHE_STORE=file
```

> ⚠️ Thay `qrwelcome`, `qruser`, `MatKhauManhNe` bằng thông tin DB thực của bạn.

### 5.4 Sinh APP_KEY

```bash
php artisan key:generate
```

---

## 6. Cấu hình Nginx

App nằm trong **subfolder** `/qrwelcome` của domain chính. Cần thêm `location` block vào file cấu hình Nginx của `chothuecongnghetuongtac.com`.

### 6.1 Mở file cấu hình Nginx của domain chính

```bash
# Tìm file cấu hình hiện tại
ls /etc/nginx/sites-available/
# Thường có tên: chothuecongnghetuongtac.com hoặc default

nano /etc/nginx/sites-available/chothuecongnghetuongtac.com
```

### 6.2 Thêm location block vào trong `server { }` hiện có

Tìm `server { ... }` trong file và thêm đoạn sau vào bên trong (trước dấu `}` cuối cùng):

```nginx
# ── QR Welcome App ───────────────────────────────────────────
location ^~ /qrwelcome {
    alias /var/www/chothuecongnghetuongtac.com/public/qrwelcome/public;
    try_files $uri $uri/ @qrwelcome;
    index index.php;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME /var/www/chothuecongnghetuongtac.com/public/qrwelcome/public$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }
}

location @qrwelcome {
    rewrite ^/qrwelcome/(.*)$ /qrwelcome/index.php?/$1 last;
}
# ─────────────────────────────────────────────────────────────

# Upload file lớn (ảnh/video)
client_max_body_size 200M;
```

### 6.3 Kiểm tra và reload Nginx

```bash
# Kiểm tra cú pháp
nginx -t

# Reload
systemctl reload nginx
```

---

## 7. Chạy migration & seed dữ liệu ban đầu

```bash
cd /var/www/chothuecongnghetuongtac.com/public/qrwelcome

# Tạo bảng trong DB
php artisan migrate --force

# Tạo Super Admin + tất cả permissions
php artisan db:seed --class=AdminSeeder --force

# Tạo symlink storage (ảnh/video public)
php artisan storage:link

# Tối ưu cache production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 8. Phân quyền thư mục & kiểm tra

```bash
# Đổi owner
chown -R www-data:www-data /var/www/chothuecongnghetuongtac.com/public/qrwelcome

# Phân quyền
chmod -R 755 /var/www/chothuecongnghetuongtac.com/public/qrwelcome
chmod -R 775 /var/www/chothuecongnghetuongtac.com/public/qrwelcome/storage
chmod -R 775 /var/www/chothuecongnghetuongtac.com/public/qrwelcome/bootstrap/cache

# Tạo thư mục media nếu chưa có
mkdir -p /var/www/chothuecongnghetuongtac.com/public/qrwelcome/storage/app/public/qrcodes
mkdir -p /var/www/chothuecongnghetuongtac.com/public/qrwelcome/storage/app/public/media
chown -R www-data:www-data /var/www/chothuecongnghetuongtac.com/public/qrwelcome/storage
```

**Kiểm tra:**

```bash
systemctl status php8.4-fpm
systemctl status nginx

cd /var/www/chothuecongnghetuongtac.com/public/qrwelcome
php artisan db:show
```

Mở trình duyệt:
- Trang quét QR: `https://chothuecongnghetuongtac.com/qrwelcome/scan`
- Trang CMS: `https://chothuecongnghetuongtac.com/qrwelcome/cms`
- Trang login: `https://chothuecongnghetuongtac.com/qrwelcome/login`

---

## 9. Tài khoản đăng nhập mặc định

| Trường | Giá trị |
|---|---|
| URL | `https://chothuecongnghetuongtac.com/qrwelcome/login` |
| Email | `admin@admin.com` |
| Mật khẩu | `admin123` |

> ⚠️ **Đổi mật khẩu ngay sau khi đăng nhập lần đầu!**
> Vào `/qrwelcome/cms/users` → chọn Super Admin → sửa mật khẩu.

---

## 🔄 Cập nhật code lần sau

```bash
# Từ máy local — upload các thư mục thay đổi
scp -r ./app ./resources ./routes root@YOUR_VPS_IP:/var/www/chothuecongnghetuongtac.com/public/qrwelcome/

# Trên VPS
cd /var/www/chothuecongnghetuongtac.com/public/qrwelcome
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 🛠️ Xử lý lỗi thường gặp

### Lỗi 500 - Internal Server Error
```bash
tail -f /var/www/chothuecongnghetuongtac.com/public/qrwelcome/storage/logs/laravel.log
tail -f /var/log/nginx/error.log
```

### Lỗi "Permission denied" khi upload ảnh
```bash
chmod -R 775 /var/www/chothuecongnghetuongtac.com/public/qrwelcome/storage
chown -R www-data:www-data /var/www/chothuecongnghetuongtac.com/public/qrwelcome/storage
```

### Lỗi "No application encryption key"
```bash
cd /var/www/chothuecongnghetuongtac.com/public/qrwelcome
php artisan key:generate
php artisan config:cache
```

### Lỗi PHP-FPM socket không tìm thấy
```bash
ls /var/run/php/
# Nếu thấy php8.X-fpm.sock khác phiên bản thì sửa trong file Nginx
```

### Lỗi "Class not found" sau deploy
```bash
cd /var/www/chothuecongnghetuongtac.com/public/qrwelcome
composer dump-autoload --optimize
php artisan config:clear
php artisan cache:clear
```

### Link ảnh/QR không hiển thị
```bash
cd /var/www/chothuecongnghetuongtac.com/public/qrwelcome
php artisan storage:link
```

---

## 📁 Cấu trúc thư mục quan trọng

```
/var/www/chothuecongnghetuongtac.com/public/qrwelcome/
├── app/Http/Controllers/     # Controllers
├── app/Models/               # Models (Guest, User, Role...)
├── database/migrations/      # Database migrations
├── public/                   # ← Nginx alias trỏ vào đây
│   ├── index.php
│   └── storage -> storage/app/public
├── resources/views/          # Blade templates
├── routes/web.php            # Routes
├── storage/app/public/
│   ├── media/                # Ảnh/video khách mời
│   └── qrcodes/              # File QR PNG
└── .env                      # Cấu hình môi trường
```
