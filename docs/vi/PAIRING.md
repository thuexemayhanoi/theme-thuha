# Ghép cặp nội dung Anh–Việt

## Khi nào được ghép

Chỉ ghép hai trang/bài **cùng mục đích tìm kiếm và cùng phạm vi nội dung** (ví dụ bảng giá ↔ bảng giá). Nội dung Việt viết riêng nên câu chữ khác nhau là bình thường; điều quan trọng là người đọc ở hai ngôn ngữ nhận được câu trả lời tương đương. Không ghép:
- bài Việt với một chuyên mục/trang lưu trữ tiếng Anh;
- hai bài chỉ "gần giống" chủ đề;
- một trang Anh với nhiều trang Việt (hệ thống chặn: mỗi cặp là một-một).

## Cách ghép

- Trang quản trị *Tiếng Việt /vi/ → 3. Ghép cặp*: chọn trang Việt và trang/bài Anh → *Lưu cặp*. Bỏ ghép bằng ô *Bỏ ghép*.
- Hoặc trong trình soạn thảo trang/bài Việt: hộp *Ngôn ngữ (Anh / Việt)* → chọn "Tiếng Việt" → nhập ID trang/bài Anh.
- Bộ trang ban đầu có cặp **đề xuất**; bấm *Xác nhận các cặp Anh–Việt được đề xuất* sau khi đã kiểm tra.

Cặp được lưu ở phía tiếng Việt (`_rbhvi_pair`). Trang tiếng Anh không bị sửa.

## Cặp có hiệu lực khi

Trang Việt đánh dấu tiếng Việt; trang Anh không phải tiếng Việt; cả hai đã xuất bản và không có mật khẩu; không trang nào noindex; không trang Việt nào khác cũng ghép với trang Anh đó. Khi có hiệu lực: hreflang hai chiều + nút VI | EN chuyển thẳng sang trang tương ứng. Khi chưa: nút dẫn về trang chủ ngôn ngữ kia, không có hreflang.

Cột *Kiểm tra nội dung* trên trang quản trị báo "Chưa ghép cặp", "Chưa xác nhận cặp (có đề xuất)" hoặc "Cặp tiếng Anh không hợp lệ".

## Bài viết tiếng Việt mới

1. Bài viết → Viết bài mới; hộp *Ngôn ngữ* → "Tiếng Việt"; nhập ID bài Anh nếu thật sự tương ứng.
2. Chọn chuyên mục **tiếng Việt** (Bài viết → Chuyên mục → tạo chuyên mục và đánh dấu "Chuyên mục tiếng Việt"). Không gắn bài Việt vào chuyên mục tiếng Anh.
3. Điền title SEO và mô tả trong Yoast bằng tiếng Việt.
4. Xuất bản → địa chỉ là `/vi/{slug}/`.

Mặc định bài Việt **chưa ghép** không vào sitemap (cài đặt có thể tắt trong mục 5 nếu chủ website muốn index bài chỉ có bản Việt).
