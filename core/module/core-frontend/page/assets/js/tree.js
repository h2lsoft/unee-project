$(function(){

    $('form[name="tree-form"]').on('submit', function(e){

        e.preventDefault();

        uri = "?"+$(this).serialize();

        if(swup)
            swup.navigate(uri);
        else
            document.location.href = uri;


        return false;
    })


    $('select[name="xcore_page_zone_id"]').on('change', function(){

        if($('select[name="language"] option').length == 1)
            $(this).parents('form').submit();

    });


    $('.tree-wrapper ul li a.page').on('click', function(e){

        e.preventDefault();

        if($(this).hasClass('active'))return;
        $('.tree-wrapper ul li a').removeClass('active');
        $(this).addClass('active');
    });


    $('.tree-wrapper ul li a.page').on('dblclick', function(){

        uri = $(this).attr('href');
        /*if(swup)
            swup.navigate(uri);
        else*/
            document.location.href = uri;
    });

    $('.tree-wrapper ul li a.page').on('contextmenu', function(e){

        e.preventDefault();
        $(this).click();

        page_url = $(this).attr('data-url');
        page_id = $(this).attr('data-id');
        page_title = $(this).text().trim();
        page_type = $(this).attr('data-type');

        paste_action = $('#treepage_contextmenu').attr('data-paste-action');
        paste_page_id = $('#treepage_contextmenu').attr('data-paste-page-id');

        if(paste_page_id == page_id || (paste_action != 'cut' && paste_action != 'copy') || !paste_page_id)
            $('#treepage_contextmenu .btn-paste').addClass('disabled');
        else
            $('#treepage_contextmenu .btn-paste').removeClass('disabled');

        if(page_type === 'dynamic')
            $('#treepage_contextmenu .btn-view').addClass('disabled');
        else
            $('#treepage_contextmenu .btn-view').removeClass('disabled');

        $('#treepage_contextmenu').data('type', page_type);
        $('#treepage_contextmenu').data('url', page_url);
        $('#treepage_contextmenu .page-id').text(page_id);
        // $('#treepage_contextmenu .page-title').text(page_title);


        /*
        pos = $(this).position();
        $('#treepage_contextmenu').css({top:pos.top + 30, left: pos.left + 10})
                                  .show();

         */

        let pos = $(this).position();
        let menu = $('#treepage_contextmenu');
        let menu_height = menu.outerHeight();
        let window_height = $(window).height();
        let top_position = $(this).position().top - 60;
        let left_position = $(this).width() + 75;

        // auto-adjust-height
        if (top_position + menu_height > window_height - 120) {
            top_position = window_height - menu_height - 120;
        }

        menu.css({
            top: top_position,
            left: left_position
        }).show();

    });

    $(window).click(function() {
        $('#treepage_contextmenu').hide();
    });


    $('#treepage_contextmenu a').on('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        $('#treepage_contextmenu').hide();

        const page_id = $('#treepage_contextmenu .page-id').text();
        const $tree_node = $('#pages a.page.active');

        // btn-edit
        if($(this).hasClass('btn-edit'))
        {
            uri = "edit/"+page_id+"/";
            if(swup)
                swup.navigate(uri);
            else
                document.location.href = uri;
        }

        // btn-delete
        if($(this).hasClass('btn-delete'))
        {
            msg = $('#pages.tree-wrapper').data('delete-message');

            content = `<div class="text-center">`;
            content += `${msg}`;
            content += `</div>`;
            content += "<div class='text-danger text-center'>"+$('#pages.tree-wrapper').data('delete-message2')+"</div>";

            bootbox.dialog({
                size: 'normal',
                className : 'modal-delete',
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
                        label: _I18N_I_CONFIRM,
                        className: 'btn-danger',
                        callback: function(){

                            $('.modal-delete .btn-danger').attr('disabled', true);
                            $('.modal-delete .btn-danger i').removeClass('d-none');

                            let formData = new FormData();
                            formData.append("_format", 'json');

                            const uri = "delete/"+page_id+"/";
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
                                        msg = msg.replace("&bull;", " - ");

                                        throw new Error(msg);
                                    }
                                    else
                                    {
                                        bootbox.hideAll();

                                        // remove node
                                        $tree_node.parents("li").remove();

                                    }


                                })
                                .catch((error)  => {

                                    error = error.toString().replace('Error:', '');
                                    alert(error);
                                    $('.modal-delete .btn-danger i').addClass('d-none');
                                    $('.modal-delete .btn-danger').attr('disabled', false);

                                });

                            return false;
                        }
                    },
                }
            });
        }

        // btn-view
        if($(this).hasClass('btn-view'))
        {
            uri = $('#treepage_contextmenu').data('url');

            if($('select[name="xcore_page_zone_id"] option:selected').data('prefix') !== '')
            {
                uri = $('select[name="xcore_page_zone_id"] option:selected').data('prefix')+'/'+uri;
            }

            windowPopup(uri);
        }

        // btn-rename
        if($(this).hasClass('btn-rename'))
        {
            let current_name = $tree_node.find('.page-name').text();
            let msg = "Rename page";

            bootbox.prompt({

                size: 'normal',
                className : 'modal-rename',
                closeButton: true,
                centerVertical: false,
                title: msg,
                onEscape: true,
                required: true,
                value: current_name,

                buttons: {
                    cancel: {
                        className: 'd-none',
                        callback: function(){}
                    },
                },

                onShow: function() {
                    const input = this.querySelector('input[type="text"]');
                    input.select();

                },

                callback: function(result){

                    if(!result)return;

                    $('.modal-rename .btn-primary').attr('disabled', true);
                    $('.modal-rename .btn-primary i').removeClass('d-none');

                    loaderShow();

                    result = result.trim();

                    let formData = new FormData();
                    formData.append("_format", 'json');
                    formData.append("id", page_id);
                    formData.append("page_name", result);

                    fetch("rename/", {method: 'post', body: formData})
                        .then((response) => {
                            if(response.ok)return response.json();
                            throw new Error(`${response.status} : ${response.statusText}`);
                        })
                        .then((response) => {

                            if(response.error)
                            {
                                msg = response.error_stack_html;
                                msg = msg.replace("&bull;", " - ");

                                throw new Error(msg);
                            }

                            $tree_node.find('.page-name').text(result);
                            bootbox.hideAll();
                            loaderHide();

                    })
                    .catch((error)  => {

                        error = error.toString().replace('Error:', '');
                        alert(error);
                        loaderHide();
                        $('.modal-rename .btn-primary i').addClass('d-none');
                        $('.modal-rename .btn-primary').attr('disabled', false);

                    });

                    return false;



                }

            });
        }

        // btn-add-page
        if($(this).hasClass('btn-add-page'))
        {
            href = $(this).attr('href')+"&xcore_page_id="+page_id;
            document.location.href = href;
        }

        // btn-cut && btn-copy
        if($(this).hasClass('btn-cut') || $(this).hasClass('btn-copy'))
        {
            action = $(this).hasClass('btn-cut') ? 'cut' : 'copy';
            $('#treepage_contextmenu').attr('data-paste-action', action);
            $('#treepage_contextmenu').attr('data-paste-page-id', page_id);
        }

        // btn-paste
        if($(this).hasClass('btn-paste-children') || $(this).hasClass('btn-paste-before') || $(this).hasClass('btn-paste-after'))
        {
            action = $('#treepage_contextmenu').attr('data-paste-action');
            action_page_id = $('#treepage_contextmenu').attr('data-paste-page-id');

            $('#treepage_contextmenu').attr('data-paste-action', '');
            $('#treepage_contextmenu').attr('data-paste-page-id', '');

            paste_type = '';
            if($(this).hasClass('btn-paste-children'))paste_type = 'children';
            if($(this).hasClass('btn-paste-before'))paste_type = 'before';
            if($(this).hasClass('btn-paste-after'))paste_type = 'after';


            let formData = new FormData();
            formData.append("_format", 'json');
            formData.append("paste_action", action);
            formData.append("paste_type", paste_type);
            formData.append("paste_source_page_id", action_page_id);
            formData.append("paste_target_page_id", page_id);


            const uri = "paste/";
            fetch(uri,
                        {method: 'post', body: formData}
            )
            .then((response) => {
                if(response.ok)return response.json();
                throw new Error(`${response.status} : ${response.statusText}`);
            })
            .then((response) => {
                if(response.error)
                {
                    msg = response.error_stack_html;
                    throw new Error(msg);
                    return;
                }
                http_refresh();
            }).catch((error)  => {
                    error = error.toString().replace('Error:', '');
                    alert(error);
            });

        }
    });




});