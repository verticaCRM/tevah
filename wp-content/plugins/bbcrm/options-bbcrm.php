<?php
class options_page {

	public $options;

	function __construct() {
		global $options;
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'init_fields') );
	
		wp_enqueue_script('media-upload');
		wp_enqueue_script('jquery-ui-tabs',array('jquery'));
		wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');
		wp_enqueue_style('jquery-ui-tabs');
	
		$this->options=get_option('bbcrm_settings');
}

	function admin_menu() {
		add_menu_page(
			'BBCRM Settings',
			'BBCRM Settings',
			'manage_options',
			'bbcrm-options',
			array($this,'settings_page')
		);
	}

function settings_page() {
?>
<div style='margin:1em;float:right;max-width:30%'><img style="width:100%" src='<?php echo plugin_dir_url(__FILE__)."images/bbcrm.png"; ?>' /></div>
<h2><?php _e('BBCRM Front End Settings','bbcrm')?></h2>
<p>This is the frontend control panel for your BusinessBrokersCRM application. These settings are used to associate different fields with their corresponding fields from the CRM. Please use caution when modifying this information, as changes can potentially interrupt access to different pages and services on your site. If you have any questions, please contact support@verticacrm.com.</p>
<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/overcast/jquery-ui.css"/>
<script type="text/javascript">
jQuery(document).ready(function() {

imguploadid=''

jQuery("#tabs").tabs();

jQuery('.upload_image_button').click(function() {
imguploadid = jQuery(this).data("opt")
//console.log(imguploadid)
 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
 return false;
});
 
window.send_to_editor = function(html) {
//console.log(html)
imgurl = jQuery(html).attr('src');
//console.log(imgurl)
jQuery('#upload-image-'+imguploadid).val(imgurl);
tb_remove();
}
 
});
</script>
	<form action='options.php' method='post'>		
	<div id="tabs" style="display:inline-block">
	<ul>
<?php
global $wp_settings_sections, $wp_settings_fields;

foreach((array)$wp_settings_sections['pluginPage'] as $section) :
        if(!isset($section['title']))
	            continue;

	printf('<li><a href="#%1$s">%2$s</a></li>', $section['id'], $section['title']);
endforeach;
?>
	</ul>
<?php
	settings_fields( 'pluginPage' );
//////
foreach((array)$wp_settings_sections['pluginPage'] as $section) :

         printf('<div id="%1$s">',$section['id']);

	if($section['callback'])
		call_user_func($section['callback'], $section);

	if(!isset($wp_settings_fields) || !isset($wp_settings_fields['pluginPage']) || !isset($wp_settings_fields['pluginPage'][$section['id']]))
		continue;

	echo '<table class="form-table">';
	do_settings_fields('pluginPage', $section['id']);
	echo '</table>';
	echo '</div>';
endforeach;
echo "</div><!-- end tab div -->";
////
submit_button();
?>
</form>
<?php
}

public function init_fields(){
	register_setting( 'pluginPage', 'bbcrm_settings' );

	add_settings_section(
		'bbcrm_loginbar_section', 
		__( 'Login Bar Fields', 'bbcrm' ), 
		array($this,'bbcrm_settings_section_callback'), 
		'pluginPage'
	);

	add_settings_field( 
		'bbcrm_loginbar_phone', 
		__( 'Phone Number', 'bbcrm' ), 
		array($this,'bbcrm_text_render'), 
		'pluginPage', 
		'bbcrm_loginbar_section',
		array('option-name'=>'bbcrm_loginbar_phone')
	);

	add_settings_field( 
		'bbcrm_phone_showhide',
		__( 'Display Phone Number?', 'bbcrm' ), 
		array($this,'bbcrm_checkbox_render'), 
		'pluginPage', 
		'bbcrm_loginbar_section',
		array('option-name'=>'bbcrm_phone_showhide')
	);

	add_settings_field( 
		'bbcrm_loginbar_fax', 
		__( 'Fax Number', 'bbcrm' ), 
		array($this,'bbcrm_text_render'), 
		'pluginPage', 
		'bbcrm_loginbar_section',
		array('option-name'=>'bbcrm_loginbar_fax')
	);
	
	add_settings_field( 
		'bbcrm_fax_showhide',
		__( 'Display Fax Number?', 'bbcrm' ), 
		array($this,'bbcrm_checkbox_render'), 
		'pluginPage', 
		'bbcrm_loginbar_section',
		array('option-name'=>'bbcrm_fax_showhide')
	);

	add_settings_field( 
		'bbcrm_loginbar_email', 
		__( 'Main Email Address', 'bbcrm' ), 
		array($this,'bbcrm_text_render'), 
		'pluginPage', 
		'bbcrm_loginbar_section',
		array('option-name'=>'bbcrm_loginbar_email')
	);

	add_settings_field( 
		'bbcrm_email_showhide',
		__( 'Display Email Address?', 'bbcrm' ), 
		array($this,'bbcrm_checkbox_render'), 
		'pluginPage', 
		'bbcrm_loginbar_section',
		array('option-name'=>'bbcrm_email_showhide')
	);

	add_settings_field( 
		'bbcrm_contactus_showhide',
		__( 'Display Contact Us Link?', 'bbcrm' ), 
		array($this,'bbcrm_checkbox_render'), 
		'pluginPage', 
		'bbcrm_loginbar_section',
		array('option-name'=>'bbcrm_contactus_showhide')
	);

	add_settings_section(
		'bbcrm_pages_section',
		__('Default Pages','bbcrm'),
		array($this,'bbcrm_settings_section_callback'),
		'pluginPage'
	);
	add_settings_field( 
		'bbcrm_loginbar_contactus', 
		__( 'Contact Us Page', 'bbcrm' ), 
		array($this,'bbcrm_selectpage_render'), 
		'pluginPage', 
		'bbcrm_pages_section',
		array('option-name'=>'bbcrm_loginbar_contactus')
	);
	add_settings_field( 
		'bbcrm_pageselect_brokerteam', 
		__( 'Broker Team Page', 'bbcrm' ), 
		array($this,'bbcrm_selectpage_render'), 
		'pluginPage', 
		'bbcrm_pages_section',
		array('option-name'=>'bbcrm_pageselect_brokerteam')
	)
	;
	add_settings_field( 
		'bbcrm_pageselect_broker', 
		__( 'Broker Detail Page', 'bbcrm' ), 
		array($this,'bbcrm_selectpage_render'), 
		'pluginPage', 
		'bbcrm_pages_section',
		array('option-name'=>'bbcrm_pageselect_broker')
	);


	add_settings_field( 
		'bbcrm_loginbar_dataroom', 
		__( 'Data Room Page', 'bbcrm' ), 
		array($this,'bbcrm_selectpage_render'), 
		'pluginPage', 
		'bbcrm_pages_section',
		array('option-name'=>'bbcrm_loginbar_dataroom')
	);

	add_settings_field( 
		'bbcrm_loginbar_searchresults', 
		__( 'Search Results Page', 'bbcrm' ), 
		array($this,'bbcrm_selectpage_render'), 
		'pluginPage', 
		'bbcrm_pages_section',
		array('option-name'=>'bbcrm_loginbar_searchresults')
	);
	
	add_settings_field( 
		'bbcrm_pages_registration', 
		__( 'Registration Page', 'bbcrm' ), 
		array($this,'bbcrm_selectpage_render'), 
		'pluginPage', 
		'bbcrm_pages_section',
		array('option-name'=>'bbcrm_pages_registration')
	);
	
	add_settings_field( 
		'bbcrm_pages_buyerprofile', 
		__( 'Buyer Profile Page', 'bbcrm' ), 
		array($this,'bbcrm_selectpage_render'), 
		'pluginPage', 
		'bbcrm_pages_section',
		array('option-name'=>'bbcrm_pages_buyerprofile')
	);
	
	add_settings_field( 
		'bbcrm_pages_welcome', 
		__( 'Buyer Registration Welcome Page', 'bbcrm' ), 
		array($this,'bbcrm_selectpage_render'), 
		'pluginPage', 
		'bbcrm_pages_section',
		array('option-name'=>'bbcrm_pages_welcome')
	);
	
	add_settings_section(
		'bbcrm_design_section', 
		__( 'Design Elements', 'bbcrm' ), 
		array($this,'bbcrm_settings_section_callback'), 
		'pluginPage'
	);
	
	add_settings_field( 
		'bbcrm_design_logo', 
		__( 'Logo', 'bbcrm' ), 
		array($this,'bbcrm_media_upload_render'), 
		'pluginPage', 
		'bbcrm_design_section',
		array('option-name'=>'bbcrm_design_logo')
	);
	add_settings_field( 
		'bbcrm_design_favicon', 
		__( 'Favicon', 'bbcrm' ), 
		array($this,'bbcrm_media_upload_render'), 
		'pluginPage', 
		'bbcrm_design_section',
		array('option-name'=>'bbcrm_design_favicon')
	);
	add_settings_field( 
		'bbcrm_design_noimage', 
		__( 'No Thumbnail Image', 'bbcrm' ), 
		array($this,'bbcrm_media_upload_render'), 
		'pluginPage', 
		'bbcrm_design_section',
		array('option-name'=>'bbcrm_design_noimage')
	);

	add_settings_section(
		'bbcrm_crm_section', 
		__( 'CRM Defaults', 'bbcrm' ), 
		array($this,'bbcrm_settings_section_callback'), 
		'pluginPage'
	);
	
	add_settings_field( 
		'bbcrm_crm_assignedTo', 
		__( 'Default Assigned To User', 'bbcrm' ), 
		array($this,'bbcrm_select_assignedTo_render'), 
		'pluginPage', 
		'bbcrm_crm_section',
		array('option-name'=>'bbcrm_crm_assignedTo')
	);

	add_settings_field( 
		'bbcrm_crm_states', 
		__( 'Default States/Territories/Regions', 'bbcrm' ), 
		array($this,'bbcrm_select_dropdowns_render'), 
		'pluginPage', 
		'bbcrm_crm_section',
		array('option-name'=>'bbcrm_crm_states')
	);

	add_settings_field( 
		'bbcrm_crm_buscats', 
		__( 'Business Categories', 'bbcrm' ), 
		array($this,'bbcrm_select_dropdowns_render'), 
		'pluginPage', 
		'bbcrm_crm_section',
		array('option-name'=>'bbcrm_crm_buscats')
	);

	add_settings_field( 
		'bbcrm_crm_buscats_parents',
		__( 'Business Category Parents', 'bbcrm' ), 
		array($this,'bbcrm_select_dropdowns_render'), 
		'pluginPage', 
		'bbcrm_crm_section',
		array('option-name'=>'bbcrm_crm_buscats_parents')
	);

	add_settings_field( 
		'bbcrm_crm_nda', 
		__( 'Registration NDA', 'bbcrm' ), 
		array($this,'bbcrm_select_docs_render'), 
		'pluginPage', 
		'bbcrm_crm_section',
		array('option-name'=>'bbcrm_crm_nda')
	);

	add_settings_section(
		'bbcrm_buyerreg_section', 
		__( 'Buyer Registration', 'bbcrm' ), 
		array($this,'bbcrm_settings_section_callback'), 
		'pluginPage'
	);
	
	add_settings_field( 
		'bbcrm_buyerreg_fields', 
		__( 'Buyer Registration', 'bbcrm' ), 
		array($this,'bbcrm_crm_fields_render'), 
		'pluginPage', 
		'bbcrm_buyerreg_section',
		array('option-name'=>'bbcrm_buyerreg_fields','model'=>"Contacts")
	);

	add_settings_field( 
		'bbcrm_broker_showhide',
		__( 'Display Broker?', 'bbcrm' ), 
		array($this,'bbcrm_checkbox_render'), 
		'pluginPage', 
		'bbcrm_buyerreg_section',
		array('option-name'=>'bbcrm_broker_showhide')
	);

	add_settings_field( 
		'bbcrm_listing_showhide',
		__( 'Display Listing?', 'bbcrm' ), 
		array($this,'bbcrm_checkbox_render'), 
		'pluginPage', 
		'bbcrm_buyerreg_section',
		array('option-name'=>'bbcrm_listing_showhide')
	);

	add_settings_section(
		'bbcrm_listingdetails_section', 
		__( 'Listing Details', 'bbcrm' ), 
		array($this,'bbcrm_settings_section_callback'), 
		'pluginPage'
	);
	
	add_settings_field( 
		'bbcrm_listingdetails_fields', 
		__( 'Listing Details', 'bbcrm' ), 
		array($this,'bbcrm_crm_fields_render'), 
		'pluginPage', 
		'bbcrm_listingdetails_section',
		array('option-name'=>'bbcrm_listingdetails_fields','model'=>"Clistings")
	);

	}

function bbcrm_media_upload_render($args ) { 
	?>
<input id="upload-image-<?php echo $args['option-name'];?>"" data-opt="<?php echo $args['option-name'];?>" class="upload_image" type="text" size="36" name="bbcrm_settings[<?php echo $args['option-name'];?>]" value="<?php echo $this->options[$args['option-name']];?>" />
<input id="upload-image-button-<?php echo $args["option-name"];?>" data-opt="<?php echo $args["option-name"];?>" class="upload_image_button" type="button" value="Upload Image" />
<br />Enter an URL or upload an image.
	<?php

}
function bbcrm_text_render($args ) { 
	?>
	<input type='text' name='bbcrm_settings[<?php echo $args['option-name'];?>]' value='<?php echo $this->options[$args['option-name']];?>'>
	<?php
}

function bbcrm_checkbox_render($args ) { 
	?>
	<input type='checkbox' name='bbcrm_settings[<?php echo $args['option-name'];?>]' value='1' <?php if(!empty($this->options[$args['option-name']])){checked($this->options[$args['option-name']],1,1);}?> />
	<?php
}

function bbcrm_selectpage_render( $args ) {
?>
	<?php wp_dropdown_pages( array('selected'=>$this->options[$args['option-name']],'name'=>"bbcrm_settings[".$args['option-name']."]",'echo'=>1) );?>
<?php
}

function bbcrm_select_assignedTo_render($args) {
	$useroptions='';
	$json = x2apicall((array('_class'=>'users')));
	$userar = json_decode($json);
	foreach($userar as $bbcrmuser){
		$useroptions .="<option value='".$bbcrmuser->username."' ".selected($this->options[$args['option-name']],$bbcrmuser->username,0).">".$bbcrmuser->firstName." ".$bbcrmuser->lastName."</option>";
	}
	echo "<select name='bbcrm_settings[".$args['option-name']."]'>";
	echo $useroptions;
	echo "</select>";
}

function bbcrm_select_docs_render($args) {
	$dropdownoptions='';
	$json = x2apicall((array('_class'=>'Docs')));
	$dropdownar = json_decode($json);
	foreach($dropdownar as $dropdown){
		$dropdownoptions .="<option value='".$dropdown->id."' ".selected($this->options[$args['option-name']],$dropdown->id,0).">".$dropdown->name."</option>";
	}
	echo "<select name='bbcrm_settings[".$args['option-name']."]'>";
	echo $dropdownoptions;
	echo "</select>";
}

function bbcrm_select_dropdowns_render($args) {
	$dropdownoptions='';
	$json = x2apicall((array('_class'=>'dropdowns')));
	$dropdownar = json_decode($json);
	foreach($dropdownar as $dropdown){
		$dropdownoptions .="<option value='".$dropdown->id."' ".selected($this->options[$args['option-name']],$dropdown->id,0).">".$dropdown->name."</option>";
	}
	echo "<select name='bbcrm_settings[".$args['option-name']."]'>";
	echo $dropdownoptions;
	echo "</select>";
}

function bbcrm_crm_fields_render($args) {
	$dropdownoptions='';
	$json = x2apicall((array('_class'=>$args["model"].'/fields')));
	$dropdownar = json_decode($json);
	foreach($dropdownar as $dropdown){
		if($dropdown->type != "assignment" && $dropdown->keyType != "PRI" && $dropdown->type !="link")
			$dropdownoptions .="<div><input type=checkbox name='bbcrm_settings[".$args['option-name']."][]' value='".$dropdown->id."' ".(in_array($dropdown->id,$this->options[$args['option-name']])?"checked":'').">".$dropdown->attributeLabel."</div>";
	}
	echo "<div style='width:400px;height:250px;overflow:auto'>";
	echo $dropdownoptions;
	echo "</div>";
}

function bbcrm_settings_section_callback($args ) { 

$description = array(
	'bbcrm_loginbar_section'=>'These fields typically appear in the customer login bar.<br>Your template can be customized to display these fields in additional places.',
	'bbcrm_pages_section'=>'These fields identify the Wordpress pages for each of the BBCRM template pages when referenced in the templates.',
	'bbcrm_design_section'=>'You can upload your logo and browser favicon using these fields.',
	'bbcrm_crm_section'=>'These fields identify which default values and dropdowns should be used in the page templates.',
	'bbcrm_buyerreg_section'=>'This is the complete list of fields that your Buyer record contains.<br>You can determine which fields will appear in your initial registration form by selecting the corresponding checkbox.',
	);

	echo $description[$args["id"]];
}

}

new options_page;
