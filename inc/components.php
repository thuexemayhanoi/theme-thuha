<?php
defined( 'ABSPATH' ) || exit;

/** Presentation context only. No new taxonomy, metadata or saved content. */
function rbh_content_context( $post_id = 0 ) {
	$post_id = $post_id ?: get_queried_object_id();
	$context = is_front_page() ? 'rental' : 'general';
	if ( is_singular() ) {
		foreach ( array( 'tips', 'travel' ) as $kind ) {
			$category = absint( rbh_setting( $kind . '_category' ) );
			foreach ( wp_get_post_categories( $post_id ) as $id ) {
				if ( $category && ( $id === $category || in_array( $category, get_ancestors( $id, 'category', 'taxonomy' ), true ) ) ) { $context = $kind; break 2; }
			}
		}
		if ( 'general' === $context ) {
			foreach ( wp_get_post_tags( $post_id ) as $tag ) {
				$tag_text = strtolower( str_replace( '-', ' ', $tag->name . ' ' . $tag->slug ) );
				if ( preg_match( '/\\b(?:rent|rental|hire)\\b/i', $tag_text ) ) { $context = 'rental'; break; }
			}
		}
		if ( 'general' === $context ) {
			foreach ( rbh_menu_items() as $item ) {
				if ( 'post_type' === $item->type && (int) $item->object_id === (int) $post_id && 'rent' === rbh_menu_group( $item ) ) { $context = 'rental'; break; }
			}
		}
	}
	$context = apply_filters( 'rbh_content_context', $context, $post_id );
	return in_array( $context, array( 'rental', 'travel', 'tips', 'general' ), true ) ? $context : 'general';
}

function rbh_cta_url() {
	if ( rbh_setting( 'cta_url' ) ) { return esc_url_raw( rbh_setting( 'cta_url' ) ); }
	$channel = rbh_setting( 'cta_channel' );
	foreach ( 'auto' === $channel ? array( 'whatsapp', 'phone', 'zalo' ) : array( $channel ) as $key ) {
		if ( in_array( $key, array( 'whatsapp', 'phone', 'zalo' ), true ) && rbh_contact_url( $key ) ) { return rbh_contact_url( $key ); }
	}
	$item = rbh_find_link( 'Contact' );
	return $item ? $item->url : '';
}

function rbh_render_cta( $placement = 'article' ) {
	if ( ! rbh_setting( 'cta' ) || ! rbh_cta_url() || ( is_singular() && post_password_required() ) ) { return; }
	get_template_part( 'template-parts/cta', null, array( 'placement' => $placement, 'context' => rbh_content_context() ) );
}

/** A small set of existing menu destinations, excluding self and in-content links. */
function rbh_context_links() {
	if ( ! rbh_setting( 'internal_links' ) || ! is_single() || post_password_required() ) { return array(); }
	$needles = array( 'rental' => array( 'Prices', 'Rules', 'FAQ' ), 'travel' => array( 'Travel Guide', 'Rules', 'Delivery' ), 'tips' => array( 'Rental Tips', 'Rules', 'Prices' ), 'general' => array( 'Rental Blog', 'FAQ', 'Contact' ) );
	$seen = array( untrailingslashit( get_permalink() ) => true );
	preg_match_all( '~href\s*=\s*["\']([^"\']+)["\']~i', get_post_field( 'post_content', get_the_ID() ), $matches );
	foreach ( $matches[1] as $url ) {
		$url = html_entity_decode( $url, ENT_QUOTES, 'UTF-8' );
		if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) { $url = home_url( $url ); }
		$seen[ untrailingslashit( strtok( $url, '#' ) ) ] = true;
	}
	$links = array();
	foreach ( $needles[ rbh_content_context() ] as $needle ) {
		$item = rbh_find_link( $needle );
		if ( ! $item || wp_parse_url( $item->url, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) { continue; }
		$key = untrailingslashit( $item->url );
		if ( isset( $seen[ $key ] ) ) { continue; }
		$links[] = $item;
		$seen[ $key ] = true;
		if ( count( $links ) >= 3 ) { break; }
	}
	return $links;
}

/** Tag matches first, then the most specific categories. Maximum six, no private posts. */
function rbh_related_ids() {
	$id = get_the_ID();
	if ( ! rbh_setting( 'related' ) || ! is_single() || post_password_required( $id ) ) { return array(); }
	$limit = rbh_related_limit( rbh_setting( 'related_limit' ) );
	$exclude = array( $id );
	foreach ( rbh_context_links() as $link ) { if ( 'post_type' === $link->type ) { $exclude[] = (int) $link->object_id; } }
	$terms = get_the_category( $id );
	$parents = array();
	foreach ( $terms as $term ) { $parents = array_merge( $parents, get_ancestors( $term->term_id, 'category', 'taxonomy' ) ); }
	$categories = array_values( array_diff( wp_list_pluck( $terms, 'term_id' ), $parents ) );
	$passes = array( array( 'category__in' => $categories ), array( 'tag__in' => wp_get_post_tags( $id, array( 'fields' => 'ids' ) ) ) );
	$ids = array();
	foreach ( $passes as $tax ) {
		if ( ! reset( $tax ) || count( $ids ) >= $limit ) { continue; }
		$query = new WP_Query( array_merge( array( 'post_type' => 'post', 'post_status' => 'publish', 'has_password' => false, 'posts_per_page' => $limit - count( $ids ), 'post__not_in' => array_merge( $exclude, $ids ), 'ignore_sticky_posts' => true, 'no_found_rows' => true, 'fields' => 'ids', 'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' ) ), $tax ) );
		$ids = array_merge( $ids, $query->posts );
	}
	return $ids;
}

function rbh_article_end() {
	rbh_render_cta();
	get_template_part( 'template-parts/context-links', null, array( 'links' => rbh_context_links() ) );
}
function rbh_footer_cta() { rbh_render_cta( 'footer' ); }
function rbh_banner() { get_template_part( 'template-parts/banner' ); }
add_action( 'rbh_article_end', 'rbh_article_end', 10 );
add_action( 'rbh_related', 'rbh_related', 10 );
add_action( 'rbh_footer_cta', 'rbh_footer_cta', 10 );
add_action( 'rbh_banner', 'rbh_banner', 10 );

// Honor the theme's master TOC flag without changing LuckyWP's saved settings.
function rbh_plugin_toc_enabled( $enabled ) { return is_admin() ? $enabled : ( $enabled && rbh_setting( 'toc' ) ); }
add_filter( 'lwptoc_active', 'rbh_plugin_toc_enabled' );
