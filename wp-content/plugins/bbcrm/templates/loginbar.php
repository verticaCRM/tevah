<?php
global $bbcrm_option;
?>
  <div class="top-bar">
      <ul class="left-bar-side theme-color2-lt">
     <?php if($bbcrm_option["bbcrm_contactus_showhide"]): ?>
		<li id="contactusli"><a class="theme-color2-lt" href="<?php echo get_permalink($bbcrm_option['bbcrm_loginbar_contactus']);?>"><i class="theme-color2-lt fa fa-envelope"></i> <?php _e('Contact Us','bbcrm');?> </a><span>&nbsp;&nbsp;|&nbsp;&nbsp;</span></li> 	
	<?php endif; ?>

     <?php if($bbcrm_option["bbcrm_phone_showhide"]): ?>
		<li id="contactusli"><a class="theme-color2-lt" href="tel:<?php echo $bbcrm_option['bbcrm_loginbar_phone']; ?>"><i class="theme-color2-lt fa fa-phone"></i> <?php echo $bbcrm_option['bbcrm_loginbar_phone'];  ?> </a><span>&nbsp;&nbsp;|&nbsp;&nbsp;</span></li> 	
	<?php endif; ?>
     
     <?php if($bbcrm_option["bbcrm_fax_showhide"]): ?>
		<li id="contactusli"><span class="theme-color2-lt"><i class="theme-color2-lt fa fa-fax"></i> <?php echo $bbcrm_option['bbcrm_loginbar_fax'];  ?> </a><span>&nbsp;&nbsp;|&nbsp;&nbsp;</span></li> 	
	<?php endif; ?>

     <?php if($bbcrm_option["bbcrm_email_showhide"]): ?>
		<li id="contactusli"><span class="theme-color2-lt"><i class="theme-color2-lt fa fa-envelope-o"></i> <?php echo $bbcrm_option['bbcrm_loginbar_email'];  ?> </a><span>&nbsp;&nbsp;|&nbsp;&nbsp;</span></li> 	
	<?php endif; ?>

	<?php if(!is_user_logged_in() ): ?>
		<li><a class="theme-color2-lt" href="<?php echo get_permalink($bbcrm_option["bbcrm_pages_registration"]);?>"><i class="fa  fa-plus-circle"></i> <?php _e('Member Registration','bbcrm');?> </a> <span>&nbsp;&nbsp;|&nbsp;&nbsp;</span></li>
		<li id="loginli"><a href="#" class="loginlink theme-color2-lt"><i class="theme-color2-lt fa fa-lock"></i> <?php _e('Buyer Login','bbcrm');?> </a><span>&nbsp;&nbsp;|&nbsp;&nbsp;</span></li>
<div class="theme-color2-lt" id="logindiv"><?php 
$args = array(
	'form_id' => 'headerloginform',
	'remember' => false,
	 'value_remember' => false,
	 'redirect' => get_permalink($bbcrm_option['bbcrm_loginbar_dataroom']) ,
'label_username' => __( 'Username' , 'bbcrm' ),
	'label_password' => __( 'Password' , 'bbcrm' ),
	'label_remember' => __( 'Remember Me' , 'bbcrm' ),
	'label_log_in'   => __( 'Log In' , 'bbcrm'),
	);
wp_login_form($args); ?>
<script>jQuery("#wp-submit").addClass('theme-background1-lt').css('border','none')</script>
</div>

<li><a class="theme-color2-lt" href="<?php echo wp_lostpassword_url(); ?>"><i class="theme-color2-lt fa  fa-question"></i> <?php _e('Forgot Password', 'bbcrm');?> </a><span>&nbsp;&nbsp;|&nbsp;&nbsp;</span></li>
	 	<?php else: ?>
	 	<li><a class="theme-color2-lt" href="<?php echo get_permalink($bbcrm_option['bbcrm_loginbar_dataroom']);?>"><i class="theme-color2-lt fa fa-building-o"></i> <?php echo __('Your','bbcrm')." ".get_the_title($bbcrm_option['bbcrm_loginbar_dataroom']);?> </a><span>&nbsp;&nbsp;|&nbsp;&nbsp;</span></li>
	 	<li><a class="theme-color2-lt" href="<?php echo get_permalink($bbcrm_option['bbcrm_pages_buyerprofile']);?>"><i class="theme-color2-lt fa fa-user"></i> <?php _e('Your Profile','bbcrm');?> </a><span>&nbsp;&nbsp;|&nbsp;&nbsp;</span></li>
	 	<li><a class="theme-color2-lt" href="<?php echo wp_logout_url('/'); ?>"><i class="theme-color2-lt fa fa-arrow-circle-o-right"></i> <?php _e('Log Out','bbcrm');?> </a></li>	
		<?php endif; ?>		
      </ul> 
</div>
     <script>
     jQuery(document).ready(function(){
	jQuery(".loginlink").click(function(event){
	event.preventDefault();
	jQuery("#logindiv").css('display','inline-block');jQuery("#loginli").hide();})
     });     
     </script>
