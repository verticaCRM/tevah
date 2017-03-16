<?php
/**
 * The image template file.
 *
 * @package Tevah Lite
 */

get_header(); ?>

	<?php if ( have_posts() ) : ?>

		<?php /* Start the Loop */ ?>
		<?php while ( have_posts() ) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> <?php hybrid_attr( 'post' ); ?>>
				
				<div class="entry-inner">
	
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title" ' . hybrid_get_attr( 'entry-title' ) . '>', '</h1>' ); ?>
					</header><!-- .entry-header -->
				
					<div class="entry-media">
						<?php if ( has_excerpt() ) :
							$src = wp_get_attachment_image_src( get_the_ID(), 'full' );
							echo img_caption_shortcode( array( 'align' => 'aligncenter', 'width' => esc_attr( $src[1] ), 'caption' => get_the_excerpt() ), wp_get_attachment_image( get_the_ID(), 'full', false ) );
						else :
							echo wp_get_attachment_image( get_the_ID(), 'full', false, array( 'class' => 'aligncenter' ) );
						endif; ?>
					</div><!-- .entry-media -->
	
					<div class="entry-content"  <?php hybrid_attr( 'entry-content' ); ?>>
						<?php the_content(); ?>
						<?php
							wp_link_pages( array(
								'before'    => '<div class="page-links">' . __( 'Pages:', 'tevah' ),
								'after'     => '</div>',
								'pagelink'  => '<span class="screen-reader-text">' . __( 'Page', 'tevah' ) . ' </span>%',
								'separator' => '<span class="screen-reader-text">,</span> ',
							) );
						?>
					</div><!-- .entry-content -->
					
				</div><!-- .entry-inner -->
	
			</article><!-- #post-## -->

		<?php endwhile; ?>

	<?php endif; ?>

<?php get_footer(); ?>
