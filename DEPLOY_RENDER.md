# Deploy demo miễn phí trên Render + Neon

Ứng dụng chạy bằng Docker với PHP 8.4 trên Render và lưu dữ liệu lâu dài
trong PostgreSQL của Neon. Không commit `APP_KEY`, `DB_URL` hoặc mật khẩu
quản trị vào GitHub.

## 1. Tạo Neon PostgreSQL

1. Tạo project Neon tại region Singapore.
2. Giữ Neon Auth ở trạng thái tắt vì ứng dụng đã dùng Laravel Auth.
3. Trong cửa sổ **Connect**, bật **Connection pooling**.
4. Sao chép toàn bộ connection string. Giá trị này sẽ được dùng cho `DB_URL`.

## 2. Tạo APP_KEY riêng cho bản demo

Chạy tại thư mục dự án:

```bash
php artisan key:generate --show
```

Sao chép kết quả bắt đầu bằng `base64:` và chỉ lưu trong Render.

## 3. Tạo Render Web Service

1. Chọn **New > Web Service**.
2. Chọn repository `lahieuphong/24hstore-qr-warranty`, branch `main`.
3. Chọn các giá trị:

   - Language/Runtime: `Docker`
   - Region: `Singapore`
   - Instance type: `Free`
   - Health check path: `/up`
   - Auto deploy: bật

4. Đặt tên service, ví dụ `24hstore-warranty-demo`. URL mặc định sẽ là
   `https://24hstore-warranty-demo.onrender.com` nếu tên này còn trống.

## 4. Khai báo Environment Variables

Dùng [deploy/render.env.example](deploy/render.env.example) làm danh sách.
Các giá trị bắt buộc phải thay:

- `APP_KEY`: kết quả của `php artisan key:generate --show`.
- `APP_URL`: URL HTTPS chính xác của Render.
- `DB_URL`: pooled connection string của Neon.
- `ADMIN_EMAIL`: email đăng nhập trang quản trị.
- `ADMIN_PASSWORD`: mật khẩu riêng dài ít nhất 12 ký tự.

`ADMIN_PASSWORD` mặc định yếu sẽ khiến production seeder chủ động dừng deploy.

## 5. Deploy và kiểm tra

Khi deploy, container tự động:

1. Chạy migration với tối đa 5 lần thử kết nối Neon.
2. Chạy seeder tạo role và tài khoản quản trị.
3. Cache cấu hình, route và view.
4. Khởi động Apache ở cổng `10000`.

Kiểm tra:

- `/up`: web process đang hoạt động.
- `/api/v1/health`: trả về `"database": "ok"`.
- `/check`: trang tra cứu công khai.
- `/admin/login/`: trang đăng nhập quản trị.

Sau lần deploy đầu, có thể đặt `RUN_DATABASE_SEEDER=false`. Migration vẫn chạy
ở mỗi lần khởi động, còn dữ liệu sản phẩm luôn được giữ trong Neon.

Render Free có thể ngủ sau thời gian không nhận request. Lần mở đầu sau khi ngủ
sẽ chậm hơn bình thường; đây là giới hạn chấp nhận được đối với bản demo.
