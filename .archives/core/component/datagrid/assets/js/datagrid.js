// input-page
$('body').on('keypress', '.datagrid .input-page', function(e){
	
	if(e.which != 13)return;
	
	// enter
	v = $(this).val();
	
	if(v == '' || isNaN(v) || v < 1 || v > $(this).data('page-max') || v == $(this).data('value-original'))
	{
		$(this).val($(this).data('value-original'));
		return;
	}
	
	
	loc = $(this).data('location');
	loc += v;
	
	if(swup)
	{
		swup.navigate(loc);
	}
	else
	{
		document.location.href = loc;
	}

});

// pager-records-by-page-select
$('body').on('change', '.datagrid .pager-records-by-page-select', function(e){
	loc = $(this).data('location');
	loc += $(this).val();
	
	if(swup)
	{
		swup.navigate(loc);
	}
	else
	{
		document.location.href = loc;
	}
	
	
});

// delete
$('body').on('click', '.datagrid .btn-action-delete', function(e){
	
	e.preventDefault();
	e.stopPropagation();
	
	msg = $(this).parents('.datagrid').data('datagrid-delete-message');
	msg = msg.replace('[ID]', $(this).parents('tr').data('id'));
	
	content = `<p class="text-center">`;
	content += `${msg}`;
	content += `</p>`;
	content += "<div class='text-danger datagrid-delete-error text-center'>"+$(this).parents('.datagrid').data('datagrid-delete-message-warning')+"</div>";
	
	uri = $(this).attr('href');
	
	bootbox.dialog({
		className : 'x-datatable-delete-modal',
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
				label: '<i class="spinner spinner-border spinner-border-sm text-white d-none"></i> '+$(this).parents('.datagrid').data('datagrid-delete-label-validate'),
				className: 'btn-danger',
				callback: function(){
					
					$('.x-datatable-delete-modal .btn-danger').attr('disabled', true);
					$('.x-datatable-delete-modal .btn-danger i').removeClass('d-none');
					
					let formData = new FormData();
					formData.append("_format", 'json');
					
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
						$('.x-datatable-delete-modal .datagrid-delete-error').html(error);
						$('.x-datatable-delete-modal .btn-danger i').addClass('d-none');
						$('.x-datatable-delete-modal .btn-danger').attr('disabled', false);
						
					});
					
					return false;
				}
			},
		}
	});
	
});

// search> event
$('body').on('keypress', '.datagrid-search-offcanvas .datagrid-search-input__value', function(e){
	
	keycode = (e.keyCode ? e.keyCode : e.which);
	if(keycode == '13')
	{
		$('.datagrid-search-offcanvas .btn-search-execute').click();
	}

});

// search> init
$('body').on('click', '.datagrid .btn-search', function(e){
	
	e.preventDefault();

	$('.datagrid_search_fields__wrapper').html("");
	tpl = $('#tpl__datagrid_search_fields').html();
	
	// init from search
	// params = new URLSearchParams(location.search);
	params = new URLSearchParams(QUERY_STRING);
	searches = params.getAll('search[]');
	
	for(z=0; z < searches.length; z++)
	{
		ps = searches[z].split('||');
		$('.datagrid-search-offcanvas .btn-search-condition-add').click();
		$('.datagrid-search-offcanvas .datagrid_search_field:last .datagrid-search-input__name').val(ps[0]).change();
		
		f_type = $('.datagrid-search-offcanvas .datagrid_search_field:last .datagrid-search-input__name option:selected').data('type');
		
		if(ps.length == 2)
		{
			if(ps[1] == 'empty' || ps[1] == '!empty')
			{
				$('.datagrid-search-offcanvas .datagrid_search_field:last .datagrid-search-input__operator').val(ps[1]).change();
			}
			else
			{
				$('.datagrid-search-offcanvas .datagrid_search_field:last .datagrid-search-input__value__'+f_type).val(ps[1]);
			}
		}
		else if(ps.length == 3)
		{
			$('.datagrid-search-offcanvas .datagrid_search_field:last .datagrid-search-input__operator').val(ps[1]).change();
			$('.datagrid-search-offcanvas .datagrid_search_field:last .datagrid-search-input__value__'+f_type).val(ps[2]);
		}
	}

	if(!searches.length)
		$('.datagrid-search-offcanvas .btn-search-save').hide();
	else
		$('.datagrid-search-offcanvas .btn-search-save').show();
	
	
});

// search> condition> add
$('body').on('click', '.datagrid-search-offcanvas .btn-search-condition-add', function(e){
	
	e.preventDefault();
	tpl = $('#tpl__datagrid_search_fields').html();
	$('.datagrid_search_fields__wrapper').append(tpl);
	$('.datagrid_search_fields__wrapper .datagrid-search-input__name option').each(function(){
		$(this).text(ucfirst($(this).text()));
	});
	$('.datagrid_search_fields__wrapper .datagrid_search_field:last .datagrid-search-input__name').change().focus();
	
	// multiple select
	// $('.datagrid_search_fields__wrapper .datagrid_search_field:last select[multiple]').multipleSelect();
	
	
	$('.datagrid-search-offcanvas .btn-search-save').show();
	
	
});

// search> condition> remove
$('body').on('click', '.datagrid-search-offcanvas .btn-search-condition-btn-delete', function(e){
	e.preventDefault();
	$(this).parents('.datagrid_search_field').remove();
	
	if(!$('.datagrid-search-offcanvas .datagrid_search_field').length)
		$('.datagrid-search-offcanvas .btn-search-save').hide();
	else
		$('.datagrid-search-offcanvas .btn-search-save').show();
	
});

// search> condition> input> change
$('body').on('change', '.datagrid-search-offcanvas .datagrid-search-input__name', function(){
	
	f_type = $(this).find(':selected').attr('data-type');
	f_operator_default = $(this).find(':selected').attr('data-operator-default');
	
	// operator
	$(this).parents('.datagrid_search_field').find('.datagrid-search-input__operator option').hide().attr('data-visible', 'hidden');
	$(this).parents('.datagrid_search_field').find(".datagrid-search-input__operator option[data-filter*='"+f_type+"']").show().attr('data-visible', 'visible');
	
	
	
	// auto select default option
	if(f_operator_default == '')
	{
		// auto select first option visible
		$(this).parents('.datagrid_search_field').find('.datagrid-search-input__operator option[data-visible="visible"]:eq(0)').prop('selected', 'selected').change();
	}
	else
	{
		$(this).parents('.datagrid_search_field').find('.datagrid-search-input__operator option[data-visible="visible"][value="'+f_operator_default+'"]').prop('selected', 'selected').change();
	}
	
	
	// value
	$(this).parents('.datagrid_search_field').find('.datagrid-search-input__value').hide();
	$(this).parents('.datagrid_search_field').find('.datagrid-search-input__value__'+f_type).show();
	
	// select
	if(f_type == 'select')
	{
		options = $(this).find(':selected').attr('data-options');
		options = eval(options);
		
		str = "";
		for(i=0; i < options.length; i++)
		{
			str += "<option value=\""+options[i].value+"\">"+options[i].label+"</option>\n";
		}
		
		$(this).parents('.datagrid_search_field').find('.datagrid-search-input__value__'+f_type).html(str);
	}
	
	
});

// search> condition> operator
$('body').on('change', '.datagrid-search-offcanvas .datagrid-search-input__operator', function(){

	v = $(this).val();
	if(v == 'empty'  || v == '!empty')
		disabled = true;
	else
		disabled = false;
	
	$(this).parents('.datagrid_search_field').find('.datagrid-search-input__value').attr('disabled', disabled);
	
});

// search> save
$('body').on('click', '.datagrid-search-offcanvas .btn-search-save', function(e){
	
	e.preventDefault();
	
	if(!$('.datagrid-search-offcanvas .datagrid_search_field').length)return;
	
	msg = $('.datagrid-search-offcanvas').data('search-message-save');
	bootbox.prompt(msg, function(result){
		if(!result || empty(result))return;
		if(!$('.datagrid-search-offcanvas .datagrid_search_field').length)return;
		
		loaderShow();
		
		let url = "";
		$('.datagrid-search-offcanvas .datagrid_search_field').each(function(){
			
			name = $(this).find('.datagrid-search-input__name').val();
			operator = $(this).find('.datagrid-search-input__operator').val();
			value = $(this).find('.datagrid-search-input__value:visible').val();
			
			str = name;
			if(operator == 'eq' || operator == '!eq')
			{
				str += "||"+value;
			}
			else
			{
				if(operator == 'empty' || operator == '!empty')
					str += "||"+operator;
				else
					str += "||"+operator+"||"+value;
			}
			
			if(!empty(url))url += "&";
			url += 'search[]='+str;
		});
		
		const uri = window.location.pathname+"?"+url;
		
		let formData = new FormData();
		formData.append("_format", 'json');
		formData.append("xcore_plugin_id", $('.app-plugin-title').data('id'));
		formData.append("name", result);
		formData.append("url", uri);
		
		fetch("/"+APP_BACKEND_DIRNAME+"/user-search/add/", {
			method: 'post',
			body: formData,
		}).then((response) => {
			if(response.ok)return response.json();
			throw new Error(`${response.status} : ${response.statusText}`);
		}).then((response) => {
			
			if(response.error)
			{
				msg = response.error_stack_html;
				throw new Error(msg);
			}
			
			loaderHide();
			
			$('.datagrid-search-offcanvas .btn-search-execute').click();
			
			
		}).catch((error)  => {
			loaderHide();
			error = error.toString().replace('Error:', '');
			bootbox.alert(error);
		});
		
		
	});
	
	
	
});

$('body').on('click', '.datagrid .btn-search-user', function(e){
	
	e.preventDefault();
	
	$('.dropdown-menu-user-search .li-dropdown-item').remove();
	$('.dropdown-menu-user-search .dropdown-item-loading').show();
	$('.dropdown-menu-user-search .dropdown-item-no-record').hide();
	
	
	plugin_id = $('.app-plugin-title').data('id');
	fetch("/"+APP_BACKEND_DIRNAME+"/user-search/"+plugin_id+"/", {
		method: 'get'
	}).then((response) => {
		if(response.ok)return response.json();
		throw new Error(`${response.status} : ${response.statusText}`);
	}).then((response) => {
		
		if(response.error)
		{
			msg = "Unknown error";
			throw new Error(msg);
		}
		
		$('.dropdown-menu-user-search .dropdown-item-loading').hide();
		
		let searches = response.searches;
		if(!searches.length)
		{
			$('.dropdown-menu-user-search .dropdown-item-no-record').show();
		}
		else
		{
			let tpl = $('#template_dropdown-menu-user-search-item').html();
			let str = '';
			
			for(let i=0; i < searches.length; i++)
			{
				let tmp = tpl.toString();
				tmp = str_replace('[id]', searches[i].id, tmp);
				tmp = str_replace('[name]', searches[i].name, tmp);
				tmp = str_replace('[url]', searches[i].url, tmp);
				str += tmp;
			}
			
			$('.dropdown-menu-user-search ').append(str);
			
		}
	}).catch((error)  => {
		error = error.toString().replace('Error:', '');
		console.error(error);
	});
	
	
});

$('body').on('click', '.dropdown-menu-user-search .dropdown-item-action-delete', function(e){
	
	e.preventDefault();
	e.stopPropagation();
	
	
	fetch($(this).attr('href'), {
		method: 'delete'
	}).then((response) => {
		
		if(response.ok)return response.json();
		throw new Error(`${response.status} : ${response.statusText}`);
		
	}).then((response) => {
		
		if(response.error)
		{
			msg = "Unknown error";
			throw new Error(msg);
		}
		
		$('.datagrid .btn-search-user').click();
	
	}).catch((error)  => {
		error = error.toString().replace('Error:', '');
		console.error(error);
	});
	
	
});


// search> execute
$('body').on('click', '.datagrid-search-offcanvas .btn-search-execute', function(e){
	e.preventDefault();
	
	// search error
	if($('.datagrid-search-offcanvas .datagrid_search_field').length)
	{
		error = false;
		$('.datagrid-search-offcanvas .datagrid_search_field').each(function(){
			
			f_type = $(this).find('.datagrid-search-input__name option:selected').attr('data-type');
			
			v = $(this).find('.datagrid-search-input__value__'+f_type).val();
			if(v == '')
			{
				$(this).find('.datagrid-search-input__value:visible').focus();
				error = true;
				return false;
			}
		});
		if(error)return;
	}
	
	
	params = new URLSearchParams(location.search);
	params.delete('search[]');
	
	new_searches = "";
	if($('.datagrid-search-offcanvas .datagrid_search_field').length)
	{
		$('.datagrid-search-offcanvas .datagrid_search_field').each(function(){
			
			name = $(this).find('.datagrid-search-input__name').val();
			operator = $(this).find('.datagrid-search-input__operator').val();
			value = $(this).find('.datagrid-search-input__value:visible').val();
			
			str = name;
			if(operator == 'eq' || operator == '!eq')
			{
				str += "||"+value;
			}
			else
			{
				if(operator == 'empty' || operator == '!empty')
					str += "||"+operator;
				else
					str += "||"+operator+"||"+value;
			}
			
			params.append('search[]', str);
		});
	}
	
	params.sort();
	
	uri = window.location.pathname;
	query = params.toString();
	query = decodeURIComponent(query);
	
	if(query != '')
		uri += "?"+query;
	
	if(swup)
	{
		swup.navigate(uri);
	}
	else
	{
		document.location.href = uri;
	}
	
	
});

// batch> toggle
$('body').on('click', '.datagrid-batch-checkbox-all', function(){
	$('.datagrid-batch-checkbox').prop('checked', $(this).is(':checked'));
	$('.datagrid-batch-checkbox').change();
	
});

$('body').on('change', '.datagrid-batch-checkbox', function(){
	
	disabled_state = ($('.datagrid-batch-checkbox:checked').length == 0) ? true: false
	$('.datagrid-batch-action-select').prop('disabled', disabled_state);
	$('.btn-batch-execute').prop('disabled', disabled_state);
});

// batch> execute
$('body').on('click', '.btn-batch-execute', function(){

	if($('.datagrid-batch-action-select').val() == '')
		return;
	
	const js_func = $('.datagrid-batch-action-select').val();
	$('.datagrid-batch-action-select').val('');
	eval(js_func+"()");
	
});


// @todo> search> condition> drag



function datagridBatchGetIds()
{
	let ids = "";
	$('.datagrid-batch-checkbox:checked').each(function(){
		if(ids != '')ids += ';';
		ids += $(this).val();
	});
	
	return ids;
}


