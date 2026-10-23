<?php
defined( 'ABSPATH' ) || exit;
define( 'RBH_VERSION', '1.4.0' );
foreach ( array( 'settings', 'navigation', 'content', 'components', 'upgrade', 'assistant', 'seo' ) as $rbh_module ) {
	require_once get_template_directory() . '/inc/' . $rbh_module . '.php';
}

/** Keep Yoast as the sole structured-data source while preserving other wp_head output. */
function rbh_head_buffer_start() { ob_start(); }
function rbh_head_buffer_end() {
	$head = (string) ob_get_clean();
	if ( false !== stripos( $head, 'LocalBusiness' ) ) {
		$head = preg_replace_callback(
			'~<script[^>]+application/ld\\+json[^>]*>.*?</script>~is',
			function( $match ) {
				return false !== stripos( $match[0], 'LocalBusiness' ) && false === stripos( $match[0], 'yoast-schema-graph' ) ? '' : $match[0];
			},
			$head
		);
	}
	echo $head; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted wp_head output with one targeted block removed.
}
add_action( 'wp_head', 'rbh_head_buffer_start', 0 );
add_action( 'wp_head', 'rbh_head_buffer_end', PHP_INT_MAX );

function rbh_setup() {
	load_theme_textdomain( 'rentbike-hanoi', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 240, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	register_nav_menus( array( 'primary' => __( 'Primary navigation', 'rentbike-hanoi' ), 'footer' => __( 'Additional footer links', 'rentbike-hanoi' ) ) );
	add_editor_style( array( 'assets/css/tokens.css', 'assets/css/editor.css' ) );
}
add_action( 'after_setup_theme', 'rbh_setup' );

function rbh_assets() {
	wp_enqueue_style( 'rbh-tokens', get_template_directory_uri() . '/assets/css/tokens.css', array(), RBH_VERSION );
	wp_enqueue_style( 'rbh-main', get_template_directory_uri() . '/assets/css/main.css', array( 'rbh-tokens' ), RBH_VERSION );
	wp_enqueue_script( 'rbh-main', get_template_directory_uri() . '/assets/js/main.js', array(), RBH_VERSION, true );
	wp_add_inline_script( 'rbh-main', 'window.rbhLabels=' . wp_json_encode( array( 'light' => __( 'Light', 'rentbike-hanoi' ), 'dark' => __( 'Dark', 'rentbike-hanoi' ), 'auto' => __( 'Auto', 'rentbike-hanoi' ) ) ) . ';', 'before' );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	// Only loaded while a language plugin shows the VI | EN switch; English-only pages are unchanged.
	if ( apply_filters( 'rbh_language_switch_enabled', false ) ) {
		wp_enqueue_style( 'rbh-language-switch', get_template_directory_uri() . '/assets/css/language-switch.css', array( 'rbh-main' ), RBH_VERSION );
	}
	$accent = sanitize_hex_color( rbh_setting( 'accent' ) );
	if ( $accent ) {
		wp_add_inline_style( 'rbh-main', ':root{--rbh-accent-light:' . $accent . ';}' );
	}
}
add_action( 'wp_enqueue_scripts', 'rbh_assets' );
function rbh_editor_tokens( $settings ) {
	$accent = sanitize_hex_color( rbh_setting( 'accent' ) );
	if ( $accent ) { $settings['styles'][] = array( 'css' => ':root,.editor-styles-wrapper{--rbh-accent-light:' . $accent . ';}' ); }
	return $settings;
}
add_filter( 'block_editor_settings_all', 'rbh_editor_tokens' );
function rbh_sidebar_register() {
	register_sidebar( array( 'name' => __( 'Article sidebar', 'rentbike-hanoi' ), 'id' => 'rbh-sidebar', 'description' => __( 'Optional widgets below search and rental links.', 'rentbike-hanoi' ), 'before_widget' => '<section id="%1$s" class="side-panel widget %2$s">', 'after_widget' => '</section>', 'before_title' => '<h2 class="side-title">', 'after_title' => '</h2>' ) );
}
add_action( 'widgets_init', 'rbh_sidebar_register' );

/** Touch only this theme's settings. Never create or rewrite posts or menus. */
function rbh_activate( $old_name = '', $old_theme = null ) {
	if ( ! get_option( 'rbh_activation_snapshot' ) ) {
		add_option( 'rbh_activation_snapshot', array(
			'created_at' => gmdate( 'c' ),
			'previous_stylesheet' => $old_theme instanceof WP_Theme ? $old_theme->get_stylesheet() : '',
			'front_page' => get_option( 'page_on_front' ),
			'posts_page' => get_option( 'page_for_posts' ),
			'permalink_structure' => get_option( 'permalink_structure' ),
		), '', false );
	}
	$old_mods = $old_theme instanceof WP_Theme ? get_option( 'theme_mods_' . $old_theme->get_stylesheet(), array() ) : array();
	if ( ! get_theme_mod( 'custom_logo' ) && ! empty( $old_mods['custom_logo'] ) && wp_attachment_is_image( $old_mods['custom_logo'] ) ) {
		set_theme_mod( 'custom_logo', absint( $old_mods['custom_logo'] ) );
	}
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( array( 'primary' => 'menu', 'footer' => 'footer-menu' ) as $location => $slug ) {
		if ( ! empty( $locations[ $location ] ) && wp_get_nav_menu_object( $locations[ $location ] ) ) { continue; }
		$old_id = isset( $old_mods['nav_menu_locations'][ $location ] ) ? absint( $old_mods['nav_menu_locations'][ $location ] ) : 0;
		$menu = $old_id ? wp_get_nav_menu_object( $old_id ) : false;
		if ( ! $menu && rbh_is_target_site() ) { $menu = wp_get_nav_menu_object( $slug ); }
		if ( $menu && ! is_wp_error( $menu ) ) { $locations[ $location ] = (int) $menu->term_id; }
	}
	set_theme_mod( 'nav_menu_locations', $locations );
}
add_action( 'after_switch_theme', 'rbh_activate', 10, 2 );

function rbh_body_classes( $classes ) {
	$classes[] = 'rbh-theme';
	if ( rbh_setting( 'dock' ) ) { $classes[] = 'has-contact-dock'; }
	return $classes;
}
add_filter( 'body_class', 'rbh_body_classes' );

/** Keeps the single visible title in the template; original saved content stays intact. */
function rbh_content_headings( $content ) {
	if ( ! is_admin() && is_singular() && in_the_loop() && is_main_query() ) {
		$content = preg_replace( '~<(/?)h1\b([^>]*)>~i', '<$1h2$2>', $content );
	}
	return $content;
}
add_filter( 'the_content', 'rbh_content_headings', 99 );
