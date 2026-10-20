<?php
defined( 'ABSPATH' ) || exit;
$footer = 'footer' === $args['placement'];
$context = $args['context'];
$title = rbh_setting( $footer ? 'footer_cta_title' : 'cta_' . $context . '_title' );
$text = rbh_setting( $footer ? 'footer_cta_text' : 'cta_' . $context . '_text' );
?>
<aside class="<?php echo $footer ? 'footer-top' : 'article-cta'; ?>" data-cta-context="<?php echo esc_attr( $context ); ?>">
	<div><p class="eyebrow"><?php echo esc_html( rbh_setting( $footer ? 'footer_cta_eyebrow' : 'cta_' . $context . '_eyebrow' ) ); ?></p>
	<h2><?php echo esc_html( $title ); ?></h2><?php if ( $text ) : ?><p class="cta-description"><?php echo esc_html( $text ); ?></p><?php endif; ?></div>
	<?php rbh_cta( $footer ? __( 'Contact the shop', 'rentbike-hanoi' ) : rbh_setting( 'cta_' . $context . '_label' ) ); ?>
</aside>
