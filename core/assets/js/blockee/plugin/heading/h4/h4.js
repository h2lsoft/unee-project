class BlockeePlugin__h4 {

    static info(){
        return {
            name: 'H4',
            keywords: "heading title",
            title: blockeeEditor.i18n('heading')+" 4  <kbd>alt + 4</kbd>",
            settings: true
        }
    }


    static insert() {

        let contents = "<h4 class='blockee-editor-block-element' contenteditable='true'></h4>";
        blockeeEditor.blockInsert('h4', contents);

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