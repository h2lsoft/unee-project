function logPurge()
{
	msg = $('.nav-btn-action-purge').attr('data-message');
	
	content = `<p class="text-center">`;
	content += `${msg}`;
	content += `</p>`;
	content += "<div class='text-danger datagrid-delete-error text-center'></div>";
	
	bootbox.dialog({
		className : 'x-datatable-purge-modal',
		size: 'normal',
		closeButton: true,
		centerVertical: false,
		message: content,
		buttons : {
			cancel: {
				label: _I18N_CANCEL,
				className: '',
				callback: function(){}
			},
			ok: {
				label: '<i class="spinner spinner-border spinner-border-sm text-white d-none"></i> '+_I18N_I_CONFIRM,
				className: 'btn-danger',
				callback: function(){
					
					$('.x-datatable-purge-modal .btn-danger').attr('disabled', true);
					$('.x-datatable-purge-modal .btn-danger i').removeClass('d-none');
					
					let formData = new FormData();
					formData.append("_format", 'json');
					
					const uri = "/"+APP_BACKEND_DIRNAME+"/log/purge/";
					fetch(uri, {
						method: 'delete',
						body: formData,
					})
						.then((response) => {
							if(response.ok)return response.json();
							throw new Error(`${response.status} : ${response.statusText}`);
						})
						.then((response) => {
							
							if(response.error)
							{
								msg = response.error_stack_html;
								throw new Error(msg);
							}
							else
							{
								bootbox.hideAll();
								
								if(swup)
								{
									swup.navigate(document.location.href);
								}
								else
								{
									http_refresh();
								}
								
							}
							
							
						})
						.catch((error)  => {
							
							error = error.toString().replace('Error:', '');
							$('.x-datatable-purge-modal .datagrid-delete-error').html(error);
							$('.x-datatable-purge-modal .btn-danger i').addClass('d-none');
							$('.x-datatable-purge-modal .btn-danger').attr('disabled', false);
							
						});
					
					return false;
				}
			},
		}
	});
}
