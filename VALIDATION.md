# Báo cáo kiểm tra source

Ngày kiểm tra: 16/07/2026.

## Đã kiểm tra

- `php artisan test`: 10 test vượt qua với 34 assertion.
- `vendor/bin/pint --test`: mã PHP đạt chuẩn định dạng Laravel Pint.
- `composer validate --no-check-publish`: `composer.json` hợp lệ.
- `npm run build`: Vite/Tailwind build production thành công với 55 module.
- `.env`, `vendor/`, `node_modules/`, database SQLite, cache runtime và build output đều được loại khỏi Git.
- Không phát hiện private key, API token hoặc thông tin bí mật thực tế trong source được theo dõi.

## Giới hạn của lần kiểm tra này

- Test tự động dùng SQLite; chưa xác minh trực tiếp migration và truy vấn trên MySQL/PostgreSQL.
- Chưa kiểm thử end-to-end trên VPS thật với Nginx, PHP-FPM, HTTPS, cron và systemd worker.
- Cần smoke test thủ công luồng đăng nhập, import workbook thực tế, quét QR và bản in PDF trước khi phát hành production.

Các bước cài đặt, kiểm thử và triển khai đầy đủ nằm trong `README.md` và `docs/DEPLOYMENT.md`.
