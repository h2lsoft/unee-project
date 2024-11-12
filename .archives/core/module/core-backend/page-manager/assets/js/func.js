$(function(){

    // type update
    /*
    $('input[name="type"]').on('change', function(){

        const v = $('input[name="type"]:checked').val();

        const targets = [
                                    '.row-template', '.row-resume', '.header-tContent', '.row-content', '.row-author', '.row-is_homepage', '.row-xcore_user_id', '.row-xTags',
                                    '.header-SEO', '.row-meta_title', '.row-meta_description', '.row-meta_keywords', '.row-meta_robot', '.row-meta_og_type', '.row-meta_og_image'
                                  ];

        if(v === 'url' || v === 'url external')
        {
            targets.forEach((el) => $(el).attr('disabled', true));
        }
        else
        {
            targets.forEach((el) => $(el).attr('disabled', false));
        }

    }).change();
    */


    // scheduled
    $('input[name="status"]').on('change', function(){

        let v = $(this).val();
        $('#publication_date').prop('disabled', true);

        if(v === 'scheduled')
        {
            $('#publication_date').prop('disabled', false);
        }

    }).change();



});


