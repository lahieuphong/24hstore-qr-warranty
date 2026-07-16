# 24hStore QR Warranty

Hệ thống quản lý sản phẩm và mã QR bảo hành theo IMEI, được xây dựng bằng Laravel, Livewire và Tailwind CSS.

## Chức năng chính

- Quản lý sản phẩm, IMEI và trạng thái bảo hành.
- Tạo mã QR và tra cứu thông tin bảo hành.
- Import danh sách sản phẩm từ Excel hoặc CSV.
- Xuất tem QR đơn lẻ hoặc hàng loạt dưới dạng PDF.
- Quản lý người dùng và phân quyền nội bộ.
- Xem các bảng dữ liệu nghiệp vụ trong trang quản trị dành cho super-admin.

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

Sau đó truy cập:

```text
http://127.0.0.1:8000
```

## Đăng nhập quản trị

```text
Email: admin@24hstore.local
Mật khẩu: ChangeMeNow!2026
```

Nên đổi mật khẩu mặc định sau lần đăng nhập đầu tiên.

## Xem dữ liệu hệ thống

Tài khoản có vai trò `super-admin` có thể truy cập:

```text
http://127.0.0.1:8000/admin/data
```

Trang này hỗ trợ xem, tìm kiếm, sắp xếp và phân trang cho các bảng nghiệp vụ. Đây là chế độ chỉ đọc; mật khẩu, token đăng nhập, bảng cache, session và queue không được hiển thị.

## Kiểm thử

```bash
php artisan test
```

Để dừng ứng dụng đang chạy, quay lại Terminal và nhấn `Ctrl + C`.
