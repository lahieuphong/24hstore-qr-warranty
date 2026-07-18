# 24hStore QR Warranty - Frontend

Ứng dụng Laravel + Livewire chỉ phục vụ trang tra cứu công khai. Frontend **không có migration, model Eloquent hoặc database connection**; toàn bộ dữ liệu được đọc từ backend qua `BACKEND_API_URL`.

## Chạy local

```bash
cp .env.example .env
composer install
php artisan key:generate
yarn install --frozen-lockfile
yarn build
php artisan serve --port=8001
```

Backend phải chạy ở URL đã cấu hình, mặc định `http://localhost:8000/api/v1`.

## Production

Sao chép `.env.production.example` thành `.env`, đặt `APP_URL`, `BACKEND_API_URL`, `BACKEND_ADMIN_URL`, chạy build/cache và trỏ Nginx document root vào `public/`.
