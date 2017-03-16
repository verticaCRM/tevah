<?php global $bbcrm_option;?>
<h3 class="theme-color2-lt theme-background2-dk" style="margin:0;margin-top:10px;width:40%;font-size:1.3em;padding-bottom:0;"><span class="fa fa-search theme-color2-dk theme-background2-lt" style="padding:7px 8px;margin-right:5px;"></span><?php _e($a['title'],'bbcrm');?></h3>
<div class="theme-background1-lt theme-border1-dk searchbox" style="padding:18px; margin-bottom: 18px;" id="pagewidget">

<form method="get" action="<?php echo get_permalink($bbcrm_option["bbcrm_loginbar_searchresults"]);?>" >
<div style="display:inline-block;vertical-align:top;margin-right:12px;"><label class="theme-color2-lt" for="c_listing_region_c"><?php _e("By State",'bbcrm')?></label><br><select style="background-color:#ffffff;" name="c_listing_town_c" id="c_listing_town_c"><option value=''>Please select</option>
<?php
global $bbcrm_option;
$json = x2apicall(array('_class'=>'dropdowns/'.$bbcrm_option["bbcrm_crm_states"].'.json'));
$regions = json_decode($json);
foreach ($regions->options as $k=>$v){
		echo "<option value='$v'>$k</option>";	
	}
?>
</select></div>
<?php 
	switch ($a['type']) {	// I'm echoing the full tag each time so that if we decide to make a particularly complex type, it can go in a single case. -dtalor
		case 'franch':
			echo '<input type="hidden" name="c_listing_franchise_c" value="Yes"/>';
			break;
		case 'no_franch':
			echo '<input type="hidden" name="c_listing_franchise_c" value="No"/>';
			break;
		case 'exclus':
			echo '<input type="hidden" name="c_listing_exclusive_c" value="Yes"/>';
			break;
		case 'no_exclus':
			echo '<input type="hidden" name="c_listing_exclusive_c" value="No"/>';
			break;
		case 'home':
			echo '<input type="hidden" name="c_listing_homebusiness_c" value="Yes"/>';
			break;
		case 'no_home':
			echo '<input type="hidden" name="c_listing_homebusiness_c" value="No"/>';
			break;
		default:
			break;
	}
?>
<div style="display:inline-block;vertical-align:top;margin-right:12px;"><label class="theme-color2-lt" for="c_listing_city_c"><?php _e("By County",'bbcrm')?></label><br><select style="background-color:#ffffff;" name="c_listing_city_c" id="c_listing_city_c" style="min-width:200px;"><option value=''><? _e('Please select','bbcrm');?></option></select></div>

<div style="display:inline-block;vertical-align:top;margin-right:12px;"><label class="theme-color2-lt" for="businesscategories"><?php _e('By Category','bbcrm');?></label><br><select style="background-color:#ffffff;" name="c_businesscategories" id="businesscategories"><option value=''><? _e('Choose one','bbcrm');?></option>
<?php
$json = x2apicall(array('_class'=>'dropdowns/'.$bbcrm_option["bbcrm_crm_buscats"].'.json'));
$buscats = json_decode($json);
//print_r($regions);

foreach ($buscats->options as $k=>$v){
	echo "<option value='$v'>$k</option>";
}
?>
</select>
</div>

<?php
/*
<div style="display:inline-block;vertical-align:top;margin-right:12px;">
<label class="theme-color" for="popular_searches">Popular Searches:</label>


$popular_searches = get_option('popular_searches',"nuthin");
//var_dump($popular_searches);
//echo "!!!";
foreach ($popular_searches as $search){
		echo "<a href='/search/?find=".$search."' >$search</a><br>";	
	}

</div>
*/
?>
<div style="padding:10px 0">
<a href="#" class="advancedsearch theme-color2-lt">[Advanced Search]</a>
</div>
<div id="advanced" style="display:none;">
<div style="display:inline-block;vertical-align:top;margin-right:12px;">
<label class="theme-color2-lt" style="width:140px;" for="c_ownerscashflow"><?php _e("Owner's Cash Flow:",'bbcrm')?></label><br><select style="background-color:#ffffff;" name="c_ownerscashflow" id="c_ownerscashflow">
<option value=""><?php _E("Choose one",'bbcrm');?></option>
<option value="5000|100001">0 - 100,000</option>
<option value="100000|300001">100,000 - 300,000</option>
<option value="300000|500001">300,000 - 500,000</option>
<option value="500000|750001">500,000 - 750,000</option>
<option value="750000|1000001">750,000 - 1,000,000</option>
<option value="1000000|1000000001">1,000,000 <?php _e('or more','bbcrm');?></option>
</select>
</div>
<div style="display:inline-block;vertical-align:top;margin-right:12px;">
<label class="theme-color2-lt" style="width:140px;" for="askprice"><?php _e("Asking Price:",'bbcrm')?></label><br><select style="background-color:#ffffff;" name="c_listing_askingprice_c" id="askprice">
<option value=""><?php _E("Choose one",'bbcrm');?></option>
<option value="5000|100001">0 - 100,000</option>
<option value="100000|300001">100,000 - 300,000</option>
<option value="300000|500001">300,000 - 500,000</option>
<option value="500000|750001">500,000 - 750,000</option>
<option value="750000|1000001">750,000 - 1,000,000</option>
<option value="1000000|1000000001">1,000,000 <?php _e('or more','bbcrm');?></option>
</select>
</div>
<div style="display:inline-block;vertical-align:top;margin-right:12px;"><label class="theme-color2-lt" for="generic"><?php _e('Keyword or ID:','bbcrm');?></label><br><input name="c_name_generic_c" class="" type="search" id="generic"></div>
<div style="display:inline-block;vertical-align:top;margin-right:12px;">
<label class="theme-color2-lt" style="width:140px;" for="downpayment"><?php _e("Deposit:",'bbcrm')?></label><br><select style="background-color:#ffffff;" name="c_listing_downpayment_c" id="downpayment	">
<option value=""><?php _E("Choose one",'bbcrm');?></option>
<option value="5000|100001">0 - 100,000</option>
<option value="100000|300001">100,000 - 300,000</option>
<option value="300000|500001">300,000 - 500,000</option>
<option value="500000|750001">500,000 - 750,000</option>
<option value="750000|1000001">750,000 - 1,000,000</option>
<option value="1000000|1000000001">1,000,000 <?php _e('or more','bbcrm');?></option>
</select>
</div>

<?php
//Get the brokers in the system
$json = x2apicall(array('_class'=>'Brokers/'));
$brokers =json_decode($json);

if($brokers){
	$brokerselect = array();
foreach ($brokers AS $broker){
$brokerselect[] = 	'"'.$broker->name.'":"'.$broker->nameId.'"';
	}
}
?>
<script>
	jQuery(document).ready(function(){ 
	jQuery(".advancedsearch").click(function(event){
	event.preventDefault()
	 jQuery("#advanced").show()
	})
		var newBrokers = {<?php echo join(',',$brokerselect);?>};
		var el = jQuery("#broker");
		el.append(jQuery("<option></option>").attr("value", "").text("<?php echo __('Choose A Broker','bbcrm');?>"));			
			jQuery.each(newBrokers, function(key, value) {
				el.append(jQuery("<option></option>").attr("value", value).text(key));
			});
			
jQuery("#c_listing_town_c").change(function(){
	jQuery.getJSON('<?php echo get_template_directory_uri().'/_auth.php'; ?>',
	{query:	'AJAX',action: 'x2apicall',_class:"dropdowns/"}, 
	function(response){
		console.log(response)
			jQuery.each(response, function(key, value) {
				if(value.parentVal==jQuery("#c_listing_town_c").val()){
				jQuery("#c_listing_city_c").empty().append(jQuery("<option></option>").attr("value", "").text("Please select a county"));
				jQuery.each(value.options, function(nam, val) {
//console.log(value.options);					
					jQuery("#c_listing_city_c").append(jQuery("<option></option>").attr("value", val).text(val));
					})	
				}
			});
	});
})		


jQuery("#broker").change(function(){
	jQuery.getJSON('<?php echo get_template_directory_uri().'/_auth.php'; ?>',
	{query:	'AJAX',action: 'x2apicall',_class:"Brokers/by:nameId="+encodeURI(jQuery(this).val())+".json"}, 
	function(response){
//		console.log(response)
		jQuery("#assignedTo").remove();
		jQuery("#form_buyerreg").append("<input type='hidden' id='assignedTo' name='assignedTo' value='"+response.assignedTo+"' />");
	});
})		
	})
</script>
</div>

<div id="sebu"><input type=submit value="<? _e('Search','bbcrm');?>" class="theme-background2-dk" /></div>
</form>
</div>
<div style="clear:both;height:10px;"></div>
