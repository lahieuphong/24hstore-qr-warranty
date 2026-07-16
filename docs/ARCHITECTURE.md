# Kiến trúc hệ thống

## Phạm vi và quyết định monolith

Hệ thống gồm một khu vực quản trị nội bộ và một trang tra cứu QR công khai. Không có ERP, website bán hàng, ứng dụng di động hay frontend SPA độc lập. Vì vậy dự án giữ kiến trúc Laravel monolith để dùng chung validation, phân quyền, model và database, đồng thời triển khai đơn giản trên một VPS.

Monolith không đồng nghĩa với trộn tất cả mã nguồn. Phần trình bày được chia thành hai vùng rõ ràng, còn nghiệp vụ và dữ liệu được đặt ở lớp dùng chung.

## Ranh giới Admin và Frontend

| Vùng | Trách nhiệm | Mã nguồn chính | Quyền truy cập |
|---|---|---|---|
| Admin | Dashboard, sản phẩm, import, tem QR/PDF, người dùng, hồ sơ và trình xem dữ liệu | `App\Http\Controllers\Admin`, `App\Livewire\Admin`, `resources/views/admin`, `routes/admin.php` | Đăng nhập, tài khoản hoạt động và permission/role phù hợp |
| Frontend | Trang chủ và trang tra cứu bảo hành qua QR | `App\Http\Controllers\Frontend`, `resources/views/frontend`, route công khai trong `routes/web.php` | Công khai, có giới hạn tần suất ở route tra cứu |
| Shared | Quy tắc nghiệp vụ, truy cập dữ liệu và dịch vụ tái sử dụng | `App\Models`, `App\Enums`, `App\Policies`, `App\Services`, `App\Console\Commands` | Không phụ thuộc giao diện |

Trong ngữ cảnh dự án này, “Frontend” là giao diện công khai do Laravel render. “Admin” là giao diện vận hành nội bộ và có cả controller lẫn Livewire. Phần backend dùng chung là model/service/enum/policy; không cần tạo hai project FE/BE riêng hoặc một API trung gian khi chưa có nhu cầu thực tế.

## Các lớp chính

### Presentation

- `routes/web.php` chứa entry point công khai và nạp nhóm route quản trị.
- `routes/admin.php` chứa toàn bộ URL, middleware và tên route của khu vực quản trị.
- Controller Frontend trả về trang công khai.
- Controller Admin xử lý đăng nhập, QR, file mẫu và PDF.
- Livewire Admin xử lý các màn hình quản trị có trạng thái tương tác.
- Blade được tách thành namespace view `admin.*` và `frontend.*`; component thực sự dùng chung có thể nằm ở `resources/views/components`.

### Application

- `ProductImportService`: đọc sheet, ánh xạ tiêu đề, chuẩn hóa, validation và lưu từng dòng.
- `QrCodeService`: sinh PNG hoặc data URI cho controller và PDF.
- `SyncWarrantyStatuses`: đồng bộ trạng thái hết hạn theo lịch chạy.

Controller và Livewire chỉ nên nhận input, kiểm tra quyền/validation, gọi nghiệp vụ rồi tạo response. Nghiệp vụ dùng lại hoặc có nhiều bước phải được đưa vào service/model thay vì sao chép giữa Admin và Frontend.

### Domain và Data

- `Product`: chuẩn hóa IMEI/mã hàng, tự sinh UUID, tự tính ngày hết hạn và trạng thái hiệu lực.
- `WarrantyStatus`: enum bốn trạng thái bảo hành.
- `ImportBatch`: lịch sử và lỗi import.
- `User`, role, permission và `ProductPolicy`: tài khoản và phân quyền.

Model, enum và service dùng chung không được phụ thuộc ngược vào controller, Livewire hoặc Blade của Admin/Frontend. Admin và Frontend cũng không gọi controller hoặc view của nhau.

### Infrastructure

- Laravel Eloquent và migration quản lý database.
- Laravel Excel đọc Excel/CSV.
- Endroid QR Code sinh mã QR.
- Dompdf render tem PDF.
- Queue, storage và scheduler dùng hạ tầng chuẩn của Laravel.

## Luồng route

```text
routes/web.php
├── /                         → Frontend\HomeController
├── /bao-hanh/{qr_token}      → Frontend\WarrantyLookupController
└── nạp routes/admin.php
    ├── /login, /logout       → Admin\Auth\AuthenticatedSessionController
    └── /admin/*              → Admin controllers hoặc Admin Livewire
```

Tên route hiện hữu như `warranty.show` và `admin.products.index` được giữ ổn định để việc tách thư mục không làm thay đổi URL hoặc liên kết trong ứng dụng.

## Luồng tạo sản phẩm

```text
Admin Livewire form / Excel row
    → validation
    → ProductImportService (đối với import)
    → normalize IMEI + product code
    → kiểm tra unique ở ứng dụng
    → Product::saving tính warranty_expires_at
    → Product::creating sinh UUID qr_token
    → unique index ở database
```

Hai lớp kiểm tra unique giúp trả lỗi dễ hiểu và vẫn tránh trùng khi có thao tác đồng thời.

## Luồng quét QR

```text
Tem QR
    → /bao-hanh/{uuid}
    → implicit route binding theo qr_token
    → Product::effectiveWarrantyStatus()
    → view frontend.warranty.show chỉ đọc
```

URL không dùng IMEI nên khó đoán tuần tự hơn. Ghi chú nội bộ, người tạo và thông tin tài khoản không xuất hiện trên trang công khai.

## Luồng xuất PDF

- `Admin\LabelPdfController` điều phối xuất tem.
- View `admin.pdf.single-label` tạo tem đơn 40 × 40 mm, QR 34 mm.
- View `admin.pdf.labels-a4` tạo trang A4 5 cột × 7 hàng, tối đa 35 tem mỗi trang.
- Một lượt xuất hàng loạt giới hạn 500 sản phẩm để kiểm soát bộ nhớ và kích thước URL.
- QR được nhúng dưới dạng data URI, nên PDF không phụ thuộc truy cập mạng khi render.

## Trang quản lý dữ liệu

`/admin/data` dùng `App\Livewire\Admin\Data\Index` và view `admin.livewire.data.index`. Trang chỉ cho `super-admin`, chỉ đọc các resource nằm trong whitelist, che trường nhạy cảm và không cho chạy SQL tự do. Đây là công cụ quan sát dữ liệu nghiệp vụ, không thay thế migration, model hay một database client.

## Database

### `products`

- `product_code`, `name`, `imei`
- `warehouse_date`, `warranty_months`, `warranty_expires_at`
- `warranty_status`, `internal_note`
- `qr_token`
- `created_by`, `updated_by`
- timestamps và soft delete

Index quan trọng:

- unique `imei`
- unique `qr_token`
- index mã hàng, tên, ngày nhập, ngày hết hạn và trạng thái

### `import_batches`

Lưu tên file, người import, tổng số dòng, số thành công, số lỗi, JSON lỗi theo dòng và thời điểm hoàn tất.

### Phân quyền

Dùng schema chuẩn của Spatie Permission: roles, permissions và ba bảng pivot. Route Admin áp dụng `auth`, middleware kiểm tra tài khoản hoạt động và permission/role ở sát entry point.

## Trạng thái hiệu lực

`effectiveWarrantyStatus()` chỉ chuyển cách hiển thị từ `active` sang `expired` khi ngày hết hạn nhỏ hơn ngày hiện tại. `replaced` và `locked` luôn được giữ nguyên. Command ban đêm đồng bộ trạng thái database để báo cáo và truy vấn nhất quán.

## Chiến lược kiểm thử

- `tests/Feature/Admin`: route, phân quyền và Livewire khu vực nội bộ.
- `tests/Feature/Frontend`: hành vi người dùng công khai.
- `tests/Feature/Application`: service và use case có database/hạ tầng.
- `tests/Feature/Domain`: vòng đời model và ràng buộc dữ liệu.
- `tests/Unit/Domain`: quy tắc thuần không cần Laravel/database.

Cấu trúc test phản ánh trách nhiệm của mã nguồn; không bắt buộc code domain phải đổi namespace chỉ để khớp tên thư mục test.

## Khả năng mở rộng

Có thể bổ sung trong cùng monolith:

- lịch sử thay đổi sản phẩm/audit log;
- phiếu tiếp nhận bảo hành;
- nhiều kho;
- nhiều mẫu tem;
- job queue cho file import rất lớn;
- API nội bộ có token;
- object storage cho tài liệu bảo hành.

Chỉ nên tách dịch vụ hoặc frontend độc lập khi có tải lớn, đội phát triển riêng hoặc tích hợp ngoài cần vòng đời phát hành riêng.
