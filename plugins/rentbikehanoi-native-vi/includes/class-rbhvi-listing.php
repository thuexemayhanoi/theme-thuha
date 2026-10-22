<?php
/**
 * /vi/blog/: the managed "Blog" page lists published Vietnamese posts below
 * its own introduction, using the theme's card template. Pagination lives at
 * /vi/blog/page/N/ (self-canonical, see RBHVI_SEO).
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Vietnamese post listing.
 */
class RBHVI_Listing {

	const PER_PAGE = 9;

	/**
	 * Memoised published count.
	 *
	 * @var int|null
	 */
	private static $count;

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_filter( 'the_content', array( __CLASS__, 'append' ), 20 );
		add_action( 'wp', array( __CLASS__, 'out_of_range' ) );
	}

	/**
	 * Base query args for published, public Vietnamese posts.
	 *
	 * @return array
	 */
	private static function args() {
		return array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'has_password'        => false,
			'ignore_sticky_posts' => true,
			'rbhvi_lang'          => 'vi',
			'orderby'             => array(
				'date' => 'DESC',
				'ID'   => 'DESC',
			),
		);
	}

	/**
	 * Number of published Vietnamese posts.
	 *
	 * @return int
	 */
	public static function count() {
		if ( null === self::$count ) {
			$term = RBHVI_Language::term_id();
			if ( ! $term ) {
				self::$count = 0;
			} else {
				$query       = new WP_Query(
					self::args() + array(
						'posts_per_page' => 1,
						'fields'         => 'ids',
						'rbhvi_skip'     => true,
						'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
							array(
								'taxonomy' => RBHVI_Language::TAX,
								'field'    => 'term_id',
								'terms'    => $term,
							),
						),
					)
				);
				self::$count = (int) $query->found_posts;
			}
		}
		return self::$count;
	}

	/**
	 * 404 for /vi/blog/page/N/ beyond the last page.
	 */
	public static function out_of_range() {
		$paged = (int) get_query_var( 'rbhvi_paged' );
		$blog  = RBHVI_State::page_id( 'blog' );
		if ( $paged > 1 && $blog && is_page( $blog ) && $paged > (int) ceil( self::count() / self::PER_PAGE ) ) {
			RBHVI_Router::not_found();
		}
	}

	/**
	 * Append the list to the listing page content.
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public static function append( $content ) {
		$blog = RBHVI_State::page_id( 'blog' );
		if ( ! $blog || ! is_page( $blog ) || ! in_the_loop() || ! is_main_query() || get_the_ID() !== $blog ) {
			return $content;
		}
		$paged = max( 1, (int) get_query_var( 'rbhvi_paged' ) );
		$query = new WP_Query( self::args() + array( 'posts_per_page' => self::PER_PAGE, 'paged' => $paged ) );
		if ( ! $query->have_posts() ) {
			return $content . '<p class="rbhvi-empty">Chưa có bài viết tiếng Việt nào được xuất bản. Trong lúc chờ, bạn có thể xem <a href="' . esc_url( RBHVI_Config::page_url( 'prices' ) ) . '">bảng giá</a> hoặc <a href="' . esc_url( RBHVI_Config::page_url( 'faq' ) ) . '">câu hỏi thường gặp</a>.</p>';
		}
		$card = locate_template( 'template-parts/card.php' );
		ob_start();
		echo '<div class="rbhvi-listing card-grid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			if ( $card ) {
				get_template_part( 'template-parts/card' );
			} else {
				echo '<article><h2><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2></article>';
			}
		}
		echo '</div>';
		wp_reset_postdata();
		$base  = trailingslashit( get_permalink( $blog ) );
		$links = paginate_links(
			array(
				'base'      => $base . '%_%',
				'format'    => user_trailingslashit( 'page/%#%', 'paged' ),
				'current'   => $paged,
				'total'     => (int) $query->max_num_pages,
				'prev_text' => 'Trang trước',
				'next_text' => 'Trang sau',
			)
		);
		if ( $links ) {
			echo '<nav class="navigation pagination" aria-label="Phân trang bài viết"><div class="nav-links">' . wp_kses_post( $links ) . '</div></nav>';
		}
		return $content . ob_get_clean();
	}
}
