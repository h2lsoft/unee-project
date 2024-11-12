class BlockeePlugin__h2 {

    static info(){
        return {
                    name: 'H2',
                    keywords: "heading title",
                    title: blockeeEditor.i18n('heading')+" 2 <kbd>alt + 2</kbd>",
                    settings: true
        }
    }


    static insert() {

        let contents = "<h2 class='blockee-editor-block-element' contenteditable='true'></h2>";
        blockeeEditor.blockInsert('h2', contents);

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