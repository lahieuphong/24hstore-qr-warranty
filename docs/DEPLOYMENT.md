# Deploy VPS Linux

Ví dụ dùng Nginx, PHP 8.4 FPM, Composer 2, Node.js 22.12+, Yarn 1.22 và MySQL 8 hoặc PostgreSQL. Admin và trang tra cứu công khai chạy chung một ứng dụng/domain.

## 1. Thư mục

```text
/var/www/24hstore-qr-warranty/backend
```

Document root phải trỏ vào `public/`, không trỏ vào root source.

## 2. Tạo database MySQL

Đăng nhập MySQL bằng tài khoản quản trị và chạy, thay mật khẩu trước khi dùng:

```sql
CREATE DATABASE qr_warranty CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'qr_warranty'@'127.0.0.1' IDENTIFIED BY 'replace_with_a_long_random_password';
GRANT ALL PRIVILEGES ON qr_warranty.* TO 'qr_warranty'@'127.0.0.1';
FLUSH PRIVILEGES;
```

Chỉ backend cần credential này.

## 3. Backend

```bash
cd /var/www/24hstore-qr-warranty/backend
# chuẩn bị .env và sửa APP_URL, DB_*, ADMIN_*
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
yarn install --frozen-lockfile --non-interactive --production=false
yarn build
php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
```

## 4. Quyền file

```bash
sudo chown -R www-data:www-data \
  /var/www/24hstore-qr-warranty/backend/storage \
  /var/www/24hstore-qr-warranty/backend/bootstrap/cache

sudo chmod -R ug+rwX \
  /var/www/24hstore-qr-warranty/backend/storage \
  /var/www/24hstore-qr-warranty/backend/bootstrap/cache
```

## 5. Nginx

Sao chép và sửa `server_name`, socket PHP:

```bash
sudo cp deploy/nginx/backend.conf /etc/nginx/sites-available/24hstore-warranty-backend
sudo ln -s /etc/nginx/sites-available/24hstore-warranty-backend /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Cấu hình `server_name` cho domain dùng chung, sau đó cấp TLS và giữ `FORCE_HTTPS=true` cùng cookie secure trên production.

## 6. Queue và scheduler backend

```bash
sudo cp deploy/systemd/backend-worker.service /etc/systemd/system/24hstore-warranty-worker.service
sudo systemctl daemon-reload
sudo systemctl enable --now 24hstore-warranty-worker
```

Cron:

```cron
* * * * * cd /var/www/24hstore-qr-warranty/backend && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

## 7. Kiểm tra sau deploy

```text
https://admin-warranty.example.com/up
https://admin-warranty.example.com/api/v1/health
https://admin-warranty.example.com/admin
https://admin-warranty.example.com/check
```

Tạo một sản phẩm, mở QR modal và xác nhận URL có dạng `https://admin-warranty.example.com/check/{qr_token}`.

## 8. Cập nhật phiên bản

Có thể dùng `deploy/scripts/deploy-backend.sh`. Quy trình an toàn: backup database, cập nhật code, chạy test ở staging, deploy backend, restart worker và kiểm tra admin, `/check`, API, QR, import, PDF.
