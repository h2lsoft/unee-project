function userBadgeUpdate()
{
	uri = "/"+APP_BACKEND_DIRNAME+"/my-info/";
	$.getJSON(uri, function(resp){
		
		if(resp.error)return;
		
		$(".app-header-badge-user .img-avatar").attr('src', resp.data.avatar);
		$(".app-header-badge-user .user-lastname").text(resp.data.lastname);
		$(".app-header-badge-user .user-firstname").text(resp.data.firstname);
		
		
	});
}