=== Rentbike Hanoi – Tiếng Việt gốc (/vi/) ===
Contributors: rentbikehanoi
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv2 or later

Phần tiếng Việt viết riêng dưới /vi/ cho rentbikehanoi.com. Tiếng Anh giữ nguyên ở địa chỉ gốc.

== Mô tả ==

* Định tuyến /vi/ không cần sửa .htaccess, wp-config.php hay cơ sở dữ liệu bằng tay.
* Bật / tắt / hoàn tác /vi/ an toàn, có kiểm tra trước khi bật và tự quay lại nếu kiểm tra sau khi bật thất bại.
* Tạo bộ 11 trang tiếng Việt dưới dạng BẢN NHÁP (không tự xuất bản, chạy lại không tạo trùng).
* Ghép cặp trang/bài Anh–Việt (lưu ở phía tiếng Việt; không ghi vào bài tiếng Anh).
* hreflang en / vi / x-default (→ tiếng Anh) chỉ cho cặp đã xác nhận và đã xuất bản.
* Yoast SEO vẫn là nơi duy nhất in title, mô tả, canonical, robots, Open Graph, JSON-LD và sitemap; plugin chỉ dùng bộ lọc công khai của Yoast.
* Danh sách, tìm kiếm, chuyên mục, bài liên quan, bài trước/sau và menu không trộn ngôn ngữ.
* Nút "VI | EN" cạnh nút Sáng/Tối của theme Rentbike Hanoi (cần theme 1.3.0 trở lên).
* Trang chủ /vi/ cùng bố cục trang chủ tiếng Anh (cần theme 1.4.0; theme cũ hơn → trang thường).
* Tiêu đề, breadcrumb của trang tìm kiếm, 404, chuyên mục Việt và form bình luận trên trang Việt hiển thị tiếng Việt (chỉ khi WordPress chưa có bản dịch tiếng Việt cho chuỗi đó).
* Không gọi API dịch, dịch vụ AI hay tài nguyên bên ngoài; không dùng cookie hay JavaScript để đổi ngôn ngữ.

== Cài đặt ==

1. Cập nhật theme Rentbike Hanoi lên 1.4.0 (thư mục theme đang hoạt động: wp-theme) theo quy trình WPVibe draft/preview/publish.
2. Plugins → Thêm mới → Tải plugin lên → chọn rentbikehanoi-native-vi.zip → Kích hoạt. Kích hoạt không tạo nội dung và không bật /vi/.
3. Vào menu "Tiếng Việt /vi/": xem phần kiểm tra, bấm "Tạo bộ trang Việt (bản nháp)", đọc và chỉnh từng trang.
4. Xác nhận các cặp Anh–Việt, xuất bản trang, rồi bấm "Bật /vi/". Xóa cache LiteSpeed sau khi bật/tắt.

== Gỡ / tắt ==

Nút "Tắt /vi/" làm mọi URL /vi/ trả 404 và gỡ chúng khỏi sitemap, trang Việt vẫn được giữ.
Khi vô hiệu hóa plugin, WordPress không còn tách ngôn ngữ: hãy TẮT /vi/ và chuyển trang/bài Việt về bản nháp trước nếu muốn ẩn hoàn toàn. Gỡ plugin chỉ xóa cài đặt của plugin, không xóa nội dung.

== Changelog ==

= 1.1.1 =
* Trang Việt: tiêu đề trang tìm kiếm, 404 và chuyên mục Việt (title, og:title, twitter:title, tên WebPage trong schema Yoast) và breadcrumb ("Kết quả tìm kiếm", "Không tìm thấy trang", "Trang 2") bằng tiếng Việt; trước đây là chuỗi tiếng Anh mặc định của Yoast.
* Trang Việt: form và danh sách bình luận, điều hướng bài viết/bình luận bằng tiếng Việt khi WordPress chưa có bản dịch tiếng Việt (bản dịch đã cài luôn được ưu tiên).
* Trang tiếng Anh không đổi.

= 1.1.0 =
* Trang chủ /vi/ cùng bố cục trang chủ tiếng Anh (cần theme 1.4.0), cài đặt "Bố cục trang chủ /vi/".
* Logo trang Việt dẫn về /vi/; vùng widget thanh bên tiếng Việt riêng; giữ dải giờ/điện thoại trên điện thoại.
* Kiểm tra trước khi bật chặn thêm GTranslate và Weglot.
* Chưa triển khai; được thay bằng 1.1.1.

= 1.0.0 =
* Phiên bản đầu tiên.
