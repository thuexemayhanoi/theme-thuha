<?php defined( 'ABSPATH' ) || exit; get_header(); ?>
<main id="main" class="container page-shell">
	<?php while ( have_posts() ) : the_post(); rbh_breadcrumb(); ?>
	<div class="reading-layout <?php echo rbh_setting( 'sidebar' ) ? '' : 'without-sidebar'; ?>">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'article-body' ); ?>>
			<header class="article-header"><p class="eyebrow"><?php $categories = get_the_category(); echo $categories ? esc_html( $categories[0]->name ) : esc_html__( 'Guide', 'rentbike-hanoi' ); ?></p><h1><?php the_title(); ?></h1><?php rbh_date(); ?></header>
			<?php if ( has_post_thumbnail() && ! post_password_required() ) : $featured_id = get_post_thumbnail_id(); $featured_alt = trim( (string) get_post_meta( $featured_id, '_wp_attachment_image_alt', true ) ); ?><figure class="featured-image"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'sizes' => '(max-width: 900px) 100vw, 740px', 'alt' => $featured_alt ?: get_the_title() ) ); ?></figure><?php endif; ?>
			<?php do_action( 'rbh_hero', get_the_ID() ); ?>
			<div class="entry-content"><?php rbh_display_content(); ?></div>
			<?php do_action( 'rbh_article_end', get_the_ID() ); ?>
			<?php if ( comments_open() || get_comments_number() ) { comments_template(); } ?>
		</article>
		<?php if ( rbh_setting( 'sidebar' ) ) { get_sidebar(); } ?>
	</div>
	<?php do_action( 'rbh_related', get_the_ID() ); endwhile; ?>
</main>
<?php get_footer(); ?>
