class BlockeePlugin__embed {

    static info(){
        return {
                    name: 'Embed',
                    title: blockeeEditor.i18n('embed'),
                    settings:  true,
                    keywords: "embed",
        }
    }

    static insert() {

        let contents = `<div data-blockee-type="embed" class="blockee-editor-block-element blockee-editor-block-element--embed"></div>`;
        blockeeEditor.blockInsert('embed', contents, true);
    }


    static settingsRender()
    {
        const $node = blockeeEditor.blockGetNode();

        let contents = $node.html();

        const text_paste_code_here = blockeeEditor.i18n('paste_code_here');

        let render =
            {
                tab_advanced: true,
                tabs:[
                    {
                        title: "GENERAL",
                        contents: `<textarea required  style="height: 400px" placeholder="${text_paste_code_here}...">${contents}</textarea>`
                    }
                ]
            };

        return render;
    }


    static settingsValidate()
    {
        const $node = blockeeEditor.blockGetNode();

        let contents = $('.blockee-editor-window textarea').val();
        contents = $.trim(contents);

        $node.html(contents);

    }


}