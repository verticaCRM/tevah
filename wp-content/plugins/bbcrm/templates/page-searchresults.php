<?php
/*
Template Name: Search Results
*/
session_start();
//ini_set('display_errors',true);
//error_reporting(E_ALL);
//print_r($_POST);

foreach($_REQUEST as $k=>$v){
	if(''==$v){
		unset($_REQUEST[$k]);
	}
}

// Grab our type filters
if(isset($_REQUEST["c_listing_franchise_c"]) && !empty($_REQUEST["c_listing_franchise_c"])){
	$franch = 'c_listing_franchise_c='.$_REQUEST["c_listing_franchise_c"];
	$and_franch = '&'.$franch;
	$franch_semi = $franch.';';
}

if(isset($_REQUEST["c_listing_exclusive_c"]) && !empty($_REQUEST["c_listing_exclusive_c"])){
	$exclus = 'c_listing_exclusive_c='.$_REQUEST["c_listing_exclusive_c"];
	$and_exclus = '&'.$exclus;
	$exclus_semi = $exclus.';';
}

if(isset($_REQUEST["c_listing_homebusiness_c"]) && !empty($_REQUEST["c_listing_homebusiness_c"])){
	$home = 'c_listing_homebusiness_c='.$_REQUEST["c_listing_homebusiness_c"];
	$and_home = '&'.$home;
	$home_semi = $home.';';
}

if(isset($_REQUEST['c_name_generic_c'])&& is_numeric($_REQUEST["c_name_generic_c"]) || isset($_REQUEST["id"]) && !empty($_REQUEST["id"])){

	$qy = $_REQUEST["id"];
	$_SESSION["listingid"]=$qy;

if(is_numeric($_REQUEST["c_name_generic_c"])){
$qy = $_REQUEST["c_name_generic_c"];
}
$json = x2apicall(array('_class'=>'Clistings?_partial=1&_escape=0'.$and_franch.$and_exclus.$and_home.'&c_sales_stage=Active&id='.$qy));
$idresults = json_decode($json);

/* Plugin function to propagate the frontend URL into the CRM if blank, then return */
$idresults[0]->c_listing_frontend_url = crm_add_frontend_url($idresults);

if(empty($idresults[0]->c_listing_frontend_url)){
header("Location:/listing/");
exit;
}else{
header("Location:".$idresults[0]->c_listing_frontend_url,false,307);
exit;
}



}

$qy = $_REQUEST["c_name_generic_c"];
if(isset($_REQUEST["find"]) && !empty($_REQUEST["find"])){
	$qy = $_REQUEST["find"];
}
if(isset($_REQUEST["c_listing_region_c"]) && !empty($_REQUEST["c_listing_region_c"])){
	$qy = $_REQUEST["c_listing_region_c"];
}
if(isset($_REQUEST["c_listing_town_c"]) && !empty($_REQUEST["c_listing_town_c"])){
	$qy = $_REQUEST["c_listing_town_c"];
}
if(isset($_REQUEST["broker"]) && !empty($_REQUEST["broker"])){
	$qy = urlencode($_REQUEST["broker"]);
}
if(isset($_REQUEST["c_businesscategories"]) && !empty($_REQUEST["c_businesscategories"])){
	$qy = $_REQUEST["c_businesscategories"];
}

//echo $qy;
if(!empty($qy)){
$busca3results = null;
$buscat4results = null;
if(isset($_REQUEST["find"]) || isset($_REQUEST["c_businesscategories"])){
	$json = x2apicall(array('_class'=>'Clistings?_partial=1&_escape=0'.$and_franch.$and_exclus.$and_home.'&c_businesscategories=%25'.urlencode($qy).'%25'));
	$buscat3result = json_decode($json);
	foreach($buscat3result AS $idx=>$res){
		$buscat3results[] = $res;
	}

	$json = x2apicall(array('_class'=>'Clistings?_partial=1&_escape=0'.$and_franch.$and_exclus.$and_home.'&c_listing_businesscat_c=%25'.$qy.'%25'));
	$buscat4result = json_decode($json);
	foreach($buscat4result AS $idx=>$res){
		//$buscat4results[] = $res;
	}
}

$json = x2apicall(array('_class'=>'Clistings?_partial=1&_escape=0'.$and_franch.$and_exclus.$and_home.'&c_sales_stage=Active&c_name_generic_c=%25'.urlencode($qy).'%25'));
$genresults = json_decode($json);

//echo $json;

$json = x2apicall(array('_class'=>'Clistings?_partial=1&_escape=0'.$and_franch.$and_exclus.$and_home.'&c_sales_stage=Active&description=%25'.urlencode($qy).'%25'));
$descresults = json_decode($json);

//echo "1".$json;

$json = x2apicall(array('_class'=>'Clistings?_partial=1&_escape=0'.$and_franch.$and_exclus.$and_home.'&c_sales_stage=Active&c_listing_region_c=%25'.urlencode($qy).'%25'));
$regionresults = json_decode($json);

//echo "2".$json;

$json = x2apicall(array('_class'=>'Clistings?_partial=1&_escape=0'.$and_franch.$and_exclus.$and_home.'&c_sales_stage=Active&c_listing_town_c=%25'.urlencode($qy).'%25'));
$countyresults = json_decode($json);

//echo "3".$json;

$json = x2apicall(array('_class'=>'Clistings?_partial=1&_escape=0'.$and_franch.$and_exclus.$and_home.'&c_sales_stage=Active&c_assigned_user_id=%25'.$qy.'%25'));
$brokerresults = json_decode($json);

//echo "4".$json;

$json = x2apicall(array('_class'=>'Clistings?_partial=1&_escape=0'.$and_franch.$and_exclus.$and_home.'&c_sales_stage=Active&c_name_dba_c=%25'.urlencode($qy).'%25'));
$dbaresults = json_decode($json);

//echo $json;

$params=http_build_query(array("_tags"=>$qy,"_tagOr"=>"1"));
$json = x2apicall(array('_class'=>'Clistings?_partial=1&_escape=0'.$and_franch.$and_exclus.$and_home.'&c_sales_stage=Active&_limit=20&'.$params));
$tagresults = json_decode($json);

}

if(isset($_REQUEST["c_financial_revenue_c"])){
	$params = explode("|",$_REQUEST["c_financial_revenue_c"]);
	$json = x2apicall(array('_class'=>'Clistings/by:c_sales_stage=Active;'.$franch_semi.$exclus_semi.$home_semi.'c_financial_revenue_c='.urlencode('>=').$params[0].';c_financial_revenue_c='.urlencode('<=').$params[1].'.json'));
	$finrevresults = array();
	$finrevresult = json_decode($json);
	if($finrevresult->directUris){
		foreach($finrevresult->directUris as $idx=>$uri){
				$json = x2apicall(array("_url"=>$uri));
				$finrevresults[] = json_decode($json);
		}
	}
}

if(isset($_REQUEST["c_ownerscashflow"])){
	$params = explode("|",$_REQUEST["c_ownerscashflow"]);
	$json = x2apicall(array('_class'=>'Clistings/by:c_sales_stage=Active;'.$franch_semi.$exclus_semi.$home_semi.'c_ownerscashflow='.urlencode('>=').$params[0].';c_ownerscashflow='.urlencode('<=').$params[1].'.json'));
	$cashflowresults = array();
	$cashflowresult = json_decode($json);
	if($cashflowresult->directUris){
		foreach($cashflowresult->directUris as $idx=>$uri){
				$json = x2apicall(array("_url"=>$uri));
				$cashflowresults[] = json_decode($json);
		}
	}
}

if(isset($_REQUEST["c_listing_askingprice_c"])){
	$params = explode("|",$_REQUEST["c_listing_askingprice_c"]);
	$json = x2apicall(array('_class'=>'Clistings/by:c_sales_stage=Active;'.$franch_semi.$exclus_semi.$home_semi.'c_listing_askingprice_c>='.$params[0].';c_listing_askingprice_c<'.$params[1].'.json'));
	$askingpriceresults = json_decode($json);
}

if(isset($_REQUEST["c_listing_downpayment_c"])){
	$params = explode("|",$_REQUEST["c_listing_downpayment_c"]);
	$json = x2apicall(array('_class'=>'Clistings/by:c_sales_stage=Active;'.$franch_semi.$exclus_semi.$home_semi.'c_listing_downpayment_c>='.$params[0].';c_listing_downpayment_c<'.$params[1].'.json'));
	$downpaymentresults = json_decode($json);
}

if(isset($_REQUEST["c_businesscategories"]) && !empty($_REQUEST["c_businesscategories"])){
$buscatresults = null;
$buscat2results = null;
	foreach ($_REQUEST["c_businesscategories"] AS $idx=>$cat){
		$json = x2apicall(array('_class'=>'Clistings?_partial=1&_escape=0'.$and_franch.$and_exclus.$and_home.'&c_sales_stage=Active&c_listing_businesscat_c=%25'.urlencode($cat).'%25'));
		$buscatresult = json_decode($json);
		foreach($buscatresult AS $idx=>$res){
			$buscatresults[] = $res;
		}

		$json = x2apicall(array('_class'=>'Clistings?_partial=1&_escape=0'.$and_franch.$and_exclus.$and_home.'&c_sales_stage=Active&c_businesscategories=%25'.urlencode($cat).'%25'));
		$buscat2result = json_decode($json);
		foreach($buscat2result AS $idx=>$res){
			$buscat2results[] = $res;
		}
	}
}

$results = (object) array_merge((array) $idresults,(array) $genresults, (array) $tagresults, (array) $descresults, (array) $regionresults, (array) $countyresults, (array) $dbaresults, (array) $brokerresults, (array) $buscatresults, (array) $buscat2results,(array) $buscat3results,(array) $buscat4results,(array) $downpaymentresults,(array) $askingpriceresults,(array) $cashflowresults, (array) $finrevresults);

//print_r($results);



//get_template_part('template','top');
get_header();

?>
<section style="margin-top: 72px !important;"  id="content" data="property" class="container"> 
<div class="portfolio_group">
        <div style="margin-right: 0 !important;"  class="search_result">
          <div class="row">
          <div class="col-md-12  col-sm-12 ">
      </div>
      
	<div id="business_container" class="col-md-9">
		
<?

if(count((array)$results) > 0 && $results->status != "404"){
	$listingids = array();

	foreach ($results as $searchlisting){

	if(!in_array($searchlisting->id,$listingids)){
		$listingids[]= $searchlisting->id;

		if(!empty($searchlisting->c_businesscategories)){
		$categories = substr($searchlisting->c_businesscategories,1,-1);
		$categories = explode(',',str_replace('"', '', $categories));
		$cats = '';
		foreach($categories as $cat){
			$cats .='<a href="?find='.urlencode(stripslashes($cat)).'">'.stripslashes($cat).'</a> ';
		}
		}

		$c_listing_frontend_url = crm_add_frontend_url($searchlisting);
		$html .= "<div style='display:block' class='searchresult'><p><a class='theme-color2-dk' style='font-size:1.3em' href=\"/listing/". sanitize_title($searchlisting->c_name_generic_c) ."\" class=\"listing_link\" data-id=\"". $searchlisting->id ."\">".$searchlisting->c_name_generic_c."</a></p>";
		$html .= "<div class='theme-color1-dk' style='display:inline-block; width:20%; font-size:1.3em;vertical-align:top;'>".__("","bbcrm").$searchlisting->c_listing_region_c;
	    
$html .= "<p style='font-size:1.3em;color:#4672b2;'>".__("",'bbcrm').$searchlisting->c_currency_id.number_format($searchlisting->c_listing_askingprice_c).'</p>';
	    $html .= "<p style='font-size:1.0em;color:#666666;'>".__("Cash Flow: ",'bbcrm').$searchlisting->c_currency_id.number_format($searchlisting->c_ownerscashflow)."</p>";
	$html .="</div>";

		$html .= "<div style='display:inline-block;color:#807e7e;width:75%;margin-bottom:10px; margin:0 10px; display:inline-block; height:140px;'>";

$json = x2apicall(array('_class'=>'Media/by:description=thumbnail;associationId='.$searchlisting->id.'.json'));
$thumbnail = json_decode($json);
//print_r($thumbnail);
if(!$thumbnail->fileName){
                        $html .= '<a href="/listing/'.sanitize_title($searchlisting->c_name_generic_c).'" class="listing_link" data-id="'.$searchlisting->id.'"><img src="'.plugin_dir_url(__DIR__).'images/noimage.png" align=right></a>';
                }else{
                        $html .= '<a href="/listing/'.sanitize_title($searchlisting->c_name_generic_c).'" class="listing_link" data-id="'.$searchlisting->id.'"><img src="'.get_bloginfo('url').'/crm/uploads/media/'.$thumbnail->uploadedBy.'/'.$thumbnail->fileName.'" style="width:230px;max-height:200px;overflow:hidden;border:2px solid #fff" align=right  /></a>';

                } 
		$html .='<div style="width:100%">'.$searchlisting->description.'</div>';
////
$html .=<<<HTML
<div style="position:absolute;z-index:200;bottom:0; width:75%;height:240px;background: -moz-linear-gradient(top,rgba(255,255,255,0) 0%, rgba(255,255,255,0) 80%, rgba(255,255,255,1) 100%); /* FF3.6-15 */
background: -webkit-linear-gradient(top,rgba(255,255,255,0) 0%,rgba(255,255,255,0) 80%,rgba(255,255,255,1) 100%); /* Chrome10-25,Safari5.1-6 */
background: linear-gradient(to bottom,rgba(255,255,255,0) 0%,rgba(255,255,255,0) 80%,rgba(255,255,255,1) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#00ffffff', endColorstr='#ffffff ',GradientType=0 );">
             </div>
HTML;
////
$html .= "</div>";
if(!is_user_logged_in()){	
}	
		
if(is_user_logged_in() ){
		$html .= '<form action="/listing/'.sanitize_title($searchlisting->c_name_generic_c).'" method=post><input type=hidden name="action" value="add_to_portfolio" /><input type=hidden name="id" value="'. $searchlisting->id.'" /><input type=submit style="display:none; margin-bottom:18px;" value="'. __('Add to my portfolio','bbcrm').' &#10010;" class="portfolio_action_button portfolio-add"  /></form>';
		}
		$html .= "</div>";
	}
	}
}else{
	$qy = (empty($qy))?"your search":'"'.$qy.'"';
	$html .= "<h2>No results were found for ".$qy."</h2>";
	$html .= "<div class='col-md-12' style='padding-top: 1em; vertical-align: top; text-align: left;'>";
	$html .= "<p>Please try a search with different parameters.</p>";
	$html .= do_shortcode('[featuredsearch]');
	$html .= "</div>";
}

	

//print_r($listingids);

if(!empty($listingids)){
echo '<h2>'.get_the_title().'</h2>';
echo __("Your search for ",'bbcrm');
if(is_array($qy)){
	echo join(",",$qy);
}else {
	echo $qy;
}
_e(" returned ",'bbcrm');
echo count((array)$listingids);
echo (count((array)$listingids)===1)?__(' result.','bbcrm'):__(' results.','bbcrm');
}

echo $html;

//get_template_part("home","search");
?>

	</div>
	
	<div class="col-md-3 sidebar">
       <?php dynamic_sidebar( "content-sidebar" ); ?>
    </div> 
       
       
	</div>
	
	  
       
  </div>
       
 </div>      
</section>

<?php get_footer(); ?>
