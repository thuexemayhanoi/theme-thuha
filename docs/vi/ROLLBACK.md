# Hoàn tác (rollback)

Xếp theo mức độ, từ nhẹ nhất. Không bước nào xóa nội dung tiếng Anh.

## A. Tắt phần tiếng Việt (khuyến nghị, tức thì)

*Tiếng Việt /vi/ → Tắt /vi/*, rồi xóa cache.
- Mọi URL `/vi/…` trả 404; sitemap Yoast bỏ URL `/vi/`; hreflang và nút VI | EN biến mất.
- HTML tiếng Anh trở về đúng như khi chưa có plugin (đã kiểm thử, chỉ `<lastmod>` trong sitemap index của Yoast có thể khác).
- Trang/bài Việt, cặp và cài đặt được giữ để bật lại sau.

## B. Hoàn tác lần chuyển trạng thái gần nhất

*Tiếng Việt /vi/ → Hoàn tác lần chuyển gần nhất*. Nếu lần trước là "Tắt" thì bật lại (vẫn chạy kiểm tra trước khi bật); nếu là "Bật" thì tắt.

Khi bật mà kiểm tra sau khi bật không đạt, plugin **tự** quay về trạng thái trước và ghi "rollback" trong lịch sử.

## C. Vô hiệu hóa plugin

Làm A trước. Lưu ý giới hạn đã kiểm thử: khi plugin không chạy, WordPress không còn tách ngôn ngữ, nên
- trang Việt **đã xuất bản** vẫn mở được tại `/vi/…` bằng cơ chế trang con mặc định của WordPress (giao diện tiếng Anh, không có hreflang);
- bài viết Việt đã xuất bản có thể xuất hiện trong danh sách tiếng Anh (ví dụ widget "Bài mới").

Vì vậy, nếu cần ẩn hoàn toàn: chuyển các trang/bài Việt về **Bản nháp** (hoặc Riêng tư) trước khi vô hiệu hóa. Không xóa chúng.

## D. Gỡ plugin

Gỡ (Delete) chỉ xóa các option `rbhvi_enabled`, `rbhvi_history`, `rbhvi_settings`, `rbhvi_seed_log`. Giữ lại trang/bài Việt, cặp, term ngôn ngữ, trường Yoast và `rbhvi_registry` (để cài lại thì nhận lại đúng trang).

## E. Quay lại theme 1.2.0

Theme 1.3.0 không ghi dữ liệu mới. Dùng bản sao thư mục theme `wp-theme` trước khi cập nhật (WPVibe tạo `wp-theme-wpvibe-backup`) hoặc phiên bản ở commit `057df19` của repository. Khi quay lại 1.2.0, nút VI | EN và menu Việt không còn hiển thị (theme cũ không có điểm gắn); nên làm A trước.

## F. Khôi phục toàn bộ

Chỉ khi cần: dùng bản sao lưu toàn website của hosting. Việc này đưa cả cơ sở dữ liệu về thời điểm sao lưu (mọi thay đổi sau đó sẽ mất) — cần chủ website đồng ý.

## G. Chỉ bỏ bố cục trang chủ mới (1.1.0)

*Tiếng Việt /vi/ → 5. Cài đặt → Bố cục trang chủ /vi/ → Trang thường* → Lưu → xóa cache. `/vi/` trở lại bố cục trang thường của 1.0.0; nội dung, URL, cặp, SEO không đổi. Muốn quay hẳn về bản đã triển khai: cài lại `dist/previous/wp-theme-1.3.0.zip` và `dist/previous/rentbikehanoi-native-vi-1.0.0.zip`.
