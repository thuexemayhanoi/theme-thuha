<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<main id="main" class="container page-shell">
	<?php rbh_breadcrumb(); ?>
	<header class="archive-header">
		<p class="eyebrow"><?php esc_html_e( 'The reading room', 'rentbike-hanoi' ); ?></p>
		<h1><?php
		if ( is_search() ) { printf( esc_html__( 'Search: %s', 'rentbike-hanoi' ), esc_html( get_search_query() ) ); }
		elseif ( is_category() || is_tag() || is_tax() ) { single_term_title(); }
		elseif ( is_archive() ) { echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); }
		else { esc_html_e( 'Rental guides', 'rentbike-hanoi' ); }
		?></h1>
		<?php if ( is_archive() && get_the_archive_description() ) : ?><div class="archive-intro entry-content"><?php the_archive_description(); ?></div><?php endif; ?>
		<?php if ( is_search() ) { get_search_form(); } ?>
	</header>
	<?php do_action( 'rbh_hero', get_queried_object_id() ); do_action( 'rbh_before_content', get_queried_object_id() ); ?>
	<div class="reading-layout archive-layout <?php echo rbh_setting( 'sidebar' ) ? '' : 'without-sidebar'; ?>">
		<div>
			<?php if ( have_posts() ) : ?><div class="archive-grid"><?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/card' ); endwhile; ?></div>
			<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => __( 'Previous', 'rentbike-hanoi' ), 'next_text' => __( 'Next', 'rentbike-hanoi' ) ) ); ?>
			<?php else : ?><section class="empty-state"><h2><?php esc_html_e( 'Nothing found yet.', 'rentbike-hanoi' ); ?></h2><p><?php esc_html_e( 'Try another word, or explore the rental links below.', 'rentbike-hanoi' ); ?></p><?php if ( ! is_search() ) { get_search_form(); } ?><a class="button button-outline" href="<?php echo esc_url( rbh_home_url() ); ?>"><?php esc_html_e( 'Go to homepage', 'rentbike-hanoi' ); ?></a></section><?php endif; ?>
		</div>
		<?php if ( rbh_setting( 'sidebar' ) ) { get_sidebar(); } ?>
	</div>
	<?php do_action( 'rbh_after_content', get_queried_object_id() ); ?>
</main>
<?php get_footer(); ?>
