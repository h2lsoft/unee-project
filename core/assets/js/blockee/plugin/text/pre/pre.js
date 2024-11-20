class BlockeePlugin__pre {

    static mount(){}

    static info(){
        return {
                    name: 'Pre',
                    title: "Pre",
                    keywords: "",
                    settings: true
        }
    }

    static insert() {

        const text_my_text = blockeeEditor.i18n('my_text');
        let contents = `<pre class="blockee-editor-block-element" contenteditable="true">${text_my_text}</pre>`;
        blockeeEditor.blockInsert('pre', contents);
    }


    static settingsRender()
    {
        const $node = blockeeEditor.blockGetNode();

        let contents = $node.html();
        let form = `<textarea style="height: 420px">${contents}</textarea>`;

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