<?php
/**
 * Local-only English fixture that mirrors the public structure of rentbikehanoi.com
 * (slugs, menu labels, category tree) so English URLs can be protected by tests.
 * Run with: wp eval-file tests/integration/fixtures-en.php. Never run on production.
 */
if ( ! defined( 'WP_CLI' ) || false !== strpos( home_url(), 'rentbikehanoi.com' ) ) {
	exit( "Refusing to run outside a local WP-CLI test site.\n" );
}

update_option( 'permalink_structure', '/%postname%/' );
update_option( 'blogdescription', 'Hanoi motorbike rental' );
$yoast = get_option( 'wpseo_titles', array() );
$yoast['stripcategorybase'] = true;
update_option( 'wpseo_titles', $yoast );

$term = function ( $slug, $name, $parent = 0 ) {
	$found = get_term_by( 'slug', $slug, 'category' );
	if ( $found ) {
		return (int) $found->term_id;
	}
	$made = wp_insert_term( $name, 'category', array( 'slug' => $slug, 'parent' => $parent ) );
	return (int) $made['term_id'];
};
$cats             = array();
$cats['blog']     = $term( 'blog', 'Blog' );
$cats['travel']   = $term( 'travel-guide', 'Travel Guide', $cats['blog'] );
$cats['tips']     = $term( 'tips-resources', 'Tips & Resources', $cats['blog'] );
$cats['guides']   = $term( 'motorbike-rental-guides', 'Motorbike Rental Guides', $cats['tips'] );
$cats['setup']    = $term( 'bike-setup-everyday-use', 'Bike Setup & Everyday Use', $cats['tips'] );
$cats['parking']  = $term( 'parking-weather-breakdowns', 'Parking, Weather & Breakdowns', $cats['tips'] );
$cats['trips']    = $term( 'day-trips-from-hanoi', 'Day Trips from Hanoi', $cats['travel'] );
$cats['routes']   = $term( 'hanoi-routes-sightseeing', 'Hanoi Routes & Sightseeing', $cats['travel'] );
$cats['museums']  = $term( 'hanoi-museums-collections', 'Hanoi Museums & Collections', $cats['routes'] );

$p = function ( $type, $slug, $title, $content, $cat = 0, $date = '2026-01-01 10:00:00' ) {
	$found = get_page_by_path( $slug, OBJECT, $type );
	if ( $found ) {
		return (int) $found->ID;
	}
	return (int) wp_insert_post(
		array(
			'post_type'     => $type,
			'post_status'   => 'publish',
			'post_name'     => $slug,
			'post_title'    => $title,
			'post_content'  => $content,
			'post_date'     => $date,
			'post_category' => $cat ? array( $cat ) : array(),
		)
	);
};
$para  = '<!-- wp:paragraph --><p>%s</p><!-- /wp:paragraph -->';
$home  = $p( 'page', 'home-page', 'Hanoi Motorbike Rental', sprintf( $para, 'Rent a motorbike in Hanoi with Nguyen Ha. This English fixture paragraph is long enough to be detected as the introduction text.' )
	. '<h2 id="rates">Rental rates</h2><table><tr><th>Rental Duration</th><th>Price</th><th>Deposit</th></tr><tr><td>Daily</td><td>Ask the shop</td><td>Ask the shop</td></tr></table>'
	. '<h2>FAQ</h2><table><tr><th>Question</th><th>Answer</th></tr><tr><td>Q1: Where is the shop?</td><td>Near Gia Lam Bus Station.</td></tr></table>' );
$ids   = array(
	'about'    => $p( 'page', 'about-us', 'About Us', sprintf( $para, 'About the English rental shop.' ) ),
	'contact'  => $p( 'page', 'contact-us', 'Contact Us', sprintf( $para, 'Contact the shop in English.' ) ),
	'faq'      => $p( 'page', 'faq', 'FAQ', sprintf( $para, 'English frequently asked questions.' ) ),
	'privacy'  => $p( 'page', 'privacy-police', 'Privacy Policy', sprintf( $para, 'English privacy policy.' ) ),
	'terms'    => $p( 'page', 'terms-of-use', 'Terms of Use', sprintf( $para, 'English terms of use.' ) ),
	'prices'   => $p( 'post', 'motorbike-rental-prices-in-hanoi', 'Motorbike Rental Prices in Hanoi', sprintf( $para, 'English reference prices guide for motorbike rental.' ), $cats['guides'], '2026-02-01 10:00:00' ),
	'scooter'  => $p( 'post', 'hanoi-scooter-rental', 'Hanoi Scooter Rental', sprintf( $para, 'English scooter rental guide.' ), $cats['guides'], '2026-02-02 10:00:00' ),
	'rules'    => $p( 'post', 'motorbike-renting-in-hanoi-requirements', 'Motorbike Renting in Hanoi Requirements', sprintf( $para, 'English rental rules and requirements.' ), $cats['guides'], '2026-02-03 10:00:00' ),
	'delivery' => $p( 'post', 'motorbike-rental-hanoi-delivery-and-pickup-guide-complete-service-overview-2025', 'Motorbike Rental Hanoi Delivery and Pickup Guide', sprintf( $para, 'English delivery and pickup guide.' ), $cats['guides'], '2026-02-04 10:00:00' ),
);
foreach ( array( 'setup' => 'A rental scooter tool kit', 'parking' => 'Parking in Hanoi rain', 'trips' => 'Day trip to Ninh Binh', 'routes' => 'Hoan Kiem lake route', 'museums' => 'Vietnam Museum of Ethnology visit' ) as $key => $title ) {
	$ids[ 'post_' . $key ] = $p( 'post', sanitize_title( $title ), $title, sprintf( $para, 'English guide about ' . $title . ' for riders renting a motorbike.' ), $cats[ $key ], '2026-03-0' . ( count( $ids ) % 9 + 1 ) . ' 10:00:00' );
}
for ( $i = 1; $i <= 13; $i++ ) {
	$ids[ 'extra_' . $i ] = $p( 'post', 'english-rental-tip-' . $i, 'English rental tip ' . $i, sprintf( $para, 'English tip number ' . $i . ' about riding a rental motorbike safely in Hanoi.' ), $cats['guides'], sprintf( '2026-04-%02d 10:00:00', $i ) );
}
wp_set_post_tags( $ids['prices'], array( 'rental' ) );
wp_set_post_tags( $ids['scooter'], array( 'rental' ) );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );

// Menus: exact frozen labels from docs/navigation-labels-approved-2026-10-20.json.
$snapshot = json_decode( file_get_contents( dirname( __DIR__, 2 ) . '/docs/navigation-labels-approved-2026-10-20.json' ), true );
$by_url   = array(
	'/'                => $home,
	'/about-us/'       => $ids['about'],
	'/contact-us/'     => $ids['contact'],
	'/faq/'            => $ids['faq'],
	'/privacy-police/' => $ids['privacy'],
	'/terms-of-use/'   => $ids['terms'],
);
foreach ( array( 'prices', 'scooter', 'rules', 'delivery' ) as $key ) {
	$by_url[ '/' . get_post_field( 'post_name', $ids[ $key ] ) . '/' ] = $ids[ $key ];
}
$cat_by_path = array();
foreach ( $cats as $id ) {
	$slugs = array();
	foreach ( array_merge( array_reverse( get_ancestors( $id, 'category', 'taxonomy' ) ), array( $id ) ) as $ancestor ) {
		$slugs[] = get_term( $ancestor, 'category' )->slug;
	}
	$cat_by_path[ '/' . implode( '/', $slugs ) . '/' ] = $id;
}
$locations = array();
foreach ( array( 'primary' => 'menu', 'footer' => 'footer-menu' ) as $location => $slug ) {
	$menu = wp_get_nav_menu_object( $slug );
	if ( $menu ) {
		$locations[ $location ] = $menu->term_id;
		continue;
	}
	$menu_id = wp_create_nav_menu( 'footer' === $location ? 'Footer Menu' : 'Menu' );
	wp_update_term( $menu_id, 'nav_menu', array( 'slug' => $slug ) );
	$map = array();
	foreach ( $snapshot['menus'][ $location ] as $item ) {
		$path = wp_parse_url( $item['link'], PHP_URL_PATH );
		$args = array( 'menu-item-title' => $item['title'], 'menu-item-status' => 'publish', 'menu-item-position' => $item['position'], 'menu-item-parent-id' => isset( $map[ $item['parent'] ] ) ? $map[ $item['parent'] ] : 0 );
		if ( 'taxonomy' === $item['type'] ) {
			$args += array( 'menu-item-type' => 'taxonomy', 'menu-item-object' => 'category', 'menu-item-object-id' => $cat_by_path[ $path ] );
		} else {
			$object = $by_url[ $path ];
			$args  += array( 'menu-item-type' => 'post_type', 'menu-item-object' => get_post_type( $object ), 'menu-item-object-id' => $object );
		}
		$map[ $item['db_id'] ] = wp_update_nav_menu_item( $menu_id, 0, $args );
	}
	$locations[ $location ] = $menu_id;
}
set_theme_mod( 'nav_menu_locations', $locations );
foreach ( array( 'brand' => 'Motorbike Nguyen Ha', 'phone' => '+84 334 699 969', 'whatsapp' => 'https://wa.me/84334699969', 'zalo' => 'https://zalo.me/0334699969', 'hours' => '09:00–21:00 daily', 'address' => 'Alley 5, Nguyen Van Cu, Bo De, Long Bien, Hanoi, Vietnam (near Gia Lam Bus Station)', 'hero_title' => 'Hanoi motorbike rental.' ) as $key => $value ) {
	set_theme_mod( 'rbh_' . $key, $value );
}
flush_rewrite_rules( false );
WP_CLI::success( 'English fixture ready: ' . wp_json_encode( array( 'home' => $home ) + $ids ) );
