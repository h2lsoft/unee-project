function ufmUpload()
{
    let contents = `<form action="upload/" class="dropzone" id="upload_zone" method="post">
                                <input type="file" name="file">
                            </form>`;

    const dialog = bootbox.dialog({

        className: 'ufm-modal-upload',
        title: 'Upload',
        message: contents,
        size: 'large',
        onEscape: true,
        backdrop: true,

        buttons: {
            finish: {
                label: _I18N_FINISH,
                className: "btn-success",
                callback: function() {

                    swup ? swup.navigate(document.location.href) : http_refresh();
                    return true;
                }
            }
        }

    });


    dialog.init(function(){

        const myDropzone = $(".ufm-modal-upload #upload_zone").dropzone({

            method: 'post',
            dictDefaultMessage: $('.app--file-manager').data('i18n-dropzone-default-message'),
            acceptedFiles: $('.app--file-manager').data('dropzone-exts-allowed'),


            init:function(){
                this.on('sending', function(file, xhr, formData){

                    formData.append("post", true);

                    const path = $('.app--file-manager').data('path');
                    formData.append("path", path);

                });
            },

            complete: function(file, xhr, formData){

                let response = file.xhr.responseText;

                if(typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error("Error server:", response);
                        alert("Upload error: " + response);
                        return;
                    }
                }

                if(response.error)
                {
                    bootbox.alert({title: 'Error', message:"- " +response.error_stack.join('\n- '), backdrop:false});
                    this.removeFile(file);
                }
                else
                {

                }
            }


        });




    });

}

function ufmUploadImage()
{
    const dialog_uploader = bootbox.dialog({
        title: `Upload Image`,
        className: `modal-image-uploader`,
        size:'large',
        message: `<form>

                <div class="row mb-3">
                    <div class="col">
                        <input type="url" class="form-control" name="url" required placeholder="Enter your url" onfocus="this.select()">
                    </div>                                                            
                </div>
                                                               
                <div class="form-group">                    
                    <div id="imagePreview" style="width: 100%; height: 300px; border: 1px solid #ccc; display: flex; justify-content: center; align-items: center;">
                        <p>Preview image</p>
                    </div>
                </div>
              </form>`,
        buttons: {
            confirm: {
                label: _I18N_SUBMIT,
                className: `btn-primary`,
                callback: function() {
                    const path = $('.app--file-manager').data('path');
                    let url = $('.modal-image-uploader input[name="url"]').val();

                    const formData = new FormData();
                    formData.append('path', path);
                    formData.append('url', url);

                    loaderShow();

                    $.ajax({
                        url: "upload-image/",
                        type: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(response) {

                            loaderHide();

                            if (response.error) {
                                bootbox.alert({
                                    title: 'Error',
                                    message: "- " + response.error_stack.join("\n- "),
                                    backdrop: false
                                });
                                return;
                            }

                            swup ? swup.navigate(document.location.href) : http_refresh();

                            bootbox.hideAll();

                            return true;
                        },
                        error: function(xhr, status, error) {

                            loaderHide();
                            bootbox.alert({
                                title: 'Error',
                                message: "An unexpected error occurred: " + error,
                                backdrop: false
                            });
                        },
                        always: function(){
                            loaderHide()
                        }
                    });



                    return false;
                }
            }
        }
    });

    $(document).on(`shown.bs.modal`, `.bootbox.modal-image-uploader`, function() {
        $(`.modal-image-uploader input[name="url"]`).on("change", function(e) {
            let image_url = $(this).val();
            $(`.modal-image-uploader #imagePreview`).html(`<img src="${image_url}" style="max-width: 100%; max-height: 100%;">`);
        });
    });

}

function ufmUploadImagePNG()
{
    return;
    bootbox.dialog({
        title: `Upload Image`,
        className: `modal-image-uploader`,
        size:'large',
        message: `<form>
                <div class="form-group mb-2">
                    <label for="name">Name (no extension)</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="form-group">                    
                    <div id="imagePreview" style="width: 100%; height: 300px; border: 1px solid #ccc; display: flex; justify-content: center; align-items: center;">
                        <p>Paste your image here <kbd>CTRL + V</kbd></p>
                    </div>
                </div>
              </form>`,
        buttons: {

            confirm: {
                label: _I18N_SUBMIT,
                className: `btn-primary`,
                callback: function() {
                    let name = $('.modal-image-uploader input[name="name"]').val();
                    let imageData = $(`#imagePreview img`).attr('src');
                    const path = $('.app--file-manager').data('path');

                    const formData = new FormData();
                    formData.append('path', path);
                    formData.append('file_name', name);
                    formData.append('file', imageData);

                    $.ajax({
                        url: "upload-image/",
                        type: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(response) {
                            if (response.error) {
                                bootbox.alert({
                                    title: 'Error',
                                    message: "- " + response.error_stack.join("\n- "),
                                    backdrop: false
                                });
                                return;
                            }

                            swup ? swup.navigate(document.location.href) : http_refresh();
                            return true;
                        },
                        error: function(xhr, status, error) {
                            bootbox.alert({
                                title: 'Error',
                                message: "An unexpected error occurred: " + error,
                                backdrop: false
                            });
                        }
                    });



                    return false;
                }
            }
        }
    });

    $(document).on(`shown.bs.modal`, `.bootbox`, function() {
        $(`#imagePreview`).on(`paste`, function(e) {
            e.preventDefault();
            var items = (e.clipboardData || e.originalEvent.clipboardData).items;

            for (var index in items) {
                var item = items[index];
                if (item.kind === `file`) {
                    var blob = item.getAsFile();
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        $(`#imagePreview`).html(`<img src="${event.target.result}" style="max-width: 100%; max-height: 100%;">`);
                    };
                    reader.readAsDataURL(blob);
                }
            }
        });
    });
}

function ufmFileSort(node, col, direction='asc')
{
    const query_xsort = $('.app--file-manager').data('query-xsort');

    let uri = '?'+query_xsort;
    if(!$(node).find('i').length)
    {
        col_direction = col+'-asc';
    }
    else
    {
        if($(node).find('i.bi-arrow-down').length)
            col_direction = col+'-asc';
        else
            col_direction = col+'-desc';
    }

    uri += '&sort='+col_direction;

    swup ? swup.navigate(uri) : http_redirect(uri);

}

function ufmSearch(obj) {

    v = $(obj).val();
    if(v === '')
    {
        $('.files tbody tr').show();
    }
    else
    {
        $('.files tbody tr').hide();
        $('.files tbody tr').each(function(){
            if($(this).data('filename').indexOf(v) !== -1)
                $(this).show();
        });
    }



}


function ufmItemSelect(node, target)
{
    const file_url = $(node).parents('tr').data('link-relative');

    // detect inside iframe or normal window
    const in_iframe = (window.self !== window.top);
    if(in_iframe)
    {
        if(target[0] === '@')
            window.parent.document.getElementById(target.replace('@', '')).src = file_url;
        else
            window.parent.document.getElementsByName(target)[0].value = file_url;


        window.parent.postMessage('closeFileManager', '*');
    }
    else
    {
        if(target[0] === '@')
            opener.document.getElementById(target.replace('@', '')).src = file_url;
        else
            opener.document.getElementsByName(target)[0].value = file_url;
        window.close();
    }
}

function ufmIframeClose()
{
    const in_iframe = (window.self !== window.top);
    if(in_iframe)
    {
        window.parent.postMessage('closeFileManager', '*');
    }
}



function ufmGetPath() {
    return $('#ufm_path').val();
}

function ufmItemRename(obj) {
    event.preventDefault();

    let $target = $(obj).parents('tr').find('.td--name');
    let path = ufmGetPath();
    let filename = $(obj).parents('tr').data('filename-noext');
    let filename_original = $(obj).parents('tr').data('filename');
    let type = $(obj).parents('tr').data('type');

    bootbox.prompt({
        title: _I18N_CONFIRMATION,
        value: filename,
        required: true,
        callback: function (result) {

            if (!result || result === filename) return;

            loaderShow();

            const formData = new FormData();
            formData.append('path', path);
            formData.append('type', type);
            formData.append('new_filename', result);
            formData.append('filename_original', filename_original);

            fetch('rename/', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(response => {

                    if (response.error) {
                        alert("- " + response.error_stack.join('\n - '));
                        return;
                    }

                    bootbox.hideAll();
                    swup ? swup.navigate(document.location.href) : http_refresh();


                })
                .catch(error => console.error('Fetch query error:', error))
                .finally(() => loaderHide());

            return false;

        },
        onShown: function (e) {
            $(e.target).find('input[type="text"]').focus().select();
        }
    });

}

function ufmItemDelete(obj) {
    let path = ufmGetPath();
    let filename = $(obj).parents('tr').data('filename');
    let type = $(obj).parents('tr').data('type');

    bootbox.confirm({
        title: _I18N_CONFIRMATION,
        message: (type === 'folder') ? _I18N_CONFIRMATION_DELETE_FOLDER : _I18N_CONFIRMATION_DELETE_FILE,
        buttons: {
            confirm: {
                className: 'btn-danger'
            }
        },

        callback: function (result) {

            if (!result) return;

            loaderShow();

            const formData = new FormData();
            formData.append('path', path);
            formData.append('filename', filename);
            formData.append('type', type);

            fetch('unlink/', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(response => {

                    if (response.error) {
                        alert("- " + response.error_stack.join('\n - '));
                        return;
                    }

                    bootbox.hideAll();
                    swup ? swup.navigate(document.location.href) : http_refresh();


                })
                .catch(error => console.error('Fetch query error:', error))
                .finally(() => loaderHide());


            return false;

        }
    });

}

function ufmFolderCreate() {
    let path = ufmGetPath();

    bootbox.prompt({
        title: _I18N_ENTER_NAME,
        required: true,
        callback: function (result) {

            if (!result) return;

            loaderShow();

            const formData = new FormData();
            formData.append('path', path);
            formData.append('folder_name', result);

            fetch('dir/', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(response => {

                    if (response.error) {
                        alert("- " + response.error_stack.join('\n - '));
                        return;
                    }

                    bootbox.hideAll();


                    const query_xpath = $('.app--file-manager').data('query-xpath');
                    let uri = '?'+query_xpath+'&path=';
                    let path = $('.app--file-manager').data('path');

                    if(path === '')
                    {
                        path = result;
                    }
                    else
                    {
                        path += "/"+result;
                    }

                    uri += path;

                    swup ? swup.navigate(uri) : http_redirect(uri);
                })
                .catch(error => console.error('Fetch query error:', error))
                .finally(() => loaderHide());

            return false;

        },
        onShown: function (e) {
            $(e.target).find('input[type="text"]').focus().select();
        }
    });
}

function ufmFileInfoViewer(obj) {

    $parent = $(obj).parents('tr');
    icon_uri = $parent.data('icon-url');
    icon_type = $parent.data('type');
    filename = $parent.data('filename');

    file_ext = $parent.data('extension');
    file_mime = $parent.data('mime');
    file_size = $parent.data('size');
    file_details = $parent.data('details');
    file_link = $parent.data('link');
    file_link_relative = $parent.data('link-relative');

    // default
    m_size = 'normal';
    contents = `<div class="wrapper wrapper--${icon_type}">
                    <img src="${icon_uri}" class="file-icon">
                </div>`;

    // video
    video_exts = ['mp4', 'webm', 'avi'];
    if(video_exts.indexOf(file_ext) !== -1)
    {
        m_size = 'large';
        contents = `<div class="wrapper wrapper--video">
                        <video src="${file_link}" controls preload="metadata" />
                    </div>`;
    }

    // audio
    audio_exts = ['mp3', 'odd', 'wav'];
    if(audio_exts.indexOf(file_ext) !== -1)
    contents += `<audio src="${file_link}" controls preload="metadata"></audio>`;


    // info
    contents += `<table class="table-info">
                 <tr>
                    <th>${_I18N_FILE}</th>
                    <td>${filename}</td>
                 </tr>                
                 <tr>
                    <th>${_I18N_TYPE}</th>
                    <td>${file_mime}</td>
                 </tr>                 
                 <tr>
                    <th>${_I18N_SIZE}</th>
                    <td>${file_size}</td>
                 </tr>                 
                 <tr>
                    <th>${_I18N_DETAILS}</th>
                    <td class="td--details">${file_details}</td>
                 </tr>
                 <tr style="display: none">
                    <th>Link</th>
                    <td>
                        <input class="form-control input-file-link" type="text" value="${file_link_relative}">                         
                        <input class="form-control input-file-link-absolute" type="hidden" value="${file_link}">                                                                                                          
                    </td>
                 </tr>
                 </table>`;

    let buttons = {

        copy: {
            label: '<i class="bi bi-copy me-1"></i>  '+_I18N_COPY_LINK,
            className: 'btn-light',
            callback: function () {
                const link = $('.ufm-fileinfo-modal .input-file-link').val();
                navigator.clipboard.writeText(link);
                return false;
            }
        },

        open: {
            label: '<i class="bi bi-eye me-1"></i>  '+_I18N_OPEN_FILE,
            className: 'btn-info',
            callback: function () {
                const link = $('.ufm-fileinfo-modal .input-file-link').val();
                window.open(link);
                return false;
            }
        },

        download: {
            label: '<i class="bi bi-download me-1"></i> '+_I18N_DOWNLOAD,
            className: 'btn-success',
            callback: function () {
                const url = $('.ufm-fileinfo-modal .input-file-link').val();

                const anchor = document.createElement('a');
                anchor.href = url;
                anchor.download = filename;
                document.body.appendChild(anchor);
                anchor.click();
                document.body.removeChild(anchor);

                return false;
            }
        },
    }

    const dialog = bootbox.dialog({
        title: filename,
        className: 'ufm-fileinfo-modal',
        size: m_size,
        message: contents,
        backdrop: true,
        onEscape: true,
        buttons: buttons

    });

    dialog.init(function(){

        // video ?
        if($('.ufm-fileinfo-modal video').length == 1)
        {
            const video = $('.ufm-fileinfo-modal video')[0];
            video.addEventListener('loadedmetadata', function() {
                const durationInSeconds = video.duration;
                const minutes = Math.floor(durationInSeconds / 60);
                const seconds = Math.floor(durationInSeconds % 60);
                const formattedSeconds = seconds.toString().padStart(2, '0');
                const readableDuration = `${minutes}:${formattedSeconds}`;
                $('.ufm-fileinfo-modal .td--details').text(readableDuration);
            });
        }

        // audio
        if($('.ufm-fileinfo-modal audio').length == 1)
        {
            const audio = $('.ufm-fileinfo-modal audio')[0];
            audio.addEventListener('loadedmetadata', function() {
                const durationInSeconds = audio.duration;
                const minutes = Math.floor(durationInSeconds / 60);
                const seconds = Math.floor(durationInSeconds % 60);
                const formattedSeconds = seconds.toString().padStart(2, '0');
                const readableDuration = `${minutes}:${formattedSeconds}`;

                $('.ufm-fileinfo-modal .td--details').text(readableDuration);
            });
        }




    });

}

