# Cài đặt và triển khai thử (staging) — dành cho AI/người có quyền WordPress

> Trạng thái hiện tại: đã triển khai production ngày 23/10/2026; xem `DEPLOYMENT-2026-10-23.md`. Quy trình dưới đây vẫn dành cho các lần triển khai/kiểm thử tiếp theo, không phải yêu cầu tạo lại những trang đã có.
> Cập nhật website đang chạy lên theme 1.4.0 + plugin 1.1.1: làm theo `UPDATE-1.4.0-1.1.1.md`. Các gói 1.3.0/1.0.0 nhắc tới bên dưới nay nằm trong `dist/previous/`.

Tại thời điểm bàn giao mã ngày 22/10/2026, repository **chưa** cài lên website thật. Người viết mã không có quyền wp-admin, hosting, cơ sở dữ liệu hay Search Console. Các bước triển khai phải do người/AI có quyền WordPress thực hiện và không mặc định rằng Claude có các quyền đó. Bản ghi production ngày 23/10/2026 ghi rõ thao tác thực tế và các giới hạn kiểm chứng của lần triển khai đầu.

## 0. Điều kiện bắt buộc

- Có bản sao lưu toàn website gần nhất (chủ website xác nhận sao lưu hosting hằng tuần — kiểm tra ngày của bản gần nhất) **và** bản sao thư mục theme đang chạy.
- Theme đang hoạt động: stylesheet `wp-theme`. Không đổi thư mục, không kích hoạt thư mục `*-wpvibe-draft`.
- Dùng quy trình WPVibe draft → preview → publish đã ghi trong AGENTS.md; xuất bản chỉ sau khi chủ website xem bản preview và đồng ý.
- Kiểm tra checksum: `sha256sum -c dist/SHA256SUMS` (trong thư mục `dist/`).

## 1. Staging (khuyến nghị) — một bản sao của website

1. Tạo bản sao staging bằng công cụ của hosting (không ghi gì lên production).
2. Cập nhật theme trên staging bằng `dist/wp-theme-1.3.0.zip` (thư mục đích `wp-theme`).
3. Tải `dist/rentbikehanoi-native-vi-1.0.0.zip` qua *Plugins → Thêm mới → Tải plugin lên* rồi kích hoạt. Kỳ vọng: không có trang mới, /vi/ trả 404, trang tiếng Anh không đổi.
4. Vào *Tiếng Việt /vi/*: đọc phần kiểm tra. Mọi dòng đỏ phải được xử lý trước khi bật.
5. Bấm *Tạo bộ trang Việt (bản nháp)* → 11 bản nháp. Bấm lại một lần để xác nhận không tạo trùng.
6. Xem từng bản nháp bằng nút Xem trước. Chủ website đọc hai trang pháp lý (Chính sách bảo mật, Điều khoản sử dụng) và các thông tin "cần xác nhận" trong `docs/vi/LIMITATIONS.md` (giao xe Nội Bài, phí gia hạn).
7. Bấm *Xác nhận các cặp Anh–Việt được đề xuất* (hoặc ghép từng cặp). Kiểm tra cặp `/vi/dieu-khoan-su-dung/ ↔ /terms-of-use/` có thật sự cùng nội dung không (trang Anh mang nhãn "Rental Terms"); nếu không, bỏ ghép.
8. Xuất bản cả 11 trang trong cùng một lần (liên kết nội bộ giữa các trang Việt).
9. Bấm *Bật /vi/*. Xóa cache LiteSpeed/object cache.
10. Chạy kiểm tra ở mục 3 bên dưới.

## 2. Production

Chỉ làm sau khi staging đạt mục 3 **và** chủ website đồng ý bằng văn bản. Lặp lại bước 2–9 trên production theo quy trình WPVibe (preview → duyệt → publish). Ghi lại thời điểm, ID trang và kết quả kiểm tra vào báo cáo.

## 3. Kiểm tra bắt buộc sau khi bật (staging rồi production)

Trang tiếng Anh (so với trước khi cài):
- `/`, `/about-us/`, `/contact-us/`, `/faq/`, `/motorbike-rental-prices-in-hanoi/`, `/hanoi-scooter-rental/`, `/blog/`, một bài viết, `/?s=rental`, một URL 404: cùng mã HTTP, cùng `<title>`, cùng canonical, cùng meta description, menu/footer giữ nguyên nhãn đã đóng băng.
- Trang chủ tiếng Anh: title, description, og:title, og:site_name **không đổi** (bị đóng băng). Chỉ được thêm 3 thẻ hreflang và nút VI | EN.

Trang tiếng Việt:
- 11 URL trả 200; `lang="vi"`; đúng một `<title>`, một canonical tự trỏ, một meta description, một H1; `og:locale` = `vi_VN`.
- `/vi/blog/` khi chưa có bài: `noindex` và không có trong sitemap (đúng thiết kế).
- hreflang `en`/`vi`/`x-default` hai chiều cho các cặp đã xác nhận; không có hreflang cho trang không ghép.
- Rich Results Test cho `/vi/cau-hoi-thuong-gap/` (FAQPage) và Schema Validator cho 2–3 trang.
- `page-sitemap.xml` của Yoast chứa các URL /vi/ đã xuất bản; không có bản nháp.
- `/vi/?s=xe` có `noindex`.
- Nút VI | EN cạnh nút Sáng/Tối trên điện thoại (360–390px) và máy tính; chuyển trang đúng cặp; Sáng/Tối vẫn hoạt động.
- Xóa cache rồi kiểm tra lại bằng trình duyệt ẩn danh.

Search Console (sau production): gửi lại sitemap index; kiểm tra URL `/vi/` bằng URL Inspection; theo dõi báo cáo "Trang" và hreflang trong vài tuần.

## 4. Ghi chú môi trường

- Không cần sửa `.htaccess`, `wp-config.php` hay cơ sở dữ liệu bằng tay; plugin không cần flush rewrite.
- Nếu website dùng plugin chuyển hướng (Redirection, Yoast Premium redirects…), đảm bảo không có quy tắc nào bắt `/vi/…`.
- Nếu chuyển "Đường dẫn tĩnh" sang dạng Đơn giản, /vi/ không thể hoạt động (phần kiểm tra sẽ chặn bật).
