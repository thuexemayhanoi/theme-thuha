<?php defined( 'ABSPATH' ) || exit; ?>
<!doctype html>
<html <?php language_attributes(); ?> data-appearance="<?php echo esc_attr( rbh_setting( 'appearance' ) ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script id="rbh-appearance-init">
	(function(){var d=document.documentElement,m=d.dataset.appearance;try{m=localStorage.getItem('rbh-appearance')||m;}catch(e){}if(!['light','dark','auto'].includes(m))m='auto';d.dataset.appearance=m;d.dataset.theme=m==='auto'?(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):m;d.classList.add('rbh-js');})();
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'rentbike-hanoi' ); ?></a>
<div class="utility-bar"><div class="container utility-inner">
	<span><?php echo esc_html( rbh_setting( 'brand' ) ); ?></span>
	<div><?php if ( rbh_setting( 'hours' ) ) : ?><span><?php echo esc_html( rbh_setting( 'hours' ) ); ?></span><?php endif; ?>
	<?php if ( rbh_contact_url( 'phone' ) ) : ?><a href="<?php echo esc_url( rbh_contact_url( 'phone' ) ); ?>"><?php echo esc_html( rbh_setting( 'phone' ) ); ?></a><?php endif; ?></div>
</div></div>
<header class="site-header">
	<div class="container header-inner">
		<div class="site-branding"><?php rbh_brand(); ?></div>
		<nav class="desktop-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'rentbike-hanoi' ); ?>"><?php rbh_primary_navigation(); ?></nav>
		<div class="header-actions">
				<?php if ( rbh_upgrade_enabled( 'quick_search' ) ) : ?><a class="rbh-search-trigger" data-rbh-search href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( 'Search', 'rentbike-hanoi' ); ?>"><span aria-hidden="true">⌕</span><span class="rbh-search-label"><?php esc_html_e( 'Search', 'rentbike-hanoi' ); ?></span></a><?php endif; ?>
			<label class="appearance-toggle icon-button" title="<?php esc_attr_e( 'Toggle light / dark mode', 'rentbike-hanoi' ); ?>">
				<input id="rbh-dark-mode" class="appearance-input" type="checkbox" role="switch" aria-label="<?php esc_attr_e( 'Dark mode', 'rentbike-hanoi' ); ?>" <?php checked( 'dark', rbh_setting( 'appearance' ) ); ?>>
				<svg class="icon-moon" aria-hidden="true" viewBox="0 0 24 24"><path d="M20.7 13A9 9 0 0 1 11 3.3 9 9 0 1 0 20.7 13Z"/></svg>
				<svg class="icon-sun" aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
			</label>
			<script>document.getElementById('rbh-dark-mode').checked=document.documentElement.dataset.theme==='dark';</script>
			<div class="desktop-cta"><?php rbh_cta( __( 'Book a bike', 'rentbike-hanoi' ), 'button button-small' ); ?></div>
			<button class="menu-trigger icon-button" type="button" popovertarget="site-menu" aria-controls="site-menu" aria-expanded="false"><span class="hamburger" aria-hidden="true"></span><span><?php esc_html_e( 'Menu', 'rentbike-hanoi' ); ?></span></button>
		</div>
	</div>
</header>
<?php get_template_part( 'template-parts/menu' ); do_action( 'rbh_banner' ); ?>
