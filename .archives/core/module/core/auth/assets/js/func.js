function redirectToLoginPage(login_page)
{
	setTimeout(function(){
		http_redirect(login_page)
	}, 5000);
	
}