<?php
/**
 * Local-only: adds a 240x80 PNG custom logo, like the production header (custom_logo is set there).
 * Usage: wp eval-file tests/integration/fixture-logo.php [remove]
 */
if ( ! defined( 'WP_CLI' ) || false !== strpos( home_url(), 'rentbikehanoi.com' ) ) {
	exit( "Refusing to run outside a local WP-CLI test site.\n" );
}
if ( isset( $args[0] ) && 'remove' === $args[0] ) {
	remove_theme_mod( 'custom_logo' );
	WP_CLI::success( 'Logo removed.' );
	return;
}
$upload = wp_upload_dir();
$file   = trailingslashit( $upload['path'] ) . 'rbh-test-logo.png';
$image  = imagecreatetruecolor( 240, 80 );
imagefill( $image, 0, 0, imagecolorallocate( $image, 174, 37, 44 ) );
imagestring( $image, 5, 20, 30, 'NGUYEN HA (test logo)', imagecolorallocate( $image, 255, 255, 255 ) );
imagepng( $image, $file );
$id = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => 'Test logo', 'post_status' => 'inherit' ), $file );
require_once ABSPATH . 'wp-admin/includes/image.php';
wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
set_theme_mod( 'custom_logo', $id );
WP_CLI::success( 'Logo set: ' . $id );
