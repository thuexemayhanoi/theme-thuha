# Giới hạn và những điều chưa kiểm chứng

Người thực hiện **không** có quyền truy cập WordPress production, wp-admin, hosting, cơ sở dữ liệu hay Google Search Console. Vì vậy:

## Chưa làm / chưa kiểm chứng trên website thật

1. Chưa cài theme 1.3.0 hay plugin lên staging/production; chưa tạo, xuất bản hay ghép trang nào trên website thật.
2. Chưa kiểm tra với dữ liệu thật: nội dung trang tiếng Anh, menu thật (ID 23/22), logo, Additional CSS, các plugin đang chạy (LiteSpeed Cache, Contact Form 7, LuckyWP TOC, Moto QuickCall, WPCode/SEO Engine 06…), phiên bản Yoast thật và cấu hình Organization của Yoast.
3. Chưa đo hiệu năng (PageSpeed/LCP/CLS thực tế) sau khi cài; chỉ đo CLS trong trình duyệt cục bộ (< 0,01).
4. Chưa kiểm tra cache LiteSpeed với `/vi/` (cần xóa cache sau khi bật/tắt; cache không được dùng chung giữa `/` và `/vi/` vì URL khác nhau).
5. Chưa kiểm tra Search Console, Rich Results Test, Schema Validator trên URL thật.
6. Chưa kiểm tra trên thiết bị/trình duyệt thật ngoài Chromium headless (Safari iOS, Firefox).
7. Bản dịch lõi WordPress/Yoast tiếng Việt có thể chưa được cài trên website: một số chuỗi của Yoast (ví dụ tiêu đề trang tìm kiếm mặc định) có thể vẫn tiếng Anh.
8. Đoạn mã SEO Engine 06 (WPCode) có thể đang bổ sung LocalBusiness cho Organization của Yoast trên mọi trang, kể cả trang Việt — chưa xác minh trạng thái bật.

## Cần chủ website quyết định / xác nhận

- Hai trang pháp lý (`/vi/chinh-sach-bao-mat/`, `/vi/dieu-khoan-su-dung/`) chỉ mô tả hành vi đã biết của website; cần chủ website đọc trước khi xuất bản.
- Phí gia hạn, "không giao xe tới sân bay Nội Bài", liên kết bản đồ: lấy từ repo, không có trong danh sách dữ liệu khóa của yêu cầu này (xem `BUSINESS-DATA.md`).
- Cặp `/vi/dieu-khoan-su-dung/ ↔ /terms-of-use/` (trang Anh mang nhãn "Rental Terms").
- Nhãn menu tiếng Việt (Trang chủ, Bảng giá, Thuê xe ga, Thủ tục thuê, Giao nhận xe, Blog, Hỏi đáp, Giới thiệu, Liên hệ, Bảo mật, Điều khoản) là mới; quy định đóng băng chỉ áp dụng cho nhãn tiếng Anh và **không** nhãn tiếng Anh nào bị đổi.

## Giới hạn kỹ thuật đã biết

- Khi vô hiệu hóa plugin, WordPress không còn tách ngôn ngữ (xem `ROLLBACK.md` mục C).
- Chuỗi giao diện trong xem trước bản nháp được dịch, nhưng locale toàn trang (chuỗi lõi WordPress) chỉ là `vi` trên URL `/vi/`.
- Trợ lý website tiếng Anh của theme không có bản tiếng Việt; nó được ẩn trên trang Việt.
- Trang `/vi/` chỉ có một mục breadcrumb nên Yoast không in BreadcrumbList cho riêng trang này.
- Khối FAQ dùng định dạng block của Yoast; nếu trình soạn thảo báo "khối chứa nội dung không mong đợi", chọn "Khôi phục khối" (nội dung và schema không đổi).
- Kiểm thử chạy trên WordPress 7.1.3 + Yoast SEO 28.7 + SQLite cục bộ; production dùng MySQL/MariaDB và có thể khác phiên bản.
- Mã nguồn theme cũ chưa đạt chuẩn WPCS (330 lỗi trên các tệp đã sửa ở `main`); phần thêm mới theo đúng phong cách sẵn có của tệp, plugin đạt WPCS 100%.
