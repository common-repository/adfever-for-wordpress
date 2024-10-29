jQuery(document).ready(function(){
	jQuery("#tabs").tabs({cookie: { expires: 30 }});
	
	jQuery('input.color-picker').ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			jQuery(el).val('#'+hex);
			jQuery(el).ColorPickerHide();
		},
		onChange: function (hsb, hex, rgb, el) {
			jQuery(el).val('#'+hex);
			jQuery( '#preview-' + el.name ).css('backgroundColor', '#' + hex);
		},
		onBeforeShow: function () {
			jQuery(this).ColorPickerSetColor(this.value);
		}
	}).bind('keyup', function(){
		jQuery(this).ColorPickerSetColor(this.value);
	});
});

function validFormCategoriesSelection( error_message1, error_message2, error_message3 ) {
	var m = jQuery("input#categories_selection-sel:checked").length;
	if( m == 1 )  {
		var both = jQuery("#tree input:checked").length;
		if ( both == 0 ) {
			alert( error_message1 );
			return false;
		}
		
		var children = jQuery("#tree li li input:checked").length;
		if ( children == 0 ) {
			alert( error_message2 );
			return false;
		}
		
		var flag = false;
		jQuery("#tree label.checked").next("ul:hidden, ul").each(function(){
			if ( jQuery(this).find("input:checked").length == 0 ) {
				flag = true;
			}
		});
		
		if ( flag == true ) {
			alert( error_message3 );
			return false;
		}
	}
	
	return true;
}