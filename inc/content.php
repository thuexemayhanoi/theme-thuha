<?php
defined( 'ABSPATH' ) || exit;

/** Read existing homepage HTML. No content is saved or replaced. */
function rbh_home_data() {
	static $result;
	if ( null !== $result ) { return $result; }
	$result = array( 'image' => 0, 'caption' => '', 'intro' => '', 'table' => '', 'faqs' => array(), 'sections' => array(), 'bikes' => array() );
	$post = get_post( get_option( 'page_on_front' ) );
	if ( ! $post || post_password_required( $post ) ) { return $result; }
	// Run WordPress blocks, shortcodes and plugin content filters once for the page.
	// Filtering every extracted section separately would insert several plugin TOCs.
	$raw = class_exists( 'DOMDocument' ) ? apply_filters( 'the_content', $post->post_content ) : $post->post_content;
	preg_match( '/wp-image-(\d+)/', $raw, $image );
	$result['image'] = ! empty( $image[1] ) ? absint( $image[1] ) : get_post_thumbnail_id( $post );
	if ( ! class_exists( 'DOMDocument' ) ) { return $result; }
	$doc = new DOMDocument( '1.0', 'UTF-8' );
	$previous = libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="UTF-8"><div id="rbh-source">' . $raw . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );
	$xpath = new DOMXPath( $doc );
	$caption = $xpath->query( '//figcaption' )->item( 0 );
	$result['caption'] = $caption ? wp_kses_post( $doc->saveHTML( $caption ) ) : '';
	foreach ( $xpath->query( '//p' ) as $paragraph ) {
		if ( strlen( trim( $paragraph->textContent ) ) > 70 ) { $result['intro'] = trim( $paragraph->textContent ); break; }
	}
	foreach ( $xpath->query( '//table' ) as $table ) {
		$text = $table->textContent;
		if ( ! $result['table'] && false !== stripos( $text, 'deposit' ) && false !== stripos( $text, 'Rental Duration' ) ) {
			$result['table'] = wp_kses_post( $doc->saveHTML( $table ) );
		}
		if ( false !== stripos( $text, 'Question' ) && false !== stripos( $text, 'Answer' ) ) {
			foreach ( $table->getElementsByTagName( 'tr' ) as $row ) {
				$cells = $row->getElementsByTagName( 'td' );
				if ( $cells->length < 2 ) { continue; }
				$question = preg_replace( '/^Q\d+:\s*/', '', trim( $cells->item( 0 )->textContent ) );
				$result['faqs'][] = array( $question, trim( $cells->item( 1 )->textContent ) );
			}
		}
	}
	$source = $xpath->query( '//*[@id="rbh-source"]' )->item( 0 );
	$sections = array();
	$current = array( 'title' => __( 'About our rental service', 'rentbike-hanoi' ), 'id' => '', 'html' => '' );
	if ( $source ) {
		foreach ( $source->childNodes as $node ) {
			if ( $node instanceof DOMComment || ( $node instanceof DOMElement && 'hr' === $node->tagName ) ) { continue; }
			if ( $node instanceof DOMElement && 'h2' === $node->tagName ) {
				if ( trim( $current['html'] ) ) { $sections[] = $current; }
				$current = array( 'title' => trim( $node->textContent ), 'id' => $node->getAttribute( 'id' ), 'html' => '' );
			} else { $current['html'] .= $doc->saveHTML( $node ); }
		}
		if ( trim( $current['html'] ) ) { $sections[] = $current; }
	}
	$result['sections'] = $sections;
	// Pick existing images from the rental content, not a separate inventory.
	foreach ( array( 'Automatic' => 'Honda-Vision', 'Semi-automatic' => 'Honda-Wave', '50cc' => 'Honda-Cub-50cc' ) as $label => $token ) {
		foreach ( $xpath->query( '//img' ) as $img ) {
			if ( false === stripos( $img->getAttribute( 'src' ), $token ) ) { continue; }
			preg_match( '/wp-image-(\d+)/', $img->getAttribute( 'class' ), $id );
			if ( ! empty( $id[1] ) ) { $result['bikes'][] = array( 'label' => $label, 'image' => absint( $id[1] ) ); }
			break;
		}
	}
	return $result;
}
function rbh_display_content() {
	do_action( 'rbh_before_content', get_the_ID() );
	$content = apply_filters( 'the_content', get_the_content() );
	if ( rbh_setting( 'toc' ) && is_single() && false === strpos( $content, 'lwptoc' ) && false === strpos( $content, 'ez-toc' ) && false === strpos( $content, 'rank-math-toc' ) ) {
		$headings = array();
		$index = 0;
		$content = preg_replace_callback( '~<h([23])([^>]*)>(.*?)</h[23]>~is', function( $match ) use ( &$headings, &$index, $content ) {
			$index++;
			$attrs = $match[2];
			if ( preg_match( '/\bid=["\']([^"\']+)["\']/', $attrs, $id_match ) ) { $id = $id_match[1]; }
			else {
				$id = 'rbh-section-' . $index;
				while ( strpos( $content, 'id="' . $id . '"' ) !== false ) { $id .= '-r'; }
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}
			$headings[] = array( 'id' => $id, 'title' => wp_strip_all_tags( $match[3] ), 'level' => $match[1] );
			return '<h' . $match[1] . $attrs . '>' . $match[3] . '</h' . $match[1] . '>';
		}, $content );
		if ( count( $headings ) >= 3 ) {
			echo '<details class="article-toc"><summary>' . esc_html__( 'On this page', 'rentbike-hanoi' ) . '</summary><nav aria-label="' . esc_attr__( 'Table of contents', 'rentbike-hanoi' ) . '"><ol>';
			foreach ( $headings as $heading ) {
				echo '<li class="toc-level-' . absint( $heading['level'] ) . '"><a href="#' . esc_attr( $heading['id'] ) . '">' . esc_html( $heading['title'] ) . '</a></li>';
			}
			echo '</ol></nav></details>';
		}
	}
	echo $content; // Core the_content pipeline: preserve embeds, blocks and shortcodes.
	do_action( 'rbh_after_content', get_the_ID() );
	wp_link_pages( array( 'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Article pages', 'rentbike-hanoi' ) . '">', 'after' => '</nav>' ) );
}
function rbh_date() {
	if ( ! rbh_setting( 'dates' ) ) { return; }
	echo '<p class="entry-meta">' . esc_html__( 'Updated', 'rentbike-hanoi' ) . ' <time datetime="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . esc_html( get_the_modified_date() ) . '</time></p>';
}
function rbh_related() {
	get_template_part( 'template-parts/related', null, array( 'ids' => rbh_related_ids() ) );
}
