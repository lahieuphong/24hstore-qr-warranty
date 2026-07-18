# 24hStore QR Warranty - Backend + Frontend

Bản source này đã được tách thành hai ứng dụng deploy độc lập:

- `backend/`: Laravel 12 + Livewire 4, kết nối MySQL/PostgreSQL, cung cấp REST API và trang quản trị custom tại `/admin`.
- `frontend/`: Laravel 12 + Livewire 4, chỉ hiển thị trang tra cứu QR/IMEI và gọi backend qua API; **không có database riêng**.

## 1. Kiến trúc chạy thật

```text
Nhân viên kho
    -> https://admin-warranty.example.com/admin
    -> Backend Livewire
    -> MySQL/PostgreSQL

Khách quét QR
    -> https://warranty.example.com/bao-hanh/{uuid}
    -> Frontend Livewire
    -> Backend API /api/v1/warranties/{uuid}
    -> MySQL/PostgreSQL
```

QR do backend tạo sẽ dùng `FRONTEND_URL`, vì vậy không phụ thuộc đường dẫn máy cá nhân và không chứa IMEI trong URL.

## 2. Trang quản trị custom kiểu Django Admin

Backend có:

- Trang Administration chia theo module: Bảo hành & kho, Xác thực & phân quyền, Vận hành hệ thống.
- Liên kết “Xem / sửa” và “Thêm mới” theo từng resource.
- Dashboard số lượng theo bốn trạng thái bảo hành.
- Recent actions và trang nhật ký hoạt động quản trị.
- CRUD sản phẩm, tìm kiếm, lọc, sắp xếp, chọn hàng loạt, QR/PDF.
- Import Excel/CSV và báo lỗi theo dòng.
- Quản lý tài khoản, vai trò và khóa/mở tài khoản.
- Kiểm tra trạng thái database, queue/cache và quyền ghi storage.

Chi tiết: [`docs/CUSTOM-ADMIN.md`](docs/CUSTOM-ADMIN.md).

## 3. Database production

Backend mặc định dùng MySQL:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qr_warranty
DB_USERNAME=qr_warranty
DB_PASSWORD=...
```

Có thể chuyển sang PostgreSQL bằng `DB_CONNECTION=pgsql` và đổi port/credential tương ứng. Không dùng đường dẫn database tuyệt đối của máy cá nhân. SQLite chỉ được cấu hình `:memory:` trong test tự động của backend, không tạo file database và không dùng khi deploy.

Frontend không có migration/model Eloquent, session và cache dùng file, queue dùng sync.

## 4. Chạy local

### 4.1 Backend

```bash
cd backend
cp .env.example .env
# sửa DB_* và FRONTEND_URL=http://localhost:8001
composer install
php artisan key:generate
php artisan migrate --seed
yarn install --frozen-lockfile
yarn build
php artisan serve --port=8000
```

Trang quản trị: `http://localhost:8000/admin`.

### 4.2 Frontend

Mở terminal khác:

```bash
cd frontend
cp .env.example .env
# BACKEND_API_URL=http://localhost:8000/api/v1
composer install
php artisan key:generate
yarn install --frozen-lockfile
yarn build
php artisan serve --port=8001
```

Trang tra cứu: `http://localhost:8001`.

## 5. API public

```text
GET /api/v1/health
GET /api/v1/warranties/{qr_token}
GET /api/v1/warranties/search?imei={imei}
```

API chỉ trả các trường công khai; `internal_note`, tài khoản tạo/cập nhật và dữ liệu phân quyền không xuất hiện trong response.

## 6. Deploy server

Khuyến nghị hai subdomain:

- `admin-warranty.example.com` -> `backend/public`
- `warranty.example.com` -> `frontend/public`

Hướng dẫn chi tiết và Nginx mẫu: [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

## 7. Kiểm thử và kiểm tra source

```bash
cd backend && php artisan test
cd ../frontend && php artisan test
```

Kiểm tra format:

```bash
cd backend && ./vendor/bin/pint --test
cd ../frontend && ./vendor/bin/pint --test
```

Bản đóng gói không kèm `vendor/`, `node_modules/` hoặc file `.env`; server sẽ cài dependency qua Composer và Yarn từ `composer.json`, `package.json` và `yarn.lock`.
