// {% if f.type == 'file-image' %}onchange="if($('#{{ f.name }}_image_preview').length)$('#{{ f.name }}_image_preview')[0].src = window.URL.createObjectURL(this.files[0])"{% endif %}
$('body').on('change', 'input[data-form-type="file-image"]', function(e) {

	const $preview_image = $('#'+$(this).attr('id')+'_image_preview');
	const file = $(this)[0].files[0];

	if(file)
	{
		const reader = new FileReader();

		reader.onload = function(e) {
			$preview_image.attr('src', e.target.result);
		};

		reader.readAsDataURL(file);
	}



});

$('body').on('dragover', '.file-image--placeholder', function(e) {
	e.preventDefault();
	$(this).addClass('drag-over');
});

$('body').on('dragleave', '.file-image--placeholder', function(e) {
	$(this).removeClass('drag-over');
});

$('body').on('drop', '.file-image--placeholder', function(e) {

	e.preventDefault();


	const file = e.originalEvent.dataTransfer.files[0];
	if(file && file.type.match('image.*'))
	{
		const input_id = $(this).attr('id').replace('__placeholder', '');
		const input = $('#' + input_id)[0];

		input.files = e.originalEvent.dataTransfer.files;

		if(input.files && input.files[0])
		{
			const $preview_image = $('#' + input_id + '_image_preview');
			$preview_image.attr('src', URL.createObjectURL(input.files[0]));
		}

	}


});
// {% if f.type == 'file-image' %}onchange="if($('#{{ f.name }}_image_preview').length)$('#{{ f.name }}_image_preview')[0].src = window.URL.createObjectURL(this.files[0])"{% endif %}
$('body').on('change', 'input[data-form-type="file-image"]', function(e) {

	const $preview_image = $('#'+$(this).attr('id')+'_image_preview');
	const file = $(this)[0].files[0];

	if(file)
	{
		const reader = new FileReader();

		reader.onload = function(e) {
			$preview_image.attr('src', e.target.result);
		};

		reader.readAsDataURL(file);
	}



});

$('body').on('dragover', '.file-image--placeholder', function(e) {
	e.preventDefault();
	$(this).addClass('drag-over');
});

$('body').on('dragleave', '.file-image--placeholder', function(e) {
	$(this).removeClass('drag-over');
});

$('body').on('drop', '.file-image--placeholder', function(e) {

	e.preventDefault();


	const file = e.originalEvent.dataTransfer.files[0];
	if(file && file.type.match('image.*'))
	{
		const input_id = $(this).attr('id').replace('__placeholder', '');
		const input = $('#' + input_id)[0];

		input.files = e.originalEvent.dataTransfer.files;

		if(input.files && input.files[0])
		{
			const $preview_image = $('#' + input_id + '_image_preview');
			$preview_image.attr('src', URL.createObjectURL(input.files[0]));
		}

	}


});