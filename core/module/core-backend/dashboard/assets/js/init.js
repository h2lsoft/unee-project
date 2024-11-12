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
		$('.app-header .dropdown-menu-bookmarks .render a').each(function(index){

			// $(this).prepend('<span class="dragHandle"><i class="bi bi-three-dots-vertical"></i></span>');
			links += $(this)[0].outerHTML;
		});
		
		links = str_replace('sidebar-item dropdown-item', 'btn-icon border me-3 px-2 py-1 rounded-1', links);
		links = str_replace('', '', links);

		links = $(links).prepend('<span class="dragHandle"><i class="bi bi-three-dots-vertical"></i></span>');

		$('.widget-bookmarks .widget-bookmarks-wrapper').html(links);
		$('.widget-bookmarks .widget-bookmarks-wrapper').removeClass('d-none');


		setTimeout(function(){
			$('.widget-bookmarks-wrapper').sortable({
				handle: ".dragHandle",
				items: ".btn-icon",
				helper: "clone",
				scroll: true,
				forcePlaceholderSize: true,
				axis: "x",
				grid: [ 80, 10 ],
				placeholder: "ui-state-highlight",
				update: function( event, ui ) {

					// fetch
					ids = "";
					$('.widget-bookmarks-wrapper .btn-icon').each(function(){
						ids += $(this).data('plugin-id')+";";
					});

					let formData = new FormData();
					formData.append("ids", ids);
					formData.append("_format", 'json');

					uri = "/"+APP_BACKEND_DIRNAME+"/user-bookmark/reorder/";
					fetch(uri, {method: 'post', body: formData})
						.then((response) => {
							if(response.ok)return response.json();
							throw new Error(`${response.status} : ${response.statusText}`);
						}).then((response) => {

						if(response.error)
						{
							throw new Error(msg);
						}

						// userBookmarkReload();


					}).catch((error)  => {

						error = error.toString().replace('Error:', '');
						bootbox.alert(error);
						loaderHide();
					});






				}
			}).disableSelection();
		}, 500);




	}
	
	
	
}, 250);




	


