<?php defined( 'ABSPATH' ) || exit; ?>
<?php if ( rbh_setting( 'dock' ) ) : ?>
<nav class="contact-dock" aria-label="<?php esc_attr_e( 'Quick contact', 'rentbike-hanoi' ); ?>">
	<?php if ( rbh_contact_url( 'phone' ) ) : ?><a href="<?php echo esc_url( rbh_contact_url( 'phone' ) ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 3h3l2 5-2 2a14 14 0 0 0 5 5l2-2 5 2v3a3 3 0 0 1-3 3C10 20 4 14 3 6a3 3 0 0 1 3-3Z"/></svg><span><?php esc_html_e( 'Call', 'rentbike-hanoi' ); ?></span></a><?php endif; ?>
	<?php if ( rbh_contact_url( 'whatsapp' ) ) : ?><a href="<?php echo esc_url( rbh_contact_url( 'whatsapp' ) ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 20l1-5a9 9 0 1 1 4 4Z"/><path d="M9 8c0 4 3 7 7 7"/></svg><span>WhatsApp</span></a><?php endif; ?>
	<button class="menu-trigger" type="button" popovertarget="site-menu" aria-controls="site-menu" aria-expanded="false"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg><span><?php esc_html_e( 'Menu', 'rentbike-hanoi' ); ?></span></button>
</nav>
<?php endif; ?>
