<?php
defined( 'ABSPATH' ) || exit;

function rbh_is_target_site() {
	return (bool) preg_match( '/(^|\.)rentbikehanoi\.com$/i', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
}
function rbh_defaults() {
	static $defaults;
	if ( isset( $defaults ) ) { return $defaults; }
	$site = rbh_is_target_site();
	$travel = get_term_by( 'slug', 'travel-guide', 'category' );
	$tips = get_term_by( 'slug', 'tips-resources', 'category' );
	$defaults = array(
		'brand' => $site ? 'Motorbike Nguyen Ha' : get_bloginfo( 'name' ),
		'phone' => $site ? '+84 334 699 969' : '',
		'whatsapp' => $site ? 'https://wa.me/84334699969' : '',
		'zalo' => $site ? 'https://zalo.me/0334699969' : '',
		'hours' => $site ? '09:00–21:00 daily' : '',
		'address' => $site ? 'Alley 5, Nguyen Van Cu, Bo De, Long Bien, Hanoi, Vietnam (near Gia Lam Bus Station)' : '',
		'hero_title' => $site ? 'Hanoi motorbike rental.' : get_bloginfo( 'name' ),
		'hero_text' => __( 'Choose your bike. Check the terms. Plan your ride.', 'rentbike-hanoi' ),
		'hero_image' => 0,
		'accent' => '#ae252c',
		'appearance' => 'auto',
		'home_layout' => 'rental',
		'dock' => true,
		'toc' => true,
		'related' => true,
		'dates' => true,
		'sidebar' => true,
		'legacy_home' => true,
		'cta' => true,
		'cta_label' => '',
		'cta_url' => '',
		'cta_channel' => 'auto',
		'footer_cta_eyebrow' => __( 'Your next ride', 'rentbike-hanoi' ),
		'footer_cta_title' => __( 'Let’s make a plan.', 'rentbike-hanoi' ),
		'footer_cta_text' => '',
		'banner' => false,
		'banner_text' => '',
		'banner_page' => 0,
		'banner_label' => '',
		'default_image' => 0,
		'internal_links' => true,
		'related_limit' => 3,
		'travel_category' => $travel ? (int) $travel->term_id : 0,
		'tips_category' => $tips ? (int) $tips->term_id : 0,
	);
	$copy = array(
		'general' => array( 'Have a question?', 'Talk to the shop.', '', 'Ask about a bike' ),
		'rental' => array( 'Planning a rental?', 'Talk to the shop.', '', 'Ask about a bike' ),
		'travel' => array( 'Planning your ride?', 'Find a bike for your trip.', 'Ask about availability and rental requirements before your ride.', 'Discuss your plans' ),
		'tips' => array( 'Before you rent', 'Check the details with the shop.', 'Confirm the bike, rental period and requirements directly.', 'Ask a rental question' ),
	);
	foreach ( $copy as $context => $values ) {
		foreach ( array( 'eyebrow', 'title', 'text', 'label' ) as $index => $field ) { $defaults[ 'cta_' . $context . '_' . $field ] = __( $values[ $index ], 'rentbike-hanoi' ); }
	}
	return $defaults;
}
function rbh_related_limit( $value ) { return max( 1, min( 6, absint( $value ) ) ); }
function rbh_image_id( $value ) { $id = absint( $value ); return $id && wp_attachment_is_image( $id ) ? $id : 0; }
function rbh_setting( $key ) {
	$defaults = rbh_defaults();
	return get_theme_mod( 'rbh_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}
function rbh_checkbox( $value ) { return (bool) $value; }
function rbh_choice( $input, $setting ) {
	$control = $setting->manager->get_control( $setting->id );
	return $control && isset( $control->choices[ $input ] ) ? $input : $setting->default;
}
function rbh_customize( $manager ) {
	$manager->add_panel( 'rbh', array( 'title' => __( 'Rentbike Theme', 'rentbike-hanoi' ), 'priority' => 30 ) );
	foreach ( array( 'contact' => 'Business & contact', 'homepage' => 'Homepage', 'layout' => 'Appearance & reading', 'cta' => 'Shared CTA & content context', 'banner' => 'Site banner' ) as $key => $label ) {
		$manager->add_section( 'rbh_' . $key, array( 'title' => __( $label, 'rentbike-hanoi' ), 'panel' => 'rbh' ) );
	}
	$fields = array(
		'brand' => array( 'Business name', 'contact', 'text' ),
		'phone' => array( 'Call number', 'contact', 'text' ),
		'whatsapp' => array( 'WhatsApp URL', 'contact', 'url' ),
		'zalo' => array( 'Zalo URL', 'contact', 'url' ),
		'hours' => array( 'Service hours', 'contact', 'text' ),
		'address' => array( 'Business address', 'contact', 'textarea' ),
		'hero_title' => array( 'Homepage heading', 'homepage', 'text' ),
		'hero_text' => array( 'Homepage introduction', 'homepage', 'textarea' ),
		'legacy_home' => array( 'Show the complete original homepage in expandable sections', 'homepage', 'checkbox' ),
		'dock' => array( 'Show mobile Call / WhatsApp / Menu bar', 'layout', 'checkbox' ),
		'toc' => array( 'Show table of contents (also controls LuckyWP)', 'layout', 'checkbox' ),
		'related' => array( 'Show related articles', 'layout', 'checkbox' ),
		'dates' => array( 'Show actual last updated date', 'layout', 'checkbox' ),
		'sidebar' => array( 'Show article/archive sidebar with search', 'layout', 'checkbox' ),
	);
	$fields += array(
		'cta' => array( 'Show shared CTA buttons and blocks', 'cta', 'checkbox' ),
		'cta_label' => array( 'Button label everywhere (blank uses contextual labels)', 'cta', 'text' ),
		'cta_url' => array( 'CTA destination override (blank uses contact channel)', 'cta', 'url' ),
		'footer_cta_eyebrow' => array( 'Footer CTA small heading', 'cta', 'text' ),
		'footer_cta_title' => array( 'Footer CTA title', 'cta', 'text' ),
		'footer_cta_text' => array( 'Footer CTA description', 'cta', 'textarea' ),
		'banner' => array( 'Show announcement banner', 'banner', 'checkbox' ),
		'banner_text' => array( 'Announcement (plain text)', 'banner', 'textarea' ),
		'banner_label' => array( 'Link label (blank uses the page title)', 'banner', 'text' ),
		'internal_links' => array( 'Show up to 3 contextual menu links after articles', 'layout', 'checkbox' ),
	);
	foreach ( array( 'general' => 'General', 'rental' => 'Rental', 'travel' => 'Travel guide', 'tips' => 'Rental tips' ) as $context => $label ) {
		foreach ( array( 'eyebrow' => 'small heading', 'title' => 'title', 'text' => 'description', 'label' => 'button label' ) as $key => $field_label ) {
			$fields[ 'cta_' . $context . '_' . $key ] = array( $label . ' CTA ' . $field_label, 'cta', 'text' === $key ? 'textarea' : 'text' );
		}
	}
	$defaults = rbh_defaults();
	foreach ( $fields as $key => $field ) {
		$sanitizer = 'text' === $field[2] ? 'sanitize_text_field' : ( 'url' === $field[2] ? 'esc_url_raw' : ( 'checkbox' === $field[2] ? 'rbh_checkbox' : 'sanitize_textarea_field' ) );
		$manager->add_setting( 'rbh_' . $key, array( 'default' => $defaults[ $key ], 'sanitize_callback' => $sanitizer ) );
		$manager->add_control( 'rbh_' . $key, array( 'label' => __( $field[0], 'rentbike-hanoi' ), 'section' => 'rbh_' . $field[1], 'type' => $field[2] ) );
	}
	$manager->add_setting( 'rbh_accent', array( 'default' => '#ae252c', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$manager->add_control( new WP_Customize_Color_Control( $manager, 'rbh_accent', array( 'label' => __( 'Accent color', 'rentbike-hanoi' ), 'section' => 'rbh_layout' ) ) );
	foreach ( array(
		'appearance' => array( 'Default appearance', 'layout', array( 'auto' => 'Follow device', 'light' => 'Light', 'dark' => 'Dark' ) ),
		'cta_channel' => array( 'Preferred contact channel', 'cta', array( 'auto' => 'Automatic: WhatsApp, Call, Zalo, Contact', 'whatsapp' => 'WhatsApp', 'phone' => 'Call', 'zalo' => 'Zalo', 'contact' => 'Contact page from menu' ) ),
		'home_layout' => array( 'Homepage layout', 'homepage', array( 'rental' => 'Rental landing page', 'content' => 'Original page content' ) ),
	) as $key => $field ) {
		$manager->add_setting( 'rbh_' . $key, array( 'default' => $defaults[ $key ], 'sanitize_callback' => 'rbh_choice' ) );
		$manager->add_control( 'rbh_' . $key, array( 'label' => __( $field[0], 'rentbike-hanoi' ), 'section' => 'rbh_' . $field[1], 'type' => 'select', 'choices' => $field[2] ) );
	}
	$categories = array( 0 => __( 'No category mapping', 'rentbike-hanoi' ) );
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $term ) { $categories[ $term->term_id ] = $term->name; }
	foreach ( array( 'travel_category' => 'Travel guide category', 'tips_category' => 'Rental tips category' ) as $key => $label ) {
		$manager->add_setting( 'rbh_' . $key, array( 'default' => $defaults[ $key ], 'sanitize_callback' => 'rbh_choice' ) );
		$manager->add_control( 'rbh_' . $key, array( 'label' => __( $label, 'rentbike-hanoi' ), 'section' => 'rbh_cta', 'type' => 'select', 'choices' => $categories ) );
	}
	$manager->add_setting( 'rbh_related_limit', array( 'default' => 3, 'sanitize_callback' => 'rbh_related_limit' ) );
	$manager->add_control( 'rbh_related_limit', array( 'label' => __( 'Related posts (1–6)', 'rentbike-hanoi' ), 'section' => 'rbh_layout', 'type' => 'number', 'input_attrs' => array( 'min' => 1, 'max' => 6 ) ) );
	$manager->add_setting( 'rbh_banner_page', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
	$manager->add_control( 'rbh_banner_page', array( 'label' => __( 'Announcement destination', 'rentbike-hanoi' ), 'section' => 'rbh_banner', 'type' => 'dropdown-pages' ) );
	$manager->add_setting( 'rbh_default_image', array( 'default' => 0, 'sanitize_callback' => 'rbh_image_id' ) );
	$manager->add_control( new WP_Customize_Media_Control( $manager, 'rbh_default_image', array( 'label' => __( 'Fallback card photo (optional, never replaces featured images)', 'rentbike-hanoi' ), 'section' => 'rbh_layout', 'mime_type' => 'image' ) ) );
	$manager->add_setting( 'rbh_hero_image', array( 'default' => 0, 'sanitize_callback' => 'rbh_image_id' ) );
	$manager->add_control( new WP_Customize_Media_Control( $manager, 'rbh_hero_image', array( 'label' => __( 'Hero photo (otherwise uses the existing homepage image)', 'rentbike-hanoi' ), 'section' => 'rbh_homepage', 'mime_type' => 'image' ) ) );
}
add_action( 'customize_register', 'rbh_customize' );

function rbh_settings_menu() {
	add_theme_page( __( 'Rentbike Theme', 'rentbike-hanoi' ), __( 'Rentbike Theme', 'rentbike-hanoi' ), 'edit_theme_options', 'rbh-settings', 'rbh_settings_page' );
}
add_action( 'admin_menu', 'rbh_settings_menu' );
function rbh_settings_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) { return; }
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Rentbike Theme', 'rentbike-hanoi' ); ?></h1>
		<p><?php esc_html_e( 'Your existing pages, menus, images and SEO data remain in WordPress. The rental homepage is a presentation of that content; changing themes restores its normal presentation.', 'rentbike-hanoi' ); ?></p>
		<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=rbh' ) ); ?>"><?php esc_html_e( 'Customize theme', 'rentbike-hanoi' ); ?></a>
		<a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Manage menus', 'rentbike-hanoi' ); ?></a></p>
		<?php if ( defined( 'LWPToc\PLUGIN_VERSION' ) || class_exists( 'LuckyWP\TableOfContents\Plugin' ) || shortcode_exists( 'lwptoc' ) ) : ?>
		<p><?php esc_html_e( 'LuckyWP Table of Contents is detected. The theme’s Table of Contents switch can turn it off; when enabled, LuckyWP’s existing settings apply. The theme never adds a second TOC.', 'rentbike-hanoi' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}
