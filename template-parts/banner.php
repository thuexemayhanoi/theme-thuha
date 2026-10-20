<?php defined( 'ABSPATH' ) || exit; if ( ! rbh_setting( 'banner' ) || ! rbh_setting( 'banner_text' ) ) { return; } ?>
<aside class="site-banner" aria-label="<?php esc_attr_e( 'Announcement', 'rentbike-hanoi' ); ?>"><div class="container">
	<p><?php echo esc_html( rbh_setting( 'banner_text' ) ); ?>
	<?php $page_id = absint( rbh_setting( 'banner_page' ) ); if ( $page_id && 'publish' === get_post_status( $page_id ) && ! post_password_required( $page_id ) ) : ?>
	<a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>"><?php echo esc_html( rbh_setting( 'banner_label' ) ?: get_the_title( $page_id ) ); ?> <span aria-hidden="true">↗</span></a>
	<?php endif; ?></p>
</div></aside>
