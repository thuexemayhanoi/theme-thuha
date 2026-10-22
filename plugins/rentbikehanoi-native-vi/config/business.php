<?php
/**
 * Locked business facts — the single source for every Vietnamese page, the
 * Vietnamese header/footer and the seed content placeholders.
 *
 * Owner-provided values. Do not change NAP or prices without the owner's
 * explicit approval; update docs/vi/BUSINESS-DATA.md and the tests together.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

return array(
	'name'           => 'Thuê xe máy Nguyễn Hà',
	'address'        => 'Ngõ 5 Nguyễn Văn Cừ, Bồ Đề, Long Biên, Hà Nội (gần Bến xe Gia Lâm)',
	// Short form for meta descriptions; must stay consistent with 'address'.
	'address_short'  => 'Ngõ 5 Nguyễn Văn Cừ, Bồ Đề, Long Biên',
	'phone_display'  => '0334 699 969',
	'phone_intl'     => '+84 334 699 969',
	'phone_href'     => 'tel:+84334699969',
	'zalo_url'       => 'https://zalo.me/0334699969',
	'whatsapp_url'   => 'https://wa.me/84334699969',
	// Existing public map link already used by the theme's contact panel.
	'map_url'        => 'https://share.google/pJ2KSOJcrYDJYM42M',
	'hours'          => '09:00–21:00 hằng ngày',
	// Owner-confirmed in AGENTS.md (wp-writer business-facts → extension_policy).
	'extension_fee'  => '20.000đ/giờ; gia hạn quá 6 giờ được tính là một ngày thuê',
	// Reference prices. "null" means the owner has not set a public price: never invent one.
	'prices'         => array(
		array(
			'key'   => 'xe-so',
			'label' => 'Xe số',
			'day'   => '150.000–200.000đ',
			'week'  => '700.000–800.000đ',
			'month' => '1.000.000–1.200.000đ',
		),
		array(
			'key'   => 'xe-ga',
			'label' => 'Xe ga',
			'day'   => '150.000–200.000đ',
			'week'  => '600.000–1.000.000đ',
			'month' => '1.200.000–2.000.000đ',
		),
		array(
			'key'   => 'xe-dien',
			'label' => 'Xe điện',
			'day'   => null,
			'week'  => '600.000–800.000đ',
			'month' => '1.500.000–1.800.000đ',
		),
		array(
			'key'   => 'xe-50cc',
			'label' => 'Xe 50cc',
			'day'   => '200.000đ',
			'week'  => '1.000.000đ',
			'month' => '2.000.000đ',
		),
		array(
			'key'   => 'honda-vision',
			'label' => 'Honda Vision',
			'day'   => '200.000đ',
			'week'  => '1.000.000đ',
			'month' => '2.000.000đ',
		),
		array(
			'key'   => 'honda-air-blade',
			'label' => 'Honda Air Blade',
			'day'   => '200.000đ',
			'week'  => '900.000đ',
			'month' => '1.500.000đ',
		),
	),
	'price_day_unknown' => 'Tùy mẫu xe – hỏi trước',
	'price_note'        => 'Giá tham khảo, cần xác nhận với cửa hàng trước khi đặt xe.',
);
