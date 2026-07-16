# Đối chiếu yêu cầu và phần triển khai

| Yêu cầu | Phần triển khai |
|---|---|
| Thêm/sửa/xóa sản phẩm | `App\Livewire\Admin\Products\Index`, model `Product`, xóa mềm |
| Mã hàng, tên hàng, IMEI, ngày nhập | Cột tương ứng trong migration `products` và form Admin Livewire |
| Thời hạn, trạng thái, ghi chú nội bộ | `warranty_months`, enum `WarrantyStatus`, `internal_note` |
| Mỗi IMEI một QR | Unique index `imei`, UUID `qr_token`, `QrCodeService` |
| QR chứa link tra cứu | `Product::publicLookupUrl()` và route Frontend `/bao-hanh/{qr_token}` |
| Tạo QR hàng loạt | Mọi sản phẩm/import đều tự có token; PDF hàng loạt render QR cho danh sách đã chọn |
| Không trùng IMEI | Validation ứng dụng, phát hiện trùng trong file và unique index database |
| Tem nhỏ chỉ có QR | View `admin.pdf.single-label`, khổ 40 × 40 mm |
| In hàng loạt, xuất PDF | `App\Http\Controllers\Admin\LabelPdfController::bulk`, view `admin.pdf.labels-a4` 5 × 7 tem/trang |
| Tra cứu mã/tên/IMEI/ngày nhập | `App\Http\Controllers\Frontend\WarrantyLookupController` và view `frontend.warranty.show` |
| Thời hạn và ngày hết hạn | Tự tính trong event `Product::saving` |
| Bốn trạng thái bảo hành | Enum: `active`, `expired`, `replaced`, `locked` |
| Import Excel | `App\Livewire\Admin\Imports\Index`, `ProductImportService`, lịch sử `ImportBatch` |
| Các cột import theo tài liệu | Ánh xạ tiêu đề Việt/Anh trong `ProductImportService::buildHeaderMap()` |
| Tìm IMEI/mã hàng/tên | Scope `Product::search()` và ô tìm kiếm tại `App\Livewire\Admin\Products\Index` |
| Giao diện đơn giản, responsive | Blade + Livewire + Tailwind; view tách thành `admin.*` và `frontend.*` |
| Tối ưu nhân viên kho | Thêm nhanh, tìm kiếm live, chọn theo trang, in tem hàng loạt và file mẫu |
| Chỉ nội bộ và tra cứu QR | Admin nằm trong `routes/admin.php`; route công khai nằm trong `routes/web.php`; không có ERP/website bán hàng/API công khai riêng |
| Phân quyền | Auth controller `App\Http\Controllers\Admin\Auth\AuthenticatedSessionController`, Spatie Permission và 5 vai trò mặc định |
| Xem dữ liệu kiểu Django Admin | `/admin/data`, `App\Livewire\Admin\Data\Index`; chỉ đọc, whitelist resource, che trường nhạy cảm và chỉ dành cho `super-admin` |
| Dễ bảo trì Admin/Frontend | Namespace controller, Livewire, view, route và test được tách theo ranh giới trong `docs/PROJECT-STRUCTURE.md` |
| VPS Linux | Cấu hình Nginx, PHP-FPM, systemd worker, cron và deploy script |
