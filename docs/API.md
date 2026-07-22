# Backend API v1

Base URL ví dụ:

```text
https://admin-warranty.example.com/api/v1
```

## Health

```http
GET /health
```

Trả HTTP 200 khi kết nối database hoạt động, HTTP 503 khi database không sẵn sàng.

## Tra cứu bằng QR token

```http
GET /warranties/{uuid}
```

## Tra cứu chính xác bằng IMEI

```http
GET /warranties/search?imei=012345678901234
```

## Response thành công

```json
{
  "data": {
    "product_code": "IP15-128-BLK",
    "name": "Điện thoại mẫu",
    "imei": "012345678901234",
    "warehouse_date": "2026-01-15",
    "warranty_months": 12,
    "warranty_expires_at": "2027-01-15",
    "warranty_status": "active",
    "warranty_status_label": "Còn bảo hành",
    "lookup_url": "https://admin-warranty.example.com/check/...",
    "updated_at": "2026-07-16T10:00:00+07:00"
  }
}
```

Không trả `internal_note`, `created_by`, `updated_by` hoặc dữ liệu user/permission.
