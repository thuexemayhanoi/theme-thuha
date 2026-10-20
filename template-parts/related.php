<?php defined( 'ABSPATH' ) || exit; if ( empty( $args['ids'] ) ) { return; }
$query = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'has_password' => false, 'post__in' => $args['ids'], 'orderby' => 'post__in', 'posts_per_page' => count( $args['ids'] ), 'ignore_sticky_posts' => true, 'no_found_rows' => true ) ); ?>
<section class="related section"><p class="eyebrow"><?php esc_html_e( 'Keep exploring', 'rentbike-hanoi' ); ?></p><h2><?php esc_html_e( 'Related guides', 'rentbike-hanoi' ); ?></h2><div class="card-grid">
	<?php while ( $query->have_posts() ) { $query->the_post(); get_template_part( 'template-parts/card' ); } wp_reset_postdata(); ?>
</div></section>
