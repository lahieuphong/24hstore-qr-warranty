# Kiến trúc hệ thống

## Quyết định monolith

Phạm vi hệ thống là quản trị nội bộ và một trang tra cứu QR công khai. Không có ERP, website bán hàng hay ứng dụng di động độc lập. Vì vậy, Laravel monolith giúp giảm số thành phần phải vận hành, dùng chung validation/phân quyền/model và triển khai trên một VPS đơn giản hơn so với tách API và front-end.

## Các lớp chính

1. **Presentation**
   - Blade layout và component.
   - Livewire cho dashboard, sản phẩm, import, tài khoản và hồ sơ.
   - Blade thuần cho trang tra cứu công khai và mẫu PDF.

2. **Application**
   - `ProductImportService`: đọc sheet, ánh xạ tiêu đề, chuẩn hóa, validation và lưu từng dòng.
   - `QrCodeService`: sinh PNG hoặc data URI.
   - Controllers: QR PNG, PDF tem, file mẫu và trang tra cứu.
   - Command: đồng bộ sản phẩm hết hạn.

3. **Domain/Data**
   - `Product`: chuẩn hóa IMEI/mã hàng, tự sinh UUID, tự tính ngày hết hạn và trạng thái hiệu lực.
   - `WarrantyStatus`: enum bốn trạng thái.
   - `ImportBatch`: lịch sử và lỗi import.
   - `User`, role, permission và policy.

## Luồng tạo sản phẩm

```text
Livewire form / Excel row
    → validation
    → normalize IMEI + product code
    → unique IMEI ở ứng dụng
    → Product::saving tính warranty_expires_at
    → Product::creating sinh UUID qr_token
    → unique index ở database
```

Hai lớp kiểm tra unique giúp tránh trùng do thao tác đồng thời.

## Luồng quét QR

```text
Tem QR
    → https://domain/bao-hanh/{uuid}
    → implicit route binding theo qr_token
    → tính trạng thái hiệu lực
    → render trang công khai chỉ đọc
```

URL không dùng IMEI nên khó đoán tuần tự hơn. Ghi chú nội bộ, người tạo và thông tin tài khoản không xuất hiện trên trang công khai.

## Luồng xuất PDF

- Tem đơn: khổ 40 × 40 mm, QR 34 mm.
- Tem A4: 5 cột × 7 hàng, tối đa 35 tem mỗi trang.
- Một lượt xuất hàng loạt giới hạn 500 sản phẩm để kiểm soát bộ nhớ và kích thước URL.
- QR được nhúng dưới dạng data URI, nên PDF không phụ thuộc truy cập mạng khi render.

## Database

### `products`

- `product_code`, `name`, `imei`
- `warehouse_date`, `warranty_months`, `warranty_expires_at`
- `warranty_status`, `internal_note`
- `qr_token`
- `created_by`, `updated_by`
- timestamps và soft delete

Index quan trọng:

- unique `imei`
- unique `qr_token`
- index mã hàng, tên, ngày nhập, ngày hết hạn và trạng thái

### `import_batches`

Lưu tên file, người import, tổng số dòng, số thành công, số lỗi, JSON lỗi theo dòng và thời điểm hoàn tất.

### Phân quyền

Dùng schema chuẩn của Spatie Permission: roles, permissions và ba bảng pivot.

## Trạng thái hiệu lực

`effectiveWarrantyStatus()` chỉ chuyển cách hiển thị từ `active` sang `expired` khi ngày hết hạn nhỏ hơn ngày hiện tại. `replaced` và `locked` luôn được giữ nguyên. Command ban đêm đồng bộ trạng thái database để báo cáo và truy vấn nhất quán.

## Khả năng mở rộng

Có thể bổ sung trong cùng monolith:

- lịch sử thay đổi sản phẩm/audit log;
- phiếu tiếp nhận bảo hành;
- nhiều kho;
- nhiều mẫu tem;
- job queue cho file import rất lớn;
- API nội bộ có token;
- object storage cho tài liệu bảo hành.

Chỉ nên tách dịch vụ khi có tải lớn, đội phát triển độc lập hoặc tích hợp ngoài cần vòng đời phát hành riêng.
