<?php
/**
 * Language model: a private taxonomy marks Vietnamese posts/pages, term meta
 * marks Vietnamese categories, and pairs are stored on the Vietnamese side
 * only (English posts are never written to).
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Language detection and pairing.
 */
class RBHVI_Language {

	const TAX         = 'rbhvi_language';
	const TERM        = 'vi';
	const META_PAIR   = '_rbhvi_pair';
	const META_SUGG   = '_rbhvi_pair_suggested';
	const META_KEY    = '_rbhvi_managed';
	const META_TITLE  = '_rbhvi_seo_title';
	const META_DESC   = '_rbhvi_seo_desc';
	const TERM_META   = '_rbhvi_lang';
	const POST_TYPES  = array( 'post', 'page' );

	/**
	 * Per-request lookup memo (never persisted, so it cannot go stale).
	 *
	 * @var array
	 */
	private static $memo = array();

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_action( 'init', array( __CLASS__, 'register' ), 0 );
		// Locale for Vietnamese URLs: decided from the URL only, so it is stable and cache-safe.
		add_filter( 'locale', array( __CLASS__, 'filter_locale' ) );
		add_action( 'save_post', array( __CLASS__, 'flush_caches' ) );
		add_action( 'deleted_post', array( __CLASS__, 'flush_caches' ) );
		add_action( 'set_object_terms', array( __CLASS__, 'flush_caches' ) );
		add_action( 'updated_post_meta', array( __CLASS__, 'flush_caches' ) );
		add_action( 'added_post_meta', array( __CLASS__, 'flush_caches' ) );
		add_action( 'deleted_post_meta', array( __CLASS__, 'flush_caches' ) );
		add_action( 'updated_term_meta', array( __CLASS__, 'flush_caches' ) );
		add_action( 'added_term_meta', array( __CLASS__, 'flush_caches' ) );
		add_action( 'deleted_term_meta', array( __CLASS__, 'flush_caches' ) );
	}

	/**
	 * Private taxonomy: no UI, no URLs, no REST, no sitemap.
	 */
	public static function register() {
		register_taxonomy(
			self::TAX,
			self::POST_TYPES,
			array(
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => false,
				'show_in_rest'       => false,
				'show_in_nav_menus'  => false,
				'show_tagcloud'      => false,
				'query_var'          => false,
				'rewrite'            => false,
				'hierarchical'       => false,
				'label'              => 'Ngôn ngữ (Rentbike VI)',
			)
		);
	}

	/**
	 * Path of the current request relative to the site home, without slashes.
	 *
	 * @return string
	 */
	public static function request_path() {
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- only parsed for its path.
		$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
		$home = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
		$path = trim( rawurldecode( $path ), '/' );
		if ( '' !== $home ) {
			if ( $path !== $home && 0 !== strpos( $path, $home . '/' ) ) {
				return $path;
			}
			$path = trim( substr( $path, strlen( $home ) ), '/' );
		}
		return $path;
	}

	/**
	 * Whether a request path belongs to the Vietnamese section.
	 *
	 * @param string $path Path without leading slash.
	 * @return bool
	 */
	public static function path_is_vi( $path ) {
		$path = trim( (string) $path, '/' );
		return 'vi' === $path || 0 === strpos( $path, 'vi/' );
	}

	/**
	 * Whether the current request is a public front-end request.
	 *
	 * @return bool
	 */
	public static function is_frontend() {
		return ! is_admin() && ! wp_doing_ajax() && ! wp_doing_cron() && ! ( defined( 'WP_CLI' ) && WP_CLI ) && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST );
	}

	/**
	 * Locale filter for public Vietnamese URLs.
	 *
	 * @param string $locale Locale.
	 * @return string
	 */
	public static function filter_locale( $locale ) {
		if ( self::is_frontend() && RBHVI_State::is_enabled() && self::path_is_vi( self::request_path() ) ) {
			return 'vi';
		}
		return $locale;
	}

	/**
	 * Current request language ('vi' or 'en').
	 *
	 * @return string
	 */
	public static function current() {
		if ( ! self::is_frontend() ) {
			return 'en';
		}
		if ( RBHVI_State::is_enabled() && self::path_is_vi( self::request_path() ) ) {
			return 'vi';
		}
		// Editors previewing a Vietnamese draft (preview URLs are not under /vi/).
		if ( did_action( 'wp' ) && is_singular() && self::is_vi( get_queried_object_id() ) ) {
			return 'vi';
		}
		return 'en';
	}

	/**
	 * The Vietnamese language term ID, or 0 when none exists yet.
	 *
	 * @return int
	 */
	public static function term_id() {
		$term = get_term_by( 'slug', self::TERM, self::TAX );
		return $term ? (int) $term->term_id : 0;
	}

	/**
	 * Create the language term when needed (admin actions only).
	 *
	 * @return int
	 */
	public static function ensure_term() {
		$id = self::term_id();
		if ( $id ) {
			return $id;
		}
		$made = wp_insert_term( 'Tiếng Việt', self::TAX, array( 'slug' => self::TERM ) );
		return is_wp_error( $made ) ? 0 : (int) $made['term_id'];
	}

	/**
	 * Whether a post is Vietnamese.
	 *
	 * @param int|WP_Post $post Post.
	 * @return bool
	 */
	public static function is_vi( $post ) {
		$post = get_post( $post );
		if ( ! $post || ! in_array( $post->post_type, self::POST_TYPES, true ) ) {
			return false;
		}
		return has_term( self::TERM, self::TAX, $post );
	}

	/**
	 * Set or clear the Vietnamese marker on a post.
	 *
	 * @param int  $post_id Post ID.
	 * @param bool $is_vi   Vietnamese?.
	 */
	public static function set_vi( $post_id, $is_vi ) {
		if ( $is_vi ) {
			self::ensure_term();
			wp_set_object_terms( $post_id, self::TERM, self::TAX );
		} else {
			wp_set_object_terms( $post_id, array(), self::TAX );
		}
	}

	/**
	 * Whether a category term is Vietnamese.
	 *
	 * @param int|WP_Term $term Term.
	 * @return bool
	 */
	public static function term_is_vi( $term ) {
		$term_id = is_object( $term ) ? $term->term_id : (int) $term;
		return $term_id && 'vi' === get_term_meta( $term_id, self::TERM_META, true );
	}

	/**
	 * Whether any Vietnamese category exists (keeps English term queries
	 * untouched until one does).
	 *
	 * @return bool
	 */
	public static function has_vi_terms() {
		if ( ! isset( self::$memo['has_vi_terms'] ) ) {
			global $wpdb;
			self::$memo['has_vi_terms'] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->termmeta} WHERE meta_key = %s AND meta_value = 'vi'", self::TERM_META ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}
		return self::$memo['has_vi_terms'] > 0;
	}

	/**
	 * IDs of every Vietnamese post/page (any status).
	 *
	 * @return int[]
	 */
	public static function vi_ids() {
		if ( ! isset( self::$memo['vi_ids'] ) ) {
			$term = self::term_id();
			$ids  = $term ? get_objects_in_term( $term, self::TAX ) : array();
			self::$memo['vi_ids'] = is_array( $ids ) ? array_map( 'intval', $ids ) : array();
		}
		return self::$memo['vi_ids'];
	}

	/**
	 * Confirmed English partner of a Vietnamese post, if the pair is valid:
	 * both published, VI marked Vietnamese, EN not Vietnamese, one-to-one.
	 *
	 * @param int $vi_id Vietnamese post ID.
	 * @return int English post ID or 0.
	 */
	public static function valid_en_for( $vi_id ) {
		$en_id = (int) get_post_meta( $vi_id, self::META_PAIR, true );
		if ( ! $en_id || ! self::is_vi( $vi_id ) || self::is_vi( $en_id ) ) {
			return 0;
		}
		if ( 'publish' !== get_post_status( $vi_id ) || 'publish' !== get_post_status( $en_id ) || post_password_required( $en_id ) || post_password_required( $vi_id ) ) {
			return 0;
		}
		if ( ! in_array( get_post_type( $en_id ), self::POST_TYPES, true ) ) {
			return 0;
		}
		return count( self::vi_ids_paired_with( $en_id ) ) === 1 ? $en_id : 0;
	}

	/**
	 * Vietnamese posts that declare a pair with the English post.
	 *
	 * @param int $en_id English post ID.
	 * @return int[]
	 */
	public static function vi_ids_paired_with( $en_id ) {
		$key = 'pairs_' . (int) $en_id;
		if ( ! isset( self::$memo[ $key ] ) ) {
			global $wpdb;
			self::$memo[ $key ] = array_map( 'intval', $wpdb->get_col( $wpdb->prepare( "SELECT pm.post_id FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND pm.meta_value = %s AND p.post_status <> 'trash'", self::META_PAIR, (string) (int) $en_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}
		return self::$memo[ $key ];
	}

	/**
	 * Valid Vietnamese partner of an English post.
	 *
	 * @param int $en_id English post ID.
	 * @return int
	 */
	public static function valid_vi_for( $en_id ) {
		$ids = self::vi_ids_paired_with( $en_id );
		if ( count( $ids ) !== 1 ) {
			return 0;
		}
		return self::valid_en_for( $ids[0] ) === (int) $en_id ? $ids[0] : 0;
	}

	/**
	 * Partner of any post in the other language (0 when none/invalid).
	 *
	 * @param int $post_id Post ID.
	 * @return int
	 */
	public static function partner( $post_id ) {
		return self::is_vi( $post_id ) ? self::valid_en_for( $post_id ) : self::valid_vi_for( $post_id );
	}

	/**
	 * Home URL of a language.
	 *
	 * @param string $lang vi|en.
	 * @return string
	 */
	public static function home( $lang ) {
		return 'vi' === $lang ? RBHVI_Config::page_url( 'home' ) : home_url( '/' );
	}

	/**
	 * Whether the Vietnamese home page (/vi/) is published.
	 *
	 * @return bool
	 */
	public static function vi_home_published() {
		$id = RBHVI_State::page_id( 'home' );
		return $id && 'publish' === get_post_status( $id ) && self::is_vi( $id );
	}

	/**
	 * Invalidate lookup caches.
	 */
	public static function flush_caches() {
		self::$memo = array();
	}
}
