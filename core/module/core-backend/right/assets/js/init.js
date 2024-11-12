$('#xcore_group_id').on('change', function(){
	
	let uri = "?";
	if($(this).val() != '')
		uri += "xcore_group_id="+$(this).val()
	
	swup.navigate(uri);
});


$('.form-check-input-plugin').on('click', function(){
	state = $(this).prop('checked');
	plugin_id = $(this).val();
	
	$('.form-check-input-plugin-action[data-plugin-id="'+plugin_id+'"]').prop('checked', state);
});



$('.form-check-input-menu').on('click', function(){
	state = $(this).prop('checked');
	menu = $(this).attr('data-name');
	
	$('.form-check-input[data-menu="'+menu+'"]').prop('checked', state);
	
});
