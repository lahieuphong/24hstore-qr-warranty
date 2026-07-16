# Cấu trúc dự án

Tài liệu này quy định vị trí đặt mã nguồn để tính năng mới không làm Admin, Frontend và nghiệp vụ dùng chung bị trộn lẫn.

## Cây thư mục chính

```text
app/
├── Console/Commands/                 # Tác vụ CLI và scheduler
├── Enums/                            # Kiểu và quy tắc giá trị dùng chung
├── Http/Controllers/
│   ├── Admin/
│   │   ├── Auth/                     # Đăng nhập/đăng xuất quản trị
│   │   ├── LabelPdfController.php
│   │   ├── ProductsTemplateController.php
│   │   └── QrCodeController.php
│   └── Frontend/
│       ├── HomeController.php
│       └── WarrantyLookupController.php
├── Livewire/Admin/
│   ├── Dashboard.php
│   ├── Data/Index.php
│   ├── Imports/Index.php
│   ├── Products/Index.php
│   ├── Users/Index.php
│   └── Profile.php
├── Models/                           # Eloquent model và quy tắc vòng đời dữ liệu
├── Policies/                         # Authorization theo resource
└── Services/                         # Use case/nghiệp vụ dùng lại

resources/views/
├── admin/
│   ├── auth/                         # Màn hình đăng nhập nội bộ
│   ├── layouts/app.blade.php         # Layout quản trị
│   ├── livewire/                     # View của App\Livewire\Admin
│   └── pdf/                          # Mẫu tem PDF nội bộ
├── frontend/
│   └── warranty/show.blade.php       # Kết quả quét QR công khai
└── components/                       # Blade component thực sự dùng chung

routes/
├── web.php                           # Route công khai và điểm nạp admin.php
├── admin.php                         # Route đăng nhập và toàn bộ /admin/*
└── console.php                       # Lịch chạy/command

tests/
├── Feature/
│   ├── Admin/                        # Access control và Livewire nội bộ
│   ├── Frontend/                     # Trang công khai
│   ├── Application/                  # Service/use case
│   └── Domain/                       # Model và database constraint
└── Unit/Domain/                      # Quy tắc thuần
```

## Backend dùng chung

Phần dùng chung gồm `Models`, `Enums`, `Policies`, `Services` và `Console/Commands`. Đây là nơi đặt quy tắc mà nhiều entry point có thể sử dụng.

- Quy tắc luôn đúng với một entity, như chuẩn hóa IMEI hoặc tính ngày hết hạn, đặt trong model/enum hoặc một domain object phù hợp.
- Một quy trình có nhiều bước hoặc tích hợp thư viện, như import file hay sinh QR, đặt trong service.
- Kiểm tra một người có được thao tác trên resource hay không đặt trong policy/permission.
- Tác vụ chạy theo lịch hoặc CLI đặt trong command, nhưng command gọi lại service/model thay vì sao chép nghiệp vụ.

Phần dùng chung không được import class trong `Http\Controllers\Admin`, `Http\Controllers\Frontend` hoặc `Livewire\Admin`, và không được biết tên Blade view.

## Module Admin

Admin phục vụ nhân viên nội bộ và luôn đi qua authentication, kiểm tra tài khoản hoạt động, permission hoặc role.

- Controller Admin dùng cho response dạng request/response rõ ràng: đăng nhập, QR PNG, tải file Excel và PDF.
- Livewire Admin dùng cho màn hình tương tác: dashboard, CRUD sản phẩm, import, người dùng, hồ sơ và xem dữ liệu.
- Mọi Blade riêng của Admin đặt dưới `resources/views/admin` và dùng tên `admin.*`.
- Mọi route nội bộ đặt trong `routes/admin.php`, dùng prefix/tên `admin.*` khi phù hợp.
- `/admin/data` chỉ đọc, whitelist bảng/resource và chỉ dành cho `super-admin`; không thêm trình chạy SQL tự do.

Khi thêm tính năng nội bộ, ví dụ “phiếu tiếp nhận bảo hành”, vị trí mặc định là:

```text
app/Livewire/Admin/WarrantyReceipts/Index.php
resources/views/admin/livewire/warranty-receipts/index.blade.php
tests/Feature/Admin/WarrantyReceiptsTest.php
```

Nếu nghiệp vụ của tính năng được dùng ở nơi khác, tách phần đó thành service/model dùng chung.

## Module Frontend

Frontend chỉ chứa trải nghiệm công khai. Hiện tại đó là trang chủ và kết quả tra cứu bảo hành qua QR.

- Controller đặt trong `App\Http\Controllers\Frontend`.
- Blade đặt trong `resources/views/frontend` và dùng tên `frontend.*`.
- Route đặt trực tiếp trong `routes/web.php`.
- Không render ghi chú nội bộ, người tạo/cập nhật, dữ liệu tài khoản hoặc trường nhạy cảm.
- Route công khai có nguy cơ bị quét phải áp dụng throttle phù hợp.

Frontend được phép gọi model/service dùng chung, nhưng không gọi controller, Livewire hoặc view của Admin.

## Quy tắc cho controller và Livewire

Controller/Livewire thuộc lớp trình bày, nên giữ mỏng:

1. Nhận input và route binding.
2. Kiểm tra quyền và validation.
3. Gọi model/service để thực hiện nghiệp vụ.
4. Chuyển kết quả thành HTML, file hoặc redirect.

Không đặt truy vấn hoặc xử lý giống nhau ở nhiều controller. Khi logic bắt đầu được dùng lại, có nhiều nhánh nghiệp vụ hoặc cần test độc lập, chuyển nó vào scope/model/service phù hợp.

## Quy tắc đặt view

- View chỉ dành cho Admin: `admin.*`.
- View chỉ dành cho trang công khai: `frontend.*`.
- Component thật sự dùng ở cả hai vùng: `components.*`.
- Không đưa toàn bộ view vào `components` chỉ để rút ngắn đường dẫn.
- PDF do Admin xuất vẫn thuộc `admin.pdf.*`, dù người dùng có thể in và dán tem ra bên ngoài.

## Quy tắc đặt test

Namespace của test phải khớp thư mục:

| Thư mục | Namespace |
|---|---|
| `tests/Feature/Admin` | `Tests\Feature\Admin` |
| `tests/Feature/Frontend` | `Tests\Feature\Frontend` |
| `tests/Feature/Application` | `Tests\Feature\Application` |
| `tests/Feature/Domain` | `Tests\Feature\Domain` |
| `tests/Unit/Domain` | `Tests\Unit\Domain` |

Feature test được phép dùng Laravel, database và HTTP/Livewire. Unit test không nên boot Laravel hoặc truy cập database. Tên test mô tả hành vi quan sát được, không khóa chặt vào chi tiết triển khai khi không cần thiết.

## Checklist khi thêm tính năng

1. Xác định tính năng là Admin, Frontend hay nghiệp vụ dùng chung.
2. Đặt route đúng file và middleware ở sát route.
3. Đặt controller/Livewire/view đúng namespace tương ứng.
4. Di chuyển logic dùng lại vào model/service, không import chéo Admin ↔ Frontend.
5. Thêm test vào đúng nhóm.
6. Cập nhật `docs/REQUIREMENTS-MAPPING.md` nếu tính năng đến từ yêu cầu nghiệp vụ.
