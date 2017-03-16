$(document).ready(function(){

	$("#Clistings_c_listing_country_c").on('change',function(){
	
		$.ajax({
		            url:"/crm/index.php/site/dynamicDropdown",
		            type:"GET",
		            data:{"val":$(this).val(),"dropdownId":"1001","field":"true", "module":"Clistings"},
		            dataType:"Json",
		            success:function(data){
		            	$("#Clistings_c_listing_region_c").html(data);
		            	$('#Clistings_c_listing_region_c').val(selected_region);						},
						failure:function(data){
							console.log ("FAILURE");
							console.log(data);
						}
		})
	});
	
	/* http://community.x2crm.com/topic/885-cascading-or-dependent-dropdown-list/ */

	$("#Clistings_c_listing_region_c").on('change',function(){
			$.ajax({
		            url:"/crm/index.php/site/dynamicDropdown",
		            type:"GET",
		            data:{"val":$(this).val(),"dropdownId":"1002","field":"true", "module":"Clistings"},
		            dataType:"Json",
		            success:function(data){
			            	$("#Clistings_c_listing_town_c").html(data);
			            	$('#Clistings_c_listing_town_c').val(selected_town);						},
						failure:function(data){
							console.log ("FAILURE");
							console.log(data);
						}
		})
	
	});
	
	if (selected_country !='' )
	{
		//$('#Clistings_c_listing_country_c').trigger('change').val(selected_country);
	}
	
	if (selected_region !='' )
	{
		$('#Clistings_c_listing_region_c').trigger('change').val(selected_region);
	}

});