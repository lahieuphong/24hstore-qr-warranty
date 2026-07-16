# Triển khai VPS Linux với Nginx và PHP-FPM

Hướng dẫn giả định source đặt tại `/var/www/24hstore-qr-warranty`, user chạy web là `www-data` và domain là `warranty.example.com`.

## 1. Thành phần máy chủ

Cài các thành phần tương ứng bản phân phối Linux:

- Nginx
- PHP 8.4 CLI/FPM và extension: bcmath, curl, gd, intl, mbstring, mysql hoặc pgsql, xml, zip
- Composer 2
- Node.js 22+ và npm
- MySQL 8+ hoặc PostgreSQL 15+
- Git, unzip

Kiểm tra:

```bash
php -v
php -m
composer --version
node -v
npm -v
nginx -v
```

## 2. Đưa source lên server

```bash
sudo mkdir -p /var/www/24hstore-qr-warranty
sudo chown -R "$USER":www-data /var/www/24hstore-qr-warranty
cd /var/www/24hstore-qr-warranty
# giải nén hoặc git clone source vào đây
cp .env.example .env
```

Sửa `.env` tối thiểu:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://warranty.example.com
FORCE_HTTPS=true
APP_TIMEZONE=Asia/Ho_Chi_Minh

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qr_warranty
DB_USERNAME=qr_warranty
DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=150

ADMIN_NAME="Quản trị hệ thống"
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD="..."
```

## 3. Cài ứng dụng

```bash
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

if [ -f package-lock.json ]; then
  npm ci
else
  npm install --no-audit --no-fund
fi
npm run build

php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
```

Sau lần đầu, thay hoặc xóa `ADMIN_PASSWORD` khỏi `.env` không làm đổi mật khẩu tài khoản đang tồn tại. Nên đăng nhập và đổi mật khẩu ngay trong trang Hồ sơ.

## 4. Phân quyền file

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
```

Source còn lại có thể chỉ đọc đối với user web.

## 5. PHP-FPM

Sao chép cấu hình tham khảo:

```bash
sudo cp deploy/php/99-qr-warranty.ini /etc/php/8.4/fpm/conf.d/99-qr-warranty.ini
sudo systemctl restart php8.4-fpm
```

Điều chỉnh đường dẫn theo cách PHP được cài trên VPS.

## 6. Nginx

```bash
sudo cp deploy/nginx/24hstore-qr-warranty.conf /etc/nginx/sites-available/24hstore-qr-warranty
sudo ln -s /etc/nginx/sites-available/24hstore-qr-warranty /etc/nginx/sites-enabled/24hstore-qr-warranty
sudo nginx -t
sudo systemctl reload nginx
```

Sửa `server_name`, đường dẫn root và socket PHP-FPM trước khi reload. Sau đó cấp chứng chỉ TLS theo quy trình của đơn vị vận hành và chuyển hướng HTTP sang HTTPS.

## 7. Scheduler

```bash
sudo crontab -u www-data -e
```

Thêm:

```cron
* * * * * cd /var/www/24hstore-qr-warranty && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Kiểm tra:

```bash
sudo -u www-data php artisan schedule:list
sudo -u www-data php artisan warranty:sync-statuses
```

## 8. Queue worker

```bash
sudo cp deploy/systemd/qr-warranty-worker.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now qr-warranty-worker
sudo systemctl status qr-warranty-worker
```

## 9. Cập nhật phiên bản

Có thể chạy `deploy/deploy.sh` sau khi đã kiểm tra các biến môi trường và sao lưu:

```bash
sudo -u www-data APP_DIR=/var/www/24hstore-qr-warranty ./deploy/deploy.sh
```

Quy trình an toàn:

1. Sao lưu database.
2. Đưa code mới lên.
3. Chạy test ở CI/staging.
4. Chạy migration và build.
5. Restart queue.
6. Kiểm tra đăng nhập, tra cứu QR, import và PDF.

## 10. Sao lưu

Cần sao lưu tối thiểu:

- toàn bộ database;
- file `.env` bằng kho bí mật an toàn;
- source/lock files;
- `storage/app` nếu sau này có tệp tải lên cần lưu.

Kiểm thử khôi phục định kỳ quan trọng hơn việc chỉ xác nhận job backup đã chạy.

## 11. Health check

Laravel cung cấp endpoint:

```text
GET /up
```

Ngoài ra nên giám sát:

- HTTP 2xx của `/up`;
- trạng thái PHP-FPM, Nginx và worker;
- dung lượng ổ đĩa;
- lỗi trong `storage/logs/laravel.log`;
- kết nối database;
- thời gian phản hồi trang tra cứu QR.
