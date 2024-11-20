class BlockeePlugin__h6 {

    static mount(){}

    static info(){
        return {
            name: 'H6',
            keywords: "heading title",
            title: blockeeEditor.i18n('heading')+" 6  <kbd>alt + 6</kbd>",
            settings: true
        }
    }


    static insert() {

        let contents = "<h6 class='blockee-editor-block-element' contenteditable='true'></h6>";
        blockeeEditor.blockInsert('h6', contents);

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