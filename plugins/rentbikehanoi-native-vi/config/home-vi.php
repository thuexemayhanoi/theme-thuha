<?php
/**
 * Copy of the Vietnamese home page (/vi/) landing layout. Written natively in
 * Vietnamese; prices, NAP and fees come from business.php through
 * {{placeholders}}; links use {{link:key}}. Images are not configured here:
 * they are reused from the English homepage's existing media.
 *
 * Never add prices, policies or claims here that are not in business.php.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

return array(
	'lede'        => 'Chọn xe phù hợp, hỏi rõ giá và điều kiện, rồi yên tâm lên đường khám phá Hà Nội.',
	'buttons'     => array(
		array(
			'label' => 'Gọi {{phone_display}}',
			'url'   => '{{phone_href}}',
		),
		array(
			'label' => 'Nhắn Zalo',
			'url'   => '{{zalo_url}}',
		),
	),
	'notes'       => array( 'Thuê theo ngày · tuần · tháng', '{{hours}}' ),
	'photo_label' => array( 'HÀ NỘI', 'XE CỦA BẠN. NHỊP CỦA BẠN.' ),
	'fallback'    => 'HÀ NỘI<br>TRÊN HAI<br>BÁNH XE.',
	'quick_links' => array( 'prices', 'procedure', 'delivery', 'faq' ),
	'bikes'       => array(
		'eyebrow' => 'Chọn xe phù hợp',
		'title'   => 'Xe cho từng nhu cầu.',
		'text'    => 'Mẫu xe còn trống thay đổi theo ngày. Hãy hỏi trước và xác nhận điều kiện thuê trước khi đặt.',
		// token = part of the existing English homepage image file name (same rule as the theme).
		'items'   => array(
			array(
				'token'      => 'Honda-Vision',
				'label'      => 'Xe ga',
				'alt'        => 'Xe ga Honda Vision',
				'text'       => 'Honda Vision, Air Blade và các mẫu xe ga khác. Giá tham khảo {{price_day:xe-ga}}/ngày.',
				'link'       => 'scooter',
				'link_label' => 'Thuê xe ga',
			),
			array(
				'token'      => 'Honda-Wave',
				'label'      => 'Xe số',
				'alt'        => 'Xe số Honda Wave',
				'text'       => 'Phù hợp nếu bạn đã quen sang số. Giá tham khảo {{price_day:xe-so}}/ngày.',
				'link'       => 'prices',
				'link_label' => 'Bảng giá',
			),
			array(
				'token'      => 'Honda-Cub-50cc',
				'label'      => 'Xe 50cc',
				'alt'        => 'Xe 50cc Honda Cub',
				'text'       => 'Xe phân khối nhỏ, giá tham khảo {{price_day:xe-50cc}}/ngày. Hỏi cửa hàng mẫu đang có.',
				'link'       => 'procedure',
				'link_label' => 'Thủ tục thuê',
			),
		),
	),
	'rates'       => array(
		'eyebrow'    => 'Trước khi đặt xe',
		'title'      => 'Rõ giá, rõ điều kiện.',
		'text'       => 'Giá tham khảo theo ngày, tuần và tháng; xe điện có giá ngày tùy mẫu. Phí gia hạn: {{extension_fee}}. Giá chính xác cho mẫu xe và lịch thuê của bạn cần được cửa hàng xác nhận.',
		'link'       => 'prices',
		'link_label' => 'Xem bảng giá đầy đủ',
	),
	'steps'       => array(
		'eyebrow' => 'Ba bước đơn giản',
		'title'   => 'Sẵn sàng lên đường.',
		'items'   => array(
			array(
				'title' => 'Chọn xe',
				'text'  => 'Cho cửa hàng biết loại xe bạn muốn, ngày nhận và ngày trả dự kiến.',
			),
			array(
				'title' => 'Hỏi rõ điều kiện',
				'text'  => 'Xác nhận giá, tiền cọc (nếu có), giấy tờ cần mang và giờ trả xe.',
			),
			array(
				'title' => 'Hẹn nhận xe',
				'text'  => 'Nhận xe tại cửa hàng gần Bến xe Gia Lâm hoặc hỏi phương án giao nhận.',
			),
		),
	),
	'faq'         => array(
		'eyebrow'    => 'Giải đáp nhanh',
		'title'      => 'Trước chuyến đi đầu tiên.',
		'link'       => 'faq',
		'link_label' => 'Xem tất cả câu hỏi',
		// Indexes into content/faq-items.php (shown without schema; FAQPage stays on the FAQ page only).
		'items'      => array( 0, 1, 3, 5 ),
	),
	'details'     => array(
		'eyebrow'     => 'Thông tin thuê xe',
		'title'       => 'Toàn bộ chi tiết ở một nơi.',
		'first_title' => 'Về dịch vụ cho thuê xe',
	),
	'guides'      => array(
		'eyebrow'    => 'Đọc trước khi đi',
		'title'      => 'Bài viết hữu ích.',
		'link'       => 'blog',
		'link_label' => 'Blog',
	),
);
