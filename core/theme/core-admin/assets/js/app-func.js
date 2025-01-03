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


function CodeMirrorFullscreen(obj)
{
	let $cm = $(obj).siblings('.CodeMirror')

	if ($cm[0].requestFullscreen) {
		$cm[0].requestFullscreen();
	} else if ($cm[0].mozRequestFullScreen) { // Firefox
		$cm[0].mozRequestFullScreen();
	} else if ($cm[0].webkitRequestFullscreen) { // Chrome, Safari and Opera
		$cm[0].webkitRequestFullscreen();
	} else if ($cm[0].msRequestFullscreen) { // IE/Edge
		$cm[0].msRequestFullscreen();
	}


}