<?php
/**
 * Access to the locked business data and the managed page registry.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Config reader and placeholder renderer.
 */
class RBHVI_Config {

	/**
	 * Cached business data.
	 *
	 * @var array|null
	 */
	private static $business;

	/**
	 * Cached page registry.
	 *
	 * @var array|null
	 */
	private static $pages;

	/**
	 * Locked business facts.
	 *
	 * @return array
	 */
	public static function business() {
		if ( null === self::$business ) {
			self::$business = require RBHVI_DIR . 'config/business.php';
		}
		return self::$business;
	}

	/**
	 * Managed page definitions, keyed by page key.
	 *
	 * @return array
	 */
	public static function pages() {
		if ( null === self::$pages ) {
			self::$pages = require RBHVI_DIR . 'config/pages.php';
		}
		return self::$pages;
	}

	/**
	 * One page definition.
	 *
	 * @param string $key Page key.
	 * @return array|null
	 */
	public static function page( $key ) {
		$pages = self::pages();
		return isset( $pages[ $key ] ) ? $pages[ $key ] : null;
	}

	/**
	 * Full request path of a managed page, e.g. "vi/lien-he".
	 *
	 * @param string $key Page key.
	 * @return string
	 */
	public static function page_path( $key ) {
		$page = self::page( $key );
		if ( ! $page ) {
			return '';
		}
		return '' === $page['path'] ? 'vi' : 'vi/' . $page['path'];
	}

	/**
	 * Final public URL of a managed page (valid once the page is published).
	 *
	 * @param string $key Page key.
	 * @return string
	 */
	public static function page_url( $key ) {
		return home_url( user_trailingslashit( self::page_path( $key ) ) );
	}

	/**
	 * Replace {{placeholders}} with locked data. Unknown placeholders are left
	 * visible on purpose so tests can catch them.
	 *
	 * @param string $text Template text.
	 * @return string
	 */
	public static function render( $text ) {
		$data = self::business();
		return preg_replace_callback(
			'/\{\{([a-z_]+)(?::([a-z0-9_-]+))?\}\}/',
			function ( $found ) use ( $data ) {
				$name = $found[1];
				$arg  = isset( $found[2] ) ? $found[2] : '';
				if ( 'link' === $name && self::page( $arg ) ) {
					return esc_url( self::page_url( $arg ) );
				}
				if ( 'price_table' === $name ) {
					return self::price_table( '' === $arg ? array() : explode( '-and-', $arg ) );
				}
				if ( preg_match( '/^price_(day|week|month)$/', $name, $field ) ) {
					foreach ( $data['prices'] as $price ) {
						if ( $price['key'] === $arg ) {
							return esc_html( null === $price[ $field[1] ] ? $data['price_day_unknown'] : $price[ $field[1] ] );
						}
					}
					return $found[0];
				}
				if ( 'contact_buttons' === $name ) {
					return self::contact_buttons();
				}
				if ( 'faq' === $name ) {
					return self::faq_block();
				}
				if ( isset( $data[ $name ] ) && is_string( $data[ $name ] ) ) {
					return esc_html( $data[ $name ] );
				}
				return $found[0];
			},
			$text
		);
	}

	/**
	 * Price table (block markup) built from the locked prices.
	 *
	 * @param string[] $only Optional price keys to include.
	 * @return string
	 */
	public static function price_table( $only = array() ) {
		$data = self::business();
		$rows = '';
		foreach ( $data['prices'] as $price ) {
			if ( $only && ! in_array( $price['key'], $only, true ) ) {
				continue;
			}
			$day   = null === $price['day'] ? $data['price_day_unknown'] : $price['day'];
			$rows .= '<tr><td>' . esc_html( $price['label'] ) . '</td><td>' . esc_html( $day ) . '</td><td>' . esc_html( $price['week'] ) . '</td><td>' . esc_html( $price['month'] ) . '</td></tr>';
		}
		return '<!-- wp:table {"className":"rbhvi-price-table"} --><figure class="wp-block-table rbhvi-price-table"><table><thead><tr><th>Loại xe</th><th>Theo ngày</th><th>Theo tuần</th><th>Theo tháng</th></tr></thead><tbody>'
			. $rows . '</tbody></table><figcaption class="wp-element-caption">' . esc_html( $data['price_note'] ) . '</figcaption></figure><!-- /wp:table -->';
	}

	/**
	 * Call / Zalo / WhatsApp buttons (core Buttons block markup).
	 *
	 * @return string
	 */
	public static function contact_buttons() {
		$data    = self::business();
		$buttons = array(
			array( $data['phone_href'], 'Gọi ' . $data['phone_display'] ),
			array( $data['zalo_url'], 'Nhắn Zalo' ),
			array( $data['whatsapp_url'], 'Nhắn WhatsApp' ),
		);
		$html    = '<!-- wp:buttons --><div class="wp-block-buttons">';
		foreach ( $buttons as $button ) {
			$html .= '<!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $button[0] ) . '">' . esc_html( $button[1] ) . '</a></div><!-- /wp:button -->';
		}
		return $html . '</div><!-- /wp:buttons -->';
	}

	/**
	 * Visible FAQ rendered as a Yoast FAQ block, so Yoast (not this plugin)
	 * emits FAQPage schema from exactly the questions shown on the page.
	 *
	 * @return string
	 */
	public static function faq_block() {
		$items     = require RBHVI_DIR . 'content/faq-items.php';
		$questions = array();
		$html      = '';
		foreach ( $items as $index => $item ) {
			$id          = 'faq-question-' . ( $index + 1 );
			$question    = self::render( $item[0] );
			$answer      = self::render( $item[1] );
			$questions[] = array(
				'id'           => $id,
				'question'     => array( $question ),
				'answer'       => array( $answer ),
				'jsonQuestion' => $question,
				'jsonAnswer'   => $answer,
			);
			$html       .= '<div class="schema-faq-section" id="' . esc_attr( $id ) . '"><strong class="schema-faq-question">' . $question . '</strong> <p class="schema-faq-answer">' . $answer . '</p> </div>';
		}
		$attrs = wp_json_encode( array( 'questions' => $questions ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP );
		return '<!-- wp:yoast/faq-block ' . $attrs . ' --><div class="schema-faq wp-block-yoast-faq-block">' . $html . '</div><!-- /wp:yoast/faq-block -->';
	}

	/**
	 * Rendered seed content of a managed page, as Gutenberg block markup.
	 *
	 * @param string $key Page key.
	 * @return string
	 */
	public static function page_content( $key ) {
		$file = RBHVI_DIR . 'content/' . $key . '.txt';
		if ( ! is_readable( $file ) ) {
			return '';
		}
		return self::render( self::blocks( (string) file_get_contents( $file ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
	}

	/**
	 * Minimal authoring format → core blocks. Blank lines separate blocks;
	 * "## " / "### " headings, "- " bullet lists, "1. " numbered lists, a
	 * lone {{placeholder}} line is inserted as-is, anything else is a
	 * paragraph. Inline: [text](url) and **bold**. No H1 is ever produced:
	 * the page title is the only H1.
	 *
	 * @param string $text Source text.
	 * @return string
	 */
	public static function blocks( $text ) {
		$out    = array();
		$chunks = preg_split( "/\n\s*\n/", trim( str_replace( "\r", '', $text ) ) );
		foreach ( $chunks as $chunk ) {
			$chunk = trim( $chunk );
			$lines = array_map( 'trim', explode( "\n", $chunk ) );
			if ( preg_match( '/^\{\{[a-z_:0-9-]+\}\}$/', $chunk ) ) {
				$out[] = $chunk;
			} elseif ( preg_match( '/^(#{2,3}) (.+)$/', $chunk, $m ) ) {
				$level = strlen( $m[1] );
				$attrs = 3 === $level ? ' {"level":3}' : '';
				$out[] = '<!-- wp:heading' . $attrs . ' --><h' . $level . ' class="wp-block-heading">' . self::inline( $m[2] ) . '</h' . $level . '><!-- /wp:heading -->';
			} elseif ( self::all_match( $lines, '/^- /' ) || self::all_match( $lines, '/^\d+\. /' ) ) {
				$ordered = ! self::all_match( $lines, '/^- /' );
				$tag     = $ordered ? 'ol' : 'ul';
				$items   = '';
				foreach ( $lines as $line ) {
					$items .= '<!-- wp:list-item --><li>' . self::inline( preg_replace( '/^(- |\d+\. )/', '', $line ) ) . '</li><!-- /wp:list-item -->';
				}
				$out[] = '<!-- wp:list' . ( $ordered ? ' {"ordered":true}' : '' ) . ' --><' . $tag . ' class="wp-block-list">' . $items . '</' . $tag . '><!-- /wp:list -->';
			} else {
				$out[] = '<!-- wp:paragraph --><p>' . self::inline( implode( ' ', $lines ) ) . '</p><!-- /wp:paragraph -->';
			}
		}
		return implode( "\n\n", $out );
	}

	/**
	 * Whether every line matches.
	 *
	 * @param string[] $lines Lines.
	 * @param string   $regex Pattern.
	 * @return bool
	 */
	private static function all_match( $lines, $regex ) {
		foreach ( $lines as $line ) {
			if ( ! preg_match( $regex, $line ) ) {
				return false;
			}
		}
		return (bool) $lines;
	}

	/**
	 * Inline links and bold.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private static function inline( $text ) {
		$text = preg_replace( '/\[([^\]]+)\]\(([^)\s]+)\)/', '<a href="$2">$1</a>', $text );
		return preg_replace( '/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text );
	}
}
