<?php defined( 'ABSPATH' ) || exit; $rbh_search_id = wp_unique_id( 'rbh-search-' ); ?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $rbh_search_id ); ?>"><?php esc_html_e( 'Search this site', 'rentbike-hanoi' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $rbh_search_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search guides, rental info…', 'rentbike-hanoi' ); ?>">
	<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'rentbike-hanoi' ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
</form>
