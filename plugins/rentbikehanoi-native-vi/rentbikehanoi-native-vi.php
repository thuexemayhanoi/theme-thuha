<?php
/**
 * Plugin Name:       Rentbike Hanoi – Tiếng Việt gốc (/vi/)
 * Description:       Phần tiếng Việt viết riêng dưới /vi/ cho rentbikehanoi.com: định tuyến /vi/, ghép cặp Anh–Việt, hreflang, sitemap qua Yoast, nút VI | EN và bộ trang Việt dạng nháp. Không dịch máy, không gọi dịch vụ ngoài.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Rentbike Hanoi
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rentbikehanoi-native-vi
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

define( 'RBHVI_VERSION', '1.0.0' );
define( 'RBHVI_FILE', __FILE__ );
define( 'RBHVI_DIR', plugin_dir_path( __FILE__ ) );

foreach ( array( 'config', 'state', 'language', 'router', 'query', 'seo', 'switcher', 'theme-integration', 'listing', 'seeder', 'preflight', 'admin' ) as $rbhvi_module ) {
	require_once RBHVI_DIR . 'includes/class-rbhvi-' . $rbhvi_module . '.php';
}
unset( $rbhvi_module );

RBHVI_Language::boot();
RBHVI_Router::boot();
RBHVI_Query::boot();
RBHVI_SEO::boot();
RBHVI_Switcher::boot();
RBHVI_Theme_Integration::boot();
RBHVI_Listing::boot();
if ( is_admin() ) {
	RBHVI_Admin::boot();
}

// Activation writes nothing: /vi/ stays OFF and no content is created or published.
register_activation_hook( __FILE__, array( 'RBHVI_State', 'activate' ) );
// Deactivation keeps every Vietnamese page, pair and option; only routing stops.
register_deactivation_hook( __FILE__, array( 'RBHVI_State', 'deactivate' ) );

/**
 * Template tag: prints the VI | EN switch. Themes normally use the
 * rbh_header_actions action instead.
 */
function rbhvi_language_switcher() {
	RBHVI_Switcher::render();
}

/**
 * Current request language: 'vi' or 'en'.
 *
 * @return string
 */
function rbhvi_current_language() {
	return RBHVI_Language::current();
}
