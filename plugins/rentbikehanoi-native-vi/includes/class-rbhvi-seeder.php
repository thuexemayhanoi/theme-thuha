<?php
/**
 * Creates the Vietnamese core pages as DRAFTS on explicit admin request.
 * Safe to run repeatedly: existing managed pages are never duplicated or
 * overwritten, foreign pages at a target path are reported as conflicts
 * and left untouched, nothing is ever published, English posts are never
 * written to.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seeder.
 */
class RBHVI_Seeder {

	/**
	 * Create missing drafts.
	 *
	 * @return array<string,array{status:string,id:int,message:string}>
	 */
	public static function run() {
		$report = array();
		if ( ! RBHVI_Language::ensure_term() ) {
			return array(
				'_error' => array(
					'status'  => 'error',
					'id'      => 0,
					'message' => 'Không tạo được nhãn ngôn ngữ tiếng Việt.',
				),
			);
		}
		$pages = RBHVI_Config::pages();
		// Home first: every other page is its child.
		uksort(
			$pages,
			function ( $a, $b ) {
				return ( 'home' === $b ) - ( 'home' === $a );
			}
		);
		foreach ( $pages as $key => $page ) {
			$report[ $key ] = self::seed_one( $key, $page );
			if ( 'home' === $key && ! $report[ $key ]['id'] ) {
				break; // Without /vi/ itself no child page can be created safely.
			}
		}
		update_option(
			RBHVI_State::OPT_SEED_LOG,
			array(
				'time'   => time(),
				'user'   => get_current_user_id(),
				'report' => $report,
			),
			false
		);
		return $report;
	}

	/**
	 * Seed one page.
	 *
	 * @param string $key  Page key.
	 * @param array  $page Definition.
	 * @return array
	 */
	private static function seed_one( $key, $page ) {
		$existing = RBHVI_State::page_id( $key );
		if ( $existing ) {
			return self::result( 'exists', $existing, 'Đã có, giữ nguyên nội dung hiện tại.' );
		}
		$path  = RBHVI_Config::page_path( $key );
		$found = get_page_by_path( $path, OBJECT, 'page' );
		if ( $found && 'trash' !== $found->post_status ) {
			if ( get_post_meta( $found->ID, RBHVI_Language::META_KEY, true ) === $key ) {
				RBHVI_State::remember_page( $key, $found->ID );
				return self::result( 'adopted', $found->ID, 'Đã nhận lại trang do plugin tạo trước đó.' );
			}
			return self::result( 'conflict', $found->ID, 'Đã có một trang khác ở /' . $path . '/. Không tạo trang mới để tránh trùng lặp.' );
		}
		$parent = 'home' === $key ? 0 : RBHVI_State::page_id( 'home' );
		$slug   = '' === $page['path'] ? 'vi' : $page['path'];
		$meta   = array(
			RBHVI_Language::META_KEY   => $key,
			RBHVI_Language::META_TITLE => RBHVI_Config::render( $page['seo_title'] ),
			RBHVI_Language::META_DESC  => RBHVI_Config::render( $page['seo_desc'] ),
			'_yoast_wpseo_title'       => RBHVI_Config::render( $page['seo_title'] ),
			'_yoast_wpseo_metadesc'    => RBHVI_Config::render( $page['seo_desc'] ),
		);
		$suggestion = self::suggest_english( $page['en'] );
		if ( $suggestion ) {
			$meta[ RBHVI_Language::META_SUGG ] = $suggestion;
		}
		$id = wp_insert_post(
			array(
				'post_type'      => 'page',
				'post_status'    => 'draft',
				'post_title'     => RBHVI_Config::render( $page['title'] ),
				'post_name'      => $slug,
				'post_parent'    => $parent,
				'menu_order'     => (int) $page['menu_order'],
				'post_content'   => RBHVI_Config::page_content( $key ),
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'meta_input'     => $meta,
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return self::result( 'error', 0, 'Không tạo được trang: ' . $id->get_error_message() );
		}
		RBHVI_Language::set_vi( $id, true );
		RBHVI_State::remember_page( $key, $id );
		return self::result( 'created', $id, 'Đã tạo bản nháp. Chưa xuất bản.' );
	}

	/**
	 * Suggested English counterpart (stored as a suggestion only).
	 *
	 * @param array|null $en Definition.
	 * @return int
	 */
	public static function suggest_english( $en ) {
		if ( ! $en ) {
			return 0;
		}
		if ( 'front' === $en['type'] ) {
			$id = 'page' === get_option( 'show_on_front' ) ? (int) get_option( 'page_on_front' ) : 0;
		} else {
			$post = get_page_by_path( $en['path'], OBJECT, $en['type'] );
			$id   = $post ? (int) $post->ID : 0;
		}
		return $id && 'publish' === get_post_status( $id ) && ! RBHVI_Language::is_vi( $id ) ? $id : 0;
	}

	/**
	 * Result row.
	 *
	 * @param string $status  Status.
	 * @param int    $id      Post ID.
	 * @param string $message Message.
	 * @return array
	 */
	private static function result( $status, $id, $message ) {
		return array(
			'status'  => $status,
			'id'      => (int) $id,
			'message' => $message,
		);
	}
}
