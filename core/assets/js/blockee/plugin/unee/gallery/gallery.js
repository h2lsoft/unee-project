class BlockeePlugin__gallery {

    static mount(){

        $('body').on('click', '[data-blockee-type="gallery"]', function(e){
            $('.blockee-editor-block').removeClass('active');
            $(this).parents('.blockee-editor-block').addClass('active');
            $('.blockee-editor__menu-block').data('blockee-type', 'gallery');
            blockeeEditor.blockSettingsOpen();
        });

    }

    static info(){
        return {
            name: 'Gallery',
            title: blockeeEditor.i18n('gallery'),
            keywords: "gallery",
            settings:  true
        }
    }

    static insert() {

        let contents = `<x-gallery data-blockee-type="gallery" class="blockee-editor-block-element blockee-editor-block-element--xgallery"></x-gallery>`;
        blockeeEditor.blockInsert('gallery', contents, true);

    }

    static settingsRender()
    {
        const $node = blockeeEditor.blockGetNode();

        const text_gallery = blockeeEditor.i18n('gallery');
        let gallery = $node.data('name') ?? '';

        let form = `<div class="blockee-editor-form-row">                                
                                <div class="blockee-editor-form-label">${text_gallery}</div>
                                <input type="text" name="gallery" value="${gallery}" placeholder="gallery name - #id" list="datalist_galeries">
                                <button type="button" class="blockee-editor-form-button blockee-editor-form-button-filemanager" onclick="windowPopup('/'+APP_BACKEND_DIRNAME+'/gallery/?target=gallery&_popup=1')">Select...</button>
                             </div>`;

        form += `<datalist id="datalist_galeries"></datalist>`;

        // list blocks
        $.getJSON(`/${APP_BACKEND_DIRNAME}/gallery/all/?_format=json`, function(data){

            let options = '';
            data.forEach(function(item){
                options += `<option value="${item.name} - #${item.id}"></option>`;
            });

            $('#datalist_galeries').html(options);
        });

        let render =
            {
                tab_advanced: false,
                tabs:[{
                    title: text_gallery,
                    contents: form,
                }]
            };

        return render;
    }

    static settingsValidate()
    {
        let $node = blockeeEditor.blockGetNode();
        let name =  $('.blockee-editor-window--settings input[name="gallery"]').val();

        $node.attr("data-name", name);
    }

}