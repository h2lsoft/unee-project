class BlockeePlugin__p {

    static mount(){}

    static info(){
        return {
                    name: 'P',
                    title: blockeeEditor.i18n('paragraph')+" <kbd>alt + t</kbd>",
                    keywords: "text paragraph",
                    settings:  true
        }
    }

    static insert() {

        const text_paragraph = blockeeEditor.i18n('my_text');
        let contents = "<p class='blockee-editor-block-element' contenteditable='true'>"+text_paragraph+"</p>";
        blockeeEditor.blockInsert('p', contents, false);
    }




    static settingsRender()
    {
        const $node = blockeeEditor.blockGetNode();

        let render =
            {
                tab_advanced: true,
                tabs:[]
            };

        return render;
    }


    static settingsValidate()
    {

    }


}