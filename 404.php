<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<main id="main" class="container error-page">
	<p class="eyebrow">404 · <?php esc_html_e( 'A small detour', 'rentbike-hanoi' ); ?></p>
	<h1><?php esc_html_e( 'This road ends here.', 'rentbike-hanoi' ); ?></h1>
	<p><?php esc_html_e( 'We couldn’t find that page. Search the site or start again from the homepage.', 'rentbike-hanoi' ); ?></p>
	<?php get_search_form(); ?>
	<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to homepage', 'rentbike-hanoi' ); ?> →</a>
</main>
<?php get_footer(); ?>
