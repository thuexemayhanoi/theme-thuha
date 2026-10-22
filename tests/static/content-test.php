<?php
/**
 * Static checks without WordPress: locked business data, Vietnamese seed
 * content quality, SEO uniqueness/anti-cannibalisation, forbidden claims,
 * theme string map. Run: php tests/static/content-test.php
 *
 * @package RentbikeHanoiNativeVi
 */

// Minimal stand-ins for the few WordPress helpers the renderer uses.
define( 'ABSPATH', __DIR__ . '/' );
define( 'RBHVI_DIR', dirname( __DIR__, 2 ) . '/plugins/rentbikehanoi-native-vi/' );
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return esc_html( $s ); }
function esc_url( $s ) { return str_replace( '&', '&#038;', (string) $s ); }
function home_url( $path = '' ) { return 'https://rentbikehanoi.com/' . ltrim( $path, '/' ); }
function user_trailingslashit( $s ) { return rtrim( $s, '/' ) . '/'; }
function wp_json_encode( $data, $flags = 0 ) { return json_encode( $data, $flags ); }
require RBHVI_DIR . 'includes/class-rbhvi-config.php';

$failures = 0;
$passes   = 0;
function check( $name, $ok, $detail = '' ) {
	global $failures, $passes;
	if ( $ok ) {
		++$passes;
		echo "  ✔ $name\n";
	} else {
		++$failures;
		echo "  ✖ $name" . ( $detail ? "\n      $detail" : '' ) . "\n";
	}
}
function words( $html ) {
	return count( preg_split( '/\s+/u', trim( html_entity_decode( strip_tags( preg_replace( '/<!--.*?-->/s', ' ', $html ) ) ) ), -1, PREG_SPLIT_NO_EMPTY ) );
}
function wp_list_pluck_stub( $list, $field ) { return array_map( function ( $row ) use ( $field ) { return $row[ $field ]; }, $list ); }
function lower( $s ) { return mb_strtolower( html_entity_decode( $s, ENT_QUOTES, 'UTF-8' ), 'UTF-8' ); }

echo "Locked business data\n";
$b = RBHVI_Config::business();
$expected = array(
	'name'          => 'Thuê xe máy Nguyễn Hà',
	'address'       => 'Ngõ 5 Nguyễn Văn Cừ, Bồ Đề, Long Biên, Hà Nội (gần Bến xe Gia Lâm)',
	'phone_display' => '0334 699 969',
	'phone_intl'    => '+84 334 699 969',
	'hours'         => '09:00–21:00 hằng ngày',
);
foreach ( $expected as $k => $v ) {
	check( "business.$k is the owner-provided value", $b[ $k ] === $v, var_export( $b[ $k ], true ) );
}
check( 'address_short is a prefix of the address', 0 === strpos( $b['address'], $b['address_short'] ) );
check( 'tel/Zalo/WhatsApp use the same number', 'tel:+84334699969' === $b['phone_href'] && 'https://zalo.me/0334699969' === $b['zalo_url'] && 'https://wa.me/84334699969' === $b['whatsapp_url'] );
$prices = array(
	'xe-so'           => array( 'Xe số', '150.000–200.000đ', '700.000–800.000đ', '1.000.000–1.200.000đ' ),
	'xe-ga'           => array( 'Xe ga', '150.000–200.000đ', '600.000–1.000.000đ', '1.200.000–2.000.000đ' ),
	'xe-dien'         => array( 'Xe điện', null, '600.000–800.000đ', '1.500.000–1.800.000đ' ),
	'xe-50cc'         => array( 'Xe 50cc', '200.000đ', '1.000.000đ', '2.000.000đ' ),
	'honda-vision'    => array( 'Honda Vision', '200.000đ', '1.000.000đ', '2.000.000đ' ),
	'honda-air-blade' => array( 'Honda Air Blade', '200.000đ', '900.000đ', '1.500.000đ' ),
);
$actual = array();
foreach ( $b['prices'] as $p ) {
	$actual[ $p['key'] ] = array( $p['label'], $p['day'], $p['week'], $p['month'] );
}
check( 'price list is exactly the owner-provided list (no invented e-bike daily price)', $actual === $prices, json_encode( $actual, JSON_UNESCAPED_UNICODE ) );
$table = RBHVI_Config::price_table();
foreach ( $prices as $p ) {
	check( "price table shows {$p[0]}", false !== strpos( $table, '<td>' . $p[0] . '</td><td>' . ( null === $p[1] ? $b['price_day_unknown'] : $p[1] ) . '</td><td>' . $p[2] . '</td><td>' . $p[3] . '</td>' ) );
}

echo "Vietnamese seed content\n";
$pages     = RBHVI_Config::pages();
$rendered  = array();
$urls      = array();
foreach ( $pages as $key => $page ) {
	$urls[ RBHVI_Config::page_url( $key ) ] = $key;
}
check( 'exactly the 11 required URLs', array_keys( $urls ) === array_map( function ( $p ) { return 'https://rentbikehanoi.com/vi/' . ( $p ? $p . '/' : '' ); }, array( '', 'gioi-thieu', 'bang-gia-thue-xe-may-ha-noi', 'thue-xe-ga-ha-noi', 'thu-tuc-thue-xe-may-ha-noi', 'giao-nhan-xe-may-ha-noi', 'cau-hoi-thuong-gap', 'lien-he', 'blog', 'chinh-sach-bao-mat', 'dieu-khoan-su-dung' ) ) );
foreach ( $pages as $key => $page ) {
	$html             = RBHVI_Config::page_content( $key );
	$rendered[ $key ] = $html;
	$min              = ! empty( $page['listing'] ) ? 50 : 180;
	check( "$key: content exists, ≥ $min words (" . words( $html ) . ')', words( $html ) >= $min );
	check( "$key: no unresolved placeholder", false === strpos( $html, '{{' ) && false === strpos( RBHVI_Config::render( $page['seo_title'] . $page['seo_desc'] . $page['title'] ), '{{' ) );
	check( "$key: no H1 in content (title is the only H1)", ! preg_match( '/<h1[\s>]/i', $html ) );
	check( "$key: no lorem ipsum / placeholder text", ! preg_match( '/lorem|ipsum|TODO|FIXME|xxx/i', $html ) );
	check( "$key: block markup is balanced", substr_count( $html, '<!-- wp:' ) === substr_count( $html, '<!-- /wp:' ) );
	$title = RBHVI_Config::render( $page['seo_title'] );
	$desc  = RBHVI_Config::render( $page['seo_desc'] );
	check( "$key: SEO title 25–65 chars (" . mb_strlen( $title ) . ')', mb_strlen( $title ) >= 25 && mb_strlen( $title ) <= 65 );
	check( "$key: meta description 110–165 chars (" . mb_strlen( $desc ) . ')', mb_strlen( $desc ) >= 110 && mb_strlen( $desc ) <= 165 );
	check( "$key: primary keyword appears in title or SEO title", false !== strpos( lower( RBHVI_Config::render( $page['title'] . ' ' . $page['seo_title'] ) ), $page['keyword'] ), $page['keyword'] );
	preg_match_all( '/href="([^"]+)"/', $html, $m );
	$internal = array_unique( array_filter( $m[1], function ( $u ) { return 0 === strpos( $u, 'https://rentbikehanoi.com/' ); } ) );
	$bad      = array_filter( $m[1], function ( $u ) use ( $urls, $b ) { return ! isset( $urls[ $u ] ) && ! in_array( $u, array( $b['phone_href'], $b['zalo_url'], $b['whatsapp_url'], $b['map_url'] ), true ); } );
	check( "$key: links only to managed /vi/ pages or the shop's contact channels", ! $bad, implode( ', ', $bad ) );
	check( "$key: no self link; ≥ 2 contextual links to other Vietnamese pages", ! in_array( RBHVI_Config::page_url( $key ), $internal, true ) && count( $internal ) >= 2, implode( ', ', $internal ) );
	check( "$key: no English page linked from Vietnamese content", ! preg_match( '#rentbikehanoi\.com/(?!vi/)[a-z]#', $html ) );
}
foreach ( array( 'title', 'seo_title', 'seo_desc', 'keyword', 'menu_label' ) as $field ) {
	$values = array_map( function ( $p ) use ( $field ) { return lower( RBHVI_Config::render( $p[ $field ] ) ); }, $pages );
	check( "unique $field across pages (no competing URLs)", count( array_unique( $values ) ) === count( $values ) );
}
foreach ( $pages as $key => $page ) {
	$label = RBHVI_Config::render( $page['menu_label'] );
	$n     = count( preg_split( '/\s+/u', $label ) );
	check( "$key: menu label \"$label\" has 1–3 words", $n >= 1 && $n <= 3 );
}
// Same intent must not be targeted twice: another page's SEO title may not start with this page's keyword.
foreach ( $pages as $key => $page ) {
	foreach ( $pages as $other => $o ) {
		if ( $other !== $key && 0 === strpos( lower( RBHVI_Config::render( $o['seo_title'] ) ), $page['keyword'] ) ) {
			check( "cannibalisation: $other's title starts with $key's keyword", false );
		}
	}
}
check( 'cannibalisation check completed', true );

echo "Business-claim guardrails\n";
$all = lower( implode( ' ', $rendered ) . ' ' . file_get_contents( RBHVI_DIR . 'content/faq-items.php' ) );
foreach ( array( 'miễn cọc', 'không cần thế chấp', 'giảm giá', 'ưu đãi', 'khuyến mãi', 'năm kinh nghiệm', 'cam kết hoàn', 'bảo hiểm xe', '5 sao', 'hàng trăm xe', 'xe mới 100', 'uy tín số 1', 'rẻ nhất', 'tốt nhất', 'giao xe miễn phí', 'đánh giá của khách' ) as $claim ) {
	check( "no unsupported claim \"$claim\"", false === strpos( $all, $claim ) );
}
preg_match_all( '/.{0,40}không cần (?:đặt )?cọc/u', $all, $m );
check( '"không cần cọc" only appears as "not advertised"', (bool) $m[0] && ! array_filter( $m[0], function ( $s ) { return false === strpos( $s, 'quảng cáo' ); } ), implode( ' | ', $m[0] ) );
check( 'e-bike daily price never stated', false === strpos( $all, 'xe điện ' . '150.000' ) && false !== strpos( $all, 'tùy mẫu' ) );
foreach ( array( 'home', 'contact', 'faq' ) as $key ) {
	$text = $rendered[ $key ] . ( 'faq' === $key ? '' : '' );
	foreach ( array( 'phone_display', 'address', 'hours' ) as $field ) {
		if ( 'faq' === $key && 'address' !== $field && 'hours' !== $field && 'phone_display' !== $field ) {
			continue;
		}
		check( "$key shows $field from config", false !== strpos( $text, esc_html( $b[ $field ] ) ) );
	}
}

echo "FAQ\n";
$faq = RBHVI_Config::faq_block();
preg_match( '/<!-- wp:yoast\/faq-block (\{.*?\}) -->/s', $faq, $m );
$attrs = isset( $m[1] ) ? json_decode( $m[1], true ) : null;
check( 'FAQ block attributes are valid JSON with ≥ 8 questions', is_array( $attrs ) && count( $attrs['questions'] ) >= 8 );
foreach ( (array) ( $attrs ? $attrs['questions'] : array() ) as $q ) {
	check( 'visible FAQ contains: ' . $q['jsonQuestion'], false !== strpos( $faq, '<strong class="schema-faq-question">' . $q['jsonQuestion'] . '</strong>' ) && false !== strpos( $faq, $q['jsonAnswer'] ) );
}

echo "Vietnamese home page (landing) copy\n";
$home_cfg = require RBHVI_DIR . 'config/home-vi.php';
$strings  = array();
array_walk_recursive(
	$home_cfg,
	function ( $value, $key ) use ( &$strings ) {
		if ( is_string( $value ) && ! in_array( $key, array( 'token', 'link' ), true ) ) {
			$strings[] = $value;
		}
	}
);
$home_text = lower( implode( ' ', array_map( array( 'RBHVI_Config', 'render' ), $strings ) ) );
check( 'home copy: every placeholder resolves', false === strpos( $home_text, '{{' ) );
check( 'home copy: no price, phone or address typed literally (placeholders only)', ! preg_match( '/\d{3}\.000|0334|nguyễn văn cừ|09:00/u', lower( implode( ' ', $strings ) ) ) );
foreach ( array( 'miễn cọc', 'không cần cọc', 'không cần đặt cọc', 'giảm giá', 'ưu đãi', 'khuyến mãi', 'năm kinh nghiệm', 'bảo hiểm', '5 sao', 'rẻ nhất', 'tốt nhất', 'uy tín', 'cam kết', 'miễn phí', 'luôn có xe', 'giao tận nơi miễn phí' ) as $claim ) {
	check( "home copy: no unsupported claim \"$claim\"", false === strpos( $home_text, $claim ) );
}
check( 'home copy: e-bike daily price is not stated', false !== strpos( $home_text, 'xe điện có giá ngày tùy mẫu' ) );
check( 'home copy: shows the extension fee from config', false !== strpos( $home_text, lower( $b['extension_fee'] ) ) );
$keys = array_merge( $home_cfg['quick_links'], wp_list_pluck_stub( $home_cfg['bikes']['items'], 'link' ), array( $home_cfg['rates']['link'], $home_cfg['faq']['link'], $home_cfg['guides']['link'] ) );
check( 'home copy: every link points to a managed /vi/ page', ! array_diff( $keys, array_keys( $pages ) ), implode( ', ', array_diff( $keys, array_keys( $pages ) ) ) );
$faq_items = require RBHVI_DIR . 'content/faq-items.php';
check( 'home copy: FAQ excerpts exist in content/faq-items.php', ! array_diff( $home_cfg['faq']['items'], array_keys( $faq_items ) ) );
$front_page = file_get_contents( dirname( __DIR__, 2 ) . '/inc/content.php' ); // rbh_home_data() picks the English homepage bike photos.
foreach ( $home_cfg['bikes']['items'] as $item ) {
	check( "home copy: bike photo token {$item['token']} is the one the English homepage uses", false !== strpos( $front_page, "'" . $item['token'] . "'" ) );
}
check( 'home copy: 3 bike cards like the English homepage, 3 steps, 2 hero buttons', 3 === count( $home_cfg['bikes']['items'] ) && 3 === count( $home_cfg['steps']['items'] ) && 2 === count( $home_cfg['buttons'] ) );
check( 'landing template part exists and prints exactly one <h1>', 1 === substr_count( file_get_contents( dirname( __DIR__, 2 ) . '/template-parts/landing.php' ), '<h1>' ) );

echo "Theme interface strings\n";
$theme   = '';
foreach ( array_merge( glob( dirname( __DIR__, 2 ) . '/*.php' ), glob( dirname( __DIR__, 2 ) . '/inc/*.php' ), glob( dirname( __DIR__, 2 ) . '/template-parts/*.php' ) ) as $file ) {
	$theme .= file_get_contents( $file );
}
$strings = require RBHVI_DIR . 'includes/theme-strings-vi.php';
foreach ( $strings as $en => $vi ) {
	check( "theme uses \"$en\"", false !== strpos( $theme, "'" . $en . "'" ) && '' !== trim( $vi ) );
}
preg_match_all( "/(?:esc_html_e|esc_html__|esc_attr_e|esc_attr__|__)\( '((?:[^'\\\\]|\\\\.)*)', 'rentbike-hanoi' \)/", implode( '', array_map( 'file_get_contents', array_merge( glob( dirname( __DIR__, 2 ) . '/{header,footer,index,page,single,404,searchform,sidebar,comments}.php', GLOB_BRACE ), glob( dirname( __DIR__, 2 ) . '/template-parts/{menu,contact-dock,cta,banner,context-links,related,card}.php', GLOB_BRACE ) ) ) ), $m );
$missing = array_diff( array_unique( $m[1] ), array_keys( $strings ) );
check( 'every front-end template string has a Vietnamese translation', ! $missing, implode( ', ', $missing ) );

echo "\n$passes passed, $failures failed\n";
exit( $failures ? 1 : 0 );
