class BlockeePlugin__hr {

    static mount(){}

    static info(){
        return {
                    name: 'hr',
                    title: "Hr <kbd>alt + -</kbd>",
                    keywords: "line",
                    settings: true,

        }
    }


    static insert() {

        let contents = `<div data-blockee-type="hr" class='blockee-editor-block-element'><hr /></div>`;
        blockeeEditor.blockInsert('hr', contents);
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