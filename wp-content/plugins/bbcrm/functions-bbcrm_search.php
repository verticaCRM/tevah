<?php

// Define function for applying filters
function filter_listings_obj($obj) {
	global $_REQUEST, $askingprice_params, $ownerscashflow_params, $listing_downpayment_params, $keyword, $minimum_investment, $maximum_investment, $adjusted_net_profit, $brokers, $sold_selection,$real_est_categories, $listing_regions, $minimum_rent, $maximum_rent,$businesscategories, $franchise;
	
	foreach($obj as $k=>$v){
		//we need to show only Sold Business 
		if ($sold_selection){
			if( $v->c_sales_stage != 'Sold' ){
				$obj[$k] = false;
			}
		}else{
			//we need to show Business that are not Sold
			if( $v->c_sales_stage != 'Active' && $v->c_sales_stage != 'Needs Refresh' ){
				$obj[$k] = false;
			}
		}
		if(isset($_REQUEST["c_listing_askingprice_c"]) && !empty($_REQUEST["c_listing_askingprice_c"])){
			if( !($v->c_listing_askingprice_c >= $askingprice_params[0]) || !($v->c_listing_askingprice_c < $askingprice_params[1]) ){
				$obj[$k] = false;
			}
		}
		if(isset($_REQUEST["c_ownerscashflow"]) && !empty($_REQUEST["c_ownerscashflow"])){
			if( !($v->c_ownerscashflow >= $ownerscashflow_params[0]) || !($v->c_ownerscashflow < $ownerscashflow_params[1]) ){
				$obj[$k] = false;
			}
		}
		if(isset($_REQUEST["c_listing_downpayment_c"]) && !empty($_REQUEST["c_listing_downpayment_c"])){
			if( !($v->c_listing_downpayment_c >= $listing_downpayment_params[0]) || !($v->c_listing_downpayment_c < $listing_downpayment_params[1]) ){
				$obj[$k] = false;
			}
		}
		if(isset($_REQUEST["c_keyword_c"]) && !empty($_REQUEST["c_keyword_c"])){
			if( (string) strpos( strtolower($v->c_name_generic_c) , strtolower($keyword) ) == '' ){
				$obj[$k] = false;
			}
		}
		if(isset($_REQUEST["c_minimum_investment_c"]) && !empty($_REQUEST["c_minimum_investment_c"])){
			if( $v->c_listing_askingprice_c < $minimum_investment )
			{
				$obj[$k] = false;
			}
		}
		if(isset($_REQUEST["c_maximum_investment_c"]) && !empty($_REQUEST["c_maximum_investment_c"])){
			if( $v->c_listing_askingprice_c > $maximum_investment )
			{
				$obj[$k] = false;
			}
		}
		if(isset($_REQUEST["c_minimum_rent_c"]) && !empty($_REQUEST["c_minimum_rent_c"])){
			if( $v->c_real_estate_rent < $minimum_rent )
			{
				$obj[$k] = false;
			}
		}
		if(isset($_REQUEST["c_maximum_rent_c"]) && !empty($_REQUEST["c_maximum_rent_c"])){
			if( $v->c_real_estate_rent > $maximum_rent )
			{
				$obj[$k] = false;
			}
		}	
		if(isset($_REQUEST["c_adjusted_net_profit_c"]) && !empty($_REQUEST["c_adjusted_net_profit_c"])){
			if( !($v->c_financial_net_profit_c >= $adjusted_net_profit[0]) || !($v->c_financial_net_profit_c < $adjusted_net_profit[1]) )
			{
				$obj[$k] = false;
			}
		}
		if( isset($_REQUEST["c_franchise_c"]) && !empty($_REQUEST["c_franchise_c"])){
			if( trim($v->c_listing_franchise_c) !=  trim($franchise) )
			{
				$obj[$k] = false;
			}
		}
		if(isset($_REQUEST["c_Broker"]) && !empty($_REQUEST["c_Broker"])){

			$broker_flag = 0;
			foreach($brokers as $broker){
				if( $broker == $v->assignedTo ){
					$broker_flag = 1;
				}
			}

			if( !$broker_flag ){
				$obj[$k] = false;
			}
		}	
		// If we have Categories
		if(isset($_REQUEST["c_businesscategories"]) && !empty($_REQUEST["c_businesscategories"])){

			$businesscategories_flag = 0;
			foreach($businesscategories as $businesscategory)
			{
				$businesscategory = '"'.$businesscategory.'"';
				if( strpos( $v->c_businesscategories, $businesscategory ) !== false )
				{
					$businesscategories_flag = 1;
				}
			}

			if( !$businesscategories_flag )
			{
				$obj[$k] = false;
			}
		}
		// If we have Region selected
		if(isset($_REQUEST["c_listing_region_c"]) && !empty($_REQUEST["c_listing_region_c"])){

			$listing_region_flag = 0;
			foreach($listing_regions as $listing_region)
			{
				if( trim($listing_region) == trim($v->c_listing_region_c) )
				{				
					$listing_region_flag = 1;
				}
			}

			if( !$listing_region_flag )
			{
				$obj[$k] = false;
			}
		}

		// If we have Categories
		if(isset($_REQUEST["c_real_estate_categories"]) && !empty($_REQUEST["c_real_estate_categories"])){
			
			$db_real_est_categories = explode( ',', rtrim( ltrim( $v->c_real_estate_categories, '[' ), ']' ) );

			$real_est_cat_flag = 0;
			foreach($real_est_categories as $real_est_category){
				$real_est_category = strtolower(str_replace( array(' ', '/'), array('_', '_'), $real_est_category ));

				foreach($db_real_est_categories as $db_real_est_category){
					$db_real_est_category = rtrim( ltrim( strtolower( str_replace( array(' ', '/'), array('_', '_'), stripslashes( $db_real_est_category ) ) ), '"' ), '"' );

					if( $real_est_category == $db_real_est_category ){					
						$real_est_cat_flag = 1;
					}
				}
			}

			if( !$real_est_cat_flag ){
				$obj[$k] = false;
			}
		}
	}
	return $obj;
}



?>
