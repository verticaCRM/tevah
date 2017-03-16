<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #main and all content after
 *
 * @package Tevah Lite
 */
?>

					</main><!-- #main -->
				</div><!-- #primary -->

			<?php get_sidebar( 'primary' ); // Loads the sidebar-primary.php template. ?>
			
			</div><!-- .wrap-inside -->
		</div><!-- .wrap -->
	</div><!-- #content -->
	
	<?php get_sidebar( 'subsidiary' ); // Loads the sidebar-subsidiary.php template. ?>

	<footer id="colophon" class="site-footer" role="contentinfo" <?php hybrid_attr( 'footer' ); ?>>
		
		<div class="site-info">
			<a href="<?php echo esc_url( __( 'https://businessbrokerscrm.com/', 'tevah' ) ); ?>"><?php printf( __( 'Proudly powered by %s', 'tevah' ), 'BBCRM' ); ?></a>
			<span class="sep"><?php echo _x( '&middot;', 'Separator in site info.', 'tevah' ); ?></span>
			<?php printf( __( '%1$s Theme adapted from Toivo by %2$s', 'tevah' ), 'Tevah', '<a href="https://foxland.fi" rel="designer">Foxland</a>' ); ?>
		</div><!-- .site-info -->
		
	</footer><!-- #colophon -->
	
	<?php do_action( 'toivo_after_footer' ); // Hook after footer. ?>
	
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
