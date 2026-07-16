# 24hStore QR Warranty

Hệ thống quản lý sản phẩm và mã QR bảo hành theo IMEI, được xây dựng bằng Laravel, Livewire và Tailwind CSS.

## Chức năng chính

- Quản lý sản phẩm, IMEI và trạng thái bảo hành.
- Tạo mã QR và tra cứu thông tin bảo hành công khai.
- Import danh sách sản phẩm từ Excel hoặc CSV.
- Xuất tem QR đơn lẻ hoặc hàng loạt dưới dạng PDF.
- Quản lý người dùng và phân quyền nội bộ.
- Xem các bảng dữ liệu nghiệp vụ ở chế độ chỉ đọc dành cho `super-admin`.

## Tổ chức mã nguồn

Dự án vẫn là một Laravel monolith, nhưng phần trình bày được tách rõ để dễ tìm và bảo trì:

- **Admin (nội bộ):** controller tại `App\Http\Controllers\Admin`, Livewire tại `App\Livewire\Admin`, view tại `resources/views/admin` và route tại `routes/admin.php`.
- **Frontend (công khai):** controller tại `App\Http\Controllers\Frontend`, view tại `resources/views/frontend` và route công khai tại `routes/web.php`.
- **Dùng chung:** model, enum, policy, service và command nằm trong các thư mục tương ứng dưới `app`; cả Admin và Frontend đều có thể sử dụng phần này.

Việc tách thư mục trên là tách theo trách nhiệm trong cùng ứng dụng, không phải tách thành hai dự án hoặc thêm một API không cần thiết. Xem chi tiết tại [Kiến trúc](docs/ARCHITECTURE.md) và [Cấu trúc dự án](docs/PROJECT-STRUCTURE.md).

## Yêu cầu môi trường

- PHP 8.4 trở lên.
- Composer 2.
- Node.js 22 trở lên và npm.
- SQLite cho môi trường local.

## Cài đặt lần đầu

Mở Terminal tại thư mục dự án. Nếu chưa có file `.env`, tạo từ file mẫu:

```bash
cp .env.example .env
```

Tạo database SQLite:

```bash
touch database/database.sqlite
```

Cập nhật các dòng sau trong `.env`:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
DB_DATABASE=/duong-dan-den-du-an/database/database.sqlite
```

Thay `/duong-dan-den-du-an` bằng đường dẫn tuyệt đối tới thư mục dự án trên máy.

## Cài dependencies và khởi tạo dữ liệu

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

## Chạy ứng dụng

```bash
composer run dev
```

Các địa chỉ chính:

```text
Điểm vào ứng dụng:        http://127.0.0.1:8000
Trang quản trị:           http://127.0.0.1:8000/admin
Quản lý dữ liệu chỉ đọc:  http://127.0.0.1:8000/admin/data
Tra cứu từ mã QR:         http://127.0.0.1:8000/bao-hanh/{qr_token}
```

Tài khoản quản trị mặc định:

```text
Email: admin@24hstore.local
Mật khẩu: ChangeMeNow!2026
```

Nên đổi mật khẩu mặc định sau lần đăng nhập đầu tiên. Trang `/admin/data` chỉ dành cho `super-admin`; mật khẩu, token đăng nhập và các bảng hạ tầng như cache, session, queue không được hiển thị.

## Kiểm thử

Chạy toàn bộ test:

```bash
php artisan test
```

Test được chia theo phạm vi `Admin`, `Frontend`, `Application` và `Domain` trong thư mục `tests`. Để dừng ứng dụng đang chạy, quay lại Terminal và nhấn `Ctrl + C`.
