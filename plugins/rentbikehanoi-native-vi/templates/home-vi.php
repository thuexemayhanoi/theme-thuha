<?php
/**
 * Vietnamese home page (/vi/): theme header/footer around the theme's landing
 * part, fed with Vietnamese data. Used only by RBHVI_Home::template().
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/landing', null, RBHVI_Home::data() );
}
get_footer();
