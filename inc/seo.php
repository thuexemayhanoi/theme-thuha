<?php
/** Audited presentation improvements. Yoast remains the only head/schema owner. */
defined( 'ABSPATH' ) || exit;

// Keep the widget shell; its public searches use WordPress's built-in search API.
remove_action( 'wp_footer', 'rbh_assistant_render', 8 );
add_action( 'wp_footer', function() { get_template_part( 'template-parts/assistant' ); }, 8 );
add_action( 'wp_enqueue_scripts', function() {
    // A new asset filename also refreshes caches that strip version query strings.
    wp_dequeue_script( 'rbh-assistant' );
    wp_deregister_script( 'rbh-assistant' );
    wp_enqueue_script( 'rbh-assistant', get_template_directory_uri() . '/assets/js/assistant-seo-20261019.js', array( 'rbh-upgrade' ), '20261019', true );
}, 35 );

/** Opt only the actual hero/featured image out of LiteSpeed's lazy rewriting. */
add_filter( 'wp_get_attachment_image_attributes', function( $attr, $attachment ) {
    $hero = is_front_page() && false !== strpos( $attr['class'] ?? '', 'hero-photo' );
    $featured = is_single() && (int) get_post_thumbnail_id( get_queried_object_id() ) === (int) $attachment->ID && 'eager' === ( $attr['loading'] ?? '' );
    if ( $hero || $featured ) {
        $attr['data-no-lazy'] = '1'; $attr['loading'] = 'eager'; $attr['fetchpriority'] = 'high';
    }
    if ( is_front_page() && empty( $attr['alt'] ) ) {
        $file = (string) get_attached_file( $attachment->ID );
        if ( false !== stripos( $file, 'Honda-Wave' ) ) { $attr['alt'] = 'Honda Wave semi-automatic motorbike'; }
    }
    return $attr;
}, 20, 2 );

/** Existing taxonomy only: no category/URL changes, and no homepage SEO filters. */
function rbh_seo_hubs() {
    return array(
        'blog' => array( 'Practical Hanoi motorbike rental and travel guides: compare costs, prepare for handover, care for your bike and plan useful city visits.', 'Start with your rental decisions, then choose a guide for the task ahead. Rental guidance covers costs, requirements and return arrangements; travel guidance helps you plan places to visit and sensible stops.', array( 211, 239, 252 ) ),
        'tips-resources' => array( 'Prepare for a Hanoi motorbike rental with guides to quotes, handover checks, everyday bike setup, parking, weather and unexpected problems.', 'Use these resources before collection, during your rental and before returning the bike. Start with the rental agreement and a clear quote, then use the relevant setup or problem-solving guide for your situation.', array( 211, 239, 252 ) ),
        'motorbike-rental-guides' => array( 'Understand Hanoi motorbike rental quotes, requirements, deposits, handover and extensions before choosing a bike and agreeing on the rental terms.', 'Make the rental agreement easier to understand before you pay. Compare the exact model and rental period, check the requirements that apply to you and agree on collection, return and any extension directly with the shop.', array( 211, 239, 252, 1901, 1929, 1899 ) ),
        'bike-setup-everyday-use' => array( 'Practical guides to setting up and using a rental motorbike in Hanoi, from controls and everyday checks to carrying essentials and caring for the bike.', 'Get familiar with the bike before setting off. These guides focus on everyday controls, carrying your belongings and noticing issues that should be discussed with the shop. Stop and ask for help whenever a task is beyond your experience.', array() ),
        'parking-weather-breakdowns' => array( 'Plan for Hanoi parking, rain and motorbike problems with practical guides to stopping safely, protecting your belongings and contacting the rental shop.', 'Choose the guide that matches the problem in front of you: a parking stop, changing weather or a bike that needs attention. Safe stopping and communication with the shop come before trying an unfamiliar repair.', array() ),
        'travel-guide' => array( 'Plan Hanoi visits and nearby trips with guides to city sights, museums, walking stops and transport choices that suit your experience and the weather.', 'Build your visit around the places you want to see and the time you have. Explore city routes and museums first, or choose a nearby trip after considering road conditions, daylight and alternative transport.', array( 311, 326, 2051, 2087 ) ),
        'day-trips-from-hanoi' => array( 'Compare day trips from Hanoi and plan arrival, stops and return timing, with practical advice on when self-riding or another transport option makes sense.', 'Choose a trip that fits your riding experience and the available daylight. Read the destination guide before departure, check current visitor information and leave room to change your transport or return plan if conditions worsen.', array( 2051, 2053, 2064, 2074 ) ),
        'hanoi-routes-sightseeing' => array( 'Explore Hanoi sights with routes built around sensible stops, parking and walking, including lakes, landmarks, gardens and museum visits.', 'Plan a manageable visit instead of rushing between distant sights. These guides connect arrival and parking with time on foot, helping you choose a route that fits your interests and the conditions on the day.', array( 311, 326, 2107, 2109 ) ),
        'hanoi-museums-collections' => array( 'Choose a Hanoi museum visit and plan the journey, arrival and time inside with guides to collections, current locations and practical transport choices.', 'Choose a collection that interests you, then read its practical visit guide. Confirm the current address, visitor hours and any ticket requirements with the museum before travelling; allow time for parking and the walk to the entrance.', array( 2087, 2104, 2117 ) ),
    );
}
function rbh_seo_hub() {
    if ( ! is_category() ) { return null; }
    $term = get_queried_object(); $hubs = rbh_seo_hubs();
    return $hubs[ $term->slug ] ?? null;
}
function rbh_seo_category_description( $description ) {
    $hub = rbh_seo_hub();
    return $hub ? $hub[0] : $description;
}
add_filter( 'wpseo_metadesc', 'rbh_seo_category_description', 20 );
add_filter( 'wpseo_opengraph_desc', 'rbh_seo_category_description', 20 );
add_filter( 'wpseo_schema_webpage', function( $data ) {
    $hub = rbh_seo_hub(); if ( $hub ) { $data['description'] = $hub[0]; } return $data;
}, 20 );
add_filter( 'get_the_archive_description', function( $description ) {
    $hub = rbh_seo_hub();
    if ( ! $hub || is_paged() ) { return $description; }
    return '<p>' . esc_html( $hub[1] ) . '</p>';
}, 20 );
add_action( 'rbh_before_content', function() {
    $hub = rbh_seo_hub(); if ( ! $hub || is_paged() ) { return; }
    $ids = $hub[2];
    if ( ! $ids ) {
        // Stable starting points for specialist hubs, independent of the newest-post grid.
        $query = new WP_Query( array( 'cat' => get_queried_object_id(), 'post_type' => 'post', 'post_status' => 'publish', 'has_password' => false, 'posts_per_page' => 3, 'orderby' => array( 'date' => 'ASC', 'ID' => 'ASC' ), 'no_found_rows' => true, 'fields' => 'ids' ) );
        $ids = $query->posts;
    }
    $links = array();
    foreach ( $ids as $id ) {
        $post = get_post( $id );
        if ( ! $post || 'publish' !== $post->post_status || $post->post_password ) { continue; }
        $links[] = '<li><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></li>';
    }
    if ( $links ) { echo '<nav class="rbh-category-hub" aria-label="Start here"><h2>Start here</h2><ul>' . implode( '', $links ) . '</ul></nav>'; }
}, 6 );

/** One category path, reused by Yoast's rendered and structured breadcrumbs. */
add_filter( 'wpseo_breadcrumb_links', function( $links ) {
    if ( ! is_singular( 'post' ) || count( $links ) < 2 ) { return $links; }
    $id = get_queried_object_id(); $terms = get_the_category( $id );
    if ( ! $terms ) { return $links; }
    $primary = (int) get_post_meta( $id, '_yoast_wpseo_primary_category', true );
    usort( $terms, function( $a, $b ) use ( $primary ) {
        if ( $a->term_id === $primary ) { return -1; } if ( $b->term_id === $primary ) { return 1; }
        return count( get_ancestors( $b->term_id, 'category', 'taxonomy' ) ) <=> count( get_ancestors( $a->term_id, 'category', 'taxonomy' ) );
    } );
    $term = $terms[0];
    $path = array_merge( array_reverse( get_ancestors( $term->term_id, 'category', 'taxonomy' ) ), array( $term->term_id ) );
    $result = array( $links[0] );
    foreach ( $path as $term_id ) {
        $category = get_term( $term_id, 'category' ); $url = get_term_link( $term_id, 'category' );
        if ( is_wp_error( $category ) || is_wp_error( $url ) ) { continue; }
        $result[] = array( 'url' => $url, 'text' => $category->name, 'term_id' => (int) $term_id, 'taxonomy' => 'category' );
    }
    $result[] = end( $links ); return $result;
}, 20 );
add_filter( 'wpseo_opengraph_desc', function( $description ) {
    if ( is_page( 'contact-us' ) ) { return 'Contact Nguyen Ha Motorbike Rental in Long Bien, Hanoi. Call or message +84 334 699 969; open 09:00–21:00 daily to discuss your rental.'; }
    return $description;
}, 25 );

/** Rank a bounded, public candidate pool by shared topic words and tags. */
function rbh_seo_related() {
    $id = get_the_ID();
    if ( ! is_single() || ! rbh_setting( 'related' ) || post_password_required( $id ) ) { return; }
    $terms = get_the_category( $id ); $parents = array();
    foreach ( $terms as $term ) { $parents = array_merge( $parents, get_ancestors( $term->term_id, 'category', 'taxonomy' ) ); }
    $categories = array_values( array_diff( wp_list_pluck( $terms, 'term_id' ), $parents ) );
    $tags = wp_get_post_tags( $id, array( 'fields' => 'ids' ) );
    $tax = array( 'relation' => 'OR' );
    if ( $categories ) { $tax[] = array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $categories, 'include_children' => true ); }
    if ( $tags ) { $tax[] = array( 'taxonomy' => 'post_tag', 'field' => 'term_id', 'terms' => $tags ); }
    if ( count( $tax ) < 2 ) { return; }
    $exclude = array( $id );
    foreach ( rbh_context_links() as $link ) { if ( 'post_type' === $link->type ) { $exclude[] = (int) $link->object_id; } }
    $query = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'has_password' => false,
        'tax_query' => $tax, 'post__not_in' => $exclude, 'posts_per_page' => 60, 'no_found_rows' => true,
        'ignore_sticky_posts' => true, 'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' ) ) );
    $words = function( $title ) {
        $all = preg_split( '/[^a-z0-9]+/', strtolower( remove_accents( wp_strip_all_tags( $title ) ) ), -1, PREG_SPLIT_NO_EMPTY );
        return array_values( array_unique( array_filter( $all, function( $word ) {
            return strlen( $word ) > 3 && ! in_array( $word, array( 'hanoi', 'motorbike', 'motorcycle', 'rental', 'renting', 'scooter', 'guide', 'with', 'from', 'your', 'what', 'when', 'before', 'after', 'that', 'this', 'tips', 'complete', 'practical' ), true );
        } ) ) );
    };
    $topic = $words( get_the_title( $id ) ); $ranked = array();
    foreach ( $query->posts as $position => $post ) {
        if ( '1' === (string) get_post_meta( $post->ID, '_yoast_wpseo_meta-robots-noindex', true ) ) { continue; }
        $score = 4 * count( array_intersect( $topic, $words( $post->post_title ) ) ) + 2 * count( array_intersect( $tags, wp_get_post_tags( $post->ID, array( 'fields' => 'ids' ) ) ) );
        $ranked[] = array( 'id' => $post->ID, 'score' => $score, 'position' => $position );
    }
    usort( $ranked, function( $a, $b ) { return ( $b['score'] <=> $a['score'] ) ?: ( $a['position'] <=> $b['position'] ); } );
    get_template_part( 'template-parts/related', null, array( 'ids' => array_slice( array_column( $ranked, 'id' ), 0, rbh_related_limit( rbh_setting( 'related_limit' ) ) ) ) );
}
remove_action( 'rbh_related', 'rbh_related', 10 );
add_action( 'rbh_related', 'rbh_seo_related', 10 );
