$(function(){

    // scheduled
    $('input[name="status"]').on('change', function(){

        let v = $('input[name="status"]:checked').val();
        $('#publication_date').attr('disabled', true);

        if(v === 'scheduled')
        {
            $('#publication_date').attr('disabled', false);
        }

    }).change();





});


