# Kiểm tra sau khi chủ website cài theme 1.4.0 và plugin 1.1.1

Ngày 23/10/2026, Asia/Ho_Chi_Minh. Website duy nhất: https://rentbikehanoi.com.
Người dùng yêu cầu kiểm tra bản vừa cài và khắc phục lỗi. Đây là kiểm tra trực tiếp bởi trợ lý có kết nối WordPress;
không phải kết quả crawl hay truy cập WordPress của Claude. Claude tiếp tục chỉ cần dữ liệu trong repo.

## Đã xác nhận và khắc phục

- WordPress báo active theme `wp-theme` 1.4.0 và `rentbikehanoi-native-vi` 1.1.1. Không thiếu template chính; GTranslate không active.
- Ban đầu khách chưa đăng nhập vẫn thấy HTML cũ: trang Việt dạng trang thường, chỉ có logo và logo dẫn về gốc tiếng Anh.
- Đã chạy thao tác cache purge chính thức: LiteSpeed Cache được xóa, object cache được flush. Không đổi option, nội dung hoặc file runtime.
- Tải lại `/vi/`: body có `rbhvi-home`; hiện hero, quick links, ba thẻ xe, bảng giá, ba bước, FAQ và chi tiết thu gọn.
- Năm ảnh (logo, hero, Vision, Wave, Cub) tải được và có alt Việt. Logo dẫn về `/vi/`.
- Thử mở/đóng câu hỏi đặt cọc; nội dung hiện đúng. Thử dark mode: switch bật và `data-theme=dark`, rồi trả lại sáng.
- Bấm VI -> EN -> VI đến đúng hai trang chủ. Gọi điện/Zalo/WhatsApp có đích đúng số doanh nghiệp; không thực hiện cuộc gọi hay gửi tin.

## Các trang đã kiểm tra

11 URL quản lý: home, prices, scooter, procedure, delivery, FAQ, about, contact, blog, privacy, terms.
Tất cả hiển thị nội dung tiếng Việt với `lang=vi`, đúng một H1, một meta description và logo dẫn về `/vi/`.
10 trang indexable có đúng một canonical tự trỏ; 9 cặp có ba alternate en/vi/x-default.
Terms không ghép cặp, giữ canonical riêng. Blog Việt trống giữ `noindex,follow`, không canonical/alternate như thiết kế.

Tìm kiếm `/vi/?s=xe` và trang không tồn tại `/vi/khong-co-trang-nay/` có tiêu đề Việt, một H1, `noindex,follow`, không canonical/hreflang.
Hai archive đã lấy URL từ WordPress và mở trực tiếp: `/vi/chuyen-muc/cam-nang/` và `/vi/chuyen-muc/chon-xe-va-so-sanh/`.
Title, H1, breadcrumb cha/con, `lang=vi` và canonical đều khớp. Không tuyên bố đã kiểm tra mọi archive/phân trang/bài viết.

Trang chủ Anh: title, description, canonical, H1 và header/footer navigation khớp chính xác snapshot lấy trước cache purge.
Schema trang chủ Việt và Anh đọc được dưới dạng JSON-LD hợp lệ. Không đổi SEO đã đóng băng, URL hay nhãn menu.

## Giới hạn và dữ liệu còn cần xác nhận

- Đã kiểm tra trực quan ở viewport desktop 1363px; không thấy cuộn ngang trên các trang vừa mở. Chưa kiểm tra thiết bị di động thật.
- Lần đọc sitemap XML qua trình duyệt bị timeout; công cụ đọc công khai cũng không truy cập được. Không suy ra sitemap hỏng hoặc đã đạt.
  Kết quả sitemap trong bản ghi triển khai trước là lịch sử, chưa xác minh lại ở lần này.
- Điểm phí gia hạn phát hiện ban đầu đã được xử lý sau khi chủ cửa hàng xác nhận; xem mục tiếp theo.
- Không tuyên bố đã kiểm tra Search Console, Core Web Vitals, mọi liên kết ngoài, thủ tục/chính sách hoặc form bình luận của bài Việt.
- Không cài thêm plugin publisher, không sửa `/thuha-writer`, không merge `main`, không gọi webhook triển khai.

Các ZIP gốc, manifest và mã băm không đổi; tài liệu này không phải bản phát hành runtime mới.

## Chủ cửa hàng xác nhận phí gia hạn và đồng bộ nội dung

Ngày 23/10/2026, chủ website xác nhận: **20.000đ/giờ; quá 6 giờ tính một ngày thuê**.
Giữ điều kiện `>6`, không đổi thành từ 6 giờ trở lên.

Đã sửa đúng đoạn phí gia hạn trong nội dung WordPress đang xuất bản: IDs **67, 211, 274, 278, 283, 289**.
Mỗi lần sửa là thay thế đoạn khớp duy nhất qua API nội dung, giữ nguyên phần khác, URL và metadata.
Bỏ giới hạn giờ cũ 1-5 và câu yêu cầu xác nhận riêng cho đúng 6 giờ. Các giá 30.000đ không liên quan (vé, xăng, ăn uống...) không bị thay thế hàng loạt.
Đọc lại cả sáu ID: vẫn `publish`, có 20,000 VND per hour, điều kiện more than 6 hours và không còn câu exactly 6 hours cũ.
Quét nội dung post/page đã xuất bản bằng mẫu 30.000/30,000 gần đơn vị giờ: không còn kết quả. Đã xóa cache LiteSpeed/object cache.

Bản Việt và config plugin vốn đã đúng 20.000đ nên không sửa runtime, không cần ZIP hoặc cài lại.
Đồng bộ facts và hướng dẫn của repo writer; không viết lại tài liệu nguồn bất biến hoặc giả biên nhận xuất bản của factory.
