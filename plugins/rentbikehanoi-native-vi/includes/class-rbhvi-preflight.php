<?php
/**
 * Checks run before /vi/ is switched ON, plus the post-switch route
 * verification used for automatic rollback. Read-only.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Preflight.
 */
class RBHVI_Preflight {

	/**
	 * Build a check row.
	 *
	 * @param string $id      Check ID.
	 * @param string $level   ok|warning|error.
	 * @param string $message Vietnamese, plain-language message.
	 * @return array
	 */
	private static function row( $id, $level, $message ) {
		return array(
			'id'      => $id,
			'level'   => $level,
			'message' => $message,
		);
	}

	/**
	 * Whether a list of checks contains an error.
	 *
	 * @param array $checks Checks.
	 * @return bool
	 */
	public static function has_errors( $checks ) {
		foreach ( $checks as $check ) {
			if ( 'error' === $check['level'] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * All preflight checks.
	 *
	 * @return array
	 */
	public static function run() {
		$checks   = array();
		$checks[] = get_option( 'permalink_structure' )
			? self::row( 'permalinks', 'ok', 'Đường dẫn tĩnh đang bật: ' . get_option( 'permalink_structure' ) )
			: self::row( 'permalinks', 'error', 'Đường dẫn tĩnh đang ở dạng "Đơn giản" (?p=123) nên /vi/ không thể hoạt động. Không cần sửa hosting: vào Cài đặt → Đường dẫn tĩnh và giữ cấu trúc hiện có của website trước khi bật.' );

		$checks = array_merge( $checks, self::slug_conflicts(), self::duplicate_checks(), self::rewrite_checks() );

		$multilingual = array_filter(
			array(
				'Polylang'      => defined( 'POLYLANG_VERSION' ),
				'WPML'          => defined( 'ICL_SITEPRESS_VERSION' ),
				'TranslatePress' => defined( 'TRP_PLUGIN_VERSION' ),
			)
		);
		$checks[]     = $multilingual
			? self::row( 'multilingual', 'error', 'Đang bật plugin đa ngôn ngữ khác (' . implode( ', ', array_keys( $multilingual ) ) . '). Hai hệ thống ngôn ngữ cùng lúc sẽ tạo URL và hreflang trùng.' )
			: self::row( 'multilingual', 'ok', 'Không có plugin đa ngôn ngữ khác.' );

		$seo_plugins = array_filter(
			array(
				'Yoast SEO'      => defined( 'WPSEO_VERSION' ),
				'Rank Math'      => class_exists( 'RankMath' ) || defined( 'RANK_MATH_VERSION' ),
				'All in One SEO' => defined( 'AIOSEO_VERSION' ),
				'SEOPress'       => defined( 'SEOPRESS_VERSION' ),
			)
		);
		if ( count( $seo_plugins ) > 1 ) {
			$checks[] = self::row( 'seo', 'error', 'Có nhiều plugin SEO cùng bật (' . implode( ', ', array_keys( $seo_plugins ) ) . '): title, canonical và schema sẽ bị in trùng.' );
		} elseif ( isset( $seo_plugins['Yoast SEO'] ) ) {
			$checks[] = self::row( 'seo', 'ok', 'Yoast SEO quản lý title, mô tả, canonical, robots, Open Graph, schema và sitemap. Plugin này chỉ thêm hreflang và bộ lọc chính thức của Yoast.' );
		} elseif ( $seo_plugins ) {
			$checks[] = self::row( 'seo', 'warning', key( $seo_plugins ) . ' chưa được kiểm thử với plugin này. Hreflang vẫn được in; hãy nhập title/mô tả tiếng Việt trong ' . key( $seo_plugins ) . ' và kiểm tra sitemap thủ công.' );
		} else {
			$checks[] = self::row( 'seo', 'warning', 'Không thấy plugin SEO. Plugin sẽ in title và mô tả dự phòng cho trang Việt; canonical và sitemap do WordPress tạo.' );
		}

		$checks[] = function_exists( 'rbh_home_url' )
			? self::row( 'theme', 'ok', 'Theme hỗ trợ nút VI | EN cạnh nút Sáng/Tối và menu tiếng Việt.' )
			: self::row( 'theme', 'warning', 'Theme đang dùng chưa có điểm gắn (hook) của bản 1.3.0: nút VI | EN và menu tiếng Việt sẽ không hiển thị. Cập nhật theme trước khi bật.' );

		$checks[] = 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' )
			? self::row( 'front', 'ok', 'Trang chủ tiếng Anh là một trang tĩnh; có thể ghép cặp với /vi/.' )
			: self::row( 'front', 'warning', 'Trang chủ tiếng Anh không phải trang tĩnh, nên trang chủ /vi/ sẽ không có hreflang.' );

		$home = RBHVI_State::page_id( 'home' );
		if ( ! $home ) {
			$checks[] = self::row( 'pages', 'warning', 'Chưa tạo bộ trang Việt. Có thể bật /vi/, nhưng mọi URL Việt sẽ trả 404 cho tới khi trang được tạo và xuất bản.' );
		} elseif ( 'publish' !== get_post_status( $home ) ) {
			$checks[] = self::row( 'pages', 'warning', 'Trang chủ /vi/ chưa xuất bản. Có thể bật, nhưng /vi/ trả 404 và nút VI | EN chưa hiện cho tới khi xuất bản.' );
		} else {
			$checks[] = self::row( 'pages', 'ok', 'Trang chủ /vi/ đã xuất bản.' );
		}
		return $checks;
	}

	/**
	 * Slug conflicts for /vi/ and the managed child paths.
	 *
	 * @return array
	 */
	private static function slug_conflicts() {
		$rows    = array();
		$home_id = RBHVI_State::page_id( 'home' );
		foreach ( array( 'post', 'attachment' ) as $type ) {
			$found = get_page_by_path( 'vi', OBJECT, $type );
			if ( $found && 'trash' !== $found->post_status && ( 'post' === $type || ! $found->post_parent ) ) {
				$rows[] = self::row( 'slug_vi_' . $type, 'error', 'Đã có ' . ( 'post' === $type ? 'bài viết' : 'tệp đính kèm' ) . ' dùng đường dẫn "vi" (ID ' . $found->ID . '). Đổi đường dẫn của mục đó trước khi bật /vi/.' );
			}
		}
		$category = get_term_by( 'slug', 'vi', 'category' );
		if ( $category && trim( (string) wp_parse_url( get_term_link( $category ), PHP_URL_PATH ), '/' ) === 'vi' ) {
			$rows[] = self::row( 'slug_vi_category', 'error', 'Chuyên mục "' . $category->name . '" đang dùng địa chỉ /vi/. Đổi đường dẫn chuyên mục trước khi bật.' );
		}
		foreach ( array_keys( RBHVI_Config::pages() ) as $key ) {
			$path  = RBHVI_Config::page_path( $key );
			$found = get_page_by_path( $path, OBJECT, 'page' );
			if ( $found && 'trash' !== $found->post_status && (int) $found->ID !== RBHVI_State::page_id( $key ) && get_post_meta( $found->ID, RBHVI_Language::META_KEY, true ) !== $key ) {
				$rows[] = self::row( 'slug_' . $key, 'error', 'Trang "' . $found->post_title . '" (ID ' . $found->ID . ') đã dùng /' . $path . '/ nhưng không do plugin quản lý. Đổi đường dẫn trang đó hoặc liên hệ người quản trị.' );
			}
		}
		if ( $home_id && 'vi' !== get_post_field( 'post_name', $home_id ) ) {
			$rows[] = self::row( 'slug_home', 'error', 'Trang chủ tiếng Việt (ID ' . $home_id . ') không còn đường dẫn "vi". Đặt lại đường dẫn là "vi".' );
		}
		if ( ! $rows ) {
			$rows[] = self::row( 'slugs', 'ok', 'Không có xung đột đường dẫn /vi/.' );
		}
		return $rows;
	}

	/**
	 * Duplicate managed pages and Vietnamese pages outside /vi/.
	 *
	 * @return array
	 */
	private static function duplicate_checks() {
		global $wpdb;
		$rows = array();
		$dups = $wpdb->get_results( $wpdb->prepare( "SELECT pm.meta_value AS k, COUNT(*) AS n FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND p.post_status <> 'trash' GROUP BY pm.meta_value HAVING n > 1", RBHVI_Language::META_KEY ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( $dups as $dup ) {
			$rows[] = self::row( 'duplicate_' . $dup->k, 'error', 'Có ' . (int) $dup->n . ' trang cùng là "' . $dup->k . '". Giữ một trang và chuyển các bản trùng vào thùng rác.' );
		}
		$outside = array();
		foreach ( RBHVI_Language::vi_ids() as $id ) {
			if ( 'page' !== get_post_type( $id ) || 'trash' === get_post_status( $id ) ) {
				continue;
			}
			$uri = get_page_uri( $id );
			if ( $uri && ! RBHVI_Language::path_is_vi( $uri ) ) {
				$outside[] = $id;
			}
		}
		if ( $outside ) {
			$rows[] = self::row( 'outside', 'warning', 'Trang tiếng Việt nằm ngoài /vi/ (ID ' . implode( ', ', $outside ) . ') sẽ không hiển thị và không vào sitemap. Đặt trang cha là trang chủ tiếng Việt.' );
		}
		return $rows;
	}

	/**
	 * Which existing rewrite rule would catch the /vi/ sample paths. The
	 * router overrides the result for /vi/, so foreign rules are warnings.
	 *
	 * @return array
	 */
	private static function rewrite_checks() {
		$rules = get_option( 'rewrite_rules' );
		if ( ! is_array( $rules ) || ! $rules ) {
			return array( self::row( 'rewrite', 'ok', 'Không có quy tắc rewrite riêng cần kiểm tra.' ) );
		}
		$foreign = array();
		foreach ( array( 'vi', 'vi/gioi-thieu', 'vi/blog/page/2', 'vi/chuyen-muc/meo' ) as $sample ) {
			foreach ( $rules as $match => $query ) {
				if ( preg_match( "#^$match#", $sample ) || preg_match( "#^$match#", urldecode( $sample ) ) ) {
					if ( ! preg_match( '/(pagename|name|attachment|page_id|p)=/', $query ) ) {
						$foreign[] = '/' . $sample . '/ → ' . $query;
					}
					break;
				}
			}
		}
		return $foreign
			? array( self::row( 'rewrite', 'warning', 'Có quy tắc rewrite khác khớp với /vi/ (' . implode( '; ', array_unique( $foreign ) ) . '). Plugin vẫn ưu tiên xử lý /vi/, nhưng nên kiểm tra plugin chuyển hướng hoặc plugin tạo quy tắc đó.' ) )
			: array( self::row( 'rewrite', 'ok', 'Không có quy tắc rewrite nào chiếm đường dẫn /vi/.' ) );
	}

	/**
	 * After switching ON: every published managed page must resolve to
	 * itself under /vi/ and English paths must stay English.
	 *
	 * @return array
	 */
	public static function verify_routes() {
		$rows = array();
		foreach ( array( '', 'about-us', 'blog/travel-guide', 'vietnam' ) as $english ) {
			if ( RBHVI_Language::path_is_vi( $english ) ) {
				$rows[] = self::row( 'verify_en', 'error', 'Đường dẫn tiếng Anh "/' . $english . '" bị nhận nhầm là tiếng Việt.' );
			}
		}
		foreach ( array_keys( RBHVI_Config::pages() ) as $key ) {
			$id = RBHVI_State::page_id( $key );
			if ( ! $id || 'publish' !== get_post_status( $id ) ) {
				continue;
			}
			$path     = RBHVI_Config::page_path( $key );
			$resolved = RBHVI_Router::resolve( $path );
			$link     = trim( (string) wp_parse_url( get_permalink( $id ), PHP_URL_PATH ), '/' );
			$home     = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
			if ( '' !== $home ) {
				$link = trim( substr( $link, strlen( $home ) ), '/' );
			}
			if ( empty( $resolved['pagename'] ) || $resolved['pagename'] !== $path || $link !== $path ) {
				$rows[] = self::row( 'verify_' . $key, 'error', 'Trang "' . get_the_title( $id ) . '" không mở được tại /' . $path . '/.' );
			}
		}
		if ( ! self::has_errors( $rows ) ) {
			$rows[] = self::row( 'verify', 'ok', 'Đã kiểm tra định tuyến /vi/ và URL tiếng Anh sau khi bật.' );
		}
		return $rows;
	}
}
