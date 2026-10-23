# Dữ liệu kinh doanh đã khóa

Nguồn duy nhất trong mã: `plugins/rentbikehanoi-native-vi/config/business.php`. Kiểm thử `tests/static/content-test.php` so sánh từng giá trị với danh sách dưới đây; mọi thay đổi cần chủ website duyệt và phải cập nhật cả ba nơi (tệp cấu hình, tài liệu này, kiểm thử).

| Mục | Giá trị |
| --- | --- |
| Tên | Thuê xe máy Nguyễn Hà |
| Địa chỉ | Ngõ 5 Nguyễn Văn Cừ, Bồ Đề, Long Biên, Hà Nội (gần Bến xe Gia Lâm) |
| Điện thoại / Zalo / WhatsApp | 0334 699 969 / +84 334 699 969 |
| Giờ mở cửa | 09:00–21:00 hằng ngày |

| Loại xe | Ngày | Tuần | Tháng |
| --- | --- | --- | --- |
| Xe số | 150.000–200.000đ | 700.000–800.000đ | 1.000.000–1.200.000đ |
| Xe ga | 150.000–200.000đ | 600.000–1.000.000đ | 1.200.000–2.000.000đ |
| Xe điện | tùy mẫu (không tự đặt giá) | 600.000–800.000đ | 1.500.000–1.800.000đ |
| Xe 50cc | 200.000đ | 1.000.000đ | 2.000.000đ |
| Honda Vision | 200.000đ | 1.000.000đ | 2.000.000đ |
| Honda Air Blade | 200.000đ | 900.000đ | 1.500.000đ |

Quy định: giá là giá tham khảo, phải xác nhận trước khi đặt; không quảng cáo mặc định không cần đặt cọc; không suy đoán thủ tục hay chính sách hoàn cọc; không bịa đánh giá, kinh nghiệm, số năm, số lượng xe, ưu đãi, bảo hiểm, cam kết.

## Dữ liệu bổ sung lấy từ repository (cần chủ website xác nhận lại trước khi xuất bản)

| Dữ liệu | Nguồn trong repo | Dùng ở |
| --- | --- | --- |
| Phí gia hạn 20.000đ/giờ; quá 6 giờ tính một ngày | Chủ cửa hàng xác nhận lại ngày 23/10/2026; config/business.php; dữ liệu cùng website trong thuha-writer | Trang chủ Việt, bảng giá, thủ tục, giao nhận, FAQ |
| Không giao xe tới sân bay Nội Bài | `assets/js/assistant-seo-20261019.js` | Giao nhận, FAQ |
| Liên kết bản đồ `https://share.google/pJ2KSOJcrYDJYM42M` | `template-parts/assistant.php` | Trang chủ Việt, giao nhận, liên hệ |

## Đối chiếu với mã tiếng Anh hiện có

Không phát hiện mâu thuẫn về NAP, giờ và bảng giá: theme (`inc/settings.php`) dùng "+84 334 699 969", "09:00–21:00 daily", địa chỉ tiếng Anh "Alley 5, Nguyen Van Cu, Bo De, Long Bien, Hanoi, Vietnam (near Gia Lam Bus Station)"; trợ lý website tiếng Anh dùng cùng bảng giá. Không sửa dữ liệu tiếng Anh. Nội dung trang tiếng Anh trong cơ sở dữ liệu (bảng giá, FAQ…) không có trong repo nên **chưa đối chiếu được**.
