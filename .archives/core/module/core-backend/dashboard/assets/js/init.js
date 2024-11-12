setTimeout(function(){
	
	$('.widget-bookmarks .no-record').addClass('d-none');
	$('.widget-bookmarks .widget-bookmarks-wrapper').addClass('d-none');
	
	if(!$('.app-header .dropdown-menu-bookmarks .render a').length)
	{
		$('.widget-bookmarks .no-record').removeClass('d-none');
	}
	else
	{
		// add link
		let links = "";
		$('.app-header .dropdown-menu-bookmarks .render a').each(function(){
			links += $(this)[0].outerHTML;
		});
		
		links = str_replace('sidebar-item dropdown-item', 'btn-icon border me-3 px-2 py-1 rounded-1', links);
		
		
		$('.widget-bookmarks .widget-bookmarks-wrapper').html(links);
		$('.widget-bookmarks .widget-bookmarks-wrapper').removeClass('d-none');
	}
	
	
	
}, 300);




	


