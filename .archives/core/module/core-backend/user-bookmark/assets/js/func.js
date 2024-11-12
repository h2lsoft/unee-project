function userBookmarkReload()
{
	if(!$('.bookmark-header').length)return;
	
	let uri = "/"+APP_BACKEND_DIRNAME+"/user-bookmark/?_format=json";
	fetch(uri)
		
		.then((response) => {
			if(response.ok)return response.json();
			throw new Error(`${response.status} : ${response.statusText}`);
		})
		
		.then((response) => {
			
			if(response.error)
				throw new Error(response.error_stack[0]);
			
			if(response.data.length == 0)
			{
				str = $('.bookmark-header')[0].outerHTML+$('#bookmark-no-record').html();
			}
			else
			{
				str = $('.bookmark-header')[0].outerHTML;
				for(i=0; i < response.data.length; i++)
				{
					tpl = $('#bookmark-record').html();
					tpl = tpl.replace('[CONTENT]', $(".app-menu li[data-plugin-id="+response.data[i].id+"]").html());
					str += tpl;
				}
				
			}
			
			
			$('.dropdown-menu-bookmarks').html(str);
			$('.dropdown-menu-bookmarks a').removeClass('active')
			$('.dropdown-menu-bookmarks li.render a').addClass('dropdown-item')
			
		})
		
		.catch((error)  => {
			console.error(error);
		});
	
}


userBookmarkReload();