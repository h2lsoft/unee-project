$('body').on('submit', '.form-ajax', function(e){
		
		e.preventDefault();
		
		if($(this).hasClass('form-ajax-loading'))return;
		$(this).addClass('form-ajax-loading');
		
		$(this).find("label.is-invalid").removeClass('text-danger is-invalid');
		$(this).find(".is-invalid").removeClass('is-invalid');
		
		
		// fetch
		let formData = new FormData($(this)[0]);
		formData.append("_format", 'json');
		
		const uri = $(this).attr('action');
		fetch(uri, {
			method: 'post',
			body: formData,
		})
			.then((response) => {
				if(response.ok)return response.json();
				throw new Error(`${response.status} : ${response.statusText}`);
			})
			.then((response) => {
				
				if(response.error)
				{
					$(this).removeClass('form-ajax-loading');
					
					msg = response.error_stack_html;
					if(typeof $(this).data('force-error-message') != "undefined" && $(this).data('force-error-message') != '')
						msg = $(this).data('force-error-message');
					
					// add class
					for(j=0; j < response.error_fields.length; j++)
					{
						$(this).find("[name='"+response.error_fields[j]+"']").addClass('is-invalid');
						
						if($(this).find("label[for='"+response.error_fields[j]+"']").length == 1)
							$(this).find("label[for='"+response.error_fields[j]+"']").addClass('text-danger is-invalid');
					}

					$(this).scrollTop(0);
					
					throw new Error(msg);
				}
				else
				{
					$(this).find('.form-error').html("");

					// on validate js
					if(typeof $(this).data('onvalidate') != "undefined" && $(this).data('onvalidate') != '')
					{
						eval($(this).data('onvalidate'));
					}
					
					// notification
					if(typeof $(this).data('success-notification') != "undefined" && $(this).data('success-notification') != '')
					{
						if(!$('.form-ajax-notification').length)
						{
							$('body').append('<div class="form-ajax-notification"><i class="bi bi-check-lg"></i> <span class="form-ajax-notification-message"></span></div>');
						}
						
						
						msg = $(this).data('success-notification');
						if($(this).data('success-notification').toLowerCase() == 'ok')
							msg = _I18N_MESSAGE_SAVED;
						
						$('.form-ajax-notification-message').html(msg);
						$('.form-ajax-notification').fadeIn('slow', function(){
							
							setTimeout(() => {
								$('.form-ajax-notification').fadeOut('normal');
							}, 4000);
							
						});
						
						$(this).removeClass('form-ajax-loading');
						$(window).scrollTop(0);
						
						return;
					}
					
					
					// on validate message
					if(
						(typeof $(this).data('onvalidate-message') != "undefined" && $(this).data('onvalidate-message') != '') ||
						$('.form-ajax-success').length
					)
					{
						
						if(!$('.form-ajax-success').length)
						{
							message = $(this).data('onvalidate-message');
							
							str = '<div class="form-ajax-success">'+message+'</div>';
							$(this).after(str);
							$('.form-ajax-success').html(message);
						}
						else
						{
							if(typeof $(this).data('onvalidate-message') != "undefined" && $(this).data('onvalidate-message') != '')
								$('.form-ajax-success').html($(this).data('onvalidate-message'));
						}
						
						$('.form-ajax-success').show();
						$(this).remove()
						
						return;
					}
					
					
					
					
					// redirection
					if(typeof $(this).data('redirect-url') != "undefined" && $(this).data('redirect-url') != '')
						document.location.href = $(this).data('redirect-url');
					else
						history.back();
				}
			})
			.catch((error)  => {
				
				error = error.toString().replace('Error:', '');
				
				$(this).removeClass('form-ajax-loading');
				$(this).find('.form-error').html(error);
				
				$(window).scrollTop(0);
				
			});
		
});


