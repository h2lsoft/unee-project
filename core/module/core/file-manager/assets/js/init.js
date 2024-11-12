$(function(){

    const in_iframe = (window.self !== window.top);
    if(in_iframe)
    {
        $('.btn-header-close').removeClass('d-none');
    }

    // uploader
    q = document.location.search;
    if(q.indexOf("upload=1") !== -1)
        ufmUpload();

});