# 24hStore QR Warranty - Backend

Laravel 12 + Livewire 4 backend gồm:

- Custom Administration tại `/admin`.
- Trang tra cứu IMEI công khai tại `/check` và trang QR tại `/check/{qr_token}`.
- MySQL/PostgreSQL là database production.
- REST API v1 cho các tích hợp tra cứu.
- CRUD sản phẩm, QR/PDF, import Excel, tài khoản/phân quyền và audit log.

Xem tài liệu tổng tại thư mục cha: `../README.md` và `../docs/DEPLOYMENT.md`.

## Local

```bash
# cập nhật .env: APP_URL=http://127.0.0.1:8000 và cấu hình DB_*
composer install
php artisan key:generate
php artisan migrate --seed
yarn install --frozen-lockfile
yarn build
php artisan serve --host=127.0.0.1 --port=8000
```

Mở `http://127.0.0.1:8000/admin` để quản trị hoặc `http://127.0.0.1:8000/check` để tra cứu IMEI. QR dùng URL `/check/{qr_token}` trên cùng ứng dụng.
