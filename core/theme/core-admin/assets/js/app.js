// bootbox
bootbox.setLocale(APP_LOCALE);

// menu
$('body').on('click', '.app-menu .sidebar-parent', function(e){

	e.preventDefault();
	if($(this).hasClass('active'))
	{
		$(this).removeClass('active');
		return;
	}

	$('.app-menu .sidebar-parent').removeClass('active');
	$(this).addClass('active');
});

/*
$('body').on('click', '.app-menu .sidebar-parent', function(e){
	
	e.preventDefault();
	if($(this).hasClass('active'))
	{
		$(this).removeClass('active');
		return;
	}
	
	$('.app-menu .sidebar-parent').removeClass('active');
	$(this).addClass('active');
});
$('body').on('click', '.app-menu .sidebar-item', function(e){
	$('.app-menu .sidebar-item').removeClass('active');
	$(this).addClass('active');
	$(this).find('.sidebar-parent').addClass('active');

	$('.app-menu, .app-menu-canvas').removeClass('active');

	if($('.app-menu .input-plugin-search').val() !== '')
		$('.app-menu .input-plugin-search').val('').trigger('input');

});
*/

$('body').on('click', '.app-menu .sidebar-item', function(e){
	$('.app-menu').css('width', '50px');
	setTimeout(function(){
		$('.app-menu').css('width', 'auto');
	}, 300);
});



// misc
$('body').on('input', 'input.ucfirst', function(e){
	v = ucfirst($(this).val());
	$(this).prop('value', v);
});
$('body').on('input', 'input.upper', function(e){
	v = $(this).val();
	$(this).val(v.toUpperCase());
});
$('body').on('input', 'input.lower', function(e){
	v = $(this).val();
	$(this).val(v.toLowerCase());
});


// special links
$('body').on('click', "a[target='_popup']", function(e){
	e.preventDefault();
	e.stopPropagation();
	
	const w_width = 1100;
	const w_height = 960;
	
	const w_top =( window.top.outerHeight / 2) + (window.top.screenY) - ( w_height / 2);
	const w_left = (window.top.outerWidth / 2) + (window.top.screenX) - ( w_width / 2);
	
	attributes = "toolbar=yes,status=yes,scrollbars=yes,resizable=yes,width="+w_width+",height="+w_height+",top="+w_top+",left="+w_left;

	uri = $(this).attr('href');

	if(uri.indexOf('&_popup=1') == -1)
	{
		if(uri.indexOf('?') == -1)
			uri += '?_popup=1';
		else
			uri += '&_popup=1';
	}

	window.open(uri, "poppy_window", attributes);
});
/*
$('body').on('input', '.app-menu .input-plugin-search', function(e){

	v = trim($(this).val());

	$('.app-menu li a.sidebar-parent').removeClass('active');
	$('.app-menu li li a.sidebar-parent').removeClass('active');


	if(v === '')
	{
		$('.app-menu li li').show();
	}
	else
	{
		$('.app-menu li li').hide();

		// $('.app-menu li li[data-plugin-name*="'+v+'" i]').addClass('active').parents('li').click();

		$('.app-menu li li[data-plugin-name*="'+v+'" i]').each(function(){
			$(this).show();
			$(this).parents('li').find('.sidebar-parent').addClass('active');
		});
	}
});
*/


// select colors
$('body').on('change', '.select-color', function(){
	v = $(this).val();
	$(this).parents('.input-group').find('.input-group-text').css('background-color', v);
});




// scrolly
/*$(window).scroll(function(){
	if($(window).scrollTop() < 450)
		$('body').removeClass('scrolled');
	else
		$('body').addClass('scrolled');
});
*/

// init ****************************************************************************************************************
function app_init(force_js)
{
	$('[data-parent-root]').change();
	
	// popover
	const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
	const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
	
	$('a[target="_bootbox"]').click(function(e){
		e.preventDefault();
		e.stopPropagation();
		
		let content = $(this).find('.bootbox-content').html();
		
		bootbox.dialog({
			size: 'extra-large',
			backdrop:true,
			onEscape: true,
			closeButton: true,
			centerVertical: true,
			message: content,
			buttons : {}
		});
		
	});
	
	// tooltip
	const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
	const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
	
	// zoom
	$('img.zoomable').on('click', function(e){
		
		src = $(this).attr('src');
		if(empty(src))return;
		
		e.preventDefault();
		e.stopPropagation();
		
		src = str_replace('_thumb', '', src);
		
		let content = `<div class="text-center"><img src="${src}" style="max-width: 100%; max-height: 100%; margin: auto"></div>`;
		bootbox.dialog({
			size: 'extra-large',
			backdrop:true,
			className:'modal-image-zoomable',
			onEscape: true,
			closeButton: true,
			centerVertical: true,
			scrollable:true,
			message: content,
			buttons : {}
		});
	});
	
	// datagrid
	$('.datagrid-tooltip-btn-actions').remove();
	if($('.datagrid td.position-column').length)
		$('.datagrid tbody').sortable({
			handle: ".dragHandle",
			helper: "clone",
			scroll: true,
			forcePlaceholderSize: true,
			axis: "y",
			update: function( event, ui ) {
			
				loaderShow('.app-content-container');
				
				// fetch
				ids = "";
				$('.datagrid .tr_row').each(function(){
					ids += $(this).data('id')+";";
				});

				
				let formData = new FormData();
				formData.append("ids", ids);
				formData.append("_format", 'json');
				
				uri = "?_format=json&ajaxer=1&ajaxer-action=position&ids="+ids;
				
				fetch(uri, {method: 'get'})
					.then((response) => {
						if(response.ok)return response.json();
						throw new Error(`${response.status} : ${response.statusText}`);
					}).then((response) => {
						loaderHide();
					
					if(response.error)
					{
						throw new Error(msg);
					}
					
					
				}).catch((error)  => {
					
					error = error.toString().replace('Error:', '');
					bootbox.alert(error);
					loaderHide();
				});
				
			}
		});

	// regex
	$('input[data-inputmask]').each(function(){
		$(this).mask($(this).data('inputmask'));
	});

	// input tag manager
	inputTagManagerInit();

	// select color
	$('.select-color').change();

	// select-search
	if($('.select-search').length)
	{
		$('.select-search').each(function(){
			new Choices($(this)[0], {
				searchPlaceholderValue: _I18N_SEARCH,
				noResultsText: _I18N_COMBO_NO_RESULT,
				noChoicesText: _I18N_COMBO_NO_CHOICES,
				itemSelectText: _I18N_COMBO_PRESS_SELECT,
				uniqueItemText: _I18N_COMBO_UNIQUE_ITEM,
				customAddItemText: _I18N_COMBO_ADD_ITEM,
			});
		})
	}

	// highlighter
	$('textarea.code-highlighter').each(function(){

		$(this).before('<button onclick="CodeMirrorFullscreen(this)" title="Fullscreen" class="CodeMirror-fullscreen-button btn btn-primary" type="button"><i class="bi bi-fullscreen"></i></button>');

		CodeMirror.fromTextArea($(this)[0], {
			lineNumbers: true,
			indentUnit: 2,
			autoCloseBrackets: true,
			matchBrackets: true,
		});

	});


	// force js
	if(force_js)
	{
		htmx.process(document.body);

		blockeeEditor.init();
		
		$('.app-content script').each(function(){
			src = $(this).attr('src');

			if(typeof src !== 'undefined')
			{
				$.getScript(src);
			}
			else
			{
				if(!empty($(this).html()))
					eval($(this).html());
			}

		});

	}
	
}

app_init(false);


// swup
const linkInclude = [
	'a[href^="${window.location.origin}"]',
	'a[href^="/"]',
	'a[href^="?"]',
	'a[href^="#"]',
]
const linkExclude = [
	
	
	'[href="#"]',
	'[download]',
	'[target]',
	'[data-no-swup]',
	`[href$=".pdf"]`,
	`[href^="mailto"]`,
]

const exclude = linkExclude.map(selector => `:not(${selector})`).join('')
const linkSelector = linkInclude.map(include => `${include}${exclude}`).join(',')

const swup_options = {
	cache: false,
	linkSelector:linkSelector,
	containers: ["#swup"],
	// animationSelector: false,
	plugins: []
};
const swup = new Swup(swup_options);

swup.hooks.on('content:replace', () => {
	app_init(true);
});



