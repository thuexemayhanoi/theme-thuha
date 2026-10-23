<?php
/**
 * Vietnamese FAQ (question, answer). Every answer uses only locked business
 * facts or tells the reader to confirm with the shop. All items are shown
 * on the page, so Yoast's FAQPage schema matches the visible content.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

return array(
	array( 'Cửa hàng ở đâu?', 'Cửa hàng ở {{address}}.' ),
	array( 'Cửa hàng mở cửa lúc mấy giờ?', 'Cửa hàng mở cửa {{hours}}. Nên gọi hoặc nhắn trước khi đến để chuẩn bị đúng mẫu xe.' ),
	array( 'Liên hệ bằng cách nào?', 'Gọi điện, nhắn Zalo hoặc WhatsApp theo số {{phone_display}}. Nếu gọi từ nước ngoài, dùng số {{phone_intl}}.' ),
	array( 'Giá thuê xe máy bao nhiêu một ngày?', 'Giá tham khảo theo ngày: xe số {{price_day:xe-so}}, xe ga {{price_day:xe-ga}}, xe 50cc {{price_day:xe-50cc}}, Honda Vision {{price_day:honda-vision}}, Honda Air Blade {{price_day:honda-air-blade}}. Xe điện có giá ngày tùy mẫu. Giá cần được cửa hàng xác nhận trước khi đặt.' ),
	array( 'Thuê theo tuần hoặc theo tháng có rẻ hơn không?', 'Bảng giá có mức riêng cho thuê tuần và thuê tháng, ví dụ xe số {{price_week:xe-so}}/tuần và {{price_month:xe-so}}/tháng. Hãy cho cửa hàng biết tổng thời gian thuê để được báo phương án phù hợp.' ),
	array( 'Có phải đặt cọc không?', 'Điều kiện đặt cọc được cửa hàng xác nhận cho từng trường hợp; website không quảng cáo thuê xe không cần cọc. Hãy hỏi mức cọc và cách hoàn cọc trước khi nhận xe.' ),
	array( 'Cần mang giấy tờ gì khi thuê xe?', 'Cửa hàng sẽ cho biết giấy tờ cần mang khi bạn liên hệ. Người lái cần có giấy phép lái xe phù hợp với loại xe theo quy định hiện hành.' ),
	array( 'Muốn trả xe muộn hơn thì sao?', 'Phí gia hạn là {{extension_fee}}. Hãy báo cửa hàng sớm để thống nhất giờ trả mới.' ),
	array( 'Cửa hàng có giao xe tận nơi không?', 'Bạn có thể hỏi phương án giao nhận cho địa chỉ của mình; khu vực, giờ giao và chi phí do cửa hàng xác nhận. Cửa hàng không giao xe tới sân bay Nội Bài.' ),
	array( 'Có thể giữ chỗ một mẫu xe cụ thể không?', 'Mẫu xe còn trống thay đổi theo ngày. Hãy liên hệ sớm, nêu mẫu xe và ngày cần để cửa hàng xác nhận trực tiếp.' ),
);
