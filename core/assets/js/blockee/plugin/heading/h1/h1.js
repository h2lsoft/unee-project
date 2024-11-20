class BlockeePlugin__h1 {

    static mount(){

    }

    static info(){
        return {
                    name: 'H1',
                    keywords: "heading title",
                    title: blockeeEditor.i18n('heading')+" 1 <kbd>alt + 1</kbd>",
                    settings: true,
        }
    }


    static insert() {
        let contents = "<h1 class='blockee-editor-block-element' contenteditable='true'>"+blockeeEditor.i18n('heading')+" 1</h1>";
        blockeeEditor.blockInsert('h1', contents, false);
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