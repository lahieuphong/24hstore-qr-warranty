# Validation checklist

- Source chia rõ `backend/` và `frontend/`.
- Backend mặc định MySQL; SQLite chỉ `:memory:` cho test.
- Không có đường dẫn tuyệt đối của máy cá nhân trong source.
- Frontend không có migration/model database.
- Backend có custom Administration, module index và recent actions.
- API không trả ghi chú nội bộ.
- QR URL dùng `FRONTEND_URL`.
- Có Nginx, systemd, cron và script deploy cho server.
- PHP source đã được chạy `php -l` toàn bộ hai ứng dụng trong quá trình đóng gói.
- Cần chạy `composer install`, `yarn install --frozen-lockfile`, test và build trong môi trường có Internet/dependency cache trước khi phát hành production.
