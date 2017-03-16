<?php
global $bbcrm_option;
	if ($a['sold'])

	{
		$sold_bussiness = true;
	}
	else
	{
		$sold_bussiness = false;
	}
?>
<h3 class="theme-background" style="display:none; color:#ccc;margin:0;margin-top:10px;width:40%"><?php _e($a['title'],'bbcrm');?></h3>

<div id="pagewidget-<?php echo str_replace(' ','_',$a['title']);?>" class="searchbox theme-color-border">

<form method="get" action="/search/" id="searchform" class="sidebar_search_form">

<div class="form_group">
	<label class="theme-color" for="generic"><?php _e('Keyword/s:','bbcrm');?></label><br>
	<input name="c_keyword_c" class="" type="search" id="generic">
</div>

<?php
	$business_categories = 'c_businesscategories=["'.trim($v).'"]';

//Grab only listings with 'Active' and 'Needs Refresh' Sale Stages
	$sales_stage = 'Active__Needs Refresh';
	$json_for_clistings = x2apicall(array('_class'=>'Clistings?'.'c_sales_stage=:multiple:__'.$sales_stage));
	$decoded_clistings = json_decode($json_for_clistings);
	// echo '<pre>'; print_r($decoded_clistings); echo '</pre>';
?>
<div class="sidebar_categories_base_container form_group">
	<div class="sidebar_categories_button"><?php _e('Categories','bbcrm');?>
		<span class="selected_number_of_categories">(<span class="selected_number">0</span> selected)</span>
		<i class="fa fa-chevron-up" aria-hidden="true"></i>
	</div>
	<div class="sidebar_categories_container">
<?php 
	
	$json = x2apicall(array('_class'=>'dropdowns/'.$bbcrm_option["bbcrm_crm_buscats"].'.json'));
	$buscats = json_decode($json);
	$json = x2apicall(array('_class'=>'dropdowns/'.$bbcrm_option["bbcrm_crm_buscats_parents"].'.json'));
	$buscats_subctg = json_decode($json);
?>

<?php
$i = 0;
foreach ($buscats->options as $k=>$v)
{
	$parent_flag= 0;
	foreach ($buscats_subctg->options as $kk=>$vv)
	{
		if( strtolower($v) == strtolower($vv) )
		{
			$parent_flag = 1;
		}
	}

	if($parent_flag)
	{
		if($i != 0)
		{
			// Close Child categories parent container
			echo "</div>";				
		}
		echo "<div class='parent_category_title'>". stripslashes($k) ." (<span class='parent_cat_listings'>0</span>)</div>";
		echo "<div class='child_category_container'>";
	}
	else
	{
		$i_listings = 0;
		foreach($decoded_clistings as $dec_k=>$dec_v)
		{
			if( strpos( $dec_v->c_businesscategories, '"'.$v.'"' ) !== false )
			{
				if ($sold_bussiness)
				{
					if( $dec_v->c_sales_stage == 'Sold' )
					{
						$i_listings++;
					}
				}
				else
				{
					$i_listings++;
				}
				
			}
		}
		echo "<label> <input type='checkbox' name='c_businesscategories[]' class='search_checkbox' value='$v'/> $k (<span class='child_cat_listings'>".$i_listings."</span>)</label>";
	}	


	$i++;
}

echo '</div>';

?>

	</div>
</div>
<div><input type="hidden" name="sold_bussiness" value="<?php echo $sold_bussiness;?>"/></div>
<div class="form_group">
	<label class="theme-color" for="downpayment"><?php _e("Minimum Investment:",'bbcrm')?></label><br>
	<select name="c_minimum_investment_c" id="minimum_investment">
		<option value=""></option>
		<option value="25000">$25,000</option>
		<option value="50000">$50,000</option>
		<option value="75000">$75,000</option>
		<option value="100000">$100,000</option>
		<option value="150000">$150,000</option>
		<option value="200000">$200,000</option>
		<option value="250000">$250,000</option>
		<option value="300000">$300,000</option>
		<option value="350000">$350,000</option>
		<option value="400000">$400,000</option>
		<option value="450000">$450,000</option>
		<option value="500000">$500,000</option>
		<option value="600000">$600,000</option>
		<option value="700000">$700,000</option>
		<option value="800000">$800,000</option>
		<option value="900000">$900,000</option>
		<option value="1000000">$1,000,000</option>
		<option value="1250000">$1,250,000</option>
		<option value="1500000">$1,500,000</option>
		<option value="1750000">$1,750,000</option>
		<option value="2000000">$2,000,000</option>
		<option value="2500000">$2,500,000</option>
		<option value="3000000">$3,000,000</option>
		<option value="4000000">$4,000,000</option>
		<option value="5000000">$5,000,000</option>
	</select>
</div>
<div class="form_group">
	<label class="theme-color" for="downpayment"><?php _e("Maximum Investment:",'bbcrm')?></label><br>
	<select name="c_maximum_investment_c" id="maximum_investment">
		<option value=""></option>
		<option value="25000">$25,000</option>
		<option value="50000">$50,000</option>
		<option value="75000">$75,000</option>
		<option value="100000">$100,000</option>
		<option value="150000">$150,000</option>
		<option value="200000">$200,000</option>
		<option value="250000">$250,000</option>
		<option value="300000">$300,000</option>
		<option value="350000">$350,000</option>
		<option value="400000">$400,000</option>
		<option value="450000">$450,000</option>
		<option value="500000">$500,000</option>
		<option value="600000">$600,000</option>
		<option value="700000">$700,000</option>
		<option value="800000">$800,000</option>
		<option value="900000">$900,000</option>
		<option value="1000000">$1,000,000</option>
		<option value="1250000">$1,250,000</option>
		<option value="1500000">$1,500,000</option>
		<option value="1750000">$1,750,000</option>
		<option value="2000000">$2,000,000</option>
		<option value="2500000">$2,500,000</option>
		<option value="3000000">$3,000,000</option>
		<option value="4000000">$4,000,000</option>
		<option value="5000000">$5,000,000</option>
		<option value="99999999999999999999">$5mil plus</option>
	</select>
</div>
<div id="dd_state" class="dd_bystate form_group">
	<label id="regionlabel" class="theme-color" for="c_listing_region_c"><?php _e("State",'bbcrm')?></label><br />
	<select name="c_listing_region_c" id="listing_region" class="fs_select"><option value=''></option>
	<?php
	$json = x2apicall(array('_class'=>'dropdowns/'.$bbcrm_option["bbcrm_crm_states"].'.json'));
	$regions = json_decode($json);
	foreach ($regions->options as $k=>$v){
			echo "<option value='$v'>$k</option>";	
		}
	?>
	</select>
</div>
<div class="form_group">
	<label class="theme-color" for="downpayment"><?php _e("Adjusted Net Profit:",'bbcrm')?></label><br>
	<select name="c_adjusted_net_profit_c" id="adjusted_net_profit">
		<option value=""></option>
		<option value="0|25000">up to $25,000</option>
		<option value="25000|50000">$25,000 to $50,000</option>
		<option value="50000|100000">$50,000 to $100,000</option>
		<option value="100000|150000">$100,000 to $150,000</option>
		<option value="150000|300000">$150,000 to $300,000</option>
		<option value="300000|99999999999999999999">$300,000 plus</option>
	</select>
</div>

<div class="form_group">
	<label class="theme-color" for="broker"><?php _e("Broker/s:",'bbcrm')?></label><br><select size="4" name="c_Broker[]" multiple="multiple" id="broker" class="broker-select"></select>
</div>
<?php

//Get the brokers in the system
$json = x2apicall(array('_class'=>'Brokers/'));
$brokers =json_decode($json);
// echo '<pre>'; print_r($brokers); echo '</pre>';

if($brokers){
	$brokerselect = array();
	foreach ($brokers as $broker){
		$brokerselect[] = '"'.$broker->name.'":"'.$broker->nameId.'"';
	}
}
?>
<script>
brokerJSON = {<?php echo join(",",$brokerselect);?>};
chooseABrokerTxt = "Anyone";
authURI = "<?php echo plugin_dir_url(__FILE__).'../_auth.php'; ?>";
pleaseWaitTxt = "<?php _e("Please wait a moment...",'bbcrm');?>";
selectCountyTxt = "<?php _e("Please select a city",'bbcrm');?>";
</script>

<div class="form_group">
	<div class="checkbox_container">
		<input type="checkbox" name="c_franchise_c" value="Yes" class="franchise_checkbox"><label class="theme-color" for="c_franchise_c"><?php _e("Check for Franchise",'bbcrm')?></label>
	</div>
</div>

	<div id="sebu" class="form_group">
		<button onclick="javascript: clear_business_shearch_by_id_input();" type="submit" class="btn btn-default find_business_search_button">
			<span class="glyphicon glyphicon-search"></span><?php _e('Find Business','bbcrm');?>
		</button>
		<button onclick="javascript: clear_business_all_search_fields();" type="button" class="btn btn-primary pull-right">
			<span class="glyphicon glyphicon-refresh"></span>
		</button>
	</div>
</form>
