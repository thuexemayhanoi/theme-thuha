<?php defined( 'ABSPATH' ) || exit; ?>
<aside class="site-sidebar" aria-label="<?php esc_attr_e( 'Search and rental information', 'rentbike-hanoi' ); ?>">
	<section class="side-panel"><h2 class="side-title"><?php esc_html_e( 'Find what you need', 'rentbike-hanoi' ); ?></h2><?php get_search_form(); ?></section>
	<section class="side-panel"><p class="eyebrow"><?php esc_html_e( 'Rental essentials', 'rentbike-hanoi' ); ?></p><h2 class="side-title"><?php esc_html_e( 'Plan your ride', 'rentbike-hanoi' ); ?></h2><ul class="side-links">
		<?php foreach ( rbh_menu_items() as $item ) : if ( 'rent' !== rbh_menu_group( $item ) ) { continue; } ?><li><?php rbh_link( $item ); ?><span aria-hidden="true">↗</span></li><?php endforeach; ?>
	</ul></section>
	<?php if ( rbh_setting( 'cta' ) ) : ?><section class="side-panel side-contact"><h2 class="side-title"><?php esc_html_e( 'Have a question?', 'rentbike-hanoi' ); ?></h2><p><?php echo esc_html( rbh_setting( 'hours' ) ); ?></p><?php rbh_cta(); ?></section><?php endif; ?>
	<?php if ( is_active_sidebar( 'rbh-sidebar' ) ) { dynamic_sidebar( 'rbh-sidebar' ); } ?>
</aside>
