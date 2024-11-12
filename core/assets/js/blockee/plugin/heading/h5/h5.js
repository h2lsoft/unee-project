class BlockeePlugin__h5 {

    static info(){
        return {
            name: 'H5',
            keywords: "heading title",
            title: blockeeEditor.i18n('heading')+" 5  <kbd>alt + 5</kbd>",
            settings: true
        }
    }


    static insert() {

        let contents = "<h5 class='blockee-editor-block-element' contenteditable='true'></h5>";
        blockeeEditor.blockInsert('h5', contents);

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