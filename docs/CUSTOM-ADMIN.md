# Trang quản trị custom kiểu Django Admin

## URL

```text
/admin
```

## Thành phần tương đồng Django Admin

- **Admin site header:** tên hệ thống, liên kết mở site public và thông tin người đăng nhập.
- **App/module index:** mỗi nhóm nghiệp vụ hiển thị resource, số bản ghi, “Xem / sửa” và hành động thêm mới.
- **Change list:** danh sách sản phẩm/người dùng có search, filter, sort, pagination và hành động theo dòng.
- **Add/change form:** form Livewire trong modal, validation phía server.
- **Recent actions:** dashboard hiển thị thao tác gần đây.
- **Log entries:** `/admin/activity` cho phép tìm kiếm/lọc nhật ký.
- **Permissions:** menu và route đều kiểm tra quyền, không chỉ ẩn nút ở giao diện.

## Nhật ký được ghi

- Đăng nhập / đăng xuất.
- Thêm, cập nhật, xóa sản phẩm.
- Import Excel/CSV hoàn tất.
- Thêm/cập nhật/khóa/mở tài khoản.
- Cập nhật hồ sơ và đổi mật khẩu (không ghi nội dung mật khẩu).

Nhật ký không ghi mật khẩu, nội dung session hoặc khóa bí mật.

## Tài khoản mặc định

Seeder đọc:

```dotenv
ADMIN_NAME=
ADMIN_EMAIL=
ADMIN_PASSWORD=
```

Production sẽ từ chối seed nếu mật khẩu vẫn là mẫu hoặc ngắn dưới 12 ký tự.
