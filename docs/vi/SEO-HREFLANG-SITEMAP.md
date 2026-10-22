# SEO song ngữ: canonical, hreflang, sitemap, schema

## Ai in thẻ nào

| Thẻ / dữ liệu | Có Yoast (production) | Không có plugin SEO |
| --- | --- | --- |
| `<title>` | Yoast (trường `_yoast_wpseo_title` do bộ trang tạo sẵn) | WordPress core + `pre_get_document_title` của plugin |
| meta description | Yoast | Plugin (chỉ trang Việt) |
| canonical | Yoast, tự trỏ | WordPress core, tự trỏ |
| robots | Yoast | WordPress core (`wp_robots`) |
| Open Graph | Yoast; plugin chỉ đổi `og:locale` → `vi_VN` | không có |
| JSON-LD | Yoast (một graph duy nhất) | không có |
| hreflang | **Plugin** (Yoast không có tính năng này) | Plugin |
| Sitemap | Yoast (`sitemap_index.xml`) | WordPress core (`wp-sitemap.xml`) |

Kiểm thử tự động xác nhận: mỗi trang có đúng một title, tối đa một canonical, tối đa một robots, một khối JSON-LD; theme không tự in schema; bộ lọc `rbh_head_buffer_end` cũ của theme vẫn chỉ gỡ JSON-LD LocalBusiness không phải của Yoast.

## Canonical

- Trang Anh: Yoast giữ canonical như cũ (đã so sánh HTML với theme 1.2.0: giống hệt).
- Trang Việt: canonical tự trỏ về chính URL `/vi/…`. **Không bao giờ** canonical trang Việt sang trang Anh.
- `/vi/blog/page/N/`: canonical tự trỏ về trang phân trang đó; title thêm " – Trang N"; trang vượt quá số trang trả 404.
- `/vi/blog/` khi chưa có bài: `noindex, follow` (Yoast khi đó không in canonical — đúng hành vi của Yoast).

## hreflang

In khi **đồng thời**: /vi/ đang BẬT; trang hiện tại có cặp đã xác nhận; cả hai trang đã xuất bản, không có mật khẩu, không đặt noindex trong Yoast; cặp là một-một; không phải trang phân trang.

```html
<link rel="alternate" hreflang="en" href="https://rentbikehanoi.com/about-us/" />
<link rel="alternate" hreflang="vi" href="https://rentbikehanoi.com/vi/gioi-thieu/" />
<link rel="alternate" hreflang="x-default" href="https://rentbikehanoi.com/about-us/" />
```

Cùng một bộ ba được in trên cả trang Anh và trang Việt (hai chiều). Trang không có cặp: không có hreflang.

## Cặp đề xuất cho 11 trang Việt

| Trang Việt | Trang Anh đề xuất | Ghi chú |
| --- | --- | --- |
| `/vi/` | trang chủ `/` | |
| `/vi/gioi-thieu/` | `/about-us/` | |
| `/vi/bang-gia-thue-xe-may-ha-noi/` | `/motorbike-rental-prices-in-hanoi/` | |
| `/vi/thue-xe-ga-ha-noi/` | `/hanoi-scooter-rental/` | |
| `/vi/thu-tuc-thue-xe-may-ha-noi/` | `/motorbike-renting-in-hanoi-requirements/` | |
| `/vi/giao-nhan-xe-may-ha-noi/` | `/motorbike-rental-hanoi-delivery-and-pickup-guide-complete-service-overview-2025/` | |
| `/vi/cau-hoi-thuong-gap/` | `/faq/` | |
| `/vi/lien-he/` | `/contact-us/` | |
| `/vi/blog/` | — | Blog Anh là chuyên mục; không ghép |
| `/vi/chinh-sach-bao-mat/` | `/privacy-police/` | slug tiếng Anh giữ nguyên (có lỗi chính tả sẵn) |
| `/vi/dieu-khoan-su-dung/` | `/terms-of-use/` | trang Anh mang nhãn "Rental Terms": xác nhận cùng phạm vi trước khi ghép |

Đề xuất chỉ thành hreflang sau khi quản trị viên bấm xác nhận.

## Sitemap

Không có sitemap thứ hai. Sitemap Yoast tự lấy URL `/vi/` từ permalink. Plugin loại khỏi sitemap:
- mọi trang/bài/chuyên mục Việt khi /vi/ TẮT;
- nội dung Việt nằm ngoài `/vi/` (không hợp lệ);
- bài viết Việt chưa ghép cặp khi bật cài đặt "chỉ bài đã ghép" (mặc định BẬT — đáp ứng yêu cầu "bài chưa ghép không vào sitemap"; trang tĩnh /vi/ đã xuất bản luôn có trong sitemap);
- `/vi/blog/` khi chưa có bài.
Bản nháp, riêng tư, hẹn giờ không bao giờ vào sitemap (Yoast/core không liệt kê). Yoast sitemap cache được xóa khi bật/tắt/đổi cài đặt.

## Schema (Yoast)

Trang Việt có WebPage, WebSite, BreadcrumbList (trừ `/vi/` chỉ có một mục), `inLanguage: "vi"`; bài viết Việt có Article; `/vi/cau-hoi-thuong-gap/` có FAQPage được tạo **từ khối FAQ của Yoast hiển thị trên trang** (đã kiểm thử: mọi câu hỏi trong schema đều xuất hiện trên trang). Organization/LocalBusiness do cấu hình Yoast (và đoạn mã SEO Engine 06 nếu đang bật) quyết định — plugin không thêm graph thứ hai. Tên website trong schema giữ nguyên "Site Name" đã đóng băng.

## Breadcrumb

Bộ lọc `wpseo_breadcrumb_links` thay mục đầu bằng "Trang chủ" → `/vi/`, bỏ mục trùng trang gốc `/vi/`, thêm "Blog" cho bài viết Việt. BreadcrumbList JSON-LD của Yoast dùng cùng danh sách này. Từ plugin 1.1.1, mục cuối của trang tìm kiếm Việt là "Kết quả tìm kiếm: “…”", của trang phân trang là "Trang N", của trang 404 (nếu template in breadcrumb) là "Không tìm thấy trang" — thay cho chữ tiếng Anh Yoast lưu trong cài đặt.

## Tiêu đề trang tìm kiếm, 404 và chuyên mục (plugin 1.1.1)

Yoast lưu mẫu tiêu đề mặc định bằng tiếng Anh ("You searched for %%searchphrase%%", "Page not found", "%%term_title%% Archives") và dùng chung cho mọi ngôn ngữ. Trên URL `/vi/` plugin thay bằng tiếng Việt qua `wpseo_title`, `wpseo_opengraph_title`, `wpseo_twitter_title` (Yoast chỉ in thẻ này trên trang kết quả tìm kiếm) và `wpseo_schema_webpage` (tên WebPage). Từ khóa tìm kiếm được mã hóa HTML giống hệt cách Yoast làm với tiêu đề tiếng Anh:

| Trang | Tiêu đề |
| --- | --- |
| `/vi/?s=xe` | Kết quả tìm kiếm cho “xe” \| Thuê xe máy Nguyễn Hà |
| `/vi/<không tồn tại>/` | Không tìm thấy trang \| Thuê xe máy Nguyễn Hà |
| `/vi/chuyen-muc/kinh-nghiem/` | Kinh nghiệm \| Thuê xe máy Nguyễn Hà |
| `/vi/chuyen-muc/kinh-nghiem/page/2/` | Kinh nghiệm – Trang 2 \| Thuê xe máy Nguyễn Hà |
| `/vi/page/2/?s=xe` | Kết quả tìm kiếm cho “xe” – Trang 2 \| Thuê xe máy Nguyễn Hà (breadcrumb: Trang chủ » Kết quả tìm kiếm: “xe” » Trang 2) |

Tên cửa hàng lấy từ `config/business.php`. Chuyên mục Việt có title riêng trong Yoast → giữ nguyên title đó. Trang Anh (`/?s=…`, 404, chuyên mục Anh) và "Site Name" đã đóng băng không đổi. Bài/trang Việt vẫn dùng title đã lưu trong Yoast; bài Việt chưa có title riêng sẽ nhận mẫu mặc định của Yoast (kết thúc bằng Site Name tiếng Anh) — hãy đặt title tiếng Việt cho từng bài (xem `AI-WRITER.md`).

## Tìm kiếm nội bộ

`/vi/?s=…` chỉ tìm nội dung Việt và có `noindex` (Yoast/core). `/?s=…` chỉ tìm nội dung Anh. REST `/wp/v2/search` công khai (trợ lý website tiếng Anh) chỉ trả nội dung Anh.

## Tránh trùng lặp và ăn thịt từ khóa

Mỗi trang Việt có một từ khóa chính khác nhau (`config/pages.php → keyword`), title/mô tả/H1/nhãn menu duy nhất. Kiểm thử tĩnh chặn hai trang có cùng từ khóa hoặc title của trang này bắt đầu bằng từ khóa của trang kia. Không tạo trang theo từng quận/biến thể từ khóa (doorway).
