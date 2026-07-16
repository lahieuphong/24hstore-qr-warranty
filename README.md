# 24hStore QR Warranty

Hệ thống quản lý mã QR bảo hành theo IMEI, xây dựng theo mô hình **Laravel monolith**. Giao diện quản trị dùng Laravel Blade + Livewire, giao diện responsive bằng Tailwind CSS; trang tra cứu QR là trang công khai chỉ đọc.

## 1. Phạm vi đã triển khai

- Quản lý sản phẩm: thêm, sửa, xóa mềm, tìm kiếm và lọc trạng thái.
- Dữ liệu sản phẩm: mã hàng, tên hàng, IMEI, ngày nhập kho, thời hạn bảo hành, ngày hết hạn tự tính, trạng thái và ghi chú nội bộ.
- Mỗi IMEI có một `qr_token` UUID riêng; đường dẫn QR không để lộ IMEI trong URL.
- Chặn IMEI trùng ở cả validation ứng dụng và unique index trong cơ sở dữ liệu.
- Tra cứu công khai bằng QR: mã hàng, tên hàng, IMEI, ngày nhập, thời hạn, ngày hết hạn và trạng thái.
- Trạng thái: Còn bảo hành, Hết bảo hành, Đổi bảo hành, Khóa bảo hành.
- Import XLSX/XLS/CSV bằng Laravel Excel, báo lỗi theo từng dòng và vẫn nhập các dòng hợp lệ.
- Tải file Excel mẫu trực tiếp từ trang quản trị.
- Xem QR PNG, xuất tem đơn 40 × 40 mm và PDF A4 hàng loạt; nội dung tem chỉ có QR Code.
- Phân quyền với Spatie Permission và năm vai trò mặc định.
- Quản lý tài khoản nội bộ, khóa/mở tài khoản và bảo vệ super-admin cuối cùng.
- Scheduler đồng bộ trạng thái hết hạn hằng ngày.
- Cấu hình mẫu cho VPS Linux, Nginx, PHP-FPM, queue worker và cron.

## 2. Công nghệ

- PHP 8.4
- Laravel 12
- Laravel Blade + Livewire 4
- Tailwind CSS 4 + Vite
- MySQL 8+ hoặc PostgreSQL 15+
- Laravel Excel
- Endroid QR Code
- DomPDF
- Laravel Breeze làm nền cho luồng xác thực nội bộ
- Spatie Laravel Permission
- Nginx + PHP-FPM

> Source không chứa `vendor/` và `node_modules/`. Chạy Composer và npm sau khi giải nén.

## 3. Kiến trúc

Ứng dụng dùng một codebase Laravel duy nhất:

```text
Trình duyệt quản trị
        │
        ├── Blade + Livewire ── CRUD / import / tìm kiếm / phân quyền
        │
        └── Laravel controllers ── QR PNG / PDF tem / tra cứu công khai
                                   │
                                   └── MySQL hoặc PostgreSQL
```

Cách này phù hợp với phạm vi nội bộ, ít tích hợp ngoài, triển khai một VPS và không cần API/front-end độc lập. Chi tiết nằm tại [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md). Bảng đối chiếu từng yêu cầu nằm tại [`docs/REQUIREMENTS-MAPPING.md`](docs/REQUIREMENTS-MAPPING.md).

## 4. Cấu trúc thư mục chính

```text
app/
├── Console/Commands/SyncWarrantyStatuses.php
├── Enums/WarrantyStatus.php
├── Exports/ProductsTemplateExport.php
├── Imports/ProductRowsImport.php
├── Livewire/
│   ├── Dashboard.php
│   ├── Imports/Index.php
│   ├── Products/Index.php
│   ├── Profile.php
│   └── Users/Index.php
├── Models/Product.php
├── Policies/ProductPolicy.php
└── Services/
    ├── ProductImportService.php
    └── QrCodeService.php

deploy/               # Nginx, PHP-FPM, systemd, cron, script deploy
docs/                 # Kiến trúc, import và triển khai
resources/views/      # Blade, Livewire, trang tra cứu, mẫu PDF
tests/                # Feature test và unit test
```

## 5. Cài đặt local

### 5.1. Yêu cầu hệ thống

Cần PHP 8.4 cùng các extension phổ biến:

```text
bcmath, ctype, curl, dom, fileinfo, gd, intl, mbstring,
openssl, pdo, pdo_mysql hoặc pdo_pgsql, tokenizer, xml, zip
```

Ngoài ra cần Composer 2, Node.js 22+, npm và MySQL/PostgreSQL.

### 5.2. Cài dependency

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
```

Sau lần cài đầu, nên lưu `composer.lock` và `package-lock.json` vào hệ thống quản lý mã nguồn để các lần triển khai dùng đúng phiên bản đã kiểm thử.

### 5.3. Tạo cơ sở dữ liệu

Ví dụ MySQL:

```sql
CREATE DATABASE qr_warranty
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
CREATE USER 'qr_warranty'@'localhost' IDENTIFIED BY 'mat_khau_manh';
GRANT ALL PRIVILEGES ON qr_warranty.* TO 'qr_warranty'@'localhost';
FLUSH PRIVILEGES;
```

Cấu hình `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qr_warranty
DB_USERNAME=qr_warranty
DB_PASSWORD=mat_khau_manh
```

Ví dụ PostgreSQL:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=qr_warranty
DB_USERNAME=qr_warranty
DB_PASSWORD=mat_khau_manh
DB_SSLMODE=prefer
```

### 5.4. Tạo bảng và tài khoản quản trị

Đổi các biến sau trong `.env` **trước khi seed**:

```dotenv
ADMIN_NAME="Quản trị hệ thống"
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD="một-mật-khẩu-dài-và-riêng"
```

Sau đó chạy:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

Khởi động local:

```bash
php artisan serve
```

Mở `http://localhost:8000` và đăng nhập bằng tài khoản đã khai báo trong `.env`.

## 6. Cấu hình URL cho QR

QR lưu đường dẫn tuyệt đối theo `APP_URL`. Trên môi trường thật phải dùng domain HTTPS có thể truy cập từ điện thoại quét tem:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://warranty.example.com
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
```

Sau khi đổi URL:

```bash
php artisan optimize:clear
php artisan config:cache
```

QR được tạo động từ token nên không cần sinh lại dữ liệu sản phẩm khi chỉ đổi domain. PDF tải sau khi đổi cấu hình sẽ chứa URL mới.

## 7. Import Excel

Vào **Import Excel → Tải file mẫu**. Các cột:

| Cột | Bắt buộc | Ví dụ |
|---|---:|---|
| Mã hàng | Có | `IP15-128-BLK` |
| Tên hàng | Có | `Điện thoại mẫu 128GB` |
| IMEI | Có | `012345678901234` |
| Ngày nhập | Có | `15/07/2026` |
| Thời hạn bảo hành | Không | `12` hoặc `12 tháng` |

Lưu ý:

- Cột IMEI nên đặt định dạng **Text** để Excel không làm mất số 0 đầu hoặc đổi sang dạng khoa học.
- Các định dạng ngày được hỗ trợ: `dd/mm/yyyy`, `dd-mm-yyyy`, `yyyy-mm-dd`, `mm/dd/yyyy` và ngày số chuẩn Excel.
- Thời hạn bảo hành là số tháng từ 1 đến 120.
- Dòng lỗi không chặn các dòng hợp lệ khác; kết quả và lỗi từng dòng được lưu trong lịch sử import.
- Hệ thống chỉ đọc sheet đầu tiên.

Chi tiết tại [`docs/IMPORT-EXCEL.md`](docs/IMPORT-EXCEL.md).

## 8. Quy tắc bảo hành

- `warranty_expires_at = warehouse_date + warranty_months` bằng phép cộng tháng không tràn ngày.
- Ví dụ: `31/01/2026 + 1 tháng = 28/02/2026`.
- Khi trạng thái đang là **Còn bảo hành** nhưng ngày hết hạn đã qua, giao diện tra cứu lập tức hiển thị **Hết bảo hành**.
- Scheduler chạy lệnh `warranty:sync-statuses` để đồng bộ trạng thái lưu trong database.
- Hai trạng thái **Đổi bảo hành** và **Khóa bảo hành** không bị scheduler ghi đè.
- Sản phẩm không có thời hạn bảo hành sẽ không có ngày hết hạn.

## 9. Vai trò mặc định

| Vai trò | Quyền chính |
|---|---|
| `super-admin` | Toàn quyền |
| `warehouse-manager` | CRUD, import, in tem |
| `warehouse-staff` | Xem, thêm, sửa, import, in tem |
| `warranty-staff` | Xem, cập nhật trạng thái, in tem |
| `viewer` | Chỉ xem |

Super-admin được đi qua `Gate::before`. Giao diện không cho khóa chính tài khoản đang đăng nhập và không cho loại bỏ super-admin đang hoạt động cuối cùng.

## 10. Chạy queue và scheduler

Queue hiện dùng database để sẵn sàng mở rộng tác vụ nặng:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=120
```

Cron trên VPS:

```cron
* * * * * cd /var/www/24hstore-qr-warranty && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

File mẫu có sẵn tại [`deploy/cron.txt`](deploy/cron.txt) và worker systemd tại [`deploy/systemd/qr-warranty-worker.service`](deploy/systemd/qr-warranty-worker.service).

## 11. Kiểm thử

```bash
php artisan test
```

Bộ test bao phủ:

- Trang tra cứu QR công khai và không lộ ghi chú nội bộ.
- Tự sinh token, chuẩn hóa IMEI/mã hàng và tính ngày hết hạn.
- Không tái sử dụng IMEI sau xóa mềm.
- Quyền viewer và khóa tài khoản.
- Import CSV/Excel, phát hiện IMEI trùng và dữ liệu bảo hành sai.
- Đủ bốn trạng thái theo yêu cầu.

Kiểm tra định dạng mã nguồn:

```bash
./vendor/bin/pint --test
```

## 12. Triển khai VPS

Cấu hình tham khảo:

- Nginx: [`deploy/nginx/24hstore-qr-warranty.conf`](deploy/nginx/24hstore-qr-warranty.conf)
- PHP: [`deploy/php/99-qr-warranty.ini`](deploy/php/99-qr-warranty.ini)
- Queue worker: [`deploy/systemd/qr-warranty-worker.service`](deploy/systemd/qr-warranty-worker.service)
- Deploy script: [`deploy/deploy.sh`](deploy/deploy.sh)
- Hướng dẫn đầy đủ: [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md)

Lệnh tối thiểu sau khi đưa source lên máy chủ:

```bash
cp .env.example .env
# sửa toàn bộ biến production, DB, APP_URL và ADMIN_*
composer install --no-dev --prefer-dist --optimize-autoloader
npm install --no-audit --no-fund
npm run build
php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Phân quyền thư mục:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
```

## 13. Bảo mật và vận hành

- Không bật `APP_DEBUG` trên production.
- Dùng HTTPS và cookie bảo mật.
- Không dùng mật khẩu quản trị mặc định trong `.env.example`.
- Chỉ cấp quyền đúng vai trò; tài khoản nghỉ việc cần khóa ngay.
- Sao lưu database hằng ngày và kiểm thử khôi phục định kỳ.
- Giữ `APP_KEY` an toàn; không commit file `.env`.
- Nên giới hạn truy cập `/admin` bằng VPN hoặc allowlist IP nếu quy trình nội bộ cho phép; trang `/bao-hanh/{token}` vẫn cần công khai để quét QR.
- Sau khi cập nhật code, chạy migration, test, build asset và `php artisan queue:restart`.

## 14. Điểm chưa bao gồm theo đúng phạm vi

- Không tích hợp ERP/HTS.
- Không kết nối website bán hàng.
- Không có API công khai riêng hoặc front-end SPA.
- Không gửi email/SMS bảo hành.
- Không quản lý sửa chữa nhiều bước hoặc lịch sử phiếu bảo hành; có thể bổ sung thành module sau mà không cần tách kiến trúc.
