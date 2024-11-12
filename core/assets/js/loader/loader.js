var loader_is_active = false;
function loaderShow(parent_selector='body')
{
	
	
	if(parent_selector == 'body')
	{
		$('#loader').css({
			width: '100%',
			height: '100%',
			top: 0,
			left: 0,
		});
	}
	else
	{
		pos = $(parent_selector).offset();
		$('#loader').css({
			width: $(parent_selector).width(),
			height: $(parent_selector).height(),
			top: pos.top,
			left: pos.left,
		});
	}
	
	
	$('#loader').show();
}



function loaderHide()
{
	$('#loader').hide();
}