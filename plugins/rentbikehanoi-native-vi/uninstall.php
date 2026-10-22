<?php
/**
 * Uninstall: removes only this plugin's own options and transients.
 * Vietnamese pages/posts, their language marker, pairs and Yoast fields are
 * user content and are intentionally kept. English content is never touched.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

foreach ( array( 'rbhvi_enabled', 'rbhvi_history', 'rbhvi_settings', 'rbhvi_seed_log' ) as $rbhvi_option ) {
	delete_option( $rbhvi_option );
}
// The registry (rbhvi_registry) is kept so a reinstall re-adopts the same pages instead of reporting conflicts.
