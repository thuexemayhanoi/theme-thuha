<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<main id="main" class="container page-shell">
	<?php while ( have_posts() ) : the_post(); rbh_breadcrumb(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'standard-page' ); ?>>
		<header class="article-header"><h1><?php the_title(); ?></h1></header>
		<?php do_action( 'rbh_hero', get_the_ID() ); ?>
		<div class="entry-content"><?php rbh_display_content(); ?></div>
		<?php if ( comments_open() || get_comments_number() ) { comments_template(); } ?>
	</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
