<?php
/**
 * Vietnamese home page (/vi/) in the same landing layout as the English
 * homepage. Scope: only the managed "home" page, only when the theme provides
 * template-parts/landing.php and the setting "home_layout" is "landing".
 * Nothing is written to the database: saved post_content is kept and shown in
 * the "details" section, images are reused from the English homepage.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Vietnamese landing.
 */
class RBHVI_Home {

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_filter( 'template_include', array( __CLASS__, 'template' ), 99 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ), 30 );
	}

	/**
	 * Whether the current request should use the landing layout.
	 *
	 * @return bool
	 */
	public static function applies() {
		$id = RBHVI_State::page_id( 'home' );
		if ( ! $id || is_admin() || ! is_page( $id ) || post_password_required( $id ) || ! RBHVI_Language::is_vi( $id ) ) {
			return false;
		}
		$settings = RBHVI_State::settings();
		return 'landing' === $settings['home_layout'] && '' !== locate_template( 'template-parts/landing.php' );
	}

	/**
	 * Use the plugin's thin template (theme header/footer + theme landing part).
	 *
	 * @param string $template Template.
	 * @return string
	 */
	public static function template( $template ) {
		return self::applies() ? RBHVI_DIR . 'templates/home-vi.php' : $template;
	}

	/**
	 * Body classes of the landing (same hooks as the English home).
	 *
	 * @param string[] $classes Classes.
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		if ( self::applies() ) {
			$classes[] = 'rbhvi-home';
		}
		return $classes;
	}

	/**
	 * Copy config.
	 *
	 * @return array
	 */
	public static function config() {
		return require RBHVI_DIR . 'config/home-vi.php';
	}

	/**
	 * Link item for a managed page key.
	 *
	 * @param string $key   Page key.
	 * @param string $label Label (defaults to the menu label).
	 * @return array
	 */
	private static function page_link( $key, $label = '' ) {
		$page = RBHVI_Config::page( $key );
		return array(
			'label' => '' !== $label ? $label : $page['menu_label'],
			'url'   => RBHVI_Config::page_url( $key ),
		);
	}

	/**
	 * Plain text of a config string with placeholders resolved.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private static function text( $text ) {
		return html_entity_decode( RBHVI_Config::render( $text ), ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Existing images of the English homepage (read-only, no content filters):
	 * hero = theme Customizer hero image, else first content image, else
	 * featured image (same rule as front-page.php); bikes by file-name token.
	 *
	 * @param string[] $tokens File-name tokens.
	 * @return array{hero:int,caption:string,bikes:array}
	 */
	public static function english_media( $tokens ) {
		$result = array(
			'hero'    => 0,
			'caption' => '',
			'bikes'   => array(),
		);
		$front  = 'page' === get_option( 'show_on_front' ) ? get_post( (int) get_option( 'page_on_front' ) ) : null;
		$raw    = $front && 'publish' === $front->post_status && ! post_password_required( $front ) ? (string) $front->post_content : '';
		preg_match( '/wp-image-(\d+)/', $raw, $first );
		$content_image  = ! empty( $first[1] ) ? absint( $first[1] ) : ( $front ? (int) get_post_thumbnail_id( $front ) : 0 );
		$custom         = absint( get_theme_mod( 'rbh_hero_image', 0 ) );
		$result['hero'] = $custom && wp_attachment_is_image( $custom ) ? $custom : $content_image;
		if ( $result['hero'] && $result['hero'] === $content_image && preg_match( '#<figcaption\b[^>]*>.*?</figcaption>#is', $raw, $caption ) ) {
			$result['caption'] = wp_kses_post( $caption[0] );
		}
		preg_match_all( '/<img\b[^>]*>/i', $raw, $images );
		foreach ( $tokens as $token ) {
			foreach ( $images[0] as $img ) {
				if ( preg_match( '/\bsrc=["\'][^"\']*' . preg_quote( $token, '/' ) . '/i', $img ) && preg_match( '/wp-image-(\d+)/', $img, $id ) ) {
					$result['bikes'][ $token ] = absint( $id[1] );
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * Saved page content split into collapsible sections at each H2,
	 * like the English homepage's "details" area. Content is not modified.
	 *
	 * @param string $html  Filtered content.
	 * @param string $first Title of the part before the first H2.
	 * @return array
	 */
	public static function sections( $html, $first ) {
		if ( '' === trim( $html ) ) {
			return array();
		}
		if ( ! class_exists( 'DOMDocument' ) ) {
			return array(
				array(
					'title' => $first,
					'id'    => '',
					'html'  => $html,
				),
			);
		}
		$doc      = new DOMDocument( '1.0', 'UTF-8' );
		$previous = libxml_use_internal_errors( true );
		$doc->loadHTML( '<?xml encoding="UTF-8"><div id="rbhvi-source">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		$root     = $doc->getElementById( 'rbhvi-source' );
		$sections = array();
		$current  = array(
			'title' => $first,
			'id'    => '',
			'html'  => '',
		);
		foreach ( $root ? iterator_to_array( $root->childNodes ) : array() as $node ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API.
			if ( $node instanceof DOMComment ) {
				continue;
			}
			if ( $node instanceof DOMElement && 'h2' === strtolower( $node->tagName ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API.
				if ( '' !== trim( wp_strip_all_tags( $current['html'] ) ) || false !== strpos( $current['html'], '<a ' ) ) {
					$sections[] = $current;
				}
				$current = array(
					'title' => trim( $node->textContent ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					'id'    => $node->getAttribute( 'id' ),
					'html'  => '',
				);
				continue;
			}
			$current['html'] .= $doc->saveHTML( $node );
		}
		if ( '' !== trim( wp_strip_all_tags( $current['html'] ) ) || false !== strpos( $current['html'], '<a ' ) ) {
			$sections[] = $current;
		}
		return $sections;
	}

	/**
	 * Everything template-parts/landing.php needs.
	 *
	 * @return array
	 */
	public static function data() {
		$c     = self::config();
		$b     = RBHVI_Config::business();
		$id    = get_the_ID();
		$media = self::english_media( wp_list_pluck( $c['bikes']['items'], 'token' ) );
		$data  = array(
			'post_id'      => $id,
			'eyebrow'      => $b['name'],
			'title'        => get_the_title( $id ),
			'lede'         => self::text( $c['lede'] ),
			'buttons'      => array(),
			'notes'        => array_map( array( __CLASS__, 'text' ), $c['notes'] ),
			'hero_image'   => $media['hero'],
			// Same rule as the English hero fallback (the hero title); the photo itself is not described.
			'hero_alt'     => get_the_title( $id ),
			'hero_caption' => $media['caption'],
			'photo_label'  => $c['photo_label'],
			'fallback'     => $c['fallback'],
			'quick_links'  => array(),
		);
		// Keep the phone number on one line inside narrow buttons.
		$phone_nbsp = str_replace( ' ', "\u{00A0}", $b['phone_display'] );
		foreach ( $c['buttons'] as $button ) {
			$data['buttons'][] = array(
				'label' => str_replace( $b['phone_display'], $phone_nbsp, self::text( $button['label'] ) ),
				'url'   => self::text( $button['url'] ),
			);
		}
		foreach ( $c['quick_links'] as $key ) {
			$data['quick_links'][] = self::page_link( $key );
		}
		$data['bikes'] = array(
			'eyebrow' => $c['bikes']['eyebrow'],
			'title'   => $c['bikes']['title'],
			'text'    => $c['bikes']['text'],
			'items'   => array(),
		);
		foreach ( $c['bikes']['items'] as $item ) {
			$data['bikes']['items'][] = array(
				'label' => $item['label'],
				'image' => isset( $media['bikes'][ $item['token'] ] ) ? $media['bikes'][ $item['token'] ] : 0,
				'alt'   => $item['alt'],
				'text'  => self::text( $item['text'] ),
				'link'  => self::page_link( $item['link'], $item['link_label'] ),
			);
		}
		$data['rates'] = array(
			'eyebrow' => $c['rates']['eyebrow'],
			'title'   => $c['rates']['title'],
			'text'    => self::text( $c['rates']['text'] ),
			'link'    => self::page_link( $c['rates']['link'], $c['rates']['link_label'] ),
			'table'   => preg_replace( '/<!--.*?-->/s', '', RBHVI_Config::price_table() ),
		);
		$data['steps'] = $c['steps'];
		$faq_items     = require RBHVI_DIR . 'content/faq-items.php';
		$data['faq']   = array(
			'eyebrow' => $c['faq']['eyebrow'],
			'title'   => $c['faq']['title'],
			'link'    => self::page_link( $c['faq']['link'], $c['faq']['link_label'] ),
			'items'   => array(),
		);
		foreach ( $c['faq']['items'] as $index ) {
			if ( isset( $faq_items[ $index ] ) ) {
				$data['faq']['items'][] = array( self::text( $faq_items[ $index ][0] ), self::text( $faq_items[ $index ][1] ) );
			}
		}
		$data['details'] = array(
			'eyebrow'  => $c['details']['eyebrow'],
			'title'    => $c['details']['title'],
			'sections' => self::sections( (string) apply_filters( 'the_content', get_post_field( 'post_content', $id ) ), $c['details']['first_title'] ),
		);
		$guides          = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'has_password'        => false,
				'posts_per_page'      => 3,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'fields'              => 'ids',
				'rbhvi_lang'          => 'vi',
			)
		);
		$blog_id         = RBHVI_State::page_id( 'blog' );
		$data['guides']  = array(
			'eyebrow' => $c['guides']['eyebrow'],
			'title'   => $c['guides']['title'],
			'link'    => $blog_id && 'publish' === get_post_status( $blog_id ) ? self::page_link( $c['guides']['link'], $c['guides']['link_label'] ) : array(),
			'ids'     => array_map( 'intval', $guides->posts ),
		);
		return $data;
	}
}
