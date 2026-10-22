# Cập nhật: theme 1.4.0 + plugin rentbikehanoi-native-vi 1.1.1

Trạng thái: **mã, kiểm thử và gói ZIP — chưa cài lên website.** Người viết mã chỉ có quyền GitHub; theo bản ghi `DEPLOYMENT-2026-10-23.md`, website đang chạy theme 1.3.0 + plugin 1.0.0 (chưa kiểm chứng lại). Plugin 1.1.1 thay cho bản 1.1.0 chưa từng được cài; cài thẳng từ 1.0.0 lên 1.1.1.

## Bản cập nhật làm gì

- Trang chủ tiếng Việt `/vi/` (ID 3454) có **cùng bố cục với trang chủ tiếng Anh**: ảnh lớn đầu trang (dùng lại ảnh đang có của trang chủ Anh), nút Gọi / Nhắn Zalo, các liên kết nhanh, 3 thẻ xe (dùng lại ảnh xe đang có), bảng giá tham khảo, 3 bước thuê, 4 câu hỏi nhanh và toàn bộ nội dung trang đang lưu trong mục "Toàn bộ chi tiết ở một nơi" (thu gọn như trang Anh).
- Logo trên mọi trang Việt dẫn về `/vi/` (trước đây về trang chủ Anh) và có tên tiếng Việt cho trình đọc màn hình.
- Trang Việt không còn hiện widget tiếng Anh của thanh bên (ví dụ tiêu đề "Latest Posts"); có vùng widget riêng "Thanh bên bài viết tiếng Việt (/vi/)".
- Trên điện thoại, dải màu tối đầu trang (giờ mở cửa, số điện thoại) nay hiện trên trang Việt giống trang Anh (bản 1.0.0 bị mất dải này khi ẩn trợ lý tiếng Anh).
- Trang tìm kiếm, trang 404 và trang chuyên mục tiếng Việt có tiêu đề (thẻ title, og:title, twitter:title, tên trang trong schema) và breadcrumb tiếng Việt, ví dụ "Kết quả tìm kiếm cho “xe” | Thuê xe máy Nguyễn Hà", "Không tìm thấy trang | Thuê xe máy Nguyễn Hà", "Kinh nghiệm – Trang 2 | Thuê xe máy Nguyễn Hà". Trước đây là chữ mặc định tiếng Anh của Yoast ("You searched for…", "Page not found", "… Archives", "Page 2").
- Form bình luận, danh sách bình luận và điều hướng bài viết trên bài Việt hiển thị tiếng Việt ("Để lại bình luận", "Gửi bình luận"…), chỉ khi WordPress chưa có bản dịch tiếng Việt cho chuỗi đó (bản dịch đã cài luôn được ưu tiên).
- Không đổi: trang chủ và nội dung tiếng Anh, nhãn menu Anh, SEO đã đóng băng, 11 URL Việt, ID trang, nội dung đã lưu, cặp Anh–Việt, canonical, hreflang, sitemap, schema. Không ghi gì vào cơ sở dữ liệu khi cài.

## Cần cài gói nào

| Gói | Tệp | Cài vào |
| --- | --- | --- |
| Theme | `dist/wp-theme-1.4.0.zip` (SHA-256 `05a9b6e3…6a83d6`) | thư mục theme đang hoạt động `wp-theme` (thay bản 1.3.0) |
| Plugin | `dist/rentbikehanoi-native-vi-1.1.1.zip` (SHA-256 `a1e78305…823c4bde`) | thay plugin `rentbikehanoi-native-vi` 1.0.0 |

Cần **cả hai** để có bố cục mới. Thứ tự nào cũng an toàn: chỉ có theme mới → không có gì thay đổi; chỉ có plugin mới → `/vi/` giữ bố cục trang thường (và đã có các sửa lỗi logo, thanh bên, tiêu đề, bình luận). Mã kiểm tra: `sha256sum -c dist/SHA256SUMS`.

## Các bước (người có quyền WordPress; không cần sửa code hay hosting)

1. Xác nhận có bản sao lưu gần nhất (theo quy trình WPVibe).
2. Theme: cập nhật `wp-theme` bằng `wp-theme-1.4.0.zip` qua quy trình WPVibe draft → xem trước → chủ website duyệt → xuất bản. Không kích hoạt thư mục `*-wpvibe-draft`.
3. Plugin: *Plugins → Thêm mới → Tải plugin lên* → chọn `rentbikehanoi-native-vi-1.1.1.zip` → *Thay thế bản hiện tại bằng bản tải lên*. Plugin vẫn kích hoạt; `/vi/` vẫn BẬT; không cần tạo lại trang, không bấm "Tạo bộ trang".
4. Xóa cache LiteSpeed.
5. Kiểm tra nhanh (ẩn danh, điện thoại và máy tính, sáng và tối):
   - `/vi/` hiện ảnh lớn, 3 thẻ xe có ảnh, bảng giá, 3 bước, câu hỏi nhanh, mục "Toàn bộ chi tiết"; chỉ một tiêu đề lớn (H1).
   - Bấm logo trên một trang Việt → về `/vi/`. Nút VI | EN cạnh nút sáng/tối: `/vi/` ↔ `/`.
   - `/vi/?s=xe` có tiêu đề tab "Kết quả tìm kiếm cho “xe” | Thuê xe máy Nguyễn Hà"; `/vi/khong-co-trang-nay/` có "Không tìm thấy trang | Thuê xe máy Nguyễn Hà".
   - Trang chủ Anh `/` trông như trước; title, description, canonical không đổi. `/?s=rental` vẫn là tiêu đề tiếng Anh như cũ.
   - Mã nguồn `/vi/` vẫn có một canonical `/vi/` và 3 thẻ hreflang (en, vi, x-default).
   - Không cuộn ngang ở 320–390 px.
6. (Tùy chọn) Thêm widget tiếng Việt vào *Giao diện → Tiện ích → Thanh bên bài viết tiếng Việt (/vi/)*.

## Hoàn tác

- Chỉ muốn bỏ bố cục mới: *Tiếng Việt /vi/ → 5. Cài đặt → Bố cục trang chủ /vi/ → Trang thường* → Lưu → xóa cache. Không mất nội dung.
- Quay về phiên bản cũ: cài lại `dist/previous/wp-theme-1.3.0.zip` và `dist/previous/rentbikehanoi-native-vi-1.0.0.zip` (đúng các gói đã triển khai ngày 23/10, cùng mã SHA-256 ghi trong `DEPLOYMENT-2026-10-23.md`).
- Không vô hiệu hóa plugin khi chưa đọc `ROLLBACK.md`.

## Chưa kiểm chứng

Ảnh thật của trang chủ Anh trên production (tên tệp phải chứa `Honda-Vision`, `Honda-Wave`, `Honda-Cub-50cc` như mã theme đang dùng; nếu không tìm thấy, thẻ xe vẫn hiện nhưng không có ảnh), cấu hình tiêu đề thật trong Yoast (nếu chuyên mục Việt đã có title riêng trong Yoast thì title đó được giữ), gói ngôn ngữ tiếng Việt của WordPress trên website, cache LiteSpeed, thiết bị thật, Safari/Firefox, PageSpeed. Xem `LIMITATIONS.md` và `dist/QA-REPORT.md`.
