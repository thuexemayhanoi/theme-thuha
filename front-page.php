<?php
defined( 'ABSPATH' ) || exit;
if ( 'page' !== get_option( 'show_on_front' ) ) { require get_template_directory() . '/index.php'; return; }
if ( 'content' === rbh_setting( 'home_layout' ) ) { require get_template_directory() . '/page.php'; return; }
get_header();
while ( have_posts() ) : the_post();
if ( post_password_required() ) : ?>
<main id="main" class="container page-shell"><h1><?php the_title(); ?></h1><?php the_content(); ?></main>
<?php else :
$home = rbh_home_data();
$hero_id = absint( rbh_setting( 'hero_image' ) ) ?: $home['image'];
$prices = rbh_find_link( 'Prices' );
$scooter = rbh_find_link( 'Scooter' );
$rules = rbh_find_link( 'Rules' );
$faq_link = rbh_find_link( 'FAQ' );
?>
<main id="main" class="home-main">
	<?php do_action( 'rbh_before_content', get_the_ID() ); ?>
	<section class="hero container">
		<div class="hero-copy">
			<p class="eyebrow"><span class="status-dot"></span><?php echo esc_html( rbh_setting( 'brand' ) ); ?></p>
			<h1><?php echo esc_html( rbh_setting( 'hero_title' ) ); ?></h1>
			<p class="hero-lede"><?php echo esc_html( rbh_setting( 'hero_text' ) ); ?></p>
			<div class="button-row"><?php rbh_cta( __( 'Chat on WhatsApp', 'rentbike-hanoi' ) ); ?><?php if ( $prices ) : ?><a class="button button-outline" href="<?php echo esc_url( $prices->url ); ?>"><?php echo esc_html( $prices->title ); ?> <span aria-hidden="true">→</span></a><?php endif; ?></div>
			<div class="hero-notes"><span><?php esc_html_e( 'Daily · Weekly · Monthly', 'rentbike-hanoi' ); ?></span><?php if ( rbh_setting( 'hours' ) ) : ?><span><?php echo esc_html( rbh_setting( 'hours' ) ); ?></span><?php endif; ?></div>
		</div>
		<figure class="hero-visual">
			<?php if ( $hero_id && wp_attachment_is_image( $hero_id ) ) { $hero_alt = trim( (string) get_post_meta( $hero_id, '_wp_attachment_image_alt', true ) ); echo wp_get_attachment_image( $hero_id, 'large', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 760px) 100vw, 50vw', 'class' => 'hero-photo', 'alt' => $hero_alt ?: rbh_setting( 'hero_title' ) ) ); }
			else { echo '<div class="photo-fallback"><span>HANOI<br>ON TWO<br>WHEELS.</span></div>'; } ?>
			<div class="photo-label" aria-hidden="true"><span>HANOI</span><span>YOUR RIDE. YOUR PACE.</span></div>
		</figure>
	</section>
	<?php do_action( 'rbh_hero', get_the_ID() ); ?>
	<?php if ( $hero_id === $home['image'] && $home['caption'] ) : ?><div class="container image-credit"><?php echo $home['caption']; ?></div><?php endif; ?>
	<div class="container quick-links">
		<?php foreach ( array( 'Prices', 'Rules', 'Delivery', 'FAQ' ) as $needle ) : $item = rbh_find_link( $needle ); if ( ! $item ) { continue; } ?>
		<a href="<?php echo esc_url( $item->url ); ?>"><span><?php echo esc_html( $item->title ); ?></span><span aria-hidden="true">↗</span></a>
		<?php endforeach; ?>
	</div>
	<?php if ( $home['bikes'] ) : ?>
	<section class="section container" id="rental-options">
		<div class="section-heading"><div><p class="eyebrow"><?php esc_html_e( 'Find your fit', 'rentbike-hanoi' ); ?></p><h2><?php esc_html_e( 'A bike for your plans.', 'rentbike-hanoi' ); ?></h2></div><p><?php esc_html_e( 'Ask about current availability and confirm the rental terms before booking.', 'rentbike-hanoi' ); ?></p></div>
		<div class="bike-grid">
			<?php foreach ( $home['bikes'] as $i => $bike ) : ?>
			<article class="bike-card">
				<div class="bike-card-image"><span class="bike-number"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><?php echo wp_get_attachment_image( $bike['image'], 'medium_large', false, array( 'loading' => 'lazy', 'sizes' => '(max-width: 640px) 90vw, 33vw' ) ); ?></div>
				<div class="bike-card-body"><h3><?php echo esc_html( $bike['label'] ); ?></h3><p><?php esc_html_e( 'Confirm the model, price and requirements.', 'rentbike-hanoi' ); ?></p>
				<?php $bike_link = 'Automatic' === $bike['label'] ? $scooter : $rules; if ( $bike_link ) : ?><a class="text-link" href="<?php echo esc_url( $bike_link->url ); ?>"><?php echo esc_html( $bike_link->title ); ?> <span aria-hidden="true">↗</span></a><?php endif; ?></div>
			</article>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>
	<?php if ( $home['table'] ) : ?>
	<section class="rates-section"><div class="container rates-layout">
		<div><p class="eyebrow"><?php esc_html_e( 'Before you book', 'rentbike-hanoi' ); ?></p><h2><?php esc_html_e( 'Know the price. Know the terms.', 'rentbike-hanoi' ); ?></h2><p><?php esc_html_e( 'Confirm the exact rate for your bike and rental period directly with the shop.', 'rentbike-hanoi' ); ?></p><?php if ( $prices ) : ?><a class="text-link" href="<?php echo esc_url( $prices->url ); ?>"><?php echo esc_html( $prices->title ); ?> ↗</a><?php endif; ?></div>
		<div class="rate-table entry-content"><?php echo $home['table']; ?></div>
	</div></section>
	<?php endif; ?>
	<section class="section container plan-section">
		<div class="section-heading"><div><p class="eyebrow"><?php esc_html_e( 'Simple next steps', 'rentbike-hanoi' ); ?></p><h2><?php esc_html_e( 'Get ready to ride.', 'rentbike-hanoi' ); ?></h2></div></div>
		<div class="steps-grid">
			<div><span>01</span><h3><?php esc_html_e( 'Choose a bike', 'rentbike-hanoi' ); ?></h3><p><?php esc_html_e( 'Tell us your dates, preferred bike and rental duration.', 'rentbike-hanoi' ); ?></p></div>
			<div><span>02</span><h3><?php esc_html_e( 'Check the details', 'rentbike-hanoi' ); ?></h3><p><?php esc_html_e( 'Review the price, deposit, documents and applicable riding requirements.', 'rentbike-hanoi' ); ?></p></div>
			<div><span>03</span><h3><?php esc_html_e( 'Confirm directly', 'rentbike-hanoi' ); ?></h3><p><?php esc_html_e( 'Contact the shop to agree on availability and handover arrangements.', 'rentbike-hanoi' ); ?></p></div>
		</div>
	</section>
	<?php if ( $home['faqs'] ) : ?>
	<section class="section container faq-layout"><div><p class="eyebrow"><?php esc_html_e( 'A little clarity', 'rentbike-hanoi' ); ?></p><h2><?php esc_html_e( 'Before your first ride.', 'rentbike-hanoi' ); ?></h2><?php if ( $faq_link ) : ?><a class="text-link" href="<?php echo esc_url( $faq_link->url ); ?>"><?php echo esc_html( $faq_link->title ); ?> ↗</a><?php endif; ?></div>
		<div class="faq-list"><?php foreach ( $home['faqs'] as $faq ) : ?><details><summary><?php echo esc_html( $faq[0] ); ?></summary><p><?php echo esc_html( $faq[1] ); ?></p></details><?php endforeach; ?></div>
	</section>
	<?php endif; ?>
	<?php if ( rbh_setting( 'legacy_home' ) ) : ?>
	<section class="section container source-sections">
		<p class="eyebrow"><?php esc_html_e( 'Rental information', 'rentbike-hanoi' ); ?></p><h2><?php esc_html_e( 'The details, all in one place.', 'rentbike-hanoi' ); ?></h2>
		<?php if ( $home['sections'] ) : foreach ( $home['sections'] as $section ) : ?>
		<details class="source-section"<?php if ( $section['id'] ) { echo ' id="' . esc_attr( $section['id'] ) . '"'; } ?>><summary><h3><?php echo esc_html( $section['title'] ); ?></h3></summary><div class="entry-content"><?php
		echo $section['html']; // Already passed through the complete WordPress content pipeline once.
		?></div></details>
		<?php endforeach; else : ?><div class="entry-content"><?php rbh_display_content(); ?></div><?php endif; ?>
	</section>
	<?php endif; ?>
	<?php
	$blog = rbh_find_link( 'Rental Blog' );
	$guide_args = array( 'post_type' => 'post', 'posts_per_page' => 3, 'ignore_sticky_posts' => true, 'no_found_rows' => true );
	if ( $blog && 'taxonomy' === $blog->type ) { $guide_args['cat'] = absint( $blog->object_id ); }
	$guides = new WP_Query( $guide_args );
	if ( $guides->have_posts() ) : ?>
	<section class="section container"><div class="section-heading"><div><p class="eyebrow"><?php esc_html_e( 'Read before you ride', 'rentbike-hanoi' ); ?></p><h2><?php esc_html_e( 'Useful guides.', 'rentbike-hanoi' ); ?></h2></div><?php if ( $blog ) : ?><a class="text-link" href="<?php echo esc_url( $blog->url ); ?>"><?php echo esc_html( $blog->title ); ?> ↗</a><?php endif; ?></div><div class="card-grid"><?php while ( $guides->have_posts() ) : $guides->the_post(); get_template_part( 'template-parts/card' ); endwhile; ?></div></section>
	<?php endif; wp_reset_postdata(); do_action( 'rbh_after_content', get_the_ID() ); ?>
</main>
<?php endif; endwhile; get_footer(); ?>
