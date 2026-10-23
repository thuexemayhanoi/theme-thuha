<?php
defined( 'ABSPATH' ) || exit;

/** Home link used by shared templates. A language plugin may point it to its own home (e.g. /vi/). */
function rbh_home_url() {
	return (string) apply_filters( 'rbh_home_url', home_url( '/' ) );
}
function rbh_menu_items( $location = 'primary' ) {
	static $cache = array();
	if ( isset( $cache[ $location ] ) ) { return apply_filters( 'rbh_menu_items', $cache[ $location ], $location ); }
	$locations = get_nav_menu_locations();
	$menu_id = isset( $locations[ $location ] ) ? $locations[ $location ] : 0;
	if ( ! $menu_id && rbh_is_target_site() ) {
		$menu = wp_get_nav_menu_object( 'footer' === $location ? 'footer-menu' : 'menu' );
		$menu_id = $menu ? $menu->term_id : 0;
	}
	$items = $menu_id ? wp_get_nav_menu_items( $menu_id ) : array();
	$cache[ $location ] = is_array( $items ) ? array_filter( $items, function( $item ) { return empty( $item->_invalid ) && ! empty( $item->url ); } ) : array();
	// Presentation-only hook: never renames or saves menu items (labels are frozen).
	return apply_filters( 'rbh_menu_items', $cache[ $location ], $location );
}
function rbh_menu_group( $item ) {
	$label = strtolower( $item->title );
	if ( preg_match( '/privacy|terms|legal/', $label ) ) { $group = 'legal'; }
	elseif ( 'taxonomy' === $item->type || preg_match( '/blog|guide|tips|resources/', $label ) ) { $group = 'guides'; }
	elseif ( preg_match( '/price|scooter|rental rules|requirement|delivery|monthly|50cc/', $label ) ) { $group = 'rent'; }
	else { $group = 'company'; }
	$group = apply_filters( 'rbh_menu_group', $group, $item );
	return in_array( $group, array( 'rent', 'guides', 'company', 'legal' ), true ) ? $group : 'company';
}
function rbh_link( $item, $class = '' ) {
	$url = $item->url;
	$current = untrailingslashit( $url ) === untrailingslashit( home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) ) );
	$target = '_blank' === $item->target ? ' target="_blank" rel="noopener noreferrer"' : '';
	echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '"' . ( $current ? ' aria-current="page"' : '' ) . $target . '>' . esc_html( $item->title ) . '</a>';
}
function rbh_menu_tree( $items, $parent = 0, $depth = 0 ) {
	if ( $depth > 8 ) { return; }
	echo '<ul>';
	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent !== (int) $parent ) { continue; }
		echo '<li>';
		rbh_link( $item );
		$children = array_filter( $items, function( $child ) use ( $item ) { return (int) $child->menu_item_parent === (int) $item->ID; } );
		if ( $children ) { rbh_menu_tree( $items, $item->ID, $depth + 1 ); }
		echo '</li>';
	}
	echo '</ul>';
}
function rbh_primary_navigation() {
	$items = rbh_menu_items();
	if ( ! $items ) {
		echo '<a href="' . esc_url( rbh_home_url() ) . '">' . esc_html__( 'Home', 'rentbike-hanoi' ) . '</a>';
		return;
	}
	$shown = array();
	foreach ( $items as $item ) {
		if ( 0 === (int) $item->menu_item_parent && in_array( rbh_menu_group( $item ), array( 'rent', 'guides' ), true ) && count( $shown ) < 5 ) {
			rbh_link( $item );
			$shown[] = $item->ID;
		}
	}
	// The shared Menu button exposes every item on desktop, tablet and phone.
}
function rbh_footer_groups() {
	$groups = array( 'rent' => array(), 'guides' => array(), 'company' => array(), 'legal' => array() );
	$seen = array();
	foreach ( array_merge( array_values( rbh_menu_items() ), array_values( rbh_menu_items( 'footer' ) ) ) as $item ) {
		$key = untrailingslashit( $item->url );
		if ( isset( $seen[ $key ] ) ) { continue; }
		$seen[ $key ] = true;
		$groups[ rbh_menu_group( $item ) ][] = $item;
	}
	return $groups;
}
function rbh_find_link( $needle ) {
	foreach ( rbh_menu_items() as $item ) {
		if ( false !== stripos( $item->title, $needle ) ) { return $item; }
	}
	return null;
}
function rbh_contact_url( $channel ) {
	if ( 'phone' === $channel ) {
		$number = preg_replace( '/[^0-9+]/', '', rbh_setting( 'phone' ) );
		return $number ? 'tel:' . $number : '';
	}
	return rbh_setting( $channel );
}
function rbh_cta( $label = '', $class = 'button' ) {
	if ( ! rbh_setting( 'cta' ) ) { return; }
	$url = rbh_cta_url();
	$label = rbh_setting( 'cta_label' ) ?: ( $label ?: rbh_setting( 'cta_' . rbh_content_context() . '_label' ) );
	if ( $url ) {
		echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $label ) . ' <span aria-hidden="true">↗</span></a>';
	}
}
function rbh_brand() {
	if ( has_custom_logo() ) { the_custom_logo(); }
	else {
		echo '<a class="brand" href="' . esc_url( rbh_home_url() ) . '"><span class="brand-mark" aria-hidden="true">NH<span>.</span></span><span class="brand-text">' . esc_html( rbh_setting( 'brand' ) ) . '</span></a>';
	}
}
function rbh_breadcrumb() {
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		$html = yoast_breadcrumb( '', '', false );
		if ( $html ) { echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'rentbike-hanoi' ) . '">' . $html . '</nav>'; return; }
	}
	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'rentbike-hanoi' ) . '"><a href="' . esc_url( rbh_home_url() ) . '">' . esc_html__( 'Home', 'rentbike-hanoi' ) . '</a><span aria-hidden="true"> / </span>';
	if ( is_single() ) {
		$terms = get_the_category();
		if ( $terms ) { echo '<a href="' . esc_url( get_category_link( $terms[0] ) ) . '">' . esc_html( $terms[0]->name ) . '</a><span aria-hidden="true"> / </span>'; }
	}
	echo '<span aria-current="page">' . esc_html( is_singular() ? get_the_title() : ( is_search() ? __( 'Search', 'rentbike-hanoi' ) : wp_strip_all_tags( get_the_archive_title() ) ) ) . '</span></nav>';
}
