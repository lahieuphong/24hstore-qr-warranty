# 24hStore QR Warranty - Backend + Frontend

Hệ thống gồm hai ứng dụng độc lập:

- `backend/`: Laravel + Livewire, quản trị tại `/admin`, REST API và database.
- `frontend/`: trang tra cứu bảo hành công khai, chỉ lấy dữ liệu qua backend API.

## 1. Kiến trúc

```text
Nhân viên -> Backend /admin -> MySQL/PostgreSQL
Khách hàng -> Frontend -> Backend /api/v1 -> MySQL/PostgreSQL
```

QR luôn trỏ về frontend qua cấu hình `FRONTEND_URL`.

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
cp .env.example .env
# cấu hình DB_* và FRONTEND_URL=http://localhost:8001
composer install
php artisan key:generate
php artisan migrate --seed
yarn install --frozen-lockfile
yarn build
cd ..
```

Frontend:

```bash
cd frontend
cp .env.example .env
# cấu hình BACKEND_API_URL=http://localhost:8000/api/v1
composer install
php artisan key:generate
yarn install --frozen-lockfile
yarn build
cd ..
```

## 4. Chạy ứng dụng

Mở hai terminal tại thư mục gốc, chạy backend trước và frontend sau.

macOS/Linux:

```bash
./start-backend
./start-frontend
```

Windows:

```bat
start-backend.bat
start-frontend.bat
```

- Admin: `http://localhost:8000/admin`
- Frontend: `http://localhost:8001`
- Dừng server: nhấn `Ctrl+C` trong terminal tương ứng.

## 5. API public

```text
GET /api/v1/health
GET /api/v1/warranties/{qr_token}
GET /api/v1/warranties/search?imei={imei}
```

## 6. Kiểm thử

```bash
(cd backend && php artisan test && ./vendor/bin/pint --test)
(cd frontend && php artisan test && ./vendor/bin/pint --test)
```

## 7. Deploy

- Backend/admin: `backend/public`
- Frontend công khai: `frontend/public`
- Hướng dẫn chi tiết: [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md)

Không commit `vendor/`, `node_modules/` hoặc file `.env`.
