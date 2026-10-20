<?php defined( 'ABSPATH' ) || exit; $image = get_post_thumbnail_id() ?: rbh_image_id( rbh_setting( 'default_image' ) ); ?>
<article id="card-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
	<?php if ( $image && ! post_password_required() ) : ?><a class="post-card-image" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1"><?php echo wp_get_attachment_image( $image, 'medium_large', false, array( 'loading' => 'lazy', 'sizes' => '(max-width: 640px) 100vw, 380px', 'alt' => '' ) ); ?></a><?php endif; ?>
	<div class="post-card-body">
		<?php $terms = get_the_category(); if ( $terms ) : ?><p class="eyebrow"><?php echo esc_html( $terms[0]->name ); ?></p><?php endif; ?>
		<h2 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php if ( rbh_setting( 'dates' ) ) : ?><p class="entry-meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p><?php endif; ?>
		<p class="card-excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 23 ) ); ?></p>
		<a class="text-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read guide', 'rentbike-hanoi' ); ?><span class="screen-reader-text">: <?php the_title(); ?></span><span aria-hidden="true"> ↗</span></a>
	</div>
</article>
