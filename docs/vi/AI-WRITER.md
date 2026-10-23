# Hướng dẫn cho AI Writer (viết bài Việt và bài Anh)

## Nguồn dữ liệu kinh doanh

Chỉ dùng dữ liệu trong `plugins/rentbikehanoi-native-vi/config/business.php` (xem `docs/vi/BUSINESS-DATA.md`). Không thêm, đoán hay làm tròn. Khi viết nội dung seed trong repo, dùng placeholder (`{{phone_display}}`, `{{address}}`, `{{hours}}`, `{{price_day:xe-so}}`, `{{price_table}}`, `{{extension_fee}}`, `{{link:prices}}`…) thay vì gõ lại số liệu.

Không được viết:
- "không cần đặt cọc", "miễn cọc" (chỉ được nói website *không quảng cáo* thuê không cần cọc);
- giá ngày của xe điện (chỉ "tùy mẫu");
- thủ tục/giấy tờ cụ thể, chính sách hoàn cọc, bảo hiểm, ưu đãi, giảm giá, khuyến mãi;
- đánh giá/nhận xét khách hàng, số năm hoạt động, số lượng xe, "uy tín số 1", "rẻ nhất";
- cam kết dịch vụ (giờ giao chắc chắn, xe luôn sẵn…).
Khi cần thông tin chưa có: viết "hỏi cửa hàng để được xác nhận".

## Viết tiếng Việt

- Viết trực tiếp bằng tiếng Việt cho người Việt; **không dịch máy**, không dịch từng câu từ bài Anh. Bố cục, ví dụ và câu hỏi có thể khác bài Anh.
- Một URL = một mục đích tìm kiếm chính. Trước khi viết, kiểm tra `config/pages.php → keyword` và danh sách bài Việt để không trùng từ khóa chính.
- Một H1 duy nhất (là tiêu đề trang/bài — theme tự in); trong nội dung chỉ dùng H2/H3.
- Title SEO 25–65 ký tự, mô tả 110–165 ký tự, không lặp từ khóa nhồi nhét.
- Liên kết nội bộ theo ngữ cảnh tới trang `/vi/…` (bảng giá, xe ga, thủ tục, giao nhận, câu hỏi, liên hệ). Không liên kết sang trang tiếng Anh trong thân bài.
- FAQ chỉ đánh dấu bằng khối FAQ của Yoast và mọi câu hỏi phải hiển thị trên trang.
- Đánh dấu bài là "Tiếng Việt", gắn chuyên mục tiếng Việt, ghép cặp chỉ khi bài Anh thật sự tương ứng.
- Không tạo các trang chỉ khác nhau tên quận/phường (doorway), không tạo nội dung mỏng hay lặp lại.

## Viết tiếng Anh

- Không đổi URL, slug, canonical, chuyên mục hay nhãn menu đã đóng băng của trang Anh.
- Không sửa title/mô tả/OG/Site Name của trang chủ (đóng băng).
- Bài Anh mới vẫn ở gốc như trước; plugin không ảnh hưởng.

## Xuất bản

AI Writer không tự xuất bản. Tạo bản nháp → chủ website xem trước → duyệt → xuất bản theo quy trình WPVibe/wp-writer. Sau khi xuất bản bài Việt có cặp, kiểm tra hreflang hai chiều và sitemap.

## Kiểm tra trước khi gửi duyệt

`php tests/static/content-test.php` (với nội dung seed) và mục *Kiểm tra nội dung tiếng Việt* trên trang quản trị (thiếu title/mô tả/H1/cặp).
