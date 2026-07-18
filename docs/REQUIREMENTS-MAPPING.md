# Đối chiếu yêu cầu khách hàng

| Yêu cầu | Vị trí triển khai |
|---|---|
| Thêm/sửa/xóa sản phẩm | `backend/app/Livewire/Products/Index.php` |
| Mã hàng, tên, IMEI, ngày nhập, thời hạn, trạng thái, ghi chú | Product model, migration và form Livewire |
| Một QR cho mỗi IMEI | `qr_token` unique, tự sinh UUID khi tạo Product |
| Không trùng IMEI | Validation ứng dụng + unique index database, kể cả sau soft delete |
| Tạo/in QR hàng loạt | Chọn nhiều sản phẩm và xuất `labels.pdf` |
| Tem nhỏ và PDF | `LabelPdfController`, view `resources/views/pdf` |
| Tra cứu khi quét QR | Frontend `/bao-hanh/{uuid}` gọi backend API |
| Bốn trạng thái bảo hành | `WarrantyStatus` enum |
| Import Excel/CSV | Livewire Imports + `ProductImportService` |
| Tìm IMEI/mã/tên | Scope `Product::search()` và ô tìm kiếm quản trị |
| Responsive/mobile | Tailwind CSS ở backend và frontend |
| Trang custom giống Django Admin | Module index, change lists, add/change forms, recent actions và audit log |
| Tách Backend/Frontend | Hai Laravel app trong `backend/` và `frontend/` |
| Deploy server | MySQL/PostgreSQL, `.env.production.example`, Nginx/systemd/cron/deploy scripts |
| Không dùng SQLite file local | Production backend mặc định MySQL; test chỉ dùng SQLite `:memory:`; frontend không có database |
