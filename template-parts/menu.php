<?php defined( 'ABSPATH' ) || exit; ?>
<section id="site-menu" class="mobile-menu" popover="auto" role="dialog" aria-label="<?php esc_attr_e( 'Site menu', 'rentbike-hanoi' ); ?>" tabindex="-1">
	<div class="mobile-menu-heading"><div><p class="eyebrow"><?php esc_html_e( 'Explore Hanoi', 'rentbike-hanoi' ); ?></p><span><?php echo esc_html( rbh_setting( 'brand' ) ); ?></span></div><button class="menu-close icon-button" type="button" popovertarget="site-menu" popovertargetaction="hide" autofocus aria-label="<?php esc_attr_e( 'Close menu', 'rentbike-hanoi' ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m6 6 12 12M6 18 18 6"/></svg></button></div>
	<?php get_search_form(); ?>
	<nav aria-label="<?php esc_attr_e( 'Mobile navigation', 'rentbike-hanoi' ); ?>"><?php rbh_menu_tree( rbh_menu_items() ); ?></nav>
	<div class="menu-contact"><?php rbh_cta(); ?><?php if ( rbh_contact_url( 'zalo' ) ) : ?><a class="text-link" href="<?php echo esc_url( rbh_contact_url( 'zalo' ) ); ?>">Zalo ↗</a><?php endif; ?></div>
</section>
