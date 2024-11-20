class BlockeePlugin__code {

    static mount(){}

    static info(){
        return {
                    name: 'Code',
                    title: "Code",
                    keywords: "code",
                    settings:  true
        }
    }

    static insert() {

        const text_your_code = blockeeEditor.i18n('your_code')

        let contents = `<code class="blockee-editor-block-element">${text_your_code}</code>`;
        blockeeEditor.blockInsert('code', contents, true);
    }


    static settingsRender()
    {
        const $node = blockeeEditor.blockGetNode();

        let contents = $node.html();
        let form = `<textarea style="height: 420px" autofocus>${contents}</textarea>`;

        let render =
            {
                tab_advanced: true,
                tabs:[
                    {
                        title: 'INFORMATION',
                        contents: form
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

        $node.text(contents);

    }


}