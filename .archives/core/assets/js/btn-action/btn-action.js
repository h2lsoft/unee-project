$("body").on("click", ".btn-action", function(e) {
	
	href = $(this).attr('href');
	if(href.indexOf('mailto:') != -1)return;
	
	e.preventDefault();
	if($(this).data('bs-toggle'))$(this).tooltip("hide");
	
	if($(this).hasClass('loading'))return;
	
	method = $(this).data('action-method') ? $(this).data('action-method') : 'GET';
	uri = $(this).data('action');
	msg = $(this).data('action-message');
	icon_ok = $(this).data('action-icon-ok');
	icon_ko = $(this).data('action-icon-ko');
	delay = $(this).data('action-delay') || 2000;
	success_callback = $(this).data('action-success-callback') || false;
	
	$(this).addClass('loading');
	fetch(uri, {
					method: method,
					header: {
								'Content-Type': 'application/json'
					}
	})
		.then((response) => {
			
			setTimeout(() => {
				$(this).removeClass('loading');
			}, delay)
			
			
			if(response.ok)return response.json();
			throw new Error(`${response.status} : ${response.statusText}`);
		})
		.then((response) => {
			
			if(response.error)
				throw new Error(response.error_stack[0]);
			
			if(response.record_id == 0)
				$(this).children('i').removeClass(icon_ok).addClass(icon_ko)
			else
				$(this).children('i').removeClass(icon_ko).addClass(icon_ok)
			
			$.snack('success', msg, delay);
			
			if(success_callback)
				eval(success_callback);
			
			
		})
		.catch((error)  => {
			
			$.snack('danger', error, delay);
			
		});
	
	
});