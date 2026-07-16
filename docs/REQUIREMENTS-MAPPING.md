# Đối chiếu yêu cầu và phần triển khai

| Yêu cầu | Phần triển khai |
|---|---|
| Thêm/sửa/xóa sản phẩm | `App\Livewire\Products\Index`, model `Product`, xóa mềm |
| Mã hàng, tên hàng, IMEI, ngày nhập | Cột tương ứng trong migration `products` và form Livewire |
| Thời hạn, trạng thái, ghi chú nội bộ | `warranty_months`, enum `WarrantyStatus`, `internal_note` |
| Mỗi IMEI một QR | Unique index `imei`, UUID `qr_token`, `QrCodeService` |
| QR chứa link tra cứu | `Product::publicLookupUrl()` và route `/bao-hanh/{qr_token}` |
| Tạo QR hàng loạt | Mọi sản phẩm/import đều tự có token; PDF hàng loạt render QR cho danh sách đã chọn |
| Không trùng IMEI | Validation ứng dụng, phát hiện trùng trong file và unique index database |
| Tem nhỏ chỉ có QR | `pdf/single-label.blade.php`, khổ 40 × 40 mm |
| In hàng loạt, xuất PDF | `LabelPdfController::bulk`, mẫu A4 5 × 7 tem/trang |
| Tra cứu mã/tên/IMEI/ngày nhập | `WarrantyLookupController` và `warranty/show.blade.php` |
| Thời hạn và ngày hết hạn | Tự tính trong event `Product::saving` |
| Bốn trạng thái bảo hành | Enum: active, expired, replaced, locked |
| Import Excel | Laravel Excel, `ProductImportService`, lịch sử `ImportBatch` |
| Các cột import theo tài liệu | Ánh xạ tiêu đề Việt/Anh trong `buildHeaderMap()` |
| Tìm IMEI/mã hàng/tên | Scope `Product::search()` và ô tìm kiếm Livewire |
| Giao diện đơn giản, responsive | Blade + Livewire + Tailwind, bảng cuộn ngang và menu mobile |
| Tối ưu nhân viên kho | Thêm nhanh, tìm kiếm live, chọn theo trang, in tem hàng loạt, file mẫu |
| Chỉ nội bộ và tra cứu QR | Không có ERP, website bán hàng hay API công khai riêng |
| Phân quyền | Breeze-style auth + Spatie Permission, 5 vai trò mặc định |
| VPS Linux | Cấu hình Nginx, PHP-FPM, systemd worker, cron và deploy script |
