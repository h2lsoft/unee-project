class BlockeePlugin__html {

    static info(){
        return {
            name: 'Html',
            title: "Html",
            keywords: "",
            settings:  true
        }
    }

    static insert() {

        let contents = `<div data-blockee-type="html" class="blockee-editor-block-element blockee-editor-block-element--html">Your html code</div>`;
        blockeeEditor.blockInsert('html', contents, true);
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

        $node.html(contents);

    }


}