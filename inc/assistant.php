<?php
/** Theme-owned local website helper. No external services, models or booking actions. */
defined( 'ABSPATH' ) || exit;
function rbh_assistant_assets() {
    $base = get_template_directory();
    wp_enqueue_style( 'rbh-assistant', get_template_directory_uri() . '/assets/css/assistant.css', array( 'rbh-upgrade' ), (string) filemtime( $base . '/assets/css/assistant.css' ) );
    wp_enqueue_script( 'rbh-assistant', get_template_directory_uri() . '/assets/js/assistant.js', array( 'rbh-upgrade' ), (string) filemtime( $base . '/assets/js/assistant.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'rbh_assistant_assets', 30 );
function rbh_assistant_body_class( $classes ) { $classes[] = 'rbh-assistant-enabled'; return $classes; }
add_filter( 'body_class', 'rbh_assistant_body_class' );
function rbh_assistant_index() {
    // Cache only public source extracts. No chat messages or personal data are stored.
    $key = 'rbh_assistant_public_v2';
    $cached = get_transient( $key );
    if ( is_array( $cached ) ) { return $cached; }
    $query = new WP_Query( array( 'post_type' => array( 'post', 'page' ), 'post_status' => 'publish', 'has_password' => false, 'posts_per_page' => 400, 'no_found_rows' => true, 'orderby' => 'modified', 'order' => 'DESC', 'suppress_filters' => true ) );
    $index = array(); $extra = array(); $bytes = 0;
    foreach ( $query->posts as $post ) {
        if ( '1' === (string) get_post_meta( $post->ID, '_yoast_wpseo_meta-robots-noindex', true ) ) { continue; }
        $url = get_permalink( $post );
        if ( wp_parse_url( $url, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) { continue; }
        // Homepage and FAQ still contain claims awaiting owner verification in SEO06.
        if ( (int) get_option( 'page_on_front' ) === (int) $post->ID || 'faq' === $post->post_name ) { continue; }
        $body = strip_shortcodes( $post->post_content );
        $body = preg_replace( '~<(script|style|form|iframe)\b[^>]*>.*?</\1>~is', '', $body );
        $body = preg_replace( '~</(?:p|li|h[1-6]|tr|div)>|<br\s*/?>~i', "\n", $body );
        $body = html_entity_decode( wp_strip_all_tags( $body ), ENT_QUOTES, 'UTF-8' );
        $blocks = preg_split( '/\n+/u', $body, -1, PREG_SPLIT_NO_EMPTY );
        $count = 0; $heading = get_the_title( $post ); $rows = array();
        foreach ( $blocks as $block ) {
            $block = trim( preg_replace( '/\s+/u', ' ', $block ) );
            if ( strlen( $block ) < 65 ) { if ( strlen( $block ) > 8 ) { $heading = $block; } continue; }
            // Do not turn legacy service claims, prices or legal guidance into chatbot advice.
            if ( preg_match( '/(?:\d[\d,. ]*\s*(?:VND|USD|đ|₫|\$)|deposit|passport|licen[cs]e|insurance|refund|delivery|airport|guarantee|available|in stock|booking|reservation)/iu', $block ) ) { continue; }
            $text = wp_html_excerpt( $block, 700, '…' );
            $row = array( 'title' => wp_strip_all_tags( get_the_title( $post ) ), 'heading' => $heading, 'text' => $text, 'url' => $url );
            $rows[] = $row;
            if ( ++$count >= 3 ) { break; }
        }
        if ( ! $rows ) { $rows[] = array( 'title' => wp_strip_all_tags( get_the_title( $post ) ), 'heading' => '', 'text' => 'Read this website guide for the full information.', 'url' => $url, 'link_only' => true ); }
        $first = array_shift( $rows );
        $bytes += strlen( wp_json_encode( $first ) );
        $index[] = $first;
        foreach ( $rows as $row ) { $extra[] = $row; }
    }
    // Fair coverage first: one entry per public URL before adding more extracts.
    foreach ( $extra as $row ) {
        $size = strlen( wp_json_encode( $row ) );
        if ( $bytes + $size > 280000 ) { break; }
        $index[] = $row; $bytes += $size;
    }
    set_transient( $key, $index, HOUR_IN_SECONDS );
    return $index;
}
function rbh_assistant_render() {
    get_template_part( 'template-parts/assistant' );
    echo '<script type="application/json" id="rbh-assistant-data">' . wp_json_encode( rbh_assistant_index(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . '</script>';
}
add_action( 'wp_footer', 'rbh_assistant_render', 8 );

function rbh_assistant_invalidate_index() { delete_transient( 'rbh_assistant_public_v2' ); }
add_action( 'save_post', 'rbh_assistant_invalidate_index' );
add_action( 'deleted_post', 'rbh_assistant_invalidate_index' );
add_action( 'transition_post_status', 'rbh_assistant_invalidate_index' );
