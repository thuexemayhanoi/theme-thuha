<?php
/**
 * Bilingual SEO through official hooks only.
 *
 * Yoast SEO stays the single owner of title, meta description, canonical,
 * robots, Open Graph, JSON-LD and the XML sitemap. This class only:
 * - prints hreflang links for confirmed, published, indexable pairs;
 * - adjusts Yoast through its public filters (og:locale, breadcrumbs,
 *   paginated listing canonical/title, empty-listing noindex, sitemap
 *   exclusions);
 * - when no SEO plugin is active, provides a minimal title/description
 *   fallback for Vietnamese pages (WordPress core prints canonical/robots).
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO integration.
 */
class RBHVI_SEO {

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_action( 'wp_head', array( __CLASS__, 'hreflang' ), 2 );
		add_filter( 'language_attributes', array( __CLASS__, 'language_attributes' ) );
		// Yoast SEO (public filters).
		add_filter( 'wpseo_og_locale', array( __CLASS__, 'og_locale' ) );
		add_filter( 'wpseo_breadcrumb_links', array( __CLASS__, 'breadcrumbs' ), 30 );
		add_filter( 'wpseo_canonical', array( __CLASS__, 'canonical' ), 20 );
		add_filter( 'wpseo_title', array( __CLASS__, 'paged_title' ), 20 );
		add_filter( 'wpseo_robots', array( __CLASS__, 'robots' ), 20 );
		add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', array( __CLASS__, 'sitemap_exclude_posts' ) );
		add_filter( 'wpseo_exclude_from_sitemap_by_term_ids', array( __CLASS__, 'sitemap_exclude_terms' ) );
		// WordPress core sitemaps (only active when no SEO plugin replaces them).
		add_filter( 'wp_sitemaps_posts_query_args', array( __CLASS__, 'core_sitemap_args' ) );
		add_filter( 'wp_sitemaps_taxonomies_query_args', array( __CLASS__, 'core_sitemap_term_args' ) );
		// Fallback head output only without an SEO plugin.
		add_filter( 'pre_get_document_title', array( __CLASS__, 'fallback_title' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'fallback_description' ), 3 );
		add_filter( 'wp_robots', array( __CLASS__, 'fallback_robots' ), 20 );
	}

	/**
	 * Detected SEO plugin: yoast|rankmath|aioseo|seopress|'' (none).
	 *
	 * @return string
	 */
	public static function seo_plugin() {
		if ( defined( 'WPSEO_VERSION' ) ) {
			return 'yoast';
		}
		if ( class_exists( 'RankMath' ) || defined( 'RANK_MATH_VERSION' ) ) {
			return 'rankmath';
		}
		if ( defined( 'AIOSEO_VERSION' ) ) {
			return 'aioseo';
		}
		if ( defined( 'SEOPRESS_VERSION' ) ) {
			return 'seopress';
		}
		return '';
	}

	/**
	 * Whether a post is indexable according to Yoast's per-post setting.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function indexable( $post_id ) {
		return '1' !== (string) get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true );
	}

	/**
	 * Alternate URLs for the current page: array( 'en' => url, 'vi' => url ),
	 * or an empty array when the page has no valid, indexable pair.
	 *
	 * @return array
	 */
	public static function alternates() {
		if ( ! RBHVI_State::is_enabled() || ! is_singular() || is_paged() || get_query_var( 'rbhvi_paged' ) ) {
			return array();
		}
		$id      = get_queried_object_id();
		$partner = RBHVI_Language::partner( $id );
		if ( ! $partner || ! self::indexable( $id ) || ! self::indexable( $partner ) ) {
			return array();
		}
		$is_vi = RBHVI_Language::is_vi( $id );
		$en    = $is_vi ? $partner : $id;
		$vi    = $is_vi ? $id : $partner;
		return array(
			'en' => (int) get_option( 'page_on_front' ) === $en ? home_url( '/' ) : get_permalink( $en ),
			'vi' => get_permalink( $vi ),
		);
	}

	/**
	 * Bidirectional hreflang with x-default → English.
	 */
	public static function hreflang() {
		$alternates = self::alternates();
		if ( ! $alternates ) {
			return;
		}
		$links = array(
			'en'        => $alternates['en'],
			'vi'        => $alternates['vi'],
			'x-default' => $alternates['en'],
		);
		foreach ( $links as $lang => $url ) {
			printf( '<link rel="alternate" hreflang="%s" href="%s" />' . "\n", esc_attr( $lang ), esc_url( $url ) );
		}
	}

	/**
	 * Correct lang attribute for Vietnamese previews (URL-based requests
	 * already use the vi locale).
	 *
	 * @param string $output Attributes.
	 * @return string
	 */
	public static function language_attributes( $output ) {
		if ( 'vi' !== RBHVI_Language::current() ) {
			return $output;
		}
		return false !== strpos( $output, 'lang=' ) ? preg_replace( '/lang="[^"]*"/', 'lang="vi"', $output ) : trim( $output . ' lang="vi"' );
	}

	/**
	 * Open Graph locale for Vietnamese pages.
	 *
	 * @param string $locale Locale.
	 * @return string
	 */
	public static function og_locale( $locale ) {
		return 'vi' === RBHVI_Language::current() ? 'vi_VN' : $locale;
	}

	/**
	 * Vietnamese breadcrumbs start at /vi/ ("Trang chủ") and never show an
	 * English crumb. Yoast reuses these links for BreadcrumbList JSON-LD.
	 *
	 * @param array $links Crumbs.
	 * @return array
	 */
	public static function breadcrumbs( $links ) {
		if ( 'vi' !== RBHVI_Language::current() || ! is_array( $links ) || ! $links ) {
			return $links;
		}
		$root  = RBHVI_State::page_id( 'home' );
		$first = array(
			'url'  => RBHVI_Language::home( 'vi' ),
			'text' => 'Trang chủ',
		);
		if ( is_singular() && (int) get_queried_object_id() === $root ) {
			return array( $first );
		}
		$result = array( $first );
		foreach ( array_slice( $links, 1 ) as $link ) {
			if ( isset( $link['id'] ) && (int) $link['id'] === $root ) {
				continue;
			}
			$result[] = $link;
		}
		$blog_id = RBHVI_State::page_id( 'blog' );
		if ( is_singular( 'post' ) && $blog_id && 'publish' === get_post_status( $blog_id ) ) {
			array_splice(
				$result,
				1,
				0,
				array(
					array(
						'url'  => get_permalink( $blog_id ),
						'text' => get_the_title( $blog_id ),
						'id'   => $blog_id,
					),
				)
			);
		}
		return $result;
	}

	/**
	 * Paginated Vietnamese blog listing: self-referencing canonical.
	 *
	 * @param string $canonical Canonical URL.
	 * @return string
	 */
	public static function canonical( $canonical ) {
		$paged = (int) get_query_var( 'rbhvi_paged' );
		if ( $paged > 1 && is_page( RBHVI_State::page_id( 'blog' ) ) ) {
			return trailingslashit( get_permalink( RBHVI_State::page_id( 'blog' ) ) ) . user_trailingslashit( 'page/' . $paged, 'paged' );
		}
		return $canonical;
	}

	/**
	 * Paginated listing title gets a page number (unique titles).
	 *
	 * @param string $title Title.
	 * @return string
	 */
	public static function paged_title( $title ) {
		$paged = (int) get_query_var( 'rbhvi_paged' );
		if ( $paged > 1 && is_page( RBHVI_State::page_id( 'blog' ) ) ) {
			return $title . ' – Trang ' . $paged;
		}
		return $title;
	}

	/**
	 * The Vietnamese blog listing is noindex while it lists no article
	 * (no thin page in the index).
	 *
	 * @param string $robots Robots value.
	 * @return string
	 */
	public static function robots( $robots ) {
		if ( self::is_empty_listing() ) {
			return 'noindex, follow';
		}
		return $robots;
	}

	/**
	 * Whether the current page is the Vietnamese blog listing without posts.
	 *
	 * @return bool
	 */
	public static function is_empty_listing() {
		$blog = RBHVI_State::page_id( 'blog' );
		return $blog && is_page( $blog ) && 0 === RBHVI_Listing::count();
	}

	/**
	 * Post IDs that must not appear in any sitemap.
	 *
	 * @return int[]
	 */
	public static function excluded_ids() {
		$vi = RBHVI_Language::vi_ids();
		if ( ! $vi ) {
			return array();
		}
		if ( ! RBHVI_State::is_enabled() ) {
			return $vi;
		}
		$settings = RBHVI_State::settings();
		$excluded = array();
		foreach ( $vi as $id ) {
			$path = trim( (string) wp_parse_url( get_permalink( $id ), PHP_URL_PATH ), '/' );
			$home = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
			if ( '' !== $home ) {
				$path = trim( substr( $path, strlen( $home ) ), '/' );
			}
			if ( ! RBHVI_Language::path_is_vi( $path ) ) {
				$excluded[] = $id; // Invalid: Vietnamese content outside /vi/.
			} elseif ( 'post' === get_post_type( $id ) && $settings['sitemap_pairs_only'] && ! RBHVI_Language::valid_en_for( $id ) ) {
				$excluded[] = $id; // Unpaired Vietnamese post.
			} elseif ( RBHVI_State::page_id( 'blog' ) === $id && 0 === RBHVI_Listing::count() ) {
				$excluded[] = $id; // Empty listing (noindex).
			}
		}
		return $excluded;
	}

	/**
	 * Yoast: exclude posts.
	 *
	 * @param int[] $ids IDs.
	 * @return int[]
	 */
	public static function sitemap_exclude_posts( $ids ) {
		return array_values( array_unique( array_merge( (array) $ids, self::excluded_ids() ) ) );
	}

	/**
	 * Yoast: exclude Vietnamese categories while /vi/ is OFF.
	 *
	 * @param int[] $ids Term IDs.
	 * @return int[]
	 */
	public static function sitemap_exclude_terms( $ids ) {
		if ( RBHVI_State::is_enabled() || ! RBHVI_Language::has_vi_terms() ) {
			return $ids;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
				'fields'     => 'ids',
				'meta_key'   => RBHVI_Language::TERM_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => 'vi', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		return array_values( array_unique( array_merge( (array) $ids, is_array( $terms ) ? $terms : array() ) ) );
	}

	/**
	 * Core sitemaps: same exclusions.
	 *
	 * @param array $args Query args.
	 * @return array
	 */
	public static function core_sitemap_args( $args ) {
		$excluded = self::excluded_ids();
		if ( $excluded ) {
			$args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(), $excluded ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
		}
		$args['rbhvi_skip'] = true;
		return $args;
	}

	/**
	 * Core sitemaps: term exclusions while OFF.
	 *
	 * @param array $args Term query args.
	 * @return array
	 */
	public static function core_sitemap_term_args( $args ) {
		$excluded = self::sitemap_exclude_terms( array() );
		if ( $excluded ) {
			$args['exclude'] = array_merge( isset( $args['exclude'] ) ? (array) $args['exclude'] : array(), $excluded );
		}
		return $args;
	}

	/**
	 * Whether this plugin should print fallback title/description.
	 *
	 * @return bool
	 */
	private static function fallback_active() {
		return '' === self::seo_plugin() && 'vi' === RBHVI_Language::current() && is_singular();
	}

	/**
	 * Fallback document title (no SEO plugin).
	 *
	 * @param string $title Title.
	 * @return string
	 */
	public static function fallback_title( $title ) {
		if ( ! self::fallback_active() ) {
			return $title;
		}
		$custom = (string) get_post_meta( get_queried_object_id(), RBHVI_Language::META_TITLE, true );
		return '' !== $custom ? $custom : $title;
	}

	/**
	 * Fallback meta description (no SEO plugin).
	 */
	public static function fallback_description() {
		if ( ! self::fallback_active() ) {
			return;
		}
		$desc = (string) get_post_meta( get_queried_object_id(), RBHVI_Language::META_DESC, true );
		if ( '' !== $desc ) {
			printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $desc ) );
		}
	}

	/**
	 * Fallback robots for the empty listing (no SEO plugin). Core prints
	 * the single robots tag.
	 *
	 * @param array $robots Directives.
	 * @return array
	 */
	public static function fallback_robots( $robots ) {
		if ( '' === self::seo_plugin() && self::is_empty_listing() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}
}
