<?php
/**
 * Registry of the plugin-managed Vietnamese core pages.
 *
 * - path: below /vi/ ('' is the Vietnamese home page /vi/ itself).
 * - title: the page title, rendered by the theme as the single H1.
 * - seo_title / seo_desc: unique per URL; written to Yoast fields on the draft.
 * - keyword: the one primary search intent of the URL (anti-cannibalisation tests).
 * - en: suggested English counterpart. Suggestions are never used for hreflang
 *   until an administrator confirms the pair in the admin screen.
 * - review: legal pages that the owner must read before publishing.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

return array(
	'home'      => array(
		'path'       => '',
		'title'      => 'Thuê xe máy Hà Nội tại Long Biên',
		'menu_label' => 'Trang chủ',
		'group'      => 'company',
		'seo_title'  => 'Thuê xe máy Hà Nội theo ngày, tuần, tháng | Nguyễn Hà',
		'seo_desc'   => 'Thuê xe máy Hà Nội tại Long Biên, gần Bến xe Gia Lâm: xe số, xe ga, xe điện, 50cc. Xem giá tham khảo, giờ mở cửa, gọi hoặc nhắn Zalo, WhatsApp.',
		'keyword'    => 'thuê xe máy hà nội',
		'en'         => array( 'type' => 'front' ),
		'menu_order' => 0,
	),
	'about'     => array(
		'path'       => 'gioi-thieu',
		'title'      => 'Giới thiệu {{name}}',
		'menu_label' => 'Giới thiệu',
		'group'      => 'company',
		'seo_title'  => 'Giới thiệu cửa hàng {{name}}, Long Biên',
		'seo_desc'   => '{{name}} ở {{address_short}}, gần Bến xe Gia Lâm. Tìm hiểu cách cửa hàng tư vấn chọn xe, báo giá và hẹn giờ nhận xe.',
		'keyword'    => 'thuê xe máy nguyễn hà',
		'en'         => array(
			'type' => 'page',
			'path' => 'about-us',
		),
		'menu_order' => 80,
	),
	'prices'    => array(
		'path'       => 'bang-gia-thue-xe-may-ha-noi',
		'title'      => 'Bảng giá thuê xe máy Hà Nội',
		'menu_label' => 'Bảng giá',
		'group'      => 'rent',
		'seo_title'  => 'Bảng giá thuê xe máy Hà Nội theo ngày, tuần, tháng',
		'seo_desc'   => 'Giá tham khảo thuê xe số, xe ga, xe điện, xe 50cc, Honda Vision và Air Blade theo ngày, tuần, tháng tại Long Biên. Luôn xác nhận giá trước khi đặt.',
		'keyword'    => 'bảng giá thuê xe máy hà nội',
		'en'         => array(
			'type' => 'post',
			'path' => 'motorbike-rental-prices-in-hanoi',
		),
		'menu_order' => 10,
	),
	'scooter'   => array(
		'path'       => 'thue-xe-ga-ha-noi',
		'title'      => 'Thuê xe ga Hà Nội: Honda Vision, Air Blade và xe điện',
		'menu_label' => 'Thuê xe ga',
		'group'      => 'rent',
		'seo_title'  => 'Thuê xe ga Hà Nội: Honda Vision, Air Blade | Nguyễn Hà',
		'seo_desc'   => 'Thuê xe ga ở Hà Nội để đi phố, đi làm hay ở dài ngày: cách chọn giữa Honda Vision, Air Blade, xe điện, giá tham khảo và những điều nên hỏi trước.',
		'keyword'    => 'thuê xe ga hà nội',
		'en'         => array(
			'type' => 'post',
			'path' => 'hanoi-scooter-rental',
		),
		'menu_order' => 20,
	),
	'procedure' => array(
		'path'       => 'thu-tuc-thue-xe-may-ha-noi',
		'title'      => 'Thủ tục thuê xe máy ở Hà Nội: các bước và điều cần hỏi',
		'menu_label' => 'Thủ tục thuê',
		'group'      => 'rent',
		'seo_title'  => 'Thủ tục thuê xe máy Hà Nội: các bước cần biết | Nguyễn Hà',
		'seo_desc'   => 'Các bước thuê xe máy tại Nguyễn Hà, từ lúc liên hệ, nhận báo giá đến nhận và trả xe, kèm danh sách câu hỏi về giấy tờ và đặt cọc nên hỏi trước.',
		'keyword'    => 'thủ tục thuê xe máy hà nội',
		'en'         => array(
			'type' => 'post',
			'path' => 'motorbike-renting-in-hanoi-requirements',
		),
		'menu_order' => 30,
	),
	'delivery'  => array(
		'path'       => 'giao-nhan-xe-may-ha-noi',
		'title'      => 'Giao nhận xe máy thuê tại Hà Nội',
		'menu_label' => 'Giao nhận xe',
		'group'      => 'rent',
		'seo_title'  => 'Giao nhận xe máy thuê tại Hà Nội | {{name}}',
		'seo_desc'   => 'Nhận xe tại cửa hàng gần Bến xe Gia Lâm hay hỏi giao xe tận nơi? Xem thông tin cần gửi và cách xác nhận khu vực, giờ hẹn, chi phí giao nhận.',
		'keyword'    => 'giao nhận xe máy',
		'en'         => array(
			'type' => 'post',
			'path' => 'motorbike-rental-hanoi-delivery-and-pickup-guide-complete-service-overview-2025',
		),
		'menu_order' => 40,
	),
	'faq'       => array(
		'path'       => 'cau-hoi-thuong-gap',
		'title'      => 'Câu hỏi thường gặp khi thuê xe máy',
		'menu_label' => 'Hỏi đáp',
		'group'      => 'company',
		'seo_title'  => 'Câu hỏi thường gặp khi thuê xe máy ở Hà Nội | Nguyễn Hà',
		'seo_desc'   => 'Giải đáp nhanh về giá thuê, giờ mở cửa, địa chỉ, gia hạn, đặt cọc và cách liên hệ {{name}} qua điện thoại, Zalo hoặc WhatsApp.',
		'keyword'    => 'câu hỏi thường gặp khi thuê xe máy',
		'en'         => array(
			'type' => 'page',
			'path' => 'faq',
		),
		'menu_order' => 60,
	),
	'contact'   => array(
		'path'       => 'lien-he',
		'title'      => 'Liên hệ {{name}}',
		'menu_label' => 'Liên hệ',
		'group'      => 'company',
		'seo_title'  => 'Liên hệ thuê xe: {{phone_display}} | Nguyễn Hà, Long Biên',
		'seo_desc'   => 'Gọi, nhắn Zalo hoặc WhatsApp {{phone_display}} để hỏi xe và giá. Cửa hàng ở {{address_short}}, mở cửa {{hours}}.',
		'keyword'    => 'liên hệ thuê xe máy',
		'en'         => array(
			'type' => 'page',
			'path' => 'contact-us',
		),
		'menu_order' => 90,
	),
	'blog'      => array(
		'path'       => 'blog',
		'title'      => 'Blog kinh nghiệm thuê và đi xe máy',
		'menu_label' => 'Blog',
		'group'      => 'guides',
		'seo_title'  => 'Blog kinh nghiệm thuê và đi xe máy ở Hà Nội | Nguyễn Hà',
		'seo_desc'   => 'Bài viết tiếng Việt về chọn xe, chuẩn bị trước khi nhận xe, đi xe máy an toàn trong thành phố và những điều nên hỏi cửa hàng cho thuê.',
		'keyword'    => 'kinh nghiệm thuê và đi xe máy',
		'en'         => null,
		'menu_order' => 50,
		'listing'    => true,
	),
	'privacy'   => array(
		'path'       => 'chinh-sach-bao-mat',
		'title'      => 'Chính sách bảo mật',
		'menu_label' => 'Bảo mật',
		'group'      => 'legal',
		'seo_title'  => 'Chính sách bảo mật | {{name}}',
		'seo_desc'   => 'Cách website {{name}} xử lý thông tin khi bạn đọc trang, tìm kiếm hoặc liên hệ cửa hàng qua điện thoại, Zalo và WhatsApp.',
		'keyword'    => 'chính sách bảo mật',
		'en'         => array(
			'type' => 'page',
			'path' => 'privacy-police',
		),
		'menu_order' => 100,
		'review'     => true,
	),
	'terms'     => array(
		'path'       => 'dieu-khoan-su-dung',
		'title'      => 'Điều khoản sử dụng website',
		'menu_label' => 'Điều khoản',
		'group'      => 'legal',
		'seo_title'  => 'Điều khoản sử dụng website | {{name}}',
		'seo_desc'   => 'Điều kiện sử dụng thông tin trên website {{name}}: giá tham khảo, nội dung hướng dẫn và việc xác nhận điều kiện thuê trực tiếp với cửa hàng.',
		'keyword'    => 'điều khoản sử dụng',
		// The English page at /terms-of-use/ is labelled "Rental Terms"; confirm it covers the same scope before pairing.
		'en'         => array(
			'type' => 'page',
			'path' => 'terms-of-use',
		),
		'menu_order' => 110,
		'review'     => true,
	),
);
