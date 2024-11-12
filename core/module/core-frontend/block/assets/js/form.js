$('#type').on('change', function(){

    v = $(this).val();

    $('.row-file_path, .row-content').hide();

    if(v === 'content')$('.row-content').show();
    if(v === 'file')$('.row-file_path').show();


}).change();

