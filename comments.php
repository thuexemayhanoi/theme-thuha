<?php
defined( 'ABSPATH' ) || exit;
if ( post_password_required() ) { return; }
?>
<section id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
	<h2><?php esc_html_e( 'Discussion', 'rentbike-hanoi' ); ?></h2>
	<ol class="comment-list"><?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true, 'avatar_size' => 40 ) ); ?></ol>
	<?php the_comments_navigation(); endif; ?>
	<?php if ( ! comments_open() && get_comments_number() ) : ?><p><?php esc_html_e( 'Comments are closed.', 'rentbike-hanoi' ); ?></p><?php endif; ?>
	<?php comment_form(); ?>
</section>
