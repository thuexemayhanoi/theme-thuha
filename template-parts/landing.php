<?php
/**
 * Data-driven rental landing layout with the same markup and classes as
 * front-page.php. Presentation only: a plugin passes every text, link, image
 * ID and table in $args (used for the Vietnamese home page /vi/). The English
 * front page does not use this file.
 */
defined( 'ABSPATH' ) || exit;
$a = wp_parse_args( $args, array( 'post_id' => get_the_ID(), 'eyebrow' => '', 'title' => '', 'lede' => '', 'buttons' => array(), 'notes' => array(), 'hero_image' => 0, 'hero_alt' => '', 'hero_caption' => '', 'photo_label' => array(), 'fallback' => '', 'quick_links' => array(), 'bikes' => array(), 'rates' => array(), 'steps' => array(), 'faq' => array(), 'details' => array(), 'guides' => array() ) );
$link = function( $item, $class = 'text-link', $arrow = '↗' ) {
	if ( empty( $item['url'] ) ) { return; }
	echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . ' <span aria-hidden="true">' . esc_html( $arrow ) . '</span></a>';
};
$heading = function( $section, $tag = 'h2' ) {
	if ( ! empty( $section['eyebrow'] ) ) { echo '<p class="eyebrow">' . esc_html( $section['eyebrow'] ) . '</p>'; }
	echo '<' . $tag . '>' . esc_html( $section['title'] ) . '</' . $tag . '>';
};
?>
<main id="main" class="home-main rbh-landing">
	<?php do_action( 'rbh_before_content', $a['post_id'] ); ?>
	<section class="hero container">
		<div class="hero-copy">
			<?php if ( $a['eyebrow'] ) : ?><p class="eyebrow"><span class="status-dot"></span><?php echo esc_html( $a['eyebrow'] ); ?></p><?php endif; ?>
			<h1><?php echo esc_html( $a['title'] ); ?></h1>
			<?php if ( $a['lede'] ) : ?><p class="hero-lede"><?php echo esc_html( $a['lede'] ); ?></p><?php endif; ?>
			<?php if ( $a['buttons'] ) : ?><div class="button-row"><?php foreach ( $a['buttons'] as $i => $button ) { $link( $button, $i ? 'button button-outline' : 'button', $i ? '→' : '↗' ); } ?></div><?php endif; ?>
			<?php if ( $a['notes'] ) : ?><div class="hero-notes"><?php foreach ( $a['notes'] as $note ) : ?><span><?php echo esc_html( $note ); ?></span><?php endforeach; ?></div><?php endif; ?>
		</div>
		<figure class="hero-visual">
			<?php if ( $a['hero_image'] && wp_attachment_is_image( $a['hero_image'] ) ) { echo wp_get_attachment_image( $a['hero_image'], 'large', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'data-no-lazy' => '1', 'sizes' => '(max-width: 760px) 100vw, 50vw', 'class' => 'hero-photo', 'alt' => $a['hero_alt'] ) ); }
			else { echo '<div class="photo-fallback"><span>' . wp_kses( $a['fallback'], array( 'br' => array() ) ) . '</span></div>'; } ?>
			<?php if ( count( $a['photo_label'] ) === 2 ) : ?><div class="photo-label" aria-hidden="true"><span><?php echo esc_html( $a['photo_label'][0] ); ?></span><span><?php echo esc_html( $a['photo_label'][1] ); ?></span></div><?php endif; ?>
		</figure>
	</section>
	<?php do_action( 'rbh_hero', $a['post_id'] ); ?>
	<?php if ( $a['hero_caption'] ) : ?><div class="container image-credit"><?php echo wp_kses_post( $a['hero_caption'] ); ?></div><?php endif; ?>
	<?php if ( $a['quick_links'] ) : ?>
	<div class="container quick-links">
		<?php foreach ( $a['quick_links'] as $item ) : ?><a href="<?php echo esc_url( $item['url'] ); ?>"><span><?php echo esc_html( $item['label'] ); ?></span><span aria-hidden="true">↗</span></a><?php endforeach; ?>
	</div>
	<?php endif; ?>
	<?php if ( ! empty( $a['bikes']['items'] ) ) : $bikes = $a['bikes']; ?>
	<section class="section container" id="<?php echo esc_attr( $bikes['id'] ?? 'rental-options' ); ?>">
		<div class="section-heading"><div><?php $heading( $bikes ); ?></div><?php if ( ! empty( $bikes['text'] ) ) : ?><p><?php echo esc_html( $bikes['text'] ); ?></p><?php endif; ?></div>
		<div class="bike-grid">
			<?php foreach ( $bikes['items'] as $i => $bike ) : ?>
			<article class="bike-card">
				<div class="bike-card-image"><span class="bike-number"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><?php if ( ! empty( $bike['image'] ) && wp_attachment_is_image( $bike['image'] ) ) { echo wp_get_attachment_image( $bike['image'], 'medium_large', false, array( 'loading' => 'lazy', 'sizes' => '(max-width: 640px) 90vw, 33vw', 'alt' => $bike['alt'] ?? '' ) ); } ?></div>
				<div class="bike-card-body"><h3><?php echo esc_html( $bike['label'] ); ?></h3><p><?php echo esc_html( $bike['text'] ); ?></p><?php if ( ! empty( $bike['link'] ) ) { $link( $bike['link'] ); } ?></div>
			</article>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>
	<?php if ( ! empty( $a['rates']['table'] ) ) : $rates = $a['rates']; ?>
	<section class="rates-section"><div class="container rates-layout">
		<div><?php $heading( $rates ); ?><?php if ( ! empty( $rates['text'] ) ) : ?><p><?php echo esc_html( $rates['text'] ); ?></p><?php endif; ?><?php if ( ! empty( $rates['link'] ) ) { $link( $rates['link'] ); } ?></div>
		<div class="rate-table entry-content"><?php echo wp_kses_post( $rates['table'] ); ?></div>
	</div></section>
	<?php endif; ?>
	<?php if ( ! empty( $a['steps']['items'] ) ) : $steps = $a['steps']; ?>
	<section class="section container plan-section">
		<div class="section-heading"><div><?php $heading( $steps ); ?></div></div>
		<div class="steps-grid">
			<?php foreach ( $steps['items'] as $i => $step ) : ?><div><span><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><h3><?php echo esc_html( $step['title'] ); ?></h3><p><?php echo esc_html( $step['text'] ); ?></p></div><?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>
	<?php if ( ! empty( $a['faq']['items'] ) ) : $faq = $a['faq']; ?>
	<section class="section container faq-layout"><div><?php $heading( $faq ); ?><?php if ( ! empty( $faq['link'] ) ) { $link( $faq['link'] ); } ?></div>
		<div class="faq-list"><?php foreach ( $faq['items'] as $item ) : ?><details><summary><?php echo esc_html( $item[0] ); ?></summary><p><?php echo esc_html( $item[1] ); ?></p></details><?php endforeach; ?></div>
	</section>
	<?php endif; ?>
	<?php if ( ! empty( $a['details']['sections'] ) ) : $details = $a['details']; ?>
	<section class="section container source-sections">
		<?php $heading( $details ); ?>
		<?php foreach ( $details['sections'] as $section ) : ?>
		<details class="source-section"<?php if ( ! empty( $section['id'] ) ) { echo ' id="' . esc_attr( $section['id'] ) . '"'; } ?>><summary><h3><?php echo esc_html( $section['title'] ); ?></h3></summary><div class="entry-content"><?php
		echo $section['html']; // Saved page content, already passed through the WordPress content pipeline once.
		?></div></details>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>
	<?php if ( ! empty( $a['guides']['ids'] ) ) : $guides = $a['guides']; $query = new WP_Query( array( 'post_type' => 'post', 'post__in' => $guides['ids'], 'orderby' => 'post__in', 'posts_per_page' => count( $guides['ids'] ), 'ignore_sticky_posts' => true, 'no_found_rows' => true ) ); if ( $query->have_posts() ) : ?>
	<section class="section container"><div class="section-heading"><div><?php $heading( $guides ); ?></div><?php if ( ! empty( $guides['link'] ) ) { $link( $guides['link'] ); } ?></div><div class="card-grid"><?php while ( $query->have_posts() ) : $query->the_post(); get_template_part( 'template-parts/card' ); endwhile; ?></div></section>
	<?php endif; wp_reset_postdata(); endif; ?>
	<?php do_action( 'rbh_after_content', $a['post_id'] ); ?>
</main>
