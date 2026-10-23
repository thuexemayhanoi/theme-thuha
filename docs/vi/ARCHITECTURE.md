# Kiến trúc song ngữ Anh–Việt

Trạng thái: **mã nguồn và kiểm thử cục bộ — CHƯA triển khai lên https://rentbikehanoi.com**.

## Nguyên tắc

| | Tiếng Anh | Tiếng Việt |
| --- | --- | --- |
| Địa chỉ | Giữ nguyên tại gốc `https://rentbikehanoi.com/…` | `https://rentbikehanoi.com/vi/…` |
| Nội dung | Nội dung hiện có, không bị sửa | Viết riêng bằng tiếng Việt, không dịch máy |
| Canonical | Tự trỏ về URL tiếng Anh (Yoast) | Tự trỏ về URL tiếng Việt (Yoast) |
| hreflang | `en`, `vi`, `x-default` → bản Anh, chỉ cho cặp đã xác nhận | như bên trái |
| Sitemap | Sitemap Yoast hiện có | Cùng sitemap Yoast (không có sitemap thứ hai) |

Không dùng WPML, Polylang, TranslatePress, API dịch, dịch vụ AI, cookie hay JavaScript để đổi ngôn ngữ, và không chuyển hướng theo IP/quốc gia/ngôn ngữ trình duyệt.

## Ba thành phần và ranh giới

```
Theme "Rentbike Hanoi" 1.3.0 (thư mục đang hoạt động: wp-theme)
  └─ giao diện: header, footer, menu, Sáng/Tối, thẻ bài viết, CSS
     điểm gắn mới: rbh_header_actions, rbh_home_url, rbh_menu_items,
                   rbh_menu_group, rbh_assistant_enabled, rbh_language_switch_enabled
Plugin "rentbikehanoi-native-vi" 1.0.0  (plugins/rentbikehanoi-native-vi/)
  └─ ngôn ngữ: nhận biết vi/en, định tuyến /vi/, ghép cặp, tách danh sách,
     hreflang, bộ lọc Yoast, nút VI | EN, trang quản trị, bộ trang nháp
Yoast SEO (đã có trên website)
  └─ SEO: title, mô tả, canonical, robots, Open Graph, JSON-LD, sitemap
```

- Theme **không** in canonical, Open Graph hay JSON-LD (giữ như bản 1.2.0).
- Plugin **không** in canonical, robots, Open Graph hay JSON-LD khi Yoast đang chạy. Plugin chỉ in thẻ `<link rel="alternate" hreflang>` (Yoast không có tính năng này) và dùng bộ lọc công khai của Yoast: `wpseo_og_locale`, `wpseo_breadcrumb_links`, `wpseo_canonical` (chỉ trang phân trang /vi/blog/page/N/), `wpseo_title` (thêm "Trang N"), `wpseo_robots` (noindex khi /vi/blog/ chưa có bài), `wpseo_exclude_from_sitemap_by_post_ids`, `wpseo_exclude_from_sitemap_by_term_ids`.
- Khi không có plugin SEO nào, plugin in title và meta description dự phòng cho trang Việt; canonical/robots do WordPress core in (một thẻ duy nhất).

## Mô hình dữ liệu (không ghi vào nội dung tiếng Anh)

| Dữ liệu | Nơi lưu |
| --- | --- |
| Ngôn ngữ của trang/bài | Taxonomy riêng tư `rbhvi_language`, term `vi` (không có URL, không hiện trong sitemap) |
| Ngôn ngữ của chuyên mục | Term meta `_rbhvi_lang = vi` |
| Cặp Anh–Việt đã xác nhận | Post meta `_rbhvi_pair` **trên trang/bài tiếng Việt** |
| Cặp đề xuất (chưa dùng cho hreflang) | `_rbhvi_pair_suggested` trên trang tiếng Việt |
| Trang do plugin quản lý | `_rbhvi_managed` (khóa trang) + option `rbhvi_registry` |
| Tiêu đề/mô tả SEO | `_yoast_wpseo_title`, `_yoast_wpseo_metadesc` (Yoast) + bản sao `_rbhvi_seo_title/_desc` cho chế độ dự phòng |
| Bật/tắt /vi/ | option `rbhvi_enabled` (mặc định không tồn tại = TẮT) |
| Lịch sử để hoàn tác | option `rbhvi_history` (20 lần gần nhất) |
| Cài đặt sitemap | option `rbhvi_settings` |

## Định tuyến /vi/

Không thêm rewrite rule, không flush, không sửa `.htaccess`. Bộ lọc `request` của WordPress đọc đường dẫn: nếu bắt đầu bằng `vi`:

- TẮT → trả 404.
- `vi/` → trang chủ tiếng Việt (trang có slug `vi`), hoặc tìm kiếm nếu có `?s=`.
- `vi/blog/` và `vi/blog/page/N/` → trang Blog tiếng Việt + danh sách bài Việt.
- `vi/chuyen-muc/{slug}/` → chuyên mục được đánh dấu tiếng Việt.
- `vi/{đường-dẫn-trang}/` → trang con của `/vi/` được đánh dấu tiếng Việt.
- `vi/{slug-bài}/` → bài viết tiếng Việt.
- Mọi đường dẫn khác dưới `/vi/` → 404 (không đoán URL tiếng Anh).

URL tiếng Anh không đi qua nhánh này nên không bị ảnh hưởng. Nội dung tiếng Việt truy cập qua URL không phải `/vi/` trả 404.

## Tách ngôn ngữ trong danh sách

`pre_get_posts` thêm điều kiện taxonomy (NOT IN `vi` cho trang Anh, IN `vi` cho trang Việt) vào mọi truy vấn danh sách công khai: lưu trữ, chuyên mục, tìm kiếm, feed, widget bài mới, bài liên quan, REST `/wp/v2/search` công khai. Bài trước/sau dùng bộ lọc `get_{previous,next}_post_where`. Danh sách chuyên mục dùng `get_terms_args`. Chỉ hoạt động sau khi có nội dung/chuyên mục tiếng Việt, nên trước đó truy vấn tiếng Anh giữ nguyên từng byte (đã kiểm thử).

## Giao diện tiếng Việt

Trang Việt dùng nguyên template của theme (page.php, single.php, index.php, 404.php). Plugin chỉ đổi phần chữ:
chuỗi giao diện (gettext cho text domain `rentbike-hanoi`; từ 1.1.1 thêm chữ dự phòng cho vài chuỗi lõi WordPress như form bình luận, chỉ khi chưa có bản dịch đã cài), tên/giờ/địa chỉ cửa hàng (bộ lọc `theme_mod_rbh_*` lấy từ `config/business.php`), menu tiếng Việt (vị trí menu `rbhvi_primary`/`rbhvi_footer`, hoặc menu dự phòng tự sinh từ các trang Việt đã xuất bản), định dạng ngày số `d/m/Y`, `lang="vi"`, `og:locale=vi_VN`. Trợ lý website tiếng Anh của theme không hiển thị trên trang Việt.

## Trang chủ tiếng Việt cùng bố cục trang chủ Anh (theme 1.4.0 + plugin 1.1.0 trở lên, chưa triển khai)

```
/vi/ (trang quản lý "home", production ID 3454)
  └─ plugin RBHVI_Home::template()  (template_include, chỉ cho đúng trang này,
     │                                chỉ khi theme có template-parts/landing.php
     │                                và cài đặt "Bố cục trang chủ /vi/" = landing)
     └─ plugins/.../templates/home-vi.php
          get_header() → get_template_part('template-parts/landing', $data) → get_footer()
```

- `template-parts/landing.php` (theme) chỉ trình bày, dùng đúng các class của `front-page.php` (`hero`, `quick-links`, `bike-grid`, `rates-section`, `plan-section`, `faq-layout`, `source-sections`, `card-grid`) nên kế thừa toàn bộ CSS, giao diện điện thoại/máy tính và sáng/tối hiện có. Không thêm CSS.
- `RBHVI_Home::data()` (plugin) chuẩn bị dữ liệu: chữ tiếng Việt từ `config/home-vi.php`; giá, NAP, phí gia hạn từ `config/business.php`; H1 = tiêu đề trang đang lưu; ảnh lớn và 3 ảnh xe **đọc** từ nội dung trang chủ Anh (cùng quy tắc với `rbh_home_data()`: ảnh hero trong Customizer, nếu không có thì ảnh đầu tiên trong nội dung; ảnh xe theo tên tệp `Honda-Vision`, `Honda-Wave`, `Honda-Cub-50cc`); `post_content` đang lưu của trang /vi/ được chia theo H2 thành các mục thu gọn; 3 bài Việt mới nhất (nếu có).
- Không ghi cơ sở dữ liệu, không đổi `post_content`, không thêm schema (FAQ ngắn trên trang chủ không có FAQPage; FAQPage vẫn chỉ ở `/vi/cau-hoi-thuong-gap/`). Title, mô tả, canonical, hreflang, sitemap vẫn do Yoast/plugin xử lý như trước vì đây vẫn là cùng một trang.
- Trang chủ Anh vẫn dùng `front-page.php` (không đổi so với bản đã triển khai; kiểm thử so sánh HTML từng byte).
