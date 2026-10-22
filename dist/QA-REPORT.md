# Báo cáo QA — Theme 1.4.0 + plugin rentbikehanoi-native-vi 1.1.1

Ngày: 23/10/2026 · Nhánh: `feature/native-vietnamese-section` · Gói được tạo từ commit `9c30129e828046f48cd552db6fedae6ba086d156`

> **Chưa triển khai.** Người thực hiện chỉ có quyền GitHub, không có WordPress, hosting hay crawler. Mọi kiểm thử chạy trên WordPress cục bộ dùng một lần tại 127.0.0.1. Theo bản ghi `docs/vi/DEPLOYMENT-2026-10-23.md`, website đang chạy theme 1.3.0 + plugin 1.0.0 (không kiểm chứng lại được).

## 1. Cần cài gói nào

| Gói | SHA-256 | Thay cho |
| --- | --- | --- |
| `wp-theme-1.4.0.zip` (thư mục `wp-theme`) | `05a9b6e3f3213928a6c6b5c0119f57cdfaf0da5619b1c95d1f2e99b2dc6a83d6` | theme 1.3.0 |
| `rentbikehanoi-native-vi-1.1.1.zip` | `a1e78305ae7cbe29ae1ce1b51a7aab76598be371e80001d588c2f7bd823c4bde` | plugin 1.0.0 |

- Plugin 1.1.1 thay cho 1.1.0. Bản 1.1.0 chưa từng được cài; gói của nó đã bỏ khỏi `dist/`.
- Theme 1.4.0 không đổi so với bản đóng gói trước: cùng tệp, cùng SHA-256.
- Cần cả hai gói để `/vi/` có bố cục mới. Cài theo thứ tự nào cũng an toàn.
- Không cần tạo lại trang, không cần bật/tắt lại `/vi/`, không sửa code hay hosting.
- Hướng dẫn: `docs/vi/UPDATE-1.4.0-1.1.1.md`.
- Gói đã triển khai trước đó (để hoàn tác) nằm ở `dist/previous/`.

## 2. Thay đổi

**Trang chủ `/vi/` cùng bố cục trang chủ Anh**
- Ảnh lớn: dùng lại ảnh trang chủ Anh, giữ dòng ghi công ảnh.
- Nút Gọi / Nhắn Zalo, giờ mở cửa, 4 liên kết nhanh.
- 3 thẻ xe: dùng lại ảnh Honda Vision / Wave / Cub 50cc của trang Anh, alt tiếng Việt.
- Bảng giá tham khảo lấy từ cấu hình khóa.
- 3 bước thuê và 4 câu hỏi nhanh (không thêm FAQ schema).
- Nội dung trang đang lưu nằm trong mục thu gọn "Toàn bộ chi tiết ở một nơi".
- Bài viết Việt mới (khi có).

**Phạm vi trang chủ:** chỉ trang quản lý "home" (production ID 3454).
- Theme thêm `template-parts/landing.php` (chỉ trình bày). `front-page.php`, CSS, JS không đổi.
- Plugin thêm `RBHVI_Home`, `templates/home-vi.php`, `config/home-vi.php` và cài đặt "Bố cục trang chủ /vi/".

**Sửa trên mọi trang Việt (từ 1.1.0)**
- Logo dẫn về `/vi/` và có alt tiếng Việt.
- Widget thanh bên tiếng Anh ("Latest Posts") được thay bằng vùng widget tiếng Việt riêng.
- Dải giờ/điện thoại trên điện thoại hiển thị như trang Anh.

**Mới ở 1.1.1: chữ hệ thống tiếng Việt**
- Tìm kiếm, 404 và chuyên mục Việt: title, og:title, twitter:title, tên WebPage trong schema và breadcrumb nay là tiếng Việt. Ví dụ:
  - "Kết quả tìm kiếm cho “xe” | Thuê xe máy Nguyễn Hà"
  - "Không tìm thấy trang | Thuê xe máy Nguyễn Hà"
  - "Kinh nghiệm – Trang 2 | Thuê xe máy Nguyễn Hà"
  - breadcrumb "Trang chủ » Kết quả tìm kiếm: “xe” » Trang 2"
- Trước đây các chỗ này là chữ mặc định tiếng Anh mà Yoast lưu trong cài đặt ("You searched for…", "Page not found", "… Archives", "Page 2").
- Từ khóa tìm kiếm được mã hóa giống hệt cách Yoast làm ở trang Anh.
- Form bình luận, danh sách bình luận và điều hướng bài viết trên bài Việt hiển thị tiếng Việt khi WordPress chưa có bản dịch tiếng Việt. Bản dịch đã cài được ưu tiên.
- Trang Anh không đổi.

**Khác**
- Bước kiểm tra trước khi bật `/vi/` chặn thêm GTranslate và Weglot.
- Không ghi cơ sở dữ liệu. Không đổi `post_content`, ID, URL, cặp Anh–Việt, title/mô tả đã lưu, canonical, hreflang, sitemap.

## 3. Kiểm thử đã chạy và kết quả thực tế

Môi trường: PHP 8.3.6, Node 22.22.0, WordPress 7.1.3 + SQLite, Yoast SEO 28.7, Chromium (Playwright 1.56.1).

Lệnh: `RBH_WP_ROOT=… RBH_BASELINE_REF=main,e81fbd7 RBH_DEPLOYED_REF=e81fbd7 scripts/test-all.sh --integration --bootstrap --package --upgrade`

Kết quả: **ALL CHECKS PASSED (exit 0), 0 kiểm tra thất bại**.

| Bộ kiểm thử | Kết quả |
| --- | --- |
| `node --check assets/js/assistant-seo-20261019.js` + `node tests/assistant.cjs` (AGENTS.md) | PASS |
| Kiểm tra repo: cú pháp, secret, không gọi API ngoài, NAP/giá chỉ ở cấu hình, **template/CSS/JS tiếng Anh giống hệt bản đã triển khai `e81fbd7`** | 22/22 |
| Nội dung, dữ liệu khóa, chữ trang chủ Việt, bảng chữ dịch giữ nguyên `%s` và thẻ HTML | 305/305 |
| WPCS cho plugin; PHPCompatibilityWP 7.4+ cho toàn repo | 0 lỗi / 0 lỗi |
| Tích hợp WordPress + Yoast cục bộ | 65/65 |
| Giao diện trình duyệt — header chữ | 19/19 |
| Giao diện trình duyệt — header có logo | 19/19 |
| Kiểm tra gói ZIP (nội dung, loại trừ, giống từng byte với commit, checksum, php -l) | 43/43 |
| Nâng cấp thật: cài ZIP 1.3.0/1.0.0 → tạo trang, ghép 9 cặp, xuất bản, bật `/vi/` → cài đè ZIP 1.4.0/1.1.1 | 10/10 |

Những gì các kiểm thử chứng minh (cục bộ):

**Trang Anh**
- HTML của 22 URL tiếng Anh **giống từng byte** với theme ở `main` (1.2.0) và ở `e81fbd7` (1.3.0) khi plugin tắt. Gồm: trang chủ, trang, bài, chuyên mục, phân trang, tìm kiếm, 404, feed, sitemap, REST search.
- Khi plugin BẬT và đã có nội dung Việt xuất bản, HTML tiếng Anh giống bản đã triển khai (theme 1.3.0 + plugin 1.0.0).
- Chỉ khác chuỗi `?ver=` của tài nguyên.

**Nâng cấp tại chỗ**
- Không đổi cơ sở dữ liệu (ID, trạng thái, slug, nội dung, cặp, title Yoast, option). Trang Anh không đổi.
- Phần đầu (head) của 11 trang Việt không đổi.
- Chỉ những chỗ sau đổi:
  - `/vi/` chuyển sang bố cục mới;
  - logo dẫn về `/vi/`;
  - tiêu đề trang tìm kiếm/404 Việt chuyển từ tiếng Anh sang tiếng Việt.
- Cài đặt cũ tự nhận bố cục mới và có thể chuyển về "Trang thường".

**Trang chủ `/vi/`**
- Cùng thứ tự khối với trang chủ Anh; đúng một H1 (tiêu đề trang đang lưu).
- Ảnh lớn và 3 ảnh xe là đúng tệp của trang Anh; bảng giá đúng dữ liệu khóa.
- Liên kết nhanh và thẻ xe chỉ dẫn tới `/vi/`.
- Nội dung đang lưu hiển thị đủ và không bị sửa.
- Không có FAQPage trên trang chủ; canonical/hreflang như trước.

**Chữ hệ thống trên trang Việt**
- Title, og:title, twitter:title, tên WebPage và breadcrumb của tìm kiếm (cả trang 2), 404, chuyên mục và chuyên mục trang 2 là tiếng Việt, không còn "You searched for", "Page not found", "Archives", "Page N of M".
- Từ khóa có ký tự HTML được mã hóa đúng như trang Anh, không bao giờ in thô, và JSON-LD vẫn hợp lệ.
- Form bình luận tiếng Việt. Bản dịch tiếng Việt đã cài (giả lập) được ưu tiên hơn chữ dự phòng.
- Trang Anh `/?s=rental`, 404 và form bình luận tiếng Anh giữ nguyên.

**Trang Việt nói chung (11 trang, tìm kiếm, 404)**
- Không còn chữ giao diện tiếng Anh, widget tiếng Anh hay form tìm kiếm gửi về trang Anh.
- Logo/thương hiệu dẫn về `/vi/`. Trang Anh vẫn giữ widget và logo về `/`.

**Trình duyệt, `/vi/` ở 320, 360, 390, 768, 1280 px**
- Không tràn ngang, không phần tử nào ra ngoài màn hình.
- Ảnh tải được; số điện thoại không bị ngắt dòng; nút ≥ 44 px.
- Logo → `/vi/`; nút VI | EN: `/vi/` ↔ `/`.
- Chế độ tối: tương phản chữ ≥ 4,5:1.
- Cùng các kiểm tra cũ:
  - vị trí nút cạnh Sáng/Tối;
  - bàn phím và trình đọc màn hình;
  - Sáng/Tối được ghi nhớ;
  - menu điện thoại.

**Bước kiểm tra trước khi bật:** khi GTranslate đang bật thì không cho bật `/vi/` (có thông báo tiếng Việt).

## 4. Ảnh kiểm thử cục bộ (`dist/qa-screenshots/`)

- Ảnh chụp trên site cục bộ:
  - sau khi nâng cấp từ ZIP lên 1.4.0 + 1.1.1;
  - riêng ảnh form bình luận lấy từ site kiểm thử tích hợp (cùng mã), vì site nâng cấp chưa có bài Việt.
- 8 ảnh có từ bản trước được chụp lại trên 1.1.1 và giống hệt từng byte, nên giữ nguyên tệp.
- **Ảnh và logo là ảnh giả lập** (khối màu có chữ "test image/test logo"), không phải ảnh thật của website.

| Tệp | Nội dung |
| --- | --- |
| `vi-home-390-light.png`, `vi-home-390-dark.png` | `/vi/` trên điện thoại, sáng/tối (toàn trang) |
| `vi-home-1280-light.png`, `vi-home-1280-dark.png` | `/vi/` trên máy tính, sáng/tối (toàn trang) |
| `vi-home-320-top.png` | phần đầu `/vi/` ở 320 px |
| `vi-contact-360-top.png` | phần đầu `/vi/lien-he/` ở 360 px (logo, dải giờ/điện thoại) |
| `vi-search-390-top.png` | phần đầu `/vi/?s=xe` ở 390 px (breadcrumb "Kết quả tìm kiếm: “xe”") |
| `vi-post-comment-form-390.png` | form bình luận tiếng Việt trên một bài Việt thử nghiệm, 390 px |
| `en-home-1280-light-unchanged.png`, `en-home-390-light-unchanged.png` | trang chủ Anh để so sánh (không đổi) |

## 5. Chưa kiểm chứng

- Chưa cài lên website. Chưa thấy:
  - ảnh thật và nội dung thật đang lưu của trang 3454;
  - cache LiteSpeed;
  - cấu hình Yoast/WPCode thật;
  - gói ngôn ngữ tiếng Việt của WordPress trên website.
- Tên tệp ảnh thật của trang chủ Anh phải chứa `Honda-Vision`, `Honda-Wave`, `Honda-Cub-50cc` (quy tắc sẵn có của theme). Nếu không khớp, thẻ xe hiện không có ảnh.
- Dòng ghi công ảnh lấy nguyên từ trang Anh nên có thể là tiếng Anh.
- Plugin khác trên website thật (Contact Form 7, LuckyWP TOC, Moto QuickCall…) có thể in chữ tiếng Anh trên trang Việt; chưa kiểm tra.
- Bài Việt chưa có title riêng trong Yoast có title mặc định kết thúc bằng Site Name tiếng Anh. Site Name đã đóng băng, nên cách xử lý là đặt title tiếng Việt cho từng bài.
- Thẻ RSS trong `<head>` (không hiển thị) vẫn có tiêu đề tiếng Anh như bản 1.0.0.
- Bong bóng trợ lý tiếng Anh không có trên trang Việt. Đây là chủ ý, vì trợ lý chỉ có tiếng Anh.
- Chưa thử:
  - điện thoại thật, Safari, Firefox;
  - đo PageSpeed;
  - Rich Results Test, Search Console.
- Lần triển khai 23/10 ghi nhận máy của người triển khai không có PHP. Bộ kiểm thử PHP/WordPress trong báo cáo này chỉ chạy ở môi trường cục bộ nói trên.

## 6. Kiểm tra bắt buộc sau khi cài (người có quyền WordPress)

Xem `docs/vi/UPDATE-1.4.0-1.1.1.md`, mục "Các bước":
1. Sao lưu.
2. Theme: WPVibe preview, chủ website duyệt.
3. Thay plugin bằng ZIP.
4. Xóa cache.
5. Kiểm tra:
   - `/vi/`: bố cục, một H1, ảnh, bảng giá;
   - logo trang Việt → `/vi/`; nút VI | EN;
   - tiêu đề tab của `/vi/?s=xe` và một trang 404 Việt;
   - trang chủ Anh không đổi (title, description, canonical);
   - canonical/hreflang của `/vi/`;
   - không tràn ngang ở 320–390 px;
   - sáng/tối.

Hoàn tác nhanh: cài đặt "Bố cục trang chủ /vi/ → Trang thường".

Báo cáo này không tuyên bố đã triển khai hay kiểm tra thành công trên website thật.
