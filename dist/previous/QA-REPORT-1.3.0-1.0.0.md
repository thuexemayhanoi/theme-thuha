# Báo cáo QA — Theme Rentbike Hanoi 1.3.0 + plugin rentbikehanoi-native-vi 1.0.0

Ngày: 22/10/2026 · Nhánh: `feature/native-vietnamese-section` · Gói được tạo từ commit `325a150d6efdbb5be39810bd06f2f872a6988336`

> **Chưa triển khai.** Không có gì trong báo cáo này đã được cài, kích hoạt, xuất bản hay kiểm tra trên https://rentbikehanoi.com. Người thực hiện không có quyền WordPress production, wp-admin, hosting, cơ sở dữ liệu hay Search Console. Mọi kiểm thử chạy trên WordPress cục bộ dùng một lần tại 127.0.0.1.

## 1. Artifact

| Tệp | SHA-256 | Ghi chú |
| --- | --- | --- |
| `wp-theme-1.3.0.zip` | `786b781dc99184cd34b259a5cc697f9c84fd58d551fb6a09c02bd52e2a6bc545` | 43 tệp, thư mục `wp-theme` (stylesheet đang hoạt động) |
| `rentbikehanoi-native-vi-1.0.0.zip` | `a0d472f2b2d3f58f68747a2c6f4ffaa9b3da951ddc2bae4bcbba947cf7da258e` | 30 tệp, thư mục `rentbikehanoi-native-vi` |
| `manifest.json` | — | SHA-256 từng tệp trong ZIP, commit nguồn, `deployed_to_production: false` |
| `SHA256SUMS` | — | `sha256sum -c SHA256SUMS` |
| `qa-screenshots/` | — | Ảnh chụp cục bộ (logo thử nghiệm, không phải logo thật) |

ZIP chỉ chứa tệp được git theo dõi, không có tests/docs/công cụ phát triển/secret; đóng gói lại cho kết quả giống từng byte (đã kiểm tra).

## 2. Đã thay đổi gì

- **Plugin mới** `rentbikehanoi-native-vi`: định tuyến `/vi/` (không rewrite flush, không sửa `.htaccess`), bật/tắt/hoàn tác có kiểm tra trước và tự quay lại, nhận biết ngôn ngữ, ghép cặp Anh–Việt (lưu phía Việt), tách ngôn ngữ trong mọi danh sách, hreflang, tích hợp Yoast qua bộ lọc công khai, loại trừ sitemap, nút VI | EN, trang quản trị tiếng Việt, hộp "Ngôn ngữ" trong trình soạn thảo, cờ "Chuyên mục tiếng Việt", tạo 11 trang Việt dạng nháp.
- **Nội dung Việt** viết riêng cho 11 URL (không dịch máy), số liệu kinh doanh lấy từ `config/business.php`.
- **Theme 1.3.0**: chỉ thêm điểm gắn trình bày (`rbh_header_actions` ngay sau nút Sáng/Tối, `rbh_home_url()`, `rbh_menu_items`, `rbh_menu_group`, `rbh_assistant_enabled`, `rbh_language_switch_enabled`) và tệp CSS mới `assets/css/language-switch.css` (chỉ nạp khi nút hiển thị). Không đổi nhãn menu tiếng Anh đã đóng băng, không in metadata/schema.
- Kiểm thử, CI (không deploy), đóng gói tái lập, tài liệu `docs/vi/`, AGENTS.md, README.md, CHANGELOG.md.

## 3. Kiểm thử đã chạy và kết quả thực tế

Môi trường: PHP 8.3.6, Node 22.22.0, WordPress 7.1.3 + SQLite integration, Yoast SEO 28.7 (Yoast-dist), Chromium (Playwright 1.56.1). Lệnh: `RBH_WP_ROOT=… RBH_BASELINE_REF=main scripts/test-all.sh --integration --bootstrap` → **ALL CHECKS PASSED**.

| Bộ kiểm thử | Kết quả |
| --- | --- |
| `node --check assets/js/assistant-seo-20261019.js` + `node tests/assistant.cjs` (bắt buộc theo AGENTS.md) | PASS |
| `tests/static/repo-check.mjs` — php -l 51 tệp, node --check, JSON hợp lệ, không secret, không gọi HTTP ra ngoài, không tài nguyên bên thứ ba, không cookie/JS đổi ngôn ngữ, không chuyển hướng IP/ngôn ngữ, NAP/giá chỉ ở config, không workflow deploy | 21/21 |
| `tests/static/content-test.php` — dữ liệu khóa, bảng giá, 11 trang (độ dài, không H1, không placeholder, không lorem, liên kết nội bộ ≥ 2, chỉ liên kết /vi/), title/mô tả/từ khóa/nhãn menu duy nhất, chống ăn thịt từ khóa, chặn tuyên bố không có cơ sở, FAQ, chuỗi giao diện | 276/276 |
| WPCS (WordPress, WordPress-Extra, Docs) cho plugin | 0 lỗi, 0 cảnh báo |
| PHPCompatibilityWP 7.4+ cho toàn repo | 0 lỗi |
| `tests/integration/run.mjs` trên WordPress + Yoast cục bộ | 52/52 |
| `tests/ui/language-switch.test.cjs` — header chữ | 11/11 |
| `tests/ui/language-switch.test.cjs` — header có logo (như production) | 11/11 |
| Cài **từ ZIP** bằng `wp theme install` / `wp plugin install` rồi chạy lại integration + UI | 51/51 và 11/11 |
| `tests/static/package-test.py` — nội dung ZIP, loại trừ, giống từng byte với commit, manifest, checksum, php -l trong ZIP | 37/37 |
| Đóng gói lại → SHA256SUMS giống hệt | PASS |

Những gì integration chứng minh (cục bộ):
- Theme 1.3.0 (plugin chưa bật) cho HTML tiếng Anh **giống hệt** theme 1.2.0 ở `main` trên 22 URL (trang, bài, chuyên mục, phân trang, tìm kiếm, 404, feed, sitemap, REST search), chỉ khác chuỗi `?ver=`.
- Kích hoạt plugin không tạo option/trang; tạo bộ trang chỉ tạo 11 bản nháp, chạy lại không tạo trùng; bảng posts/postmeta/menu tiếng Anh không đổi (hash).
- /vi/ trả 404 khi TẮT và khi trang còn là nháp; 200 khi BẬT và đã xuất bản.
- Mỗi trang Việt: `lang="vi"`, `og:locale=vi_VN`, đúng một title/mô tả/H1, canonical tự trỏ (không có canonical khi noindex), một khối JSON-LD Yoast, robots không trùng.
- hreflang en/vi/x-default hai chiều đúng cho 10 cặp; không hreflang cho trang/bài không ghép.
- FAQPage chỉ ở trang câu hỏi, mọi câu hỏi trong schema hiển thị trên trang; Article cho bài Việt; BreadcrumbList bắt đầu "Trang chủ" /vi/.
- Menu desktop/mobile/footer trên trang Việt chỉ có liên kết /vi/; trang Anh giữ đủ 19 nhãn đã đóng băng.
- Bài/chuyên mục Việt không lọt vào blog, chuyên mục, tìm kiếm, feed, REST search, widget bài mới, bài liên quan, bài trước/sau tiếng Anh, và ngược lại; bài Việt không mở được qua URL tiếng Anh; nháp/riêng tư/hẹn giờ 404 và không xuất hiện ở đâu.
- Phân trang /vi/blog/page/2/ tự canonical, trang 3 → 404; /vi/blog/ trống → noindex và không vào sitemap.
- Sitemap Yoast có trang Việt đã xuất bản và bài Việt đã ghép; không có bài chưa ghép (mặc định), không có gì khi TẮT.
- Xem trang không ghi gì vào cơ sở dữ liệu (hash bảng trước/sau khi tải 40+ URL).
- TẮT → HTML tiếng Anh giống trạng thái chưa có plugin (trừ `<lastmod>` trong sitemap index của Yoast); Hoàn tác → BẬT lại.
- Kiểm tra trước khi bật chặn xung đột slug `vi` và slug trang chủ Việt bị đổi; lỗi sau khi bật → tự quay về TẮT (ghi "rollback").
- Không có Yoast: title/mô tả dự phòng, một canonical của core, sitemap core có /vi/.
- Vô hiệu hóa plugin: trang Anh giữ mã HTTP, title, canonical, menu; nội dung Việt được giữ (xem giới hạn ở mục 4).
- Trang quản trị qua HTTP thật: hiển thị tiếng Việt, nút Tắt/Hoàn tác/Tạo bộ trang hoạt động; khách vãng lai, tài khoản Subscriber và nonce giả không đổi được trạng thái (403); không có PHP warning từ theme/plugin trong debug.log.

UI (320, 360, 390, 768, 1024, 1280 px × 13 trang Anh/Việt gồm trang chủ, trang tĩnh, bài, chuyên mục, tìm kiếm, 404): nút VI | EN hiển thị, ngay bên phải nút Sáng/Tối (khoảng cách 0–16px, thẳng hàng), ≥ 44×44px, không cuộn ngang, chiều cao header không đổi so với khi không có nút, CLS < 0,01; Tab đi từ Sáng/Tối sang VI | EN, có viền focus, Enter mở trang cặp; tên đọc cho trình đọc màn hình "View in English"/"Xem bằng tiếng Việt", thuộc tính `lang`/`hreflang` đúng; Sáng/Tối hoạt động và được nhớ trên cả hai ngôn ngữ; tương phản chữ ≥ 4,5:1 ở cả hai chế độ; menu mobile trên trang Việt mở và chỉ có liên kết /vi/.

## 4. Chưa kiểm tra được / giới hạn

Chi tiết: `docs/vi/LIMITATIONS.md`. Tóm tắt:
- Chưa có lần cài đặt nào trên staging/production; chưa thấy dữ liệu, plugin, cache LiteSpeed, cấu hình Yoast và logo thật.
- Chưa chạy PageSpeed, Rich Results Test, Search Console, thiết bị thật (Safari/Firefox).
- Hai trang pháp lý cần chủ website duyệt; phí gia hạn, "không giao xe tới Nội Bài", liên kết bản đồ lấy từ repo (không thuộc danh sách dữ liệu khóa của yêu cầu) — cần chủ website xác nhận lại.
- Khi vô hiệu hóa plugin, WordPress không còn tách ngôn ngữ: trang Việt đã xuất bản vẫn mở được tại /vi/ và bài Việt có thể vào widget bài mới tiếng Anh → TẮT /vi/ và chuyển nội dung Việt về nháp trước khi vô hiệu hóa (`docs/vi/ROLLBACK.md`).
- Các tệp theme cũ chưa đạt WPCS (có sẵn từ 1.2.0); phần sửa theo phong cách sẵn có, thêm 5 cảnh báo cùng loại (lệnh viết trên một dòng).
- Slug không bị xung đột trong repo; không cần đổi slug Việt nào so với yêu cầu.

## 5. Hướng dẫn cho AI có quyền WordPress (staging)

Làm theo `docs/vi/INSTALL-STAGING.md`. Tóm tắt: (1) xác nhận bản sao lưu gần nhất; (2) staging; (3) cập nhật theme bằng ZIP qua WPVibe draft/preview (thư mục `wp-theme`, không kích hoạt `-wpvibe-draft`); (4) tải ZIP plugin, kích hoạt — kỳ vọng không có gì thay đổi ở trang Anh; (5) *Tiếng Việt /vi/* → xử lý dòng đỏ → *Tạo bộ trang Việt (bản nháp)*; (6) chủ website đọc và duyệt nội dung, nhất là trang pháp lý; (7) xác nhận cặp (kiểm tra cặp Điều khoản ↔ "Rental Terms"); (8) xuất bản 11 trang cùng lúc; (9) *Bật /vi/*; (10) xóa cache; (11) chạy danh sách kiểm tra.

## 6. Kiểm tra bắt buộc trước production

- Bản sao lưu toàn website mới và bản sao thư mục theme.
- Staging đạt toàn bộ danh sách trong `docs/vi/INSTALL-STAGING.md` mục 3.
- Trang chủ tiếng Anh: title, description, og:title, og:site_name không đổi; 19 nhãn menu + footer không đổi; mọi URL tiếng Anh cùng mã HTTP và canonical.
- 11 URL Việt: 200, lang vi, một title/canonical/mô tả/H1, hreflang hai chiều, FAQPage hợp lệ, sitemap có URL Việt, không có nháp.
- Nút VI | EN cạnh Sáng/Tối trên điện thoại và máy tính; Sáng/Tối hoạt động.
- Chủ website đồng ý bằng văn bản cho việc xuất bản.
- Sau production: gửi lại sitemap trong Search Console, theo dõi lập chỉ mục và hreflang.

Báo cáo này không tuyên bố đã triển khai hay kiểm tra thành công trên website thật.
