$('body').on('change', '[data-parent-root]', function(e){
	
	name = $(this).attr('name');
	val = $(this).val();
	
	// hide all
	$('[data-parent="'+name+'"]').each(function(){
		
		if($(this).attr('data-parent-wrapper'))
			$($(this).attr('data-parent-wrapper')).hide();
		else
			$(this).hide();
	});
	
	// show
	$('[data-parent="'+name+'"][data-parent-value="'+val+'"]').each(function(){
		
		if($(this).attr('data-parent-wrapper'))
			$($(this).attr('data-parent-wrapper')).show();
		else
			$(this).show();
	});

});

$('[data-parent-root]').change();






