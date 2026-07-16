# Quy chuẩn import Excel

## Cột được nhận diện

Hệ thống chấp nhận tiêu đề tiếng Việt hoặc tiếng Anh sau khi bỏ dấu và chuẩn hóa ký tự:

| Trường | Các tiêu đề được chấp nhận |
|---|---|
| Mã hàng | `Mã hàng`, `Mã sản phẩm`, `product_code`, `sku` |
| Tên hàng | `Tên hàng`, `Tên sản phẩm`, `name`, `product_name` |
| IMEI | `IMEI`, `serial`, `serial_number` |
| Ngày nhập | `Ngày nhập`, `Ngày nhập kho`, `warehouse_date`, `import_date` |
| Thời hạn | `Thời hạn bảo hành`, `Bảo hành tháng`, `warranty_months`, `warranty` |

Bốn trường đầu là bắt buộc. Thời hạn bảo hành có thể để trống.

## Dữ liệu hợp lệ

- Mã hàng: tối đa 100 ký tự, tự chuyển chữ hoa và bỏ khoảng trắng hai đầu.
- Tên hàng: tối đa 255 ký tự.
- IMEI: tối đa 64 ký tự, hỗ trợ chữ, số, dấu chấm, gạch ngang và gạch dưới; mọi khoảng trắng bị loại bỏ.
- Ngày nhập: ngày Excel hoặc một trong các định dạng `dd/mm/yyyy`, `dd-mm-yyyy`, `yyyy-mm-dd`, `mm/dd/yyyy`.
- Thời hạn: số nguyên từ 1 đến 120; chấp nhận hậu tố `tháng`, `thang`, `month`, `months`.

## Khuyến nghị với IMEI

Trong Excel, đặt toàn bộ cột IMEI thành **Text** trước khi nhập dữ liệu. Đây là cách duy nhất để giữ chính xác số 0 đầu và tránh ký hiệu khoa học. File mẫu tải từ ứng dụng đã định dạng cột này là Text.

## Cách xử lý lỗi

- Dòng trống bị bỏ qua.
- Mỗi dòng được validation độc lập.
- Dòng hợp lệ được lưu ngay cả khi dòng khác lỗi.
- IMEI được kiểm tra trùng trong chính file và trong database.
- Lịch sử import lưu số dòng thành công/lỗi và nội dung lỗi.
- Nếu thiếu tiêu đề bắt buộc hoặc file không có dữ liệu, batch sẽ ghi lỗi cấp file.

## Ví dụ CSV

```csv
Mã hàng,Tên hàng,IMEI,Ngày nhập,Thời hạn bảo hành
IP15-128-BLK,Điện thoại mẫu 128GB,012345678901234,15/07/2026,12
```
