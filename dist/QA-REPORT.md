# Báo cáo QA — Theme 1.4.0 + plugin rentbikehanoi-native-vi 1.1.0

Ngày: 23/10/2026 · Nhánh: `feature/native-vietnamese-section` · Gói được tạo từ commit `b67ced672707b12c1fca989ca4a11ae5fde7a66e`

> **Chưa triển khai.** Người thực hiện chỉ có quyền GitHub, không có WordPress, hosting hay crawler. Mọi kiểm thử chạy trên WordPress cục bộ dùng một lần tại 127.0.0.1. Theo bản ghi `docs/vi/DEPLOYMENT-2026-10-23.md`, website đang chạy theme 1.3.0 + plugin 1.0.0 (không kiểm chứng lại được).

## 1. Cần cài gói nào

| Gói | SHA-256 | Thay cho |
| --- | --- | --- |
| `wp-theme-1.4.0.zip` (thư mục `wp-theme`) | `05a9b6e3f3213928a6c6b5c0119f57cdfaf0da5619b1c95d1f2e99b2dc6a83d6` | theme 1.3.0 |
| `rentbikehanoi-native-vi-1.1.0.zip` | `b618d35106ae2f051b408d0a556bad1411449f626f733cb8845e980eb07e789c` | plugin 1.0.0 |

Cần cả hai gói để `/vi/` có bố cục mới; thứ tự cài nào cũng an toàn. Không cần tạo lại trang, không cần bật/tắt lại `/vi/`, không sửa code hay hosting. Hướng dẫn: `docs/vi/UPDATE-1.4.0-1.1.0.md`. Gói đã triển khai trước đó (để hoàn tác) nằm ở `dist/previous/`.

## 2. Thay đổi

- **Trang chủ `/vi/` cùng bố cục trang chủ Anh:** ảnh lớn (dùng lại ảnh trang chủ Anh, giữ dòng ghi công ảnh), nút Gọi / Nhắn Zalo, giờ mở cửa, 4 liên kết nhanh, 3 thẻ xe (dùng lại ảnh Honda Vision / Wave / Cub 50cc của trang Anh, alt tiếng Việt), bảng giá tham khảo từ cấu hình khóa, 3 bước, 4 câu hỏi nhanh (không thêm FAQ schema), nội dung trang đang lưu trong mục thu gọn "Toàn bộ chi tiết ở một nơi", bài viết Việt mới (khi có).
- Phạm vi: chỉ trang quản lý "home" (production ID 3454). Theme thêm `template-parts/landing.php` (chỉ trình bày); `front-page.php`, CSS, JS không đổi. Plugin thêm `RBHVI_Home`, `templates/home-vi.php`, `config/home-vi.php`, cài đặt "Bố cục trang chủ /vi/".
- Sửa trên mọi trang Việt: logo dẫn về `/vi/` (trước đây về trang chủ Anh) và có alt tiếng Việt; widget thanh bên tiếng Anh ("Latest Posts") được thay bằng vùng widget tiếng Việt riêng; dải giờ/điện thoại trên điện thoại hiển thị như trang Anh (1.0.0 làm mất dải này).
- Kiểm tra trước khi bật `/vi/` chặn thêm GTranslate và Weglot.
- Không ghi cơ sở dữ liệu; không đổi `post_content`, ID, URL, cặp Anh–Việt, title, mô tả, canonical, hreflang, sitemap, schema.

## 3. Kiểm thử đã chạy và kết quả thực tế

Môi trường: PHP 8.3.6, Node 22.22.0, WordPress 7.1.3 + SQLite, Yoast SEO 28.7, Chromium (Playwright 1.56.1). Lệnh:
`RBH_WP_ROOT=… RBH_BASELINE_REF=main,e81fbd7 RBH_DEPLOYED_REF=e81fbd7 scripts/test-all.sh --integration --bootstrap --package --upgrade` → **ALL CHECKS PASSED (exit 0)**.

| Bộ kiểm thử | Kết quả |
| --- | --- |
| `node --check assets/js/assistant-seo-20261019.js` + `node tests/assistant.cjs` (AGENTS.md) | PASS |
| Kiểm tra repo: cú pháp, secret, không gọi API ngoài, NAP/giá chỉ ở cấu hình, **template/CSS/JS tiếng Anh giống hệt bản đã triển khai `e81fbd7`** | 22/22 |
| Nội dung, dữ liệu khóa, chữ trang chủ Việt (placeholder, không tuyên bố không có cơ sở, liên kết, mã ảnh trùng quy tắc theme) | 303/303 |
| WPCS cho plugin; PHPCompatibilityWP 7.4+ cho toàn repo | 0 lỗi / 0 lỗi |
| Tích hợp WordPress + Yoast cục bộ | 63/63 |
| Giao diện trình duyệt — header chữ | 19/19 |
| Giao diện trình duyệt — header có logo | 19/19 |
| Kiểm tra gói ZIP (nội dung, loại trừ, giống từng byte với commit, checksum, php -l) | 41/41 |
| Nâng cấp thật: cài ZIP 1.3.0/1.0.0 → tạo, ghép 9 cặp, xuất bản, bật `/vi/` → cài đè ZIP 1.4.0/1.1.0 | 9/9 |

Những gì các kiểm thử chứng minh (cục bộ):
- HTML tiếng Anh (22 URL: trang chủ, trang, bài, chuyên mục, phân trang, tìm kiếm, 404, feed, sitemap, REST search) **giống từng byte** với theme ở `main` (1.2.0) và ở `e81fbd7` (1.3.0) khi plugin tắt, và giống bản đã triển khai (theme 1.3.0 + plugin 1.0.0) khi plugin BẬT và đã có nội dung Việt xuất bản — chỉ khác chuỗi `?ver=` của tài nguyên.
- Nâng cấp tại chỗ không đổi cơ sở dữ liệu (ID, trạng thái, slug, nội dung, cặp, title Yoast, option); trang Anh không đổi; phần đầu (head) 11 trang Việt không đổi; chỉ `/vi/` chuyển sang bố cục mới và logo dẫn về `/vi/`; cài đặt cũ tự nhận bố cục mới và có thể chuyển về "Trang thường".
- `/vi/`: cùng thứ tự khối với trang chủ Anh; đúng một H1 (tiêu đề trang đang lưu); ảnh lớn và 3 ảnh xe là đúng tệp của trang Anh; bảng giá đúng dữ liệu khóa; liên kết nhanh/thẻ xe chỉ dẫn tới `/vi/`; nội dung đang lưu hiển thị đủ (số mục = số H2 + 1) và không bị sửa; không có FAQPage trên trang chủ; canonical/hreflang như trước.
- Trang Việt (11 trang, tìm kiếm, 404): không còn chữ giao diện tiếng Anh, widget tiếng Anh hay form tìm kiếm gửi về trang Anh; logo/thương hiệu dẫn về `/vi/`; trang Anh vẫn giữ widget và logo về `/`.
- Trình duyệt: `/vi/` ở 320, 360, 390, 768, 1280 px — không tràn ngang, không phần tử nào ra ngoài màn hình, ảnh tải được, số điện thoại không bị ngắt dòng, nút ≥ 44 px; logo → `/vi/`, nút VI | EN `/vi/` ↔ `/`; chế độ tối: tương phản chữ tiêu đề, đoạn dẫn, các bước và bảng giá ≥ 4,5:1; dải giờ/điện thoại trên điện thoại giống trang Anh; cùng các kiểm tra cũ (vị trí nút cạnh Sáng/Tối, bàn phím, trình đọc màn hình, Sáng/Tối được ghi nhớ, menu điện thoại).
- GTranslate đang bật → không cho bật `/vi/` (thông báo tiếng Việt).

## 4. Ảnh kiểm thử cục bộ (`dist/qa-screenshots/`)

Chụp trên site cục bộ sau khi nâng cấp từ ZIP. **Ảnh và logo là ảnh giả lập** (khối màu có chữ "test image/test logo"), không phải ảnh thật của website.

| Tệp | Nội dung |
| --- | --- |
| `vi-home-390-light.png`, `vi-home-390-dark.png` | `/vi/` trên điện thoại, sáng/tối (toàn trang) |
| `vi-home-1280-light.png`, `vi-home-1280-dark.png` | `/vi/` trên máy tính, sáng/tối (toàn trang) |
| `vi-home-320-top.png` | phần đầu `/vi/` ở 320 px |
| `vi-contact-360-top.png` | phần đầu `/vi/lien-he/` ở 360 px (logo, dải giờ/điện thoại) |
| `en-home-1280-light-unchanged.png`, `en-home-390-light-unchanged.png` | trang chủ Anh để so sánh (không đổi) |

## 5. Chưa kiểm chứng

- Chưa cài lên website; chưa thấy ảnh thật, nội dung thật đang lưu của trang 3454, cache LiteSpeed, cấu hình Yoast/WPCode thật.
- Tên tệp ảnh thật của trang chủ Anh phải chứa `Honda-Vision`, `Honda-Wave`, `Honda-Cub-50cc` (quy tắc sẵn có của theme); nếu không khớp, thẻ xe hiện không có ảnh.
- Dòng ghi công ảnh lấy nguyên từ trang Anh nên có thể là tiếng Anh.
- Chưa thử trên điện thoại thật, Safari, Firefox; chưa đo PageSpeed; chưa chạy Rich Results Test hay Search Console.
- Lần triển khai 23/10 ghi nhận không có PHP ở máy của người triển khai; bộ kiểm thử PHP/WordPress trong báo cáo này chỉ chạy ở môi trường cục bộ nói trên.

## 6. Kiểm tra bắt buộc sau khi cài (người có quyền WordPress)

Xem `docs/vi/UPDATE-1.4.0-1.1.0.md` mục "Các bước": sao lưu, WPVibe preview + chủ website duyệt theme, thay plugin bằng ZIP, xóa cache, kiểm tra `/vi/` (bố cục, một H1, ảnh, bảng giá), logo trang Việt → `/vi/`, nút VI | EN, trang chủ Anh không đổi (title, description, canonical), canonical/hreflang của `/vi/`, không tràn ngang ở 320–390 px, sáng/tối. Hoàn tác nhanh: cài đặt "Bố cục trang chủ /vi/ → Trang thường".

Báo cáo này không tuyên bố đã triển khai hay kiểm tra thành công trên website thật.
