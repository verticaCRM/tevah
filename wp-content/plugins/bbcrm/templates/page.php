<?php
if(is_page()||is_home()){
$title = get_the_title();
}

if(is_front_page()){
$title = get_bloginfo("description");
}

//get_template_part("template","top");
get_header();

/** Update the post views counter */
//_WSH()->post_views( true );?>

<section class="row contentRowPad">
<div class="containerouter">
	<div class="row"  style="border:1px solid #dfdfdf;padding:12px;margin:0px">
		
		<?php if( $layout == 'left' ): ?>
		
                <div class="col-md-4 col-sm-4 col-xs-12">
                        <div id="sidebar" class="clearfix">        
							<?php dynamic_sidebar( $sidebar ); ?>
                		</div>
                </div>
		
		<?php endif; ?><!-- end sidebar -->	

		<div class="<?php echo esc_attr($classes);?>">                    
			<?php while( have_posts() ): the_post(); ?>
            	<div id="post-<?php the_ID(); ?>" <?php post_class();?>>
					<div class="blog row m0 single_post">
						
						<div class="desc">
							<?php the_content();?>
						</div>
						
						<?php the_tags('<div class="tags">', ', ', '</div>');?>
						
						<div class="clearfix"></div>
						
						<?php comments_template(); ?><!-- end comments -->
			
					</div>
				</div>
			
			<?php endwhile;?>
			
		</div>

	</div>
	</div>
</section>

<?php get_footer();//get_template_part("template","bottom");
 ?>
