# 24hStore QR Warranty

Ứng dụng đang chạy chính nằm trong `backend/`:

- Quản trị tại `/admin`.
- Tra cứu IMEI công khai tại `/check` và mở QR tại `/check/{qr_token}`.
- REST API, database, import, QR/PDF và nhật ký hoạt động.

Thư mục `frontend/` là mã giao diện tách riêng trước đây, chỉ giữ lại để tham khảo; không cần chạy khi phát triển local.

## 1. Kiến trúc

```text
Nhân viên -> Backend /admin -> MySQL/PostgreSQL
Khách hàng -> Backend /check -> MySQL/PostgreSQL
```

QR luôn trỏ về đường dẫn chuẩn `/check/{qr_token}` trên cùng backend.

## 2. Chức năng chính

- Quản lý sản phẩm, IMEI, thời hạn bảo hành và trạng thái.
- Tạo QR/PDF, import Excel/CSV và tra cứu bảo hành.
- Quản lý tài khoản, phân quyền và nhật ký hoạt động.

Chi tiết trang quản trị: [`docs/CUSTOM-ADMIN.md`](docs/CUSTOM-ADMIN.md).

## 3. Cài đặt lần đầu

MySQL/PostgreSQL phải được khởi động và thông tin `DB_*` phải khớp với database đã tạo.

Backend:

```bash
cd backend
# cập nhật file .env: APP_URL=http://127.0.0.1:8000 và cấu hình DB_*
composer install
php artisan key:generate
php artisan migrate --seed
yarn install --frozen-lockfile
yarn build
cd ..
```

## 4. Chạy ứng dụng

Chỉ cần mở một terminal tại thư mục gốc và chạy backend.

macOS/Linux:

```bash
./start-backend
```

Windows:

```bat
start-backend.bat
```

- Admin: `http://127.0.0.1:8000/admin`
- Tra cứu IMEI: `http://127.0.0.1:8000/check`
- Dừng server: nhấn `Ctrl+C` trong terminal.

## 5. API public

```text
GET /api/v1/health
GET /api/v1/warranties/{qr_token}
GET /api/v1/warranties/search?imei={imei}
```

## 6. Kiểm thử

```bash
(cd backend && php artisan test && ./vendor/bin/pint --test)
```

## 7. Deploy

- Admin và trang tra cứu công khai: `backend/public`
- Hướng dẫn chi tiết: [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md)

Không commit `vendor/`, `node_modules/` hoặc file `.env`.
