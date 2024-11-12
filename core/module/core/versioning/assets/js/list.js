$('body').on('click', '.btn-versioning-replace', function(e){
	e.preventDefault();

	uri = $(this).attr('href');
	loaderShow();
	$.getJSON(uri, function(response){

		loaderHide();
		if(response.error)
		{
			alert(response.error_message);
			return;
		}

		if(swup)
			swup.navigate(document.location.href);
		else
			http_refresh();


	});

});

