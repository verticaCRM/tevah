jQuery(document).ready(function(){ 
	
userobj = jQuery.parseJSON(userJSON);
	jQuery.each(userobj,function(key,value){
		jQuery("#"+key).val(value)	
	})
	var newOptions = statesJSON;
	var el = jQuery("#state");
	el.append(jQuery("<option></option>").attr("value", "").text(chooseTxt));
		jQuery.each(newOptions, function(key, value) {
				selected = (value == userobj.state )?" selected":"";
				el.append(jQuery("<option "+selected+"></option>").attr("value", value).text(key));
			});

jQuery("#buyerprofile").submit(function(event){
	event.preventDefault();
	jsondata =jQuery(this).serializeObject();
	jQuery.post('/wp-content/plugins/bbcrm/_auth.php',{'query':'AJAX','action':'x2apipost','_format':'json','_method':'PATCH','_class':'Contacts/'+buyerid+'.json','_data':jsondata},function(response){
//console.log(response)
		jQuery(document).scrollTop(0);
		jQuery("#editsuccess").remove();
		jQuery("#buyerprofile").prepend("<div id='editsuccess' style='background-color:#ddffdd'>Profile information successfully updated.</div>");
		jQuery(':focus').blur() ;
	})
});


jQuery.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    jQuery.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

});
