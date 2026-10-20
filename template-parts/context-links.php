<?php defined( 'ABSPATH' ) || exit; if ( empty( $args['links'] ) ) { return; } ?>
<nav class="context-links" aria-label="<?php esc_attr_e( 'Useful links', 'rentbike-hanoi' ); ?>">
	<h2 class="side-title"><?php esc_html_e( 'Useful links', 'rentbike-hanoi' ); ?></h2>
	<ul><?php foreach ( $args['links'] as $item ) : ?><li><?php rbh_link( $item ); ?></li><?php endforeach; ?></ul>
</nav>
