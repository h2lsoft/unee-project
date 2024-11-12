function inputTagManagerInit()
{
    if(!$('.input-tag-manager').length)return;

    // add wrapper
    tags = $('.input-tag-manager').val();
    $('.input-tag-manager').val('');

    if(!empty(tags))
    {
        tags = tags.split(',');
    }


    str = '<input type="hidden" id="xTags_raw" name="xTags_raw">';
    str += '<div class="input-tag-manager-tags">';

    for(let i=0; i < tags.length; i++)
    {
        tag = trim(tags[i]);
        first_letter = tag[0];

        if(!empty(tag))
            str += '<a data-first-letter="'+first_letter+'" href="#">'+tag+'</a>';
    }


    str += '</div>';

    $('.input-tag-manager').after(str);
    inputTagManagerRawRefresh();
}

function inputTagManagerRawRefresh()
{
    tags_ordered = [];
    tags = $('.input-tag-manager-tags a');

    for(let i=0; i < tags.length; i++)
    {
        tag = trim($(tags[i]).text());
        if(!in_array(tag, tags_ordered))
            tags_ordered[tags_ordered.length] = tag;
    }

    $('#xTags_raw').val(tags_ordered.join('[@]'));
}


function inputTagManagerTagAdd(tag)
{
    tag = trim(tag);
    if(empty(tag))return;

    tags = $('.input-tag-manager-tags a');
    tags_ordered = [tag];
    for(let i=0; i < tags.length; i++)
    {
        tag = trim($(tags[i]).text());

        if(!in_array(tag, tags_ordered))
            tags_ordered[tags_ordered.length] = tag;
    }

    tags_ordered.sort();


    str = '';
    for(let i=0; i < tags_ordered.length; i++)
    {
        tag = trim(tags_ordered[i]);
        first_letter = tag[0];

        str += '<a data-first-letter="'+first_letter+'"  href="#">'+tag+'</a>';
    }

    $('.input-tag-manager-tags').html(str);
    inputTagManagerRawRefresh();

}




$('body').on('keydown', '.input-tag-manager', function(e){

    let keycode = (e.keyCode ? e.keyCode : e.which);

    // enter
    if(keycode == 13)
    {
        if($(this).val() != '')
        {
            e.preventDefault();
            inputTagManagerTagAdd($(this).val());
            $(this).val('');
        }

    }

    // escape
    if(keycode == 27)
    {
        $(this).val('');
    }

});


$('body').on('click', '.input-tag-manager-tags a', function(e){
    e.preventDefault();
    $(this).remove();
    inputTagManagerRawRefresh();

});

