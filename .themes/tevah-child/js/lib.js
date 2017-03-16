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
console.log(buyer)
		}),
			jQuery.getJSON('../_auth.php',
			{query:	'AJAX',action: 'x2apicall',_class:"Clistings/"+jQuery(this).data('listingid')+".json"}
	).done(function(response){
		listing = response
console.log(listing)
		})
	).then(function(){
		assigned = (targetbroker=="contactbuyerbroker")?buyer.assignedTo:listing.assignedTo;
			gender = (buyer.c_gender == "Male")?"him":"her";
console.log(buyer.assignedTo)
console.log(listing.assignedTo)			
console.log(targetbroker)			
console.log(assigned)			
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