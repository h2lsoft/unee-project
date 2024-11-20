class BlockeePlugin__thumbpage {

    static mount(){

        $('body').on('click', '[data-blockee-type="thumbpage"]', function(e){

            $('.blockee-editor-block').removeClass('active');
            $(this).parents('.blockee-editor-block').addClass('active');
            $('.blockee-editor__menu-block').data('blockee-type', 'thumbpage');
            blockeeEditor.blockSettingsOpen();
        });

    }

    static info(){
        return {
            name: 'Thumbpage',
            title: "Thumbpage",
            keywords: "",
            settings:  true
        }
    }

    static insert() {

        let contents = `<x-thumbpage data-blockee-type="thumbpage" data-parent-page="" class="blockee-editor-block-element blockee-editor-block-element--xthumbpage"></x-thumbpage>`;
        blockeeEditor.blockInsert('thumbpage', contents, false);

    }

    static settingsRender()
    {
        const $node = blockeeEditor.blockGetNode();

        let parent_page = $node.data('parent-page') ?? '';

        const text_parent_page =  blockeeEditor.i18n('parent_page_help');
        const text_parent_page_empty =  blockeeEditor.i18n('parent_page_empty');

        let form = `<div class="blockee-editor-form-row">                                
                                <div class="blockee-editor-form-label">${text_parent_page}</div>
                                <input type="text" name="parent_page" value="${parent_page}" placeholder="name - #parent_page_id ${text_parent_page_empty}">
                             </div>`;


        let render =
            {
                tab_advanced: false,
                tabs:[{
                    title: 'THUMBPAGE',
                    contents: form,
                }]
            };

        return render;
    }

    static settingsValidate()
    {
        let $node = blockeeEditor.blockGetNode();
        let name =  $('.blockee-editor-window--settings input[name="parent_page"]').val();

        $node.attr("data-parent-page", name);
    }

}