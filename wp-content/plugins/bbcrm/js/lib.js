function formatMoney(amount){
	var n = amount, 
    c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = d == undefined ? "." : d, 
    t = t == undefined ? "," : t, 
    s = n < 0 ? "-" : "", 
    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };

jQuery(document).ready(function() {
jQuery('#form-reg').prop('disabled', true);
 jQuery('#acceptance').change(function() {
console.log( jQuery(this).prop("checked") )
if(jQuery(this).prop("checked")) {
jQuery('#form-reg').prop('disabled', false);
}else{
jQuery('#form-reg').prop('disabled', true);
}
});
});
 
jQuery(document).ready(function(){

	jQuery(".listing_link").click(function(event){
			//event.preventDefault();
			jQuery.getJSON('../_auth.php',{query:	'AJAX',action: 'setpagelistingid',_id:jQuery(this).data("id"),_href:jQuery(this).attr("href")},function(res){
				//console.log(res)
			});
			//jQuery("#listing-form").attr("action",jQuery(this).attr("href"))		
			//jQuery("#listing-id").val(jQuery(this).data("id"))
			//jQuery("#listing-form").submit();
	});
	
	jQuery(".contactbroker").click(function(event){
			var buyer;
			var listing;
			var assignedTo;
			var portfolioid;

targetbroker = jQuery(this).attr('id');
portfolioid = jQuery(this).data('portfolioid')
jQuery.when(jQuery.getJSON('../_auth.php',
	{query:	'AJAX',action: 'x2apicall',_class:"Contacts/"+jQuery(this).data('buyerid')+".json"} 
).done(function(response){
			buyer = response
//console.log(buyer)
		}),

			jQuery.getJSON('../_auth.php',
			{query:	'AJAX',action: 'x2apicall',_class:"Clistings/"+jQuery(this).data('listingid')+".json"}
	).done(function(response){
		listing = response
//console.log(listing)
		})
	).then(function(){
		assigned = (targetbroker=="contactbuyerbroker")?buyer.assignedTo:listing.assignedTo;
			gender = (buyer.c_gender == "Male")?"him":"her";
//console.log(buyer.assignedTo)
//console.log(listing.assignedTo)			
//console.log(targetbroker)			
//console.log(assigned)			
		jsondata = {
		'actionDescription':buyer.firstName+" has requested that you contact "+gender+" regarding the listing "+listing.name+"(id #"+listing.id+"). As the buyer's broker, it is your responsibility to make contact as soon as possible:<br>Phone:"+buyer.phone+"<br>Mobile:"+buyer.c_cellphone+"<br>Alt Phone:"+buyer.c_phone2+"<br><br>Please remember to log this contact and mark this action complete.",
		'assignedTo'	:	assigned,
		'associationId' : portfolioid,
		'associationType' : 'portfolio',
		'associationName' : buyer.name,
		'subject'	:	'Contact Request from '+buyer.name,
		'dueDate':'+4 hours'
		}	
//console.log(jsondata);
		jQuery.getJSON('../_auth.php',
				{query:'AJAX',action: 'x2apicall',_class:"Brokers/by:nameId="+encodeURIComponent(buyer.c_broker)+".json"}
			)
			.done(function(response){
				assignedTo = response
//console.log(jsondata)
//console.log(portfolioid)
//obj = jQuery.parseJSON(jsondata);
			jQuery.when(jQuery.getJSON('../_auth.php',
					{'query':'AJAX','action':'x2apipost','_format':'json','_class':'Portfolio/'+portfolioid+'/Actions','_data':jsondata}	
				)
				).then(function(response){
//console.log(response);
					})
			});
		
	});		
});
});



/////////////////////
/*
Featured Search jQuery Functions
These variables are set in the template file, since they rely on dynamically generated PHP values
@brokerJSON
@authURI
*/
/////////////////////
if (typeof brokerJSON=="undefined"){
brokerJSON = '';
chooseABrokerTxt = '';
pleaseWaitTxt = '';
}
	jQuery(document).ready(function(){ 
	jQuery(".advancedsearch").click(function(event){
	event.preventDefault()
//console.log( jQuery(this).parent().next() );
	 jQuery(this).parent().next("#advanced").show()
	})

jQuery(".broker-select").each( function(){	
	var newBrokers = brokerJSON;
		var el = jQuery(this);
		if(chooseABrokerTxt)
		{
			el.append(jQuery("<option></option>").attr("value", "").text(chooseABrokerTxt));
		}			
		jQuery.each(newBrokers, function(key, value) {
			el.append(jQuery("<option></option>").attr("value", value).text(key));
		});
});

jQuery(".fs_select").change(function(){
stateEl = jQuery(this)
dropdown = stateEl.closest("form").find("#c_listing_town_c")					
dropdown.empty().attr("disabled",true).append(jQuery("<option></option>").attr("value","").text(pleaseWaitTxt));
	jQuery.getJSON(authURI,
	{query:	'AJAX',action: 'x2apicall',_class:"dropdowns/"}, 
	function(response){
//console.log(response)
			jQuery.each(response, function(key, value) {

				if(value.parentVal==stateEl.val()){

				dropdown.empty().attr("disabled",false).append(jQuery("<option></option>").attr("value", "").text(selectCountyTxt));
				jQuery.each(value.options, function(nam, val) {
					dropdown.append(jQuery("<option></option>").attr("value", val).text(val));
					})	
				}
			});
	});
})		


jQuery("#broker").change(function(){
	jQuery.getJSON(authURI,
	{query:	'AJAX',action: 'x2apicall',_class:"Brokers/by:nameId="+encodeURI(jQuery(this).val())+".json"}, 
	function(response){
//		console.log(response)
		jQuery("#assignedTo").remove();
		jQuery("#form_buyerreg").append("<input type='hidden' id='assignedTo' name='assignedTo' value='"+response.assignedTo+"' />");
	});
})		
	})




/**
	Search Sidebar
*/
function do_search_sidebar() {


	// Search Sidebar Toggle
	jQuery('.sidebar_content .panel-heading').on('click', function()
	{
		jQuery(this).find('.glyphicon').toggleClass('glyphicon-circle-arrow-down');
		jQuery(this).find('.glyphicon').toggleClass('glyphicon-circle-arrow-up');
		
	});

	// Search Sidebar Categories Base Container Toggle
	jQuery('.sidebar_categories_button').on('click', function()
	{
		jQuery(this).next().toggle();
		jQuery(this).find('.fa').toggleClass('fa-chevron-down');
		jQuery(this).find('.fa').toggleClass('fa-chevron-up');
	});

	// Search Sidebar Child Categories Container Toggle
	jQuery('.parent_category_title').on('click', function()
	{
		if(jQuery(this).next().is(":visible"))
		{
			jQuery(this).next().toggle();
		}
		else
		{
			jQuery(this).parent().find('.child_category_container').hide();
			jQuery(this).next().toggle();			
		}
	});

	// Search Sidebar Checkbox Checked Incrementation Behaviour 
	jQuery('.search_checkbox').change(function()
	{
		
			//=> was used for multiple select
		var number = jQuery(this).closest('.sidebar_categories_base_container').find('.selected_number');
		var number_html = parseInt(number.html());

		if( this.checked)
		{
			number.html(number_html+1);
		}
		else
		{
			number.html(number_html-1);
		}
		
		/*if( this.checked)
		{
			//unselect all the options and check only this one (we need the functionality of the radio button)
			jQuery('.child_category_container input[type=checkbox]:checked').not(this).attr('checked', false);
		}
		*/
	});
	
	// Search Sidebar DO Parent Category number of listings 
	var parent_cat = jQuery('.parent_category_title');

	parent_cat.each( function(i) {
		var parentcat_listings_nr = 0;

		var subcat_listings = jQuery(parent_cat[i]).next().find('.child_cat_listings');

		subcat_listings.each( function(i) {
			parentcat_listings_nr += parseInt( jQuery(subcat_listings[i]).html() );			
		});

		jQuery(parent_cat[i]).find('.parent_cat_listings').html(parentcat_listings_nr);
	});
}



/**
	*Developed by: Theo@BioeliteVert
	*functions for saving and clearing sidebar search form data
	*for Business
*/



// Clear Search Fields Functions
function clear_business_shearch_fields()
{
	jQuery('[name="c_keyword_c"]').val('');
	jQuery('#minimum_investment').val('');
	jQuery('#maximum_investment').val('');
	jQuery('#listing_region').val('');
	jQuery('#adjusted_net_profit').val('');
	jQuery('#broker').val('');
	//Clear Checkboxes and Set html to default
	jQuery('.child_category_container input[type=checkbox]:checked').attr('checked', false);
	jQuery('.child_category_container').hide();
	jQuery('.selected_number_of_categories .selected_number').html(0);
    jQuery('[name="c_franchise_c"]').attr('checked', false);
}
function clear_business_shearch_by_id_input()
{
	jQuery('.id_search_input').val('');
}
function clear_business_all_search_fields()
{
	clear_business_shearch_fields();
	clear_business_shearch_by_id_input();
	//jQuery('.find_business_search_button').trigger('click');
	window.location = window.location.pathname;
}




// Keep search input value after window relaod
function get_business_search_value_after_reload()
{	
	var category_tag_val = '';
	jQuery('#business_container.searchlists_container a').on('click', function()
	{
		category_tag_val = jQuery(this).attr('data-cat');
	});


	jQuery(window).on('unload', function()
	{
	    localStorage.setItem( 'category_tag_val', category_tag_val );
	    localStorage.setItem( 'keywords', jQuery('[name="c_keyword_c"]').val() );
	    localStorage.setItem( 'minimum_investment', jQuery('[name="c_minimum_investment_c"]').val() );
	    localStorage.setItem( 'maximum_investment', jQuery('[name="c_maximum_investment_c"]').val() );
	    localStorage.setItem( 'listing_region', jQuery('[name="c_listing_region_c"]').val() );
	    localStorage.setItem( 'adjusted_net_profit', jQuery('[name="c_adjusted_net_profit_c"]').val() );
	    localStorage.setItem( 'broker', jQuery('[name="c_Broker[]"]').val().join('|') );
	    console.log( localStorage.getItem('broker') );
	    localStorage.setItem( 'id_search_input', jQuery('.id_search_input').val() );
	    localStorage.setItem( 'franchise', String(jQuery('.franchise_checkbox').is(':checked')) );

	    //Checkboxes
	    localStorage.setItem( 'selected_businesscategories', jQuery('.selected_number_of_categories .selected_number').html() );
	    localStorage.setItem( 'businesscategory', jQuery('.child_category_container input[type=checkbox]:checked').map(function(_, el) {
																														    return jQuery(el).val();
																														}).get() );
	});
	


	if( window.location.href.indexOf('/search/') >= 0 )
	{
		var category_tag_val = localStorage.getItem('category_tag_val');
	    var keywords = localStorage.getItem('keywords');
	    var minimum_investment = localStorage.getItem('minimum_investment');
	    var maximum_investment = localStorage.getItem('maximum_investment');
	    var listing_region = localStorage.getItem('listing_region');
	    var adjusted_net_profit = localStorage.getItem('adjusted_net_profit');
	    var id_search_input = localStorage.getItem('id_search_input');
	    var franchise = localStorage.getItem('franchise');
	    if (keywords !== null && keywords != 'undefined' ) jQuery('[name="c_keyword_c"]').val(keywords);
	    if (minimum_investment !== null && minimum_investment != 'undefined' ) jQuery('#minimum_investment option[value="'+ minimum_investment +'"]').attr('selected','selected');
	    if (maximum_investment !== null && maximum_investment != 'undefined' ) jQuery('#maximum_investment option[value="'+ maximum_investment +'"]').attr('selected','selected');
	    if (listing_region !== null && listing_region != 'undefined' ) jQuery('#listing_region option[value="'+ listing_region +'"]').attr('selected','selected');
	    if (adjusted_net_profit !== null && adjusted_net_profit != 'undefined' ) jQuery('#adjusted_net_profit option[value="'+ adjusted_net_profit +'"]').attr('selected','selected');
	    if (id_search_input !== null && id_search_input != 'undefined' ) jQuery('.id_search_input').val(id_search_input);
	    if ( franchise == 'true' ) jQuery('[name="c_franchise_c"]').attr('checked','checked');

	    //Brokers
	    var broker = localStorage.getItem('broker');
	    

	    if( broker && typeof broker !== 'undefined')
	    {
		    var brokers = broker.split('|');

		    brokers.forEach( function(item)
		    {
		    	if (broker !== null && broker != 'undefined' ) jQuery('#broker option[value="'+ item +'"]').attr('selected','selected');
		    });	    	
	    }

	    //Checkboxes
		var selected_businesscategories = localStorage.getItem('selected_businesscategories');
		// console.log(selected_businesscategories);
	    var businesscategory = localStorage.getItem('businesscategory');	

	    if( businesscategories && typeof businesscategories !== 'undefined')
	    {
	    	var businesscategories = businesscategory.split(',');

		    if(!selected_businesscategories || selected_businesscategories < 0)
		    {
		    	jQuery('.selected_number_of_categories .selected_number').html(0);
		    }
		    else
		    {
		    	jQuery('.selected_number_of_categories .selected_number').html(selected_businesscategories);    	
		    }

		    businesscategories.forEach( function(item)
		    {
		    	if (businesscategory !== null && businesscategory != 'undefined' )
		    	{
		    		jQuery('.child_category_container input[value="'+ item +'"]').attr('checked','checked');
		    		jQuery('.child_category_container input[value="'+ item +'"]').parent().parent().show();
		    	} 
		    });		
		}

	    // Category tag
	    localStorage.setItem( 'category_tag_val', '' );
	    if(category_tag_val)
	    {
		    jQuery('input[value="'+category_tag_val+'"]').parent().parent().show();
		    jQuery('input[value="'+category_tag_val+'"]').attr('checked', 'checked');
		}
	    category_tag_val = localStorage.getItem('category_tag_val');
	}

}



/**
	*Developed by: Theo@BioeliteVert
	*functions for saving and clearing sidebar search form data
	*for Real Estates
*/


function clear_real_est_sale_search_fields()
{
	jQuery('#for_sale [name="c_keyword_c"]').val('');
	jQuery('#for_sale #commercial_category').val('');
	jQuery('#minimum_investment').val('');
	jQuery('#maximum_investment').val('');
	jQuery('#for_sale #listing_region').val('');
	jQuery('#for_sale .find_real_est_search_button').trigger('click');
}


function clear_real_est_lease_search_fields()
{
	jQuery('#for_lease [name="c_keyword_c"]').val('');
	jQuery('#for_lease #commercial_category').val('');
	jQuery('#minimum_rent').val('');
	jQuery('#maximum_rent').val('');
	jQuery('#for_lease #listing_region').val('');
	jQuery('#for_lease .find_real_est_search_button').trigger('click');
}



function do_real_est_sale_search_value_after_reload()
{
	jQuery(window).on('unload', function()
	{
	    localStorage.setItem( 'sale_keywords', jQuery('#for_sale [name="c_keyword_c"]').val() );
	    localStorage.setItem( 'sale_real_estate_categories', jQuery('#for_sale [name="c_real_estate_categories[]"]').val() );
	    localStorage.setItem( 'minimum_investment', jQuery('[name="c_minimum_investment_c"]').val() );
	    localStorage.setItem( 'maximum_investment', jQuery('[name="c_maximum_investment_c"]').val() );
	    localStorage.setItem( 'sale_real_estate_regions', jQuery('#for_sale [name="c_listing_region_c[]"]').val() );
	});



	if( window.location.href.indexOf('/commercial-property/') >= 0 )
	{
	    var sale_keywords = localStorage.getItem('sale_keywords');
	    var sale_real_estate_categories = localStorage.getItem('sale_real_estate_categories');
	    var minimum_investment = localStorage.getItem('minimum_investment');
	    var maximum_investment = localStorage.getItem('maximum_investment');
	    var sale_real_estate_regions = localStorage.getItem('sale_real_estate_regions');

	    if (sale_keywords !== null && sale_keywords != 'undefined' ) jQuery('#for_sale [name="c_keyword_c"]').val(sale_keywords);
	    if (minimum_investment !== null && minimum_investment != 'undefined' ) jQuery('#minimum_investment option[value="'+ minimum_investment +'"]').attr('selected','selected');
	    if (maximum_investment !== null && maximum_investment != 'undefined' ) jQuery('#maximum_investment option[value="'+ maximum_investment +'"]').attr('selected','selected');


	    //Real Estate Sale Categories
	    var sale_real_estate_categories = localStorage.getItem('sale_real_estate_categories');
	    if(sale_real_estate_categories)
	    {
		    var sale_real_estate_categories_array = sale_real_estate_categories.split(',');

		    sale_real_estate_categories_array.forEach( function(item)
		    {
		    	if (sale_real_estate_categories !== null && sale_real_estate_categories != 'undefined' ) jQuery('#for_sale #commercial_category option[value="'+ item +'"]').attr('selected','selected');
		    });
		}

	    //Real Estate Sale Region
	    var sale_real_estate_regions = localStorage.getItem('sale_real_estate_regions');
	    if(sale_real_estate_regions)
	    {
		    var sale_real_estate_regions_array = sale_real_estate_regions.split(',');

		    sale_real_estate_regions_array.forEach( function(item)
		    {
		    	if (sale_real_estate_regions !== null && sale_real_estate_regions != 'undefined' ) jQuery('#for_sale #listing_region option[value="'+ item +'"]').attr('selected','selected');
		    });
		}
		
	    localStorage.clear();
	}

}


function do_real_est_lease_search_value_after_reload()
{
	jQuery(window).on('unload', function()
	{
	    localStorage.setItem( 'lease_keywords', jQuery('#for_lease [name="c_keyword_c"]').val() );
	    localStorage.setItem( 'lease_real_estate_categories', jQuery('#for_lease [name="c_real_estate_categories[]"]').val() );
	    localStorage.setItem( 'minimum_rent', jQuery('[name="c_minimum_rent_c"]').val() );
	    localStorage.setItem( 'maximum_rent', jQuery('[name="c_maximum_rent_c"]').val() );
	    localStorage.setItem( 'lease_real_estate_regions', jQuery('#for_lease [name="c_listing_region_c[]"]').val() );
	});



	if( window.location.href.indexOf('/commercial-property/') >= 0 )
	{
	    var lease_keywords = localStorage.getItem('lease_keywords');
	    var lease_real_estate_categories = localStorage.getItem('lease_real_estate_categories');
	    var minimum_rent = localStorage.getItem('minimum_rent');
	    var maximum_rent = localStorage.getItem('maximum_rent');
	    var lease_real_estate_regions = localStorage.getItem('lease_real_estate_regions');

	    
	    if (lease_keywords !== null && lease_keywords != 'undefined' ) jQuery('#for_lease [name="c_keyword_c"]').val(lease_keywords);
	    if (minimum_rent !== null && minimum_rent != 'undefined' ) jQuery('#minimum_rent option[value="'+ minimum_rent +'"]').attr('selected','selected');
	    if (maximum_rent !== null && maximum_rent != 'undefined' ) jQuery('#maximum_rent option[value="'+ maximum_rent +'"]').attr('selected','selected');

	    //Real Estate Lease Categories
	    var lease_real_estate_categories = localStorage.getItem('lease_real_estate_categories');
	    if(lease_real_estate_categories)
	    {
		    var lease_real_estate_categories_array = lease_real_estate_categories.split(',');

		    lease_real_estate_categories_array.forEach( function(item)
		    {
		    	if (lease_real_estate_categories !== null && lease_real_estate_categories != 'undefined' ) jQuery('#for_lease #commercial_category option[value="'+ item +'"]').attr('selected','selected');
		    });	    	
	    }

	    //Real Estate Lease Region
	    var lease_real_estate_regions = localStorage.getItem('lease_real_estate_regions');
	    if(lease_real_estate_regions)
	    {
		    var lease_real_estate_regions_array = lease_real_estate_regions.split(',');

		    lease_real_estate_regions_array.forEach( function(item)
		    {
		    	if (lease_real_estate_regions !== null && lease_real_estate_regions != 'undefined' ) jQuery('#for_lease #listing_region option[value="'+ item +'"]').attr('selected','selected');
		    });
		}

	    localStorage.clear();
	}

}




/**
	*Developed by: Theo@BioeliteVert
	*function for Real Estates Search Sidebar Toggles
*/
function do_sidebar_tabs() {
	jQuery('.tab_container').on('click', function()
	{
		if( !jQuery(this).hasClass('active') )
		{
			var id = jQuery(this).find('div').attr('toggle-id');
			// Do local storage form saving for specific tab
			if (id == 'for_lease')
			{
				do_real_est_lease_search_value_after_reload();
			}
			else
			{
				do_real_est_sale_search_value_after_reload();
			}

			jQuery('.toggle_content').removeClass('active');
			jQuery('#'+id).addClass('active');
			jQuery('.tab_container').removeClass('active');
			jQuery(this).addClass('active');
		}
	});
}




//Bookmark Listing
function bookmark(title, url) {
    if(document.all) { // ie
        window.external.AddFavorite(url, title);
    }
    else if(window.sidebar) { // firefox
        window.sidebar.addPanel(title, url, "");
    }
    else if(window.opera && window.print) { // opera
        var elem = document.createElement('a');
        elem.setAttribute('href',url);
        elem.setAttribute('title',title);
        elem.setAttribute('rel','sidebar');
        elem.click(); // this.title=document.title;
    }
}






/**
	ON DOCUMENT READY
*/

jQuery(document).on('ready', function()
{
	//Bookmark Listing
	jQuery("a.jQueryBookmark").click(function(e){
		e.preventDefault();
	    if (window.sidebar && window.sidebar.addPanel) { // Mozilla Firefox Bookmark
	      window.sidebar.addPanel(document.title, window.location.href, '');
	    } else if (window.external && ('AddFavorite' in window.external)) { // IE Favorite
	      window.external.AddFavorite(location.href, document.title);
	    } else if (window.opera && window.print) { // Opera Hotlist
	      this.title = document.title;
	      return true;
	    } else { // webkit - safari/chrome
	      alert('Please press <' + (navigator.userAgent.toLowerCase().indexOf('mac') != -1 ? 'Command/Cmd' : 'CTRL') + ' + D> to bookmark this page. Thank you.');
	    }
	});



	if( jQuery('.sidebar_search_by_id_container').length )
	{
		// Get Search value after Reload
		get_business_search_value_after_reload();

		// Do Search Sidebar
		do_search_sidebar();
	}

	// Do Commercial Real Estates Search Sidebar
	if( jQuery('#for_sale.toggle_content').hasClass('active') )
	{
		do_real_est_sale_search_value_after_reload();
	}
	if( jQuery('#for_lease.toggle_content').hasClass('active') )
	{
		do_real_est_lease_search_value_after_reload();
	}
	else
	{
	    localStorage.clear();
	}

	// Clear sidebar search form fields
	jQuery('.searchlisting_bottom_category a').on('click', function()
	{
		clear_business_all_search_fields();

	});
	
	// Real Estates Sidebar Toggles
	do_sidebar_tabs();
	
	jQuery('#sort_listings').on('change', function()
	{		
		addQSParm("sort_order", jQuery(this).val());
		window.location.href = myUrl;
	});
});

var myUrl = window.location.href; 
		
function addQSParm(name, value) {
    var re = new RegExp("([?&]" + name + "=)[^&]+", "");

    function add(sep) {
        myUrl += sep + name + "=" + encodeURIComponent(value);
    }

    function change() {
        myUrl = myUrl.replace(re, "$1" + encodeURIComponent(value));
    }
    if (myUrl.indexOf("?") === -1) {
        add("?");
    } else {
        if (re.test(myUrl)) {
            change();
        } else {
            add("&");
        }
    }
}



