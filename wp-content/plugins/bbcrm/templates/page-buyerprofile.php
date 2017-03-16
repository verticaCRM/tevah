<?php
/*
Template Name: Buyer Profile 
*/

session_start();

$userdata = get_userdata(get_current_user_id());
$userjson = x2apicall(array('_class'=>'Contacts/by:email='.$userdata->user_email.'.json'));
$user = json_decode($userjson);

if(!empty($_POST)){

	$userjson = x2apicall(array('_class'=>'Contacts/by:email='.$userdata->user_email.'.json'));
	$_POST["name"]=$_POST["firstName"]." ".$_POST["lastName"];
	$result = x2apipost(array('_method'=>"PUT",'_class'=>'Contacts/'.$user->id.".json",'_data'=>$_POST));
}


$json = x2apicall(array('_class'=>'dropdowns/'.$bbcrm_option["bbcrm_crm_states"].'.json'));
$states =json_decode($json);

if($states){
	$statesselect = array();
foreach ($states->options AS $k=>$v){
	$statesselect[] = 	'"'.$k.'":"'.$v.'"';
	}	
}	
?>
<script>
userJSON = "<?php echo addslashes($userjson);?>";
statesJSON = {<?php echo join(',',$statesselect);?>};
chooseTxt = "<?php _e('Choose A State','bbcrm');?>";
buyerid = <?php echo $user->id;?>;
</script>
<?php	
wp_enqueue_script('buyerreg-js',plugin_dir_url(__FILE__)."../js/buyerprofile.js", array('jquery'), '1.0.0');
get_header();
?>
	<section id="content" class="container">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="portfolio_group">
		<h2><?php the_title(); ?></h2>
		<div id="business_container">
				<?php the_content('<p class="serif">Read the rest of this page &raquo;</p>');	?>

<h3>Personal Information</h3>
<div id="form1" style="display: block;"><form id="buyerprofile" action="" method="post">
<label for="firstName">First Name:</label><input id="firstName" class="wpcf7-form-control" name="firstName" size="40" type="text" value="" /></p>
<p><label for="lastName">Last Name:</label><input id="lastName" class="wpcf7-form-control" name="lastName" size="40" type="text" value="" /></p>
<p><label for="email">Your Email:</label><input id="email" class="wpcf7-form-control" name="email" size="40" type="email" value="" /></p>
<p><label for="address">Address:</label><input id="address" class="wpcf7-form-control" name="address" size="40" type="text" value="" /></p>
<p><label for="city">City:</label><input id="city" class="wpcf7-form-control" name="city" size="40" type="text" value="" /></p>
<p><label for="c_state">State:</label><select id="state" name="state"></select></p>
<p><label for="zipcode">Zip Code: </label><input id="zipcode" class="wpcf7-form-control" name="zipcode" size="40" type="text" value="" /></p>
<p><label for="c_cellphone">Cell Phone: </label><input id="phone2" class="wpcf7-form-control" name="phone2" size="40" type="tel" value="" /></p>
<p><label for="phone">Home Phone:</label><input id="phone" class="wpcf7-form-control wpcf7-text wpcf7-tel wpcf7-validates-as-tel" name="phone" size="40" type="tel" value="" /></p>
<p><label for="c_fax">Fax Phone:</label><input id="c_fax" class="wpcf7-form-control wpcf7-text wpcf7-tel wpcf7-validates-as-tel" name="c_fax" size="40" type="tel" value="" /></p>

<h3>Financial Statement</h3>
<p><label for="c_Whatkindofworkdoyoudo">What kind of work do you do?</label> <input id="c_Whatkindofworkdoyoudo" class="wpcf7-form-control" name="c_Whatkindofworkdoyoudo" size="40" type="text" value="" /></p>
<p><label for="c_Preferedlocation">Preferred Location:</label> <input id="c_Preferedlocation" class="wpcf7-form-control" name="c_Preferedlocation" size="40" type="text" value="" /></p>
<p><label for="c_cds">Business Category:</label> <input id="c_businesscategory" class="wpcf7-form-control" name="c_businesscategory" size="40" type="text" value="" /></p>

<h3>Assets</h3>
<p><label for="c_Cashonhandandinbank">Cash on Hand &amp; in Banks:</label> <input id="c_Cashonhandandinbank" class="wpcf7-form-control" name="c_Cashonhandandinbank" size="40" type="text" value="" /></p>
<p><label for="c_cds">C.D.'s:</label> <input id="c_cds" class="wpcf7-form-control" name="c_cds" size="40" type="text" value="" /></p>
<p><label for="c_StockAndBonds">Stocks &amp; Bonds:</label> <input id="c_StockAndBonds" class="wpcf7-form-control" name="c_StockAndBonds" size="40" type="text" value="" /></p>
<p><label for="c_MoneyMarketFunds">Money Market Funds:</label> <input id="c_MoneyMarketFunds" class="wpcf7-form-control" name="c_MoneyMarketFunds" size="40" type="text" value="" /></p>
<p><label for="c_AccountsNotesReceivable">Accounts / Notes Receivable:</label> <input id="c_AccountsNotesReceivable" class="wpcf7-form-control" name="c_AccountsNotesReceivable" size="40" type="text" value="" /></p>
<p><label for="c_VestedPortionofPensionorProfitSharing">Vested Portion of Pension or Profit Sharing:</label> <input id="c_VestedPortionofPensionorProfitSharing" class="wpcf7-form-control" name="c_VestedPortionofPensionorProfitSharing" size="40" type="text" value="" /></p>
<p><label for="c_NameofBanks">Automobile(s):</label> <input id="c_Automobiles" class="wpcf7-form-control" name="c_Automobiles" size="40" type="text" value="" /></p>
<p><label for="c_OtherAssets1">Other Assets:</label> <input id="c_OtherAssets1" class="wpcf7-form-control" name="c_OtherAssets1" size="40" type="text" value="" /></p>
<p><label for="c_Furniture">Furniture, Machinery, &amp; Tools:</label> <input id="c_Furniture" class="wpcf7-form-control" name="c_Furniture" size="40" type="text" value="" /></p>

<h3>Liabilities</h3>
<p><label for="c_DueBanksLoans">Due Banks (Loans):</label> <input id="c_DueBanksLoans" class="wpcf7-form-control" name="c_DueBanksLoans" size="40" type="text" value="" /></p>
<p><label for="c_NameofBanks">Name of Bank(s):</label> <input id="c_NameofBanks" class="wpcf7-form-control" name="c_NameofBanks" size="40" type="text" value="" /></p>
<p><label for="c_RealestateMortgageHome">Real Estate, Mortgage, Home:</label> <input id="c_RealestateMortgageHome" class="wpcf7-form-control" name="c_RealestateMortgageHome" size="40" type="text" value="" /></p>
<p><label for="c_HomeAptCondoFarmBusiness">Home,Apt., Condo, Farm, Business:</label> <input id="c_HomeAptCondoFarmBusiness" class="wpcf7-form-control" name="c_HomeAptCondoFarmBusiness" size="40" type="text" value="" /></p>
<p><label for="c_AutoLoans">Auto Loans:</label> <input id="c_AutoLoans" class="wpcf7-form-control" name="c_AutoLoans" size="40" type="text" value="" /></p>
<p><label for="c_RevolvingCharges">Revolving Charges:</label> <input id="c_RevolvingCharges" class="wpcf7-form-control" name="c_RevolvingCharges" size="40" type="text" value="" /></p>
<p><label for="c_RealEstate">Real Estate Taxes Due:</label> <input id="c_RealEstate" class="wpcf7-form-control" name="c_RealEstate" size="40" type="text" value="" /></p>
<p><label for="c_OtherDebtegHealthLifeIns">Other Debt (e.g. Health, Life Insurance):</label> <input id="c_OtherDebtegHealthLifeIns" class="wpcf7-form-control" name="c_OtherDebtegHealthLifeIns" size="40" type="text" value="" /></p>

<h3>Profile</h3>
<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Doyoucurrentlyownabusiness">Do you currently own a business?</label><select id="c_Doyoucurrentlyownabusiness" name="c_Doyoucurrentlyownabusiness">
<option value="">Select an option</option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Haveyoueverownedabusinessbefore">Have you ever owned a business before?</label><select id="c_Haveyoueverownedabusinessbefore" name="c_Haveyoueverownedabusinessbefore">
<option value="">Select an option</option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Education">Education:</label><select id="c_Education" name="c_Education">
<option value="">Select an option</option>
<option value="High school">High school</option>
<option value="College">College</option>
<option value="Graduate">Graduate</option>
<option value="Professional Degree">Professional Degree</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Experiance">Experience:</label><select id="c_Experiance" name="c_Experiance">
<option value="">Select an option</option>
<option value="Entreperneur">Entreperneur</option>
<option value="Senior Executive">Senior Executive</option>
<option value="Management">Management</option>
<option value="Sales Assoc.">Sales Assoc.</option>
<option value="No Experience">No Experience</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Whatisyourcurrentworkstatus">What is your current work status?</label><select id="c_Whatisyourcurrentworkstatus" name="c_Whatisyourcurrentworkstatus">
<option value="">Select an option</option>
<option value="Employed">Employed</option>
<option value="Unemployed">Unemployed</option>
<option value="Self-Employed">Self-Employed</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Haveyoueverfiledforbankruptcy">Have you ever filed for bankruptcy?</label><select id="c_Haveyoueverfiledforbankruptcy" name="c_Haveyoueverfiledforbankruptcy">
<option value="">Select an option</option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Howlonghaveyoubeenlookingforabusiness">How long have you been looking for a business?</label><select id="c_Howlonghaveyoubeenlookingforabusiness" name="c_Howlonghaveyoubeenlookingforabusiness">
<option value="">Select an option</option>
<option value="1 Month">1 Month</option>
<option value="2 Months">2 Months</option>
<option value="3 Months">3 Months</option>
<option value="4 Months">4 Months</option>
<option value="5 Months">5 Months</option>
<option value="6 Months">6 Months</option>
<option value="7 Months">7 Months</option>
<option value="8 Months">8 Months</option>
<option value="9 Months">9 Months</option>
<option value="10 Months">10 Months</option>
<option value="12 Months">12 Months</option>
<option value="16 Months">16 Months</option>
<option value="18 Months">18 Months</option>
<option value="20 Months">20 Months</option>
<option value="24 Months">24 Months</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_NetCashFlowDesired">Net Cash Flow Desired:</label><select id="c_NetCashFlowDesired" name="c_NetCashFlowDesired">
<option value="">Select an option</option>
<option value="$0-50K">$0-50K</option>
<option value="$50-150K">$50-150K</option>
<option value="$150-300K">$150-300K</option>
<option value="$300-500K">$300-500K</option>
<option value="$500-1 Mil">$500-1 Mil</option>
<option value="1 Mil+">1 Mil+</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Whatisyourtimeframeforpurchase">What is your timeframe for purchase?</label><select id="c_Whatisyourtimeframeforpurchase" name="c_Whatisyourtimeframeforpurchase">
<option value="">Select an option</option>
<option value="1 Month">1 Month</option>
<option value="2 Months">2 Months</option>
<option value="3 Months">3 Months</option>
<option value="4 Months">4 Months</option>
<option value="5 Months">5 Months</option>
<option value="6 Months">6 Months</option>
<option value="7 Months">7 Months</option>
<option value="8 Months">8 Months</option>
<option value="9 Months">9 Months</option>
<option value="10 Months">10 Months</option>
<option value="12 Months">12 Months</option>
<option value="16 Months">16 Months</option>
<option value="18 Months">18 Months</option>
<option value="20 Months">20 Months</option>
<option value="24 Months">24 Months</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Wouldyouliketoreceiveemailsdescribingnewlistingsorlis">Would you like to receive emails describing new listings, or listings matching your profile?</label><select id="c_Wouldyouliketoreceiveemailsdescribingnewlistingsorlis" name="c_Wouldyouliketoreceiveemailsdescribingnewlistingsorlis">
<option value="">Select an option</option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Isyourdownpaymentavailabletoday">Is your down payment available today?</label><select id="c_Isyourdownpaymentavailabletoday" name="c_Isyourdownpaymentavailabletoday">
<option value="">Select an option</option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Whatisthesourceofyourdownpayment">What is the source of your down payment?</label><select id="c_Whatisthesourceofyourdownpayment" name="c_Whatisthesourceofyourdownpayment">
<option value="">Select an option</option>
<option value="Self-funded">Self-funded</option>
<option value="Home Equity">Home Equity</option>
<option value="Friend / Relative">Friend / Relative</option>
<option value="SBA Relationship">SBA Relationship</option>
<option value="Corp. Relationship">Corp. Relationship</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_AmountofDownPayment">Amount of Down Payment</label><select id="c_AmountofDownPayment" name="c_AmountofDownPayment">
<option value="">Select an option</option>
<option value="$0-50K">$0-50K</option>
<option value="$50-150K">$50-150K</option>
<option value="$150-300K">$150-300K</option>
<option value="$300-500K">$300-500K</option>
<option value="$500-1 Mil">$500-1 Mil</option>
<option value="1 Mil+">1 Mil+</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_DescribeAdditionalSourceofFunding">Describe additional source of funding:</label>
<textarea id="c_DescribeAdditionalSourceofFunding" cols="" name="c_DescribeAdditionalSourceofFunding" rows=""></textarea></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_DoYouHaveAPartnerartner">Do you have a partner?</label><select id="c_DoYouHaveAPartnerartner" name="c_DoYouHaveAPartnerartner">
<option value="">Select an option</option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Ifyeswhatisthatpersonrelationshiptoyou">If yes what is that person's relationship to you?</label><select id="c_Ifyeswhatisthatpersonrelationshiptoyou" name="c_Ifyeswhatisthatpersonrelationshiptoyou">
<option value="">Select an option</option>
<option value="Family Member">Family Member</option>
<option selected="selected" value="Spouse">Spouse</option>
<option value="Business Partner">Business Partner</option>
<option value="Corporation">Corporation</option>
<option value="Financial Institution">Financial Institution</option>
<option value="Other">Other</option>
</select></p>

<p><label style="width: auto !important; padding-right: 15px  !important;" for="c_Howdidyouhearaboutus">How did you hear about us?</label><select id="c_Howdidyouhearaboutus" name="c_Howdidyouhearaboutus">
<option value="">Select an option</option>
<option value="Internet">Internet</option>
<option value="Friend/Associate">Friend/Associate</option>
<option selected="selected" value="Magazine Ad/Trade Publication">Magazine Ad/Trade Publication</option>
<option value="Other">Other</option>
</select></p>

<input class="theme-button1-dk brokerprofilebutton" type="submit" value="Save Changes" />

</form>
		</div>

		</div>
		<?php endwhile; endif;?>
	</section>
<?php 
get_footer();
?>
