<?php
/**
 * Primary menu.
 *
 * @package Tevah Lite
 */
?>

	<button id="nav-toggle"><?php _e( 'Menu', 'tevah' ); ?></button>
	
	<nav id="menu-primary" class="menu main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'tevah' ); ?>" <?php hybrid_attr( 'menu', 'primary' ); ?>>
		<h2 class="screen-reader-text"><?php esc_attr_e( 'Primary Menu', 'tevah' ); ?></h2>
		
		<div class="wrap">
			
			<?php

				wp_nav_menu(
					array(
						'theme_location'  => 'primary',
						'menu_id'         => 'menu-primary-items',
						'depth'           => 4,
						'menu_class'      => 'menu-items'
					)
				);
			
			?>
		
		</div><!-- .wrap -->
	</nav><!-- #menu-primary -->
	
