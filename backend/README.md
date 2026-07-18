# 24hStore QR Warranty - Backend

Laravel 12 + Livewire 4 backend gồm:

- Custom Administration tại `/admin`.
- MySQL/PostgreSQL là database production.
- REST API v1 cho frontend tra cứu.
- CRUD sản phẩm, QR/PDF, import Excel, tài khoản/phân quyền và audit log.

Xem tài liệu tổng tại thư mục cha: `../README.md` và `../docs/DEPLOYMENT.md`.

## Local

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
yarn install --frozen-lockfile
yarn build
php artisan serve --port=8000
```

Cấu hình `FRONTEND_URL=http://localhost:8001` để QR trỏ đúng frontend.
