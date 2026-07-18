# Kiến trúc Backend / Frontend

## Mục tiêu tách ứng dụng

Livewire vẫn được giữ ở cả hai phía để giao diện thống nhất, nhưng trách nhiệm dữ liệu được tách rõ:

- Backend sở hữu database, model, migration, phân quyền, import, QR/PDF, audit log và API.
- Frontend không sở hữu dữ liệu; chỉ gọi API và render trang tra cứu công khai.

Cách này tránh việc đưa credential database ra frontend và cho phép thay giao diện public mà không ảnh hưởng trang quản trị.

## Backend

Các lớp chính:

1. `app/Models`: Product, User, ImportBatch, AdminActivityLog.
2. `app/Livewire`: dashboard, sản phẩm, import, users, activity log, profile.
3. `app/Services`: import Excel, QR, audit logger.
4. `app/Http/Controllers/Api/V1`: health, lookup theo token, lookup theo IMEI.
5. `database/migrations`: schema dùng được với MySQL/PostgreSQL.

## Frontend

Các lớp chính:

1. `WarrantyApiClient`: kết nối backend, timeout/retry, phân biệt 404 và 503.
2. `Home`: tra cứu thủ công theo IMEI.
3. `WarrantyLookup`: trang đích khi quét QR.
4. Blade components: hiển thị dữ liệu bảo hành và trạng thái.

Frontend dùng session/cache file và queue sync; không có migration và không gọi `DB`.

## URL QR

`Product::publicLookupUrl()` ghép từ:

```text
FRONTEND_URL + /bao-hanh/{qr_token}
```

Thay domain chỉ cần sửa `FRONTEND_URL`, chạy `php artisan config:cache` và tạo/in lại tem cần dùng domain mới.

## Bảo mật dữ liệu

- API lookup dùng UUID ngẫu nhiên thay vì ID tuần tự.
- Endpoint tìm IMEI được rate limit thấp hơn endpoint quét QR.
- Backend chỉ serialize trường công khai qua `WarrantyProductResource`.
- Admin dùng session auth, role/permission và middleware khóa tài khoản.
- Frontend không giữ DB credential.