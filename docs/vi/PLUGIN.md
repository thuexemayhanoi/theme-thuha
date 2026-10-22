# Plugin `rentbikehanoi-native-vi`

Mã nguồn: `plugins/rentbikehanoi-native-vi/` · Gói cài đặt: `dist/rentbikehanoi-native-vi-1.0.0.zip` · Yêu cầu: WordPress 6.5+, PHP 7.4+, theme Rentbike Hanoi 1.3.0 (để có nút VI | EN và menu Việt).

## Cấu trúc

| Tệp | Vai trò |
| --- | --- |
| `rentbikehanoi-native-vi.php` | Khai báo plugin, nạp module, `rbhvi_language_switcher()`, `rbhvi_current_language()` |
| `config/business.php` | **Nguồn duy nhất** của NAP, giờ mở cửa, bảng giá, phí gia hạn |
| `config/pages.php` | 11 trang Việt: đường dẫn, H1, nhãn menu, title/mô tả SEO, từ khóa chính, cặp Anh đề xuất |
| `content/*.txt`, `content/faq-items.php` | Nội dung Việt (định dạng soạn thảo đơn giản → block Gutenberg khi tạo nháp) |
| `includes/class-rbhvi-state.php` | Bật/tắt, lịch sử, hoàn tác, tự quay lại khi kiểm tra sau khi bật thất bại |
| `includes/class-rbhvi-preflight.php` | Kiểm tra trước khi bật + kiểm tra định tuyến sau khi bật |
| `includes/class-rbhvi-language.php` | Taxonomy ngôn ngữ, nhận biết ngôn ngữ, cặp Anh–Việt hợp lệ |
| `includes/class-rbhvi-router.php` | Định tuyến /vi/, permalink bài/chuyên mục Việt, chặn chuyển hướng sai ngôn ngữ |
| `includes/class-rbhvi-query.php` | Tách ngôn ngữ trong danh sách, bài trước/sau, chuyên mục |
| `includes/class-rbhvi-seo.php` | hreflang, bộ lọc Yoast, loại trừ sitemap, title/mô tả dự phòng |
| `includes/class-rbhvi-switcher.php` | Nút VI | EN |
| `includes/class-rbhvi-theme-integration.php` | Menu Việt, chuỗi giao diện, dữ liệu cửa hàng tiếng Việt cho theme |
| `includes/class-rbhvi-listing.php` | Danh sách bài Việt trên /vi/blog/ và phân trang |
| `includes/class-rbhvi-seeder.php` | Tạo 11 trang nháp, chạy lại an toàn |
| `includes/class-rbhvi-admin.php` | Trang quản trị tiếng Việt, hộp "Ngôn ngữ" trong trình soạn thảo, cột "Ngôn ngữ", ô "Chuyên mục tiếng Việt" |
| `uninstall.php` | Chỉ xóa cài đặt của plugin; giữ nội dung |

## Trang quản trị "Tiếng Việt /vi/" (quyền Quản trị viên)

1. **Trạng thái**: BẬT/TẮT; nút *Bật /vi/ (có kiểm tra trước)*, *Tắt /vi/*, *Hoàn tác lần chuyển gần nhất*; danh sách kiểm tra (đường dẫn tĩnh, xung đột slug `vi` và các trang con, trang trùng, rewrite rule chiếm /vi/, plugin SEO, plugin đa ngôn ngữ hoặc dịch tự động khác (Polylang, WPML, TranslatePress, GTranslate, Weglot — từ 1.1.0), phiên bản theme, trang chủ tĩnh, trạng thái trang chủ /vi/).
2. **Bộ trang tiếng Việt**: *Tạo bộ trang Việt (bản nháp)*, *Xác nhận các cặp Anh–Việt được đề xuất*, bảng trạng thái từng trang.
3. **Ghép cặp**: chọn trang Việt ↔ trang/bài Anh; bỏ ghép. Báo lỗi nếu trang Anh đã ghép với trang Việt khác.
4. **Kiểm tra nội dung**: thiếu tiêu đề SEO, mô tả, H1, cặp ngôn ngữ; nằm ngoài /vi/; trang pháp lý cần duyệt; liên kết tới trang Việt chưa xuất bản.
5. **Cài đặt sitemap**: "Chỉ đưa bài viết tiếng Việt đã ghép cặp vào sitemap" (mặc định BẬT).
6. **Lịch sử bật/tắt**.

Mọi nút dùng nonce + kiểm tra quyền; thông báo lỗi bằng tiếng Việt.

## Hành vi an toàn (đã kiểm thử)

- Kích hoạt: không tạo option, trang hay term nào; /vi/ TẮT; HTML tiếng Anh không đổi.
- Tạo bộ trang: chỉ tạo bản nháp, không ghi vào bài tiếng Anh; chạy lại không tạo trùng; trang lạ đã chiếm đường dẫn được báo "xung đột" và giữ nguyên.
- Bật: chạy kiểm tra; lỗi nghiêm trọng → không bật; kiểm tra sau khi bật thất bại → tự quay lại trạng thái trước (ghi "rollback" vào lịch sử).
- Tắt: URL /vi/ trả 404, sitemap bỏ /vi/, nút VI | EN ẩn, HTML tiếng Anh trở lại như trước khi có plugin.
- Khách xem trang không làm thay đổi cơ sở dữ liệu.

## Điểm mở rộng

- Theme action `rbh_header_actions` (nút được gắn ở đây), filter `rbh_language_switch_enabled`.
- Template tag `rbhvi_language_switcher()`; hàm `rbhvi_current_language()`.
- Vị trí menu `rbhvi_primary`, `rbhvi_footer` (Giao diện → Menu).

## Bổ sung ở 1.1.0 (chưa triển khai)

| Tệp | Vai trò |
| --- | --- |
| `includes/class-rbhvi-home.php` | Chọn bố cục trang chủ cho `/vi/`, chuẩn bị dữ liệu cho `template-parts/landing.php` của theme |
| `templates/home-vi.php` | Khung trang: header + phần landing của theme + footer |
| `config/home-vi.php` | Chữ tiếng Việt của trang chủ (chỉ dùng placeholder cho giá/NAP/phí) |

- Cài đặt mới trong *5. Cài đặt*: **Bố cục trang chủ /vi/** — "Giống trang chủ tiếng Anh" (mặc định) hoặc "Trang thường" (như 1.0.0).
- Vùng widget mới **Thanh bên bài viết tiếng Việt (/vi/)**: thay cho widget của "Article sidebar" trên mọi trang Việt (trống cho tới khi quản trị viên thêm widget tiếng Việt).
- Logo tùy chỉnh trên trang Việt dẫn về `/vi/`, chữ thay thế (alt) là tên tiếng Việt của cửa hàng.
- Nâng cấp từ 1.0.0: không cần chạy lại "Tạo bộ trang", không cần bật/tắt lại `/vi/`; cài đặt cũ được giữ.
