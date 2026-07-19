# Trang quản trị hệ thống

## URL

```text
/admin
```

## Chức năng chính

- **Giao diện chung:** hiển thị tên hệ thống, liên kết đến trang công khai và thông tin người đăng nhập.
- **Dashboard:** nhóm chức năng theo nghiệp vụ, hiển thị số lượng bản ghi và các thao tác nhanh.
- **Danh sách dữ liệu:** hỗ trợ tìm kiếm, lọc, sắp xếp, phân trang và thao tác theo từng dòng.
- **Biểu mẫu:** thêm và cập nhật dữ liệu bằng Livewire, có validation phía server.
- **Hoạt động gần đây:** dashboard hiển thị các thao tác quản trị mới nhất.
- **Nhật ký hoạt động:** `/admin/activity` hỗ trợ tìm kiếm và lọc nhật ký.
- **Phân quyền:** menu và route đều kiểm tra quyền truy cập.

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
