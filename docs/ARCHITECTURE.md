# Kiến trúc ứng dụng

## Ứng dụng đang vận hành

`backend/` là ứng dụng Laravel + Livewire duy nhất cần chạy. Ứng dụng sở hữu database, model, migration, phân quyền, import, QR/PDF, audit log, API và giao diện tra cứu công khai.

- Quản trị viên dùng `/admin`.
- Khách hàng nhập IMEI tại `/check`.
- QR mở trực tiếp `/check/{qr_token}`.

Thư mục `frontend/` là phiên bản tách riêng cũ và chỉ được giữ làm tài liệu tham khảo; không tham gia luồng chạy local hiện tại.

## Backend

Các lớp chính:

1. `app/Models`: Product, User, ImportBatch, AdminActivityLog.
2. `app/Livewire`: dashboard, sản phẩm, import, users, activity log, profile và tra cứu công khai.
3. `app/Services`: import Excel, QR, audit logger.
4. `app/Http/Controllers/Api/V1`: health, lookup theo token, lookup theo IMEI.
5. `database/migrations`: schema dùng được với MySQL/PostgreSQL.

## URL QR

`Product::publicLookupUrl()` tạo URL chuẩn trên chính ứng dụng:

```text
APP_URL + /check/{qr_token}
```

Khi đổi domain, cập nhật `APP_URL`, chạy `php artisan config:cache` và tạo/in lại các tem cần dùng domain mới.

## Bảo mật dữ liệu

- API lookup dùng UUID ngẫu nhiên thay vì ID tuần tự.
- Endpoint tìm IMEI được rate limit thấp hơn endpoint quét QR.
- Backend chỉ serialize trường công khai qua `WarrantyProductResource`.
- Admin dùng session auth, role/permission và middleware khóa tài khoản.
- Trang `/check` chỉ hiển thị các trường công khai cần thiết.
