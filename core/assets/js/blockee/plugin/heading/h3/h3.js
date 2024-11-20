class BlockeePlugin__h3 {

    static mount(){}

    static info(){
        return {
            name: 'H3',
            keywords: "heading title",
            title: blockeeEditor.i18n('heading')+" 3  <kbd>alt + 3</kbd>",
            settings: true
        }
    }


    static insert() {

        let contents = "<h3 class='blockee-editor-block-element' contenteditable='true'></h3>";
        blockeeEditor.blockInsert('h3', contents);

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