function rf_toggleOther(elm, val) {
	o_name = $(elm).attr('name')+"_other";
	o_elm = $('input[name='+o_name+']');
	
	if ( $(elm).val() == val ) {
		$(elm).addClass('rfa_other');
		$(o_elm).show();
	} else {
		$(elm).removeClass('rfa_other');
		$(o_elm).hide();
	}
}