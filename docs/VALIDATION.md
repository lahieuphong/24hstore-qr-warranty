# Validation checklist

- `backend/` phục vụ cả quản trị và tra cứu công khai; `frontend/` chỉ là mã cũ để tham khảo.
- Backend mặc định MySQL; SQLite chỉ `:memory:` cho test.
- Không có đường dẫn tuyệt đối của máy cá nhân trong source.
- Backend có custom Administration, module index và recent actions.
- API không trả ghi chú nội bộ.
- Tra cứu IMEI dùng URL chuẩn `/check`; QR dùng `/check/{qr_token}` trên cùng backend.
- Có Nginx, systemd, cron và script deploy cho server.
- PHP source backend đã được chạy kiểm tra cú pháp trong quá trình đóng gói.
- Cần chạy `composer install`, `yarn install --frozen-lockfile`, test và build trong môi trường có Internet/dependency cache trước khi phát hành production.
