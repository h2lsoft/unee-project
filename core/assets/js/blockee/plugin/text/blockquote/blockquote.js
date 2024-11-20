class BlockeePlugin__blockquote {

    static mount(){}

    static info(){
        return {
                    name: 'Blockquote',
                    title: "Blockquote",
                    keywords: "blockquote cite citation",
                    settings:  true

        }
    }


    static insert() {

        const text_my_quote = blockeeEditor.i18n('my_quote');
        const text_author = blockeeEditor.i18n('author');

        let contents = `<blockquote data-blockee-type="blockquote" class="blockee-editor-block-element">
                                    <p contenteditable="true">${text_my_quote}</p>
                                    <footer contenteditable="true">${text_author}</footer>
                               </blockquote>`;

        blockeeEditor.blockInsert('blockquote', contents);

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