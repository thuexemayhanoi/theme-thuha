<?php
/**
 * /vi/ routing without rewrite rules: the request path is resolved in the
 * core "request" filter, so nothing needs flushing, no .htaccess change is
 * required and switching OFF takes effect immediately (Vietnamese URLs 404).
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Router.
 */
class RBHVI_Router {

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_filter( 'request', array( __CLASS__, 'request' ), 1 );
		add_filter( 'post_link', array( __CLASS__, 'post_link' ), 10, 2 );
		add_filter( 'term_link', array( __CLASS__, 'term_link' ), 10, 3 );
		add_filter( 'redirect_canonical', array( __CLASS__, 'redirect_canonical' ), 10, 2 );
		add_filter( 'do_redirect_guess_404_permalink', array( __CLASS__, 'guess_404' ) );
		add_action( 'template_redirect', array( __CLASS__, 'guard' ), 0 );
	}

	/**
	 * Public query vars owned by the plugin.
	 *
	 * @param array $vars Vars.
	 * @return array
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'rbhvi_lang';
		$vars[] = 'rbhvi_paged';
		return $vars;
	}

	/**
	 * Replace query vars for /vi/ paths. English paths are returned untouched.
	 *
	 * @param array $query_vars Parsed vars.
	 * @return array
	 */
	public static function request( $query_vars ) {
		global $wp;
		$path = isset( $wp->request ) ? trim( (string) $wp->request, '/' ) : RBHVI_Language::request_path();
		if ( ! RBHVI_Language::path_is_vi( $path ) ) {
			return $query_vars;
		}
		if ( ! RBHVI_State::is_enabled() ) {
			return array( 'error' => '404' );
		}
		$resolved = self::resolve( $path, $query_vars );
		if ( isset( $query_vars['preview'] ) ) {
			$resolved['preview'] = $query_vars['preview'];
		}
		return $resolved;
	}

	/**
	 * Map a Vietnamese path to WordPress query vars.
	 *
	 * @param string $path       Path such as "vi/lien-he".
	 * @param array  $query_vars Original vars (search terms).
	 * @return array
	 */
	public static function resolve( $path, $query_vars = array() ) {
		$sub    = trim( substr( trim( $path, '/' ), 2 ), '/' );
		$search = isset( $query_vars['s'] ) ? trim( (string) $query_vars['s'] ) : '';
		$lang   = array( 'rbhvi_lang' => 'vi' );

		if ( '' !== $search && ( '' === $sub || preg_match( '#^page/(\d+)$#', $sub ) ) ) {
			$vars = $lang + array( 's' => $search );
			if ( preg_match( '#^page/(\d+)$#', $sub, $m ) ) {
				$vars['paged'] = (int) $m[1];
			}
			return $vars;
		}
		if ( '' === $sub ) {
			return $lang + array( 'pagename' => 'vi' );
		}
		$blog = RBHVI_Config::page( 'blog' );
		if ( $blog && preg_match( '#^' . preg_quote( $blog['path'], '#' ) . '(?:/page/(\d+))?$#', $sub, $m ) ) {
			$vars = $lang + array( 'pagename' => 'vi/' . $blog['path'] );
			if ( ! empty( $m[1] ) ) {
				$vars['rbhvi_paged'] = (int) $m[1];
			}
			return $vars;
		}
		if ( preg_match( '#^chuyen-muc/([^/]+)(?:/page/(\d+))?$#', $sub, $m ) ) {
			$term = get_term_by( 'slug', sanitize_title( $m[1] ), 'category' );
			if ( $term && RBHVI_Language::term_is_vi( $term ) ) {
				$vars = $lang + array( 'cat' => (int) $term->term_id );
				if ( ! empty( $m[2] ) ) {
					$vars['paged'] = (int) $m[2];
				}
				return $vars;
			}
			return $lang + array( 'error' => '404' );
		}
		$page = get_page_by_path( 'vi/' . $sub, OBJECT, 'page' );
		if ( $page && RBHVI_Language::is_vi( $page ) ) {
			return $lang + array( 'pagename' => 'vi/' . $sub );
		}
		if ( false === strpos( $sub, '/' ) ) {
			$post_id = self::vi_post_by_slug( $sub );
			if ( $post_id ) {
				return $lang + array( 'p' => $post_id );
			}
		}
		return $lang + array( 'error' => '404' );
	}

	/**
	 * Vietnamese post with the slug, any non-trashed status (WordPress
	 * itself then decides visibility, so drafts stay 404 for visitors).
	 *
	 * @param string $slug Post slug.
	 * @return int
	 */
	private static function vi_post_by_slug( $slug ) {
		$query = new WP_Query(
			array(
				'name'             => sanitize_title( $slug ),
				'post_type'        => 'post',
				'post_status'      => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page'   => 1,
				'fields'           => 'ids',
				'no_found_rows'    => true,
				'suppress_filters' => true,
				'rbhvi_skip'       => true,
				'tax_query'        => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => RBHVI_Language::TAX,
						'field'    => 'slug',
						'terms'    => RBHVI_Language::TERM,
					),
				),
			)
		);
		return $query->posts ? (int) $query->posts[0] : 0;
	}

	/**
	 * Vietnamese posts live at /vi/{slug}/. The URL does not depend on the
	 * ON/OFF state, so Yoast's stored permalinks stay correct.
	 *
	 * @param string  $link Link.
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function post_link( $link, $post ) {
		if ( 'publish' !== $post->post_status || '' === $post->post_name || ! RBHVI_Language::is_vi( $post ) || ! get_option( 'permalink_structure' ) ) {
			return $link;
		}
		return home_url( user_trailingslashit( 'vi/' . $post->post_name, 'single' ) );
	}

	/**
	 * Vietnamese categories live at /vi/chuyen-muc/{slug}/.
	 *
	 * @param string  $link     Link.
	 * @param WP_Term $term     Term.
	 * @param string  $taxonomy Taxonomy.
	 * @return string
	 */
	public static function term_link( $link, $term, $taxonomy ) {
		if ( 'category' !== $taxonomy || ! RBHVI_Language::term_is_vi( $term ) || ! get_option( 'permalink_structure' ) ) {
			return $link;
		}
		return home_url( user_trailingslashit( 'vi/chuyen-muc/' . $term->slug, 'category' ) );
	}

	/**
	 * Never let a canonical redirect move a Vietnamese URL outside /vi/.
	 *
	 * @param string|false $redirect  Redirect URL.
	 * @param string       $requested Requested URL.
	 * @return string|false
	 */
	public static function redirect_canonical( $redirect, $requested ) {
		if ( ! $redirect ) {
			return $redirect;
		}
		$from = trim( (string) wp_parse_url( $requested, PHP_URL_PATH ), '/' );
		$to   = trim( (string) wp_parse_url( $redirect, PHP_URL_PATH ), '/' );
		$home = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
		if ( '' !== $home ) {
			$from = trim( preg_replace( '#^' . preg_quote( $home, '#' ) . '#', '', $from ), '/' );
			$to   = trim( preg_replace( '#^' . preg_quote( $home, '#' ) . '#', '', $to ), '/' );
		}
		if ( RBHVI_Language::path_is_vi( $from ) !== RBHVI_Language::path_is_vi( $to ) ) {
			return false;
		}
		return $redirect;
	}

	/**
	 * No "did you mean" guessing from /vi/ to an English URL.
	 *
	 * @param bool $guess Whether to guess.
	 * @return bool
	 */
	public static function guess_404( $guess ) {
		return RBHVI_Language::path_is_vi( RBHVI_Language::request_path() ) ? false : $guess;
	}

	/**
	 * Vietnamese content is only served under /vi/ while ON; Vietnamese
	 * categories only under /vi/chuyen-muc/. Previews are always allowed.
	 */
	public static function guard() {
		if ( is_preview() || is_404() ) {
			return;
		}
		$on_vi_path = RBHVI_Language::path_is_vi( RBHVI_Language::request_path() );
		$vi_object  = ( is_singular() && RBHVI_Language::is_vi( get_queried_object_id() ) )
			|| ( is_category() && RBHVI_Language::term_is_vi( get_queried_object_id() ) );
		if ( $vi_object && ( ! $on_vi_path || ! RBHVI_State::is_enabled() ) ) {
			self::not_found();
		}
	}

	/**
	 * Switch the current request to a 404.
	 */
	public static function not_found() {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
