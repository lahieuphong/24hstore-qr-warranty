# Deploy VPS Linux - Backend + Frontend

Ví dụ dùng Nginx, PHP 8.4 FPM, Composer 2, Node.js 22.12+, Yarn 1.22, MySQL 8 hoặc PostgreSQL và hai subdomain.

## 1. Thư mục

```text
/var/www/24hstore-qr-warranty/backend
/var/www/24hstore-qr-warranty/frontend
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
cp .env.production.example .env
# sửa APP_URL, FRONTEND_URL, DB_*, ADMIN_*
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

## 4. Frontend

```bash
cd /var/www/24hstore-qr-warranty/frontend
cp .env.production.example .env
# sửa APP_URL, BACKEND_API_URL, BACKEND_ADMIN_URL
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
yarn install --frozen-lockfile --non-interactive --production=false
yarn build
php artisan key:generate
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
```

Frontend không chạy migrate và không cần database.

## 5. Quyền file

```bash
sudo chown -R www-data:www-data \
  /var/www/24hstore-qr-warranty/backend/storage \
  /var/www/24hstore-qr-warranty/backend/bootstrap/cache \
  /var/www/24hstore-qr-warranty/frontend/storage \
  /var/www/24hstore-qr-warranty/frontend/bootstrap/cache

sudo chmod -R ug+rwX \
  /var/www/24hstore-qr-warranty/backend/storage \
  /var/www/24hstore-qr-warranty/backend/bootstrap/cache \
  /var/www/24hstore-qr-warranty/frontend/storage \
  /var/www/24hstore-qr-warranty/frontend/bootstrap/cache
```

## 6. Nginx

Sao chép và sửa `server_name`, socket PHP:

```bash
sudo cp deploy/nginx/backend.conf /etc/nginx/sites-available/24hstore-warranty-backend
sudo cp deploy/nginx/frontend.conf /etc/nginx/sites-available/24hstore-warranty-frontend
sudo ln -s /etc/nginx/sites-available/24hstore-warranty-backend /etc/nginx/sites-enabled/
sudo ln -s /etc/nginx/sites-available/24hstore-warranty-frontend /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Cấp TLS cho cả hai subdomain. Sau đó giữ `FORCE_HTTPS=true` và cookie secure trên production.

## 7. Queue và scheduler backend

```bash
sudo cp deploy/systemd/backend-worker.service /etc/systemd/system/24hstore-warranty-worker.service
sudo systemctl daemon-reload
sudo systemctl enable --now 24hstore-warranty-worker
```

Cron:

```cron
* * * * * cd /var/www/24hstore-qr-warranty/backend && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Frontend không cần worker/scheduler.

## 8. Kiểm tra sau deploy

```text
https://admin-warranty.example.com/up
https://admin-warranty.example.com/api/v1/health
https://admin-warranty.example.com/admin
https://warranty.example.com/up
https://warranty.example.com
```

Tạo một sản phẩm, mở QR modal và xác nhận URL bắt đầu bằng domain frontend.

## 9. Cập nhật phiên bản

Có thể dùng hai script trong `deploy/scripts/`. Quy trình an toàn: backup database, cập nhật code, chạy test ở staging, deploy backend, deploy frontend, restart worker, kiểm tra API/QR/import/PDF.
