<?php
/*
BBCRM API

*/
//define('WP_USE_THEMES', false);
//ini_set('display_errors','on');
//error_reporting(E_ALL);
//print_r(getallheaders());
//die;

add_action( 'wp_ajax_contact_to_crm', 'contact_to_crm' );
add_action( 'wp_ajax_nopriv_contact_to_crm', 'contact_to_crm' );

wp_enqueue_script('jquery'); // I assume you registered it somewhere else
wp_localize_script('jquery', 'ajax_custom', array(
   'ajaxurl' => admin_url('admin-ajax.php')
));

function contact_to_crm(){
	parse_str($_REQUEST["data"],$params);
	$model = ($_REQUEST["model"])?$_REQUEST["model"]:"Contacts";

	if(isset($params["_wpnonce"])){
		$res = x2apipost(array("_class"=>$model."/","_data"=>$params));
		exit;
	}
}

function bbcrm_add_bbcrm_rewrite() {
	global $wp_rewrite,$wp_query;	
//This is to rewrite rule for creating a multi-model email. It requires a model & id and an email template Doc & id. This will be supplied in the Remote API x2Flow action. 
//flush_rewrite_rules();		
		add_rewrite_rule('^bbcrm/([A-Za-z]+)\/([0-9]+)\/Docs\/([0-9]+)\/?','index.php?bbcrm={"model":"$matches[1]","id":"$matches[2]","docId":"$matches[3]"}','top');
		add_rewrite_endpoint( 'bbcrm', EP_NONE );
//   
}
add_action( 'init', 'bbcrm_add_bbcrm_rewrite', 2,0 );

function bbcrm_email_api() {
	global $wp_query;
	$showvars = '';
	if ( !isset( $wp_query->query_vars['bbcrm'] ) )
		return;
 
//echo "Need some anti-snooping security here";
	$bbcrm = json_decode(stripslashes($wp_query->query_vars['bbcrm']));

	$wp_query->query_vars['model']=$bbcrm->model;
	$wp_query->query_vars['id']=$bbcrm->id;
	$wp_query->query_vars['docId']=$bbcrm->docId;

	if(isset( $wp_query->query_vars['model']) && isset($wp_query->query_vars['docId'])){
		$json = x2apicall(array('_class'=>'appInfo'));
		$appinfo = json_decode($json);

		$json = x2apicall(array('_class'=>$wp_query->query_vars['model'].'/'.$wp_query->query_vars['id'].'.json'));
		$record = json_decode($json);
		$model = strtolower($wp_query->query_vars['model']);

		$$model = $record;
		$emailfrom = '';
		$emailto = '';
		$listingid = ($record->c_listing_id)?$record->c_listing_id:$record->id;
		if(!isset($clistings)){
			$json = x2apicall(array('_class'=>'Clistings/'.$listingid.'.json'));
			$clistings = json_decode($json);
		}
		$json = x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($clistings->c_assigned_user_id).'.json'));
		$listingbroker = json_decode($json);

		$json = x2apicall(array('_class'=>'Seller/by:nameId='.urlencode($clistings->c_seller).'.json'));
		$listingseller = json_decode($json);

		if(isset($portfolio)){			
			$json = x2apicall(array('_class'=>'Contacts/by:nameId='.urlencode($portfolio->c_buyer).'.json'));
			$contacts = json_decode($json);
		}else{
			$portfolio = array();
		}

		if(isset($contacts)){
			$json = x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($contacts->c_broker).'.json'));
			$buyerbroker = json_decode($json);
		}else{
			$contacts = array();
			$buyerbroker = array();	
			$emailfrom = $listingbroker->firstName." ".$listingbroker->lastName."<".$listingbroker->c_email.">";
		}

		$json = x2apicall(array('_class'=>'Docs/'.$wp_query->query_vars['docId'].'.json'));
		$template = json_decode($json);

		foreach( array('appInfo'=>$appinfo,'Portfolio'=>$portfolio,'Clistings'=>$clistings,'ListingBroker'=>$listingbroker,'ListingSeller'=>$listingseller,'Contacts'=>$contacts,'BuyerBroker'=>$buyerbroker,'Doc'=>$template) AS $model=>$obj){
			foreach ($obj AS $k=>$v){
				if( is_string($v) ){
					$needles[]= "{".$model.":".$k."}";
					if(!stripos($k,'zip') &&!stripos($k,'postal') &&!stripos($k,'date') && !stripos($k,'activity')&& !stripos($k,'phone') && is_numeric($v) ){
						$v = number_format( intval($v) );				
					}
					if(stripos($k,'date') && is_numeric($v) ){
						$v = date("F j, Y", $v );
					}
					$values[]=$v;
					if($_SERVER["QUERY_STRING"]=="showvars"){
						$showvars .= "{".$model.":".$k."}=$v<br>";
					}
				}
			}
	
			foreach ($_POST as $name => $value) {
				$needles[]="{".$name."}"; 
				$values[]="$value";
			}
		}

		$template->text = str_replace($needles,$values,$template->text);
		$template->subject = str_replace($needles,$values,$template->subject);
		$from = (empty($emailfrom))?$buyerbroker->name."<".$buyerbroker->c_email.">":$emailfrom;
		$to = (empty($emailfrom))?$accounts->c_email:$emailfrom;

		$headers = array(
			'From:'.$from,
			'Cc:'.$listingbroker->name."<".$listingbroker->c_email.">",
			'Reply-To:'.$from,
			'Content-Type: text/html; charset=UTF-8',
			'X-Sender:'.$appinfo->name
		);
		if(!empty($showvars)){
			echo $showvars;
			var_dump($headers);		
			echo $accounts->c_email;	
			echo $template->subject;
			echo $template->text;
			die;
		}else{
			$emailstatus = wp_mail( $to, $template->subject, $template->text, $headers );
			print_r($emailstatus);
		}	

		if($buyer->id){
			$template->text = str_replace('<body>',"<body><!--BeginActionHeader--><strong>Subject: </strong>".$template->subject."<br /><strong>From: </strong>&quot;".$buyerbroker->name."&quot; &lt;".$buyerbroker->c_email."<br /><strong>To: </strong>&quot;".$accounts->name."&quot; &lt;".$buyer->c_email."&gt;<br /><br /><hr /><!--EndActionHeader--!><br />\r\n<br />\r\n<!--BeginSignature--><!--EndSignature-->\r\n<div>", $template->text);

			$data = array(
				'actionDescription'=>$template->text,
				'type'=>'email',
				'visibility'=>'1',
				'associationType'=>'accounts',
				'associationId'=>$accounts->id,
				'subject'=>$template->subject,
				'associationName'=>$buyer->name,
				'complete'=>'Yes',
				'assignedTo'=>$accounts->assignedTo,
				'completedBy'=>$accounts->assignedTo,
				'completeDate'=>strtotime('now'),
				'dueDate'=>strtotime('now'),
			);
			$json = x2apipost( array('_class'=>'Contacts/'.$buyer->id.'/Actions/','_data'=>$data ) );	
		}
	}
	exit;
}
add_action( 'template_redirect', 'bbcrm_email_api' );

?>
