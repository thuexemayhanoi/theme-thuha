<?php
/**
 * The "VI | EN" switch, rendered next to the theme's Light/Dark toggle via
 * the theme action rbh_header_actions. It is a plain link (no JavaScript,
 * no cookie): to the paired page when a valid pair exists, otherwise to the
 * other language's home page.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Language switch.
 */
class RBHVI_Switcher {

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_action( 'rbh_header_actions', array( __CLASS__, 'render' ) );
		add_filter( 'rbh_language_switch_enabled', array( __CLASS__, 'should_render' ) );
	}

	/**
	 * Visible only while /vi/ is ON and the Vietnamese home page is published.
	 *
	 * @return bool
	 */
	public static function should_render() {
		return RBHVI_State::is_enabled() && RBHVI_Language::vi_home_published();
	}

	/**
	 * Target URL in the other language.
	 *
	 * @param string $lang Current language.
	 * @return string
	 */
	public static function target( $lang ) {
		$other = 'vi' === $lang ? 'en' : 'vi';
		if ( is_singular() && ! is_404() ) {
			$partner = RBHVI_Language::partner( get_queried_object_id() );
			if ( $partner ) {
				return 'en' === $other && (int) get_option( 'page_on_front' ) === $partner ? home_url( '/' ) : get_permalink( $partner );
			}
		}
		return RBHVI_Language::home( $other );
	}

	/**
	 * Print the switch.
	 */
	public static function render() {
		if ( ! self::should_render() ) {
			return;
		}
		$lang  = RBHVI_Language::current();
		$other = 'vi' === $lang ? 'en' : 'vi';
		$label = 'vi' === $other ? 'Xem bằng tiếng Việt' : 'View in English';
		printf(
			'<a class="rbh-lang-switch" href="%1$s" hreflang="%2$s" lang="%2$s" data-current-lang="%3$s"><span class="rbh-lang-code%4$s" aria-hidden="true">VI</span><span class="rbh-lang-sep" aria-hidden="true">|</span><span class="rbh-lang-code%5$s" aria-hidden="true">EN</span><span class="screen-reader-text">%6$s</span></a>',
			esc_url( self::target( $lang ) ),
			esc_attr( $other ),
			esc_attr( $lang ),
			'vi' === $lang ? ' is-current' : '',
			'en' === $lang ? ' is-current' : '',
			esc_html( $label )
		);
	}
}
