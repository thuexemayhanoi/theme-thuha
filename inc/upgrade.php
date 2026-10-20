<?php
defined( 'ABSPATH' ) || exit;

/** Presentation features only: no posts, metadata, menus or options are written. */
function rbh_upgrade_enabled( $key ) {
	return (bool) get_theme_mod( 'rbh_' . $key, true );
}
function rbh_upgrade_assets() {
	$dir = get_template_directory();
	wp_enqueue_style( 'rbh-upgrade', get_template_directory_uri() . '/assets/css/upgrade.css', array( 'rbh-main' ), (string) filemtime( $dir . '/assets/css/upgrade.css' ) );
	wp_enqueue_script( 'rbh-upgrade', get_template_directory_uri() . '/assets/js/upgrade.js', array( 'rbh-main' ), (string) filemtime( $dir . '/assets/js/upgrade.js' ), true );
	wp_add_inline_script( 'rbh-upgrade', 'window.rbhUpgrade=' . wp_json_encode( array(
		'floating' => rbh_upgrade_enabled( 'floating_manager' ),
		'copied' => __( 'Link copied', 'rentbike-hanoi' ),
		'copyFailed' => __( 'Copy this link manually:', 'rentbike-hanoi' ),
	) ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'rbh_upgrade_assets', 20 );

function rbh_upgrade_reading() {
	if ( ! is_single() || post_password_required() ) { return; }
	if ( rbh_upgrade_enabled( 'reading_time' ) ) {
		$text = wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', get_the_ID() ) ) );
		$words = preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
		$minutes = max( 1, (int) ceil( count( $words ?: array() ) / 220 ) );
		echo '<p class="rbh-reading-time">' . esc_html( sprintf( _n( 'About %d minute to read', 'About %d minutes to read', $minutes, 'rentbike-hanoi' ), $minutes ) ) . '</p>';
	}
	if ( rbh_upgrade_enabled( 'reading_progress' ) ) {
		echo '<div class="rbh-reading-progress" aria-hidden="true"><span></span></div>';
	}
}
add_action( 'rbh_hero', 'rbh_upgrade_reading', 15 );

function rbh_upgrade_article_tools() {
	if ( ! is_single() || post_password_required() ) { return; }
	if ( rbh_upgrade_enabled( 'share_links' ) ) {
		$url = get_permalink();
		echo '<div class="rbh-share" data-share-url="' . esc_url( $url ) . '" data-share-title="' . esc_attr( get_the_title() ) . '"><span>' . esc_html__( 'Share this guide', 'rentbike-hanoi' ) . '</span><button type="button" data-rbh-share hidden>' . esc_html__( 'Share', 'rentbike-hanoi' ) . '</button><button type="button" data-rbh-copy hidden>' . esc_html__( 'Copy link', 'rentbike-hanoi' ) . '</button><a href="mailto:?subject=' . rawurlencode( get_the_title() ) . '&amp;body=' . rawurlencode( $url ) . '">' . esc_html__( 'Email', 'rentbike-hanoi' ) . '</a><span class="rbh-copy-status" role="status" aria-live="polite"></span><input class="rbh-copy-fallback" type="text" readonly hidden aria-label="' . esc_attr__( 'Article link', 'rentbike-hanoi' ) . '" value="' . esc_url( $url ) . '"></div>';
	}
	if ( rbh_upgrade_enabled( 'article_navigation' ) ) {
		the_post_navigation( array( 'prev_text' => '<span class="rbh-nav-label">' . esc_html__( 'Previous article', 'rentbike-hanoi' ) . '</span><span>%title</span>', 'next_text' => '<span class="rbh-nav-label">' . esc_html__( 'Next article', 'rentbike-hanoi' ) . '</span><span>%title</span>', 'in_same_term' => true, 'taxonomy' => 'category', 'screen_reader_text' => __( 'More articles in this category', 'rentbike-hanoi' ) ) );
	}
}
add_action( 'rbh_article_end', 'rbh_upgrade_article_tools', 5 );

function rbh_upgrade_category_hub() {
	if ( ! is_category() || is_paged() || ! rbh_upgrade_enabled( 'category_hub' ) ) { return; }
	$children = get_terms( array( 'taxonomy' => 'category', 'parent' => get_queried_object_id(), 'hide_empty' => true, 'number' => 50, 'orderby' => 'name', 'order' => 'ASC' ) );
	if ( is_wp_error( $children ) || ! $children ) { return; }
	echo '<nav class="rbh-category-hub" aria-label="' . esc_attr__( 'Subcategories', 'rentbike-hanoi' ) . '"><h2>' . esc_html__( 'Explore this topic', 'rentbike-hanoi' ) . '</h2><ul>';
	foreach ( $children as $term ) {
		$url = get_term_link( $term );
		if ( is_wp_error( $url ) ) { continue; }
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a></li>';
	}
	echo '</ul></nav>';
}
add_action( 'rbh_before_content', 'rbh_upgrade_category_hub', 5 );

function rbh_upgrade_search_panel() {
	if ( ! rbh_upgrade_enabled( 'quick_search' ) ) { return; }
	echo '<dialog id="rbh-quick-search" class="rbh-search-dialog" aria-labelledby="rbh-search-title"><div class="rbh-search-heading"><h2 id="rbh-search-title">' . esc_html__( 'Search rental guides', 'rentbike-hanoi' ) . '</h2><button type="button" data-rbh-search-close>' . esc_html__( 'Close', 'rentbike-hanoi' ) . '</button></div>';
	get_search_form();
	echo '</dialog>';
}
add_action( 'wp_footer', 'rbh_upgrade_search_panel', 5 );

function rbh_upgrade_customize( $manager ) {
	$manager->add_section( 'rbh_upgrade', array( 'title' => __( 'Reading & mobile tools', 'rentbike-hanoi' ), 'panel' => 'rbh' ) );
	$fields = array( 'quick_search' => 'Quick search', 'floating_manager' => 'Avoid overlapping floating controls', 'reading_progress' => 'Reading progress bar', 'reading_time' => 'Estimated reading time', 'article_navigation' => 'Previous / next article in category', 'share_links' => 'Share and copy link', 'category_hub' => 'Category child links' );
	foreach ( $fields as $key => $label ) {
		$manager->add_setting( 'rbh_' . $key, array( 'default' => true, 'sanitize_callback' => 'rbh_checkbox' ) );
		$manager->add_control( 'rbh_' . $key, array( 'label' => __( $label, 'rentbike-hanoi' ), 'section' => 'rbh_upgrade', 'type' => 'checkbox' ) );
	}
}
add_action( 'customize_register', 'rbh_upgrade_customize', 20 );
