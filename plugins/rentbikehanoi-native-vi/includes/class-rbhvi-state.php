<?php
/**
 * Plugin-owned state: /vi/ on/off, transition history (undo), settings and
 * the registry of managed page IDs. Options only — never post content.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * State and transitions.
 */
class RBHVI_State {

	const OPT_ENABLED  = 'rbhvi_enabled';
	const OPT_HISTORY  = 'rbhvi_history';
	const OPT_SETTINGS = 'rbhvi_settings';
	const OPT_REGISTRY = 'rbhvi_registry';
	const OPT_SEED_LOG = 'rbhvi_seed_log';

	/**
	 * Activation: intentionally writes nothing. /vi/ remains OFF until an
	 * administrator enables it after preflight.
	 */
	public static function activate() {}

	/**
	 * Deactivation: keep all Vietnamese content, pairs and settings.
	 * Yoast's sitemap cache is cleared so /vi/ URLs are not advertised while
	 * the plugin is inactive.
	 */
	public static function deactivate() {
		self::clear_seo_caches();
	}

	/**
	 * Whether /vi/ routing is ON.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		return (bool) get_option( self::OPT_ENABLED, false );
	}

	/**
	 * Settings with defaults.
	 *
	 * @return array
	 */
	public static function settings() {
		$saved = get_option( self::OPT_SETTINGS, array() );
		return wp_parse_args(
			is_array( $saved ) ? $saved : array(),
			array(
				// Requirement: unpaired Vietnamese posts stay out of the sitemap.
				'sitemap_pairs_only' => true,
			)
		);
	}

	/**
	 * Save settings.
	 *
	 * @param array $settings Settings.
	 */
	public static function save_settings( $settings ) {
		update_option( self::OPT_SETTINGS, array( 'sitemap_pairs_only' => ! empty( $settings['sitemap_pairs_only'] ) ), false );
		self::clear_seo_caches();
	}

	/**
	 * Managed page IDs keyed by page key.
	 *
	 * @return array
	 */
	public static function registry() {
		$registry = get_option( self::OPT_REGISTRY, array() );
		return is_array( $registry ) ? array_map( 'absint', $registry ) : array();
	}

	/**
	 * Managed page ID for a key, if the post still exists.
	 *
	 * @param string $key Page key.
	 * @return int
	 */
	public static function page_id( $key ) {
		$registry = self::registry();
		$id       = isset( $registry[ $key ] ) ? $registry[ $key ] : 0;
		if ( ! $id ) {
			return 0;
		}
		$post = get_post( $id );
		return $post && 'page' === $post->post_type && 'trash' !== $post->post_status ? $id : 0;
	}

	/**
	 * Store a managed page ID.
	 *
	 * @param string $key Page key.
	 * @param int    $id  Post ID.
	 */
	public static function remember_page( $key, $id ) {
		$registry         = self::registry();
		$registry[ $key ] = (int) $id;
		update_option( self::OPT_REGISTRY, $registry, true );
	}

	/**
	 * Transition history, newest last.
	 *
	 * @return array
	 */
	public static function history() {
		$history = get_option( self::OPT_HISTORY, array() );
		return is_array( $history ) ? $history : array();
	}

	/**
	 * Low-level switch, with a history record.
	 *
	 * @param bool   $enabled Target state.
	 * @param string $action  enable|disable|undo|rollback.
	 * @param string $note    Optional note.
	 */
	private static function write( $enabled, $action, $note = '' ) {
		$from = self::is_enabled();
		update_option( self::OPT_ENABLED, $enabled ? 1 : 0, true );
		$history   = self::history();
		$history[] = array(
			'time'   => time(),
			'user'   => get_current_user_id(),
			'from'   => $from,
			'to'     => (bool) $enabled,
			'action' => $action,
			'note'   => $note,
		);
		update_option( self::OPT_HISTORY, array_slice( $history, -20 ), false );
		self::clear_seo_caches();
	}

	/**
	 * Enable /vi/: preflight first; automatic rollback when post-switch
	 * verification fails.
	 *
	 * @param string $action History label.
	 * @return array{ok:bool,checks:array,verify:array}
	 */
	public static function enable( $action = 'enable' ) {
		$checks = RBHVI_Preflight::run();
		if ( RBHVI_Preflight::has_errors( $checks ) ) {
			return array(
				'ok'     => false,
				'checks' => $checks,
				'verify' => array(),
			);
		}
		$previous = self::is_enabled();
		self::write( true, $action );
		$verify = RBHVI_Preflight::verify_routes();
		if ( RBHVI_Preflight::has_errors( $verify ) ) {
			self::write( $previous, 'rollback', 'Kiểm tra sau khi bật thất bại; đã tự quay về trạng thái trước.' );
			return array(
				'ok'     => false,
				'checks' => $checks,
				'verify' => $verify,
			);
		}
		return array(
			'ok'     => true,
			'checks' => $checks,
			'verify' => $verify,
		);
	}

	/**
	 * Disable /vi/. Content is untouched; Vietnamese URLs answer 404.
	 *
	 * @param string $action History label.
	 */
	public static function disable( $action = 'disable' ) {
		self::write( false, $action );
	}

	/**
	 * Undo the most recent transition.
	 *
	 * @return array{ok:bool,message:string,result:array}
	 */
	public static function undo() {
		$history = self::history();
		if ( ! $history ) {
			return array(
				'ok'      => false,
				'message' => 'no_history',
				'result'  => array(),
			);
		}
		$last = end( $history );
		if ( $last['from'] ) {
			$result = self::enable( 'undo' );
			return array(
				'ok'      => $result['ok'],
				'message' => $result['ok'] ? 'undo_enabled' : 'undo_failed',
				'result'  => $result,
			);
		}
		self::disable( 'undo' );
		return array(
			'ok'      => true,
			'message' => 'undo_disabled',
			'result'  => array(),
		);
	}

	/**
	 * Clear Yoast's sitemap cache through its public class, when present.
	 */
	public static function clear_seo_caches() {
		if ( class_exists( 'WPSEO_Sitemaps_Cache' ) && method_exists( 'WPSEO_Sitemaps_Cache', 'clear' ) ) {
			WPSEO_Sitemaps_Cache::clear();
		}
		RBHVI_Language::flush_caches();
	}
}
