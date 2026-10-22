<?php
/**
 * Presentation glue for the Rentbike Hanoi theme on Vietnamese requests:
 * Vietnamese menus (never renaming the frozen English menu items),
 * Vietnamese business details, interface strings and home links.
 * English requests are untouched.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme integration.
 */
class RBHVI_Theme_Integration {

	/**
	 * Interface strings map.
	 *
	 * @var array|null
	 */
	private static $strings;

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_action( 'after_setup_theme', array( __CLASS__, 'register_menus' ), 20 );
		add_filter( 'rbh_menu_items', array( __CLASS__, 'menu_items' ), 10, 2 );
		add_filter( 'rbh_menu_group', array( __CLASS__, 'menu_group' ), 10, 2 );
		add_filter( 'rbh_home_url', array( __CLASS__, 'home_url' ) );
		add_filter( 'rbh_assistant_enabled', array( __CLASS__, 'assistant_enabled' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_assistant' ), 100 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ), 20 );
		add_filter( 'gettext_rentbike-hanoi', array( __CLASS__, 'gettext' ), 10, 2 );
		add_filter( 'ngettext_rentbike-hanoi', array( __CLASS__, 'ngettext' ), 10, 4 );
		add_filter( 'option_date_format', array( __CLASS__, 'date_format' ) );
		foreach ( array_keys( self::theme_mods() ) as $key ) {
			add_filter( 'theme_mod_rbh_' . $key, array( __CLASS__, 'theme_mod' ) );
		}
	}

	/**
	 * Vietnamese values of the theme's shared settings (from the locked
	 * business config and natural Vietnamese CTA copy).
	 *
	 * @return array
	 */
	private static function theme_mods() {
		$data = RBHVI_Config::business();
		return array(
			'brand'               => $data['name'],
			'hours'               => $data['hours'],
			'address'             => $data['address'],
			'banner'              => false,
			'cta_label'           => '',
			'footer_cta_eyebrow'  => 'Chuyến đi tiếp theo',
			'footer_cta_title'    => 'Cùng lên kế hoạch thuê xe.',
			'footer_cta_text'     => 'Gọi hoặc nhắn tin cho cửa hàng để hỏi xe còn trống, giá và điều kiện thuê.',
			'cta_general_eyebrow' => 'Bạn cần hỏi thêm?',
			'cta_general_title'   => 'Hỏi trực tiếp cửa hàng.',
			'cta_general_text'    => '',
			'cta_general_label'   => 'Hỏi về xe',
			'cta_rental_eyebrow'  => 'Bạn đang định thuê xe?',
			'cta_rental_title'    => 'Hỏi trực tiếp cửa hàng.',
			'cta_rental_text'     => '',
			'cta_rental_label'    => 'Hỏi về xe',
			'cta_travel_eyebrow'  => 'Bạn đang lên lịch đi xe?',
			'cta_travel_title'    => 'Chọn xe phù hợp chuyến đi.',
			'cta_travel_text'     => 'Hãy hỏi xe còn trống và điều kiện thuê trước ngày đi.',
			'cta_travel_label'    => 'Trao đổi kế hoạch',
			'cta_tips_eyebrow'    => 'Trước khi thuê',
			'cta_tips_title'      => 'Kiểm tra chi tiết với cửa hàng.',
			'cta_tips_text'       => 'Xác nhận trực tiếp mẫu xe, thời gian thuê và điều kiện thuê.',
			'cta_tips_label'      => 'Đặt câu hỏi về thuê xe',
		);
	}

	/**
	 * Separate menu locations for Vietnamese navigation.
	 */
	public static function register_menus() {
		register_nav_menus(
			array(
				'rbhvi_primary' => 'Menu tiếng Việt (/vi/)',
				'rbhvi_footer'  => 'Liên kết chân trang tiếng Việt (/vi/)',
			)
		);
	}

	/**
	 * Vietnamese menu items on Vietnamese requests.
	 *
	 * @param array  $items    Items.
	 * @param string $location Theme location.
	 * @return array
	 */
	public static function menu_items( $items, $location = 'primary' ) {
		if ( 'vi' !== RBHVI_Language::current() ) {
			return $items;
		}
		$vi_location = 'footer' === $location ? 'rbhvi_footer' : 'rbhvi_primary';
		$locations   = get_nav_menu_locations();
		if ( ! empty( $locations[ $vi_location ] ) ) {
			$menu = wp_get_nav_menu_items( $locations[ $vi_location ] );
			return is_array( $menu ) ? array_filter(
				$menu,
				function ( $item ) {
					return empty( $item->_invalid ) && ! empty( $item->url );
				}
			) : array();
		}
		return 'footer' === $location ? array() : self::fallback_items();
	}

	/**
	 * Fallback Vietnamese menu built from published managed pages. Nothing is
	 * written to the database.
	 *
	 * @return array
	 */
	public static function fallback_items() {
		$pages = RBHVI_Config::pages();
		uasort(
			$pages,
			function ( $a, $b ) {
				return $a['menu_order'] <=> $b['menu_order'];
			}
		);
		$items = array();
		$index = 0;
		foreach ( $pages as $key => $page ) {
			$id = RBHVI_State::page_id( $key );
			if ( ! $id || 'publish' !== get_post_status( $id ) ) {
				continue;
			}
			++$index;
			$items[] = (object) array(
				'ID'               => 9000000 + $index,
				'db_id'            => 0,
				'title'            => $page['menu_label'],
				'url'              => get_permalink( $id ),
				'type'             => 'post_type',
				'object'           => 'page',
				'object_id'        => $id,
				'menu_item_parent' => 0,
				'target'           => '',
				'classes'          => array(),
				'rbhvi_group'      => $page['group'],
			);
		}
		return $items;
	}

	/**
	 * Footer/desktop grouping of fallback items.
	 *
	 * @param string $group Group.
	 * @param object $item  Item.
	 * @return string
	 */
	public static function menu_group( $group, $item ) {
		if ( isset( $item->rbhvi_group ) ) {
			return $item->rbhvi_group;
		}
		if ( 'vi' === RBHVI_Language::current() && 'post_type' === $item->type ) {
			foreach ( RBHVI_Config::pages() as $key => $page ) {
				if ( RBHVI_State::page_id( $key ) === (int) $item->object_id ) {
					return $page['group'];
				}
			}
		}
		return $group;
	}

	/**
	 * Theme home links point to /vi/ on Vietnamese requests.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	public static function home_url( $url ) {
		return 'vi' === RBHVI_Language::current() && RBHVI_State::is_enabled() ? RBHVI_Language::home( 'vi' ) : $url;
	}

	/**
	 * The theme's English website assistant is not shown on Vietnamese pages.
	 *
	 * @param bool $enabled Enabled.
	 * @return bool
	 */
	public static function assistant_enabled( $enabled ) {
		return 'vi' === RBHVI_Language::current() ? false : $enabled;
	}

	/**
	 * Remove assistant assets on Vietnamese pages.
	 */
	public static function dequeue_assistant() {
		if ( 'vi' !== RBHVI_Language::current() ) {
			return;
		}
		wp_dequeue_script( 'rbh-assistant' );
		wp_dequeue_style( 'rbh-assistant' );
	}

	/**
	 * Body classes.
	 *
	 * @param string[] $classes Classes.
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		if ( 'vi' !== RBHVI_Language::current() ) {
			return $classes;
		}
		$classes   = array_values( array_diff( $classes, array( 'rbh-assistant-enabled' ) ) );
		$classes[] = 'rbhvi-lang-vi';
		return $classes;
	}

	/**
	 * Vietnamese interface strings.
	 *
	 * @return array
	 */
	public static function strings() {
		if ( null === self::$strings ) {
			self::$strings = require RBHVI_DIR . 'includes/theme-strings-vi.php';
		}
		return self::$strings;
	}

	/**
	 * Gettext for the theme domain.
	 *
	 * @param string $translation Translation.
	 * @param string $text        Original.
	 * @return string
	 */
	public static function gettext( $translation, $text ) {
		if ( 'vi' !== RBHVI_Language::current() ) {
			return $translation;
		}
		$strings = self::strings();
		return isset( $strings[ $text ] ) ? $strings[ $text ] : $translation;
	}

	/**
	 * Plural gettext for the theme domain.
	 *
	 * @param string $translation Translation.
	 * @param string $single      Singular.
	 * @param string $plural      Plural.
	 * @param int    $number      Count.
	 * @return string
	 */
	public static function ngettext( $translation, $single, $plural, $number ) {
		if ( 'vi' !== RBHVI_Language::current() ) {
			return $translation;
		}
		$strings = self::strings();
		$text    = 1 === (int) $number ? $single : $plural;
		return isset( $strings[ $text ] ) ? $strings[ $text ] : $translation;
	}

	/**
	 * Numeric dates on Vietnamese pages (no English month names).
	 *
	 * @param string $format Format.
	 * @return string
	 */
	public static function date_format( $format ) {
		return 'vi' === RBHVI_Language::current() ? 'd/m/Y' : $format;
	}

	/**
	 * Vietnamese values for theme settings.
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	public static function theme_mod( $value ) {
		if ( 'vi' !== RBHVI_Language::current() ) {
			return $value;
		}
		$key  = substr( current_filter(), strlen( 'theme_mod_rbh_' ) );
		$mods = self::theme_mods();
		return array_key_exists( $key, $mods ) ? $mods[ $key ] : $value;
	}
}
