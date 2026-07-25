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

- `APP_KEY`: tạo một lần bằng `php artisan key:generate --show`, sau đó giữ
  nguyên giá trị này qua mọi lần deploy.
- `APP_URL`: URL HTTPS chính xác của Render.
- `DB_URL`: pooled connection string của Neon.
- `ADMIN_EMAIL`: email đăng nhập trang quản trị.
- `ADMIN_PASSWORD`: mật khẩu riêng dài ít nhất 12 ký tự.

File `.env.production` trên máy chỉ là bản tham chiếu và bị loại khỏi Git/Docker
để không làm lộ bí mật. Sửa file này **không tự cập nhật Render**. Hãy đổi hai
biến `ADMIN_EMAIL`/`ADMIN_PASSWORD` trong mục **Environment** của Render rồi chọn
**Save Changes** để service redeploy.

Không tạo lại hoặc xóa `APP_KEY` khi redeploy: thay khóa sẽ làm mất hiệu lực cookie
phiên/CSRF đang có và người dùng có thể gặp lỗi 419. Giữ `SESSION_DRIVER=database`,
`SESSION_PATH=/`, `SESSION_SECURE_COOKIE=true` và để `SESSION_DOMAIN=null`.

`ADMIN_PASSWORD` mặc định yếu sẽ khiến production seeder chủ động dừng deploy.

## 5. Deploy và kiểm tra

Khi deploy, container tự động:

1. Xóa config cache cũ để đọc Environment hiện tại của Render.
2. Chạy migration với tối đa 5 lần thử kết nối Neon.
3. Luôn chạy seeder idempotent để đồng bộ role và tài khoản quản trị theo
   Environment hiện tại.
4. Cache lại cấu hình, route và view.
5. Khởi động Apache ở cổng `10000`.

Kiểm tra:

- `/up`: web process đang hoạt động.
- `/api/v1/health`: trả về `"database": "ok"`.
- `/check`: trang tra cứu công khai.
- `/admin/login/`: trang đăng nhập quản trị.

Mỗi lần thay `ADMIN_EMAIL` hoặc `ADMIN_PASSWORD` và service khởi động lại, cùng
một tài khoản quản trị trong database sẽ được cập nhật; email/mật khẩu cũ và các
phiên đăng nhập cũ sẽ mất hiệu lực. Dữ liệu sản phẩm không bị thay đổi.

Khi nâng cấp một database đã có nhiều hơn một `super-admin` lên phiên bản quản lý
admin theo Environment lần đầu, hãy giữ `ADMIN_EMAIL` trùng với đúng tài khoản
đang được Render quản lý và deploy một lần để đánh dấu tài khoản đó. Sau lần
deploy thành công này mới đổi email nếu cần. Seeder sẽ chủ động dừng nếu không thể
xác định an toàn tài khoản cần cập nhật.

Render Free có thể ngủ sau thời gian không nhận request. Lần mở đầu sau khi ngủ
sẽ chậm hơn bình thường; đây là giới hạn chấp nhận được đối với bản demo.
