# 🚀 Hướng dẫn Deploy QR Welcome System lên VPS

> **Stack:** Laravel 13 · PHP 8.4 · MySQL · Nginx · Ubuntu 20.04/22.04

---

## 📋 Mục lục

1. [Yêu cầu VPS](#1-yêu-cầu-vps)
2. [Cài đặt môi trường trên VPS](#2-cài-đặt-môi-trường-trên-vps)
3. [Tạo Database MySQL](#3-tạo-database-mysql)
4. [Upload code từ máy local lên VPS (SCP)](#4-upload-code-từ-máy-local-lên-vps-scp)
5. [Cấu hình dự án](#5-cấu-hình-dự-án)
6. [Cấu hình Nginx](#6-cấu-hình-nginx)
7. [Chạy migration & seed dữ liệu ban đầu](#7-chạy-migration--seed-dữ-liệu-ban-đầu)
8. [Phân quyền thư mục & kiểm tra](#8-phân-quyền-thư-mục--kiểm-tra)
9. [Cấu hình HTTPS (SSL miễn phí)](#9-cấu-hình-https-ssl-miễn-phí)
10. [Tài khoản đăng nhập mặc định](#10-tài-khoản-đăng-nhập-mặc-định)

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
# Hoặc dùng user khác:
ssh ubuntu@YOUR_VPS_IP
```

### 2.2 Cập nhật hệ thống

```bash
apt update && apt upgrade -y
```

### 2.3 Cài PHP 8.4 và các extension cần thiết

```bash
# Thêm repository PHP
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

# Cài PHP 8.4 và extensions
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
  unzip \
  curl \
  git

# Kiểm tra
php -v
```

### 2.4 Cài Composer

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Kiểm tra
composer --version
```

### 2.5 Cài MySQL

```bash
apt install -y mysql-server

# Khởi động MySQL
systemctl start mysql
systemctl enable mysql

# Bảo mật cài đặt MySQL (đặt mật khẩu root)
mysql_secure_installation
# → Trả lời: Y Y Y Y Y
```

### 2.6 Cài Nginx

```bash
apt install -y nginx
systemctl start nginx
systemctl enable nginx

# Kiểm tra Nginx đang chạy
systemctl status nginx
```

---

## 3. Tạo Database MySQL

### 3.1 Đăng nhập MySQL

```bash
mysql -u root -p
# Nhập mật khẩu root MySQL bạn vừa đặt ở bước 2.5
```

### 3.2 Tạo database và user

```sql
-- Tạo database
CREATE DATABASE qrwelcome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tạo user riêng (thay 'MatKhauManhNe' bằng mật khẩu thực của bạn)
CREATE USER 'qruser'@'localhost' IDENTIFIED BY 'MatKhauManhNe';

-- Cấp quyền
GRANT ALL PRIVILEGES ON qrwelcome.* TO 'qruser'@'localhost';

-- Áp dụng
FLUSH PRIVILEGES;

-- Kiểm tra
SHOW DATABASES;

-- Thoát MySQL
EXIT;
```

---

## 4. Upload code từ máy local lên VPS (SCP)

### 4.1 Tạo thư mục đích trên VPS

```bash
# Trên VPS
mkdir -p /var/www/qrwelcome
```

### 4.2 Tạo file ZIP trên máy local (Windows/Mac/Linux)

**Trên Linux/Mac:**
```bash
# Di chuyển vào thư mục chứa project
cd /đường/dẫn/tới/tvc

# Tạo ZIP (bỏ qua node_modules, vendor, .git, storage/app/public)
zip -r qrwelcome.zip . \
  --exclude "*.git*" \
  --exclude "*/node_modules/*" \
  --exclude "*/vendor/*" \
  --exclude "*/storage/app/public/*" \
  --exclude "*/storage/logs/*" \
  --exclude "*/.env"
```

**Trên Windows (PowerShell):**
```powershell
# Di chuyển vào thư mục project
cd C:\đường\dẫn\tới\tvc

# Nén (bỏ qua các thư mục không cần)
Compress-Archive -Path . -DestinationPath qrwelcome.zip
# (Sau đó xóa vendor, node_modules, .env trong ZIP bằng tay nếu cần)
```

### 4.3 Upload ZIP lên VPS bằng SCP

```bash
# Chạy lệnh này trên MÁY LOCAL (không phải VPS)
scp qrwelcome.zip root@YOUR_VPS_IP:/var/www/qrwelcome/

# Nếu dùng port SSH khác (ví dụ 2222):
scp -P 2222 qrwelcome.zip root@YOUR_VPS_IP:/var/www/qrwelcome/

# Nếu dùng file .pem (AWS/key):
scp -i ~/.ssh/your-key.pem qrwelcome.zip ubuntu@YOUR_VPS_IP:/var/www/qrwelcome/
```

### 4.4 Giải nén trên VPS

```bash
# Trên VPS
cd /var/www/qrwelcome
unzip qrwelcome.zip
ls -la
```

---

## 5. Cấu hình dự án

### 5.1 Cài dependencies PHP

```bash
cd /var/www/qrwelcome

# Cài Composer dependencies (không cài dev packages)
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

Sửa các dòng sau (dùng `Ctrl+O` lưu, `Ctrl+X` thoát):

```env
APP_NAME="QR Welcome"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://YOUR_DOMAIN_OR_IP

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

> ⚠️ Thay `YOUR_DOMAIN_OR_IP`, `qrwelcome`, `qruser`, `MatKhauManhNe` bằng giá trị thực của bạn.

### 5.4 Sinh APP_KEY

```bash
php artisan key:generate
```

Kiểm tra file `.env` đã có dòng `APP_KEY=base64:...`

---

## 6. Cấu hình Nginx

### 6.1 Tạo file cấu hình Nginx

```bash
nano /etc/nginx/sites-available/qrwelcome
```

Dán nội dung sau:

```nginx
server {
    listen 80;
    server_name YOUR_DOMAIN_OR_IP;

    root /var/www/qrwelcome/public;
    index index.php index.html;

    # Xử lý Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Xử lý PHP
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Bảo mật: chặn truy cập .env và các file ẩn
    location ~ /\. {
        deny all;
    }

    # Upload file lớn (video)
    client_max_body_size 200M;

    # Tăng timeout cho upload
    fastcgi_read_timeout 300;
    fastcgi_send_timeout 300;

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;
}
```

> Thay `YOUR_DOMAIN_OR_IP` bằng domain hoặc IP thực của bạn.

### 6.2 Kích hoạt site và kiểm tra

```bash
# Tạo symlink kích hoạt
ln -s /etc/nginx/sites-available/qrwelcome /etc/nginx/sites-enabled/

# Xóa site mặc định (nếu chưa xóa)
rm -f /etc/nginx/sites-enabled/default

# Kiểm tra cú pháp Nginx
nginx -t

# Reload Nginx
systemctl reload nginx
```

---

## 7. Chạy migration & seed dữ liệu ban đầu

```bash
cd /var/www/qrwelcome

# Chạy tất cả migration (tạo bảng trong DB)
php artisan migrate --force

# Seed Super Admin + tất cả permissions
php artisan db:seed --class=AdminSeeder --force

# Tạo symlink storage (ảnh/video public)
php artisan storage:link

# Tối ưu cache cho production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 8. Phân quyền thư mục & kiểm tra

### 8.1 Phân quyền

```bash
# Đổi owner về www-data (user của Nginx/PHP-FPM)
chown -R www-data:www-data /var/www/qrwelcome

# Phân quyền thư mục
chmod -R 755 /var/www/qrwelcome
chmod -R 775 /var/www/qrwelcome/storage
chmod -R 775 /var/www/qrwelcome/bootstrap/cache

# Tạo thư mục cần thiết nếu chưa có
mkdir -p /var/www/qrwelcome/storage/app/public/qrcodes
mkdir -p /var/www/qrwelcome/storage/app/public/media
chown -R www-data:www-data /var/www/qrwelcome/storage
```

### 8.2 Tạo file database SQLite nếu dùng SQLite thay MySQL

> Bỏ qua bước này nếu đã dùng MySQL.

```bash
touch /var/www/qrwelcome/database/database.sqlite
chown www-data:www-data /var/www/qrwelcome/database/database.sqlite
```

### 8.3 Kiểm tra website

```bash
# Kiểm tra PHP-FPM đang chạy
systemctl status php8.4-fpm

# Kiểm tra Nginx đang chạy
systemctl status nginx

# Test kết nối DB
cd /var/www/qrwelcome
php artisan db:show
```

Mở trình duyệt: `http://YOUR_DOMAIN_OR_IP` → phải thấy trang quét QR.

Mở CMS: `http://YOUR_DOMAIN_OR_IP/cms` → redirect về trang login.

---

## 9. Cấu hình HTTPS (SSL miễn phí)

> Cần có tên miền trỏ về IP VPS trước khi làm bước này.

```bash
# Cài Certbot
apt install -y certbot python3-certbot-nginx

# Tạo SSL certificate (thay yourdomain.com)
certbot --nginx -d yourdomain.com

# Certbot sẽ tự cập nhật file Nginx và bật HTTPS
# Kiểm tra auto-renew
certbot renew --dry-run
```

Sau khi có SSL, cập nhật `.env`:
```bash
nano /var/www/qrwelcome/.env
# Sửa: APP_URL=https://yourdomain.com

# Clear cache
php artisan config:cache
```

---

## 10. Tài khoản đăng nhập mặc định

| Trường | Giá trị |
|---|---|
| URL | `http://YOUR_DOMAIN_OR_IP/login` |
| Email | `admin@admin.com` |
| Mật khẩu | `admin123` |

> ⚠️ **Đổi mật khẩu ngay sau khi đăng nhập lần đầu!**
>
> Vào `/cms/users` → chọn Super Admin → sửa mật khẩu.

---

## 🔄 Cập nhật code lần sau

Khi có code mới, upload lại và chạy:

```bash
# Upload file mới lên VPS (từ máy local)
scp -r ./app ./resources ./routes root@YOUR_VPS_IP:/var/www/qrwelcome/

# Trên VPS
cd /var/www/qrwelcome
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
# Xem log lỗi Laravel
tail -f /var/www/qrwelcome/storage/logs/laravel.log

# Xem log Nginx
tail -f /var/log/nginx/error.log
```

### Lỗi "Permission denied" khi upload ảnh
```bash
chmod -R 775 /var/www/qrwelcome/storage
chown -R www-data:www-data /var/www/qrwelcome/storage
```

### Lỗi "No application encryption key"
```bash
cd /var/www/qrwelcome
php artisan key:generate
php artisan config:cache
```

### Lỗi PHP-FPM socket không tìm thấy
```bash
# Kiểm tra đường dẫn socket đúng
ls /var/run/php/
# Nếu thấy php8.X-fpm.sock khác thì sửa trong file Nginx
```

### Lỗi "Class not found" sau deploy
```bash
cd /var/www/qrwelcome
composer dump-autoload --optimize
php artisan config:clear
php artisan cache:clear
```

---

## 📁 Cấu trúc thư mục quan trọng

```
/var/www/qrwelcome/
├── app/
│   ├── Http/Controllers/     # Controllers
│   └── Models/               # Models (Guest, User, Role...)
├── database/
│   └── migrations/           # Database migrations
├── public/                   # Web root (Nginx trỏ vào đây)
│   └── storage -> storage/app/public
├── resources/views/          # Blade templates
├── routes/web.php            # Routes
├── storage/
│   └── app/public/
│       ├── media/            # Ảnh/video khách mời
│       └── qrcodes/          # File QR PNG
└── .env                      # Cấu hình môi trường
```
