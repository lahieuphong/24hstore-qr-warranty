# 24hStore QR Warranty

Ứng dụng Laravel 12 + Livewire 4 thống nhất gồm:

- Custom Administration tại `/admin`.
- Trang tra cứu IMEI công khai tại `/check` và trang QR tại `/check/{qr_token}`.
- MySQL/PostgreSQL là database production.
- REST API v1 cho các tích hợp tra cứu.
- CRUD sản phẩm, QR/PDF, import Excel, tài khoản/phân quyền và audit log.

## Local

```bash
# cập nhật .env.development: APP_URL=http://127.0.0.1:8000 và cấu hình DB_*
composer install
php artisan config:clear --env=development
php artisan key:generate --env=development
php artisan migrate --seed --env=development
yarn install --frozen-lockfile
yarn build
php artisan serve --env=development --host=127.0.0.1 --port=8000
```

Hoặc khởi động nhanh
Trên macOS/Linux:

```bash
./start-app
```

Trên Windows:

```bat
start-app.bat
```

Mở `http://127.0.0.1:8000/admin` để quản trị hoặc `http://127.0.0.1:8000/check` để tra cứu IMEI. QR dùng URL `/check/{qr_token}` trên cùng ứng dụng.

Khi chạy một lệnh Artisan thủ công ở local, thêm `--env=development` để Laravel
nạp `.env.development`, ví dụ `php artisan migrate --env=development`.
File `.env.production` chỉ dùng làm bản tham chiếu; khi deploy Render, khai báo các
giá trị tương ứng trong mục Environment Variables của service.

`.env.development` không được commit. Khi chuyển dự án sang máy mới, cần nhận
file này từ người quản trị dự án trước khi chạy `composer setup`.
