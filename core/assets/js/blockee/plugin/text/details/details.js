class BlockeePlugin__details {

    static mount(){}

    static info(){
        return {
            name: 'Details',
            title: "Details",
            keywords: "details accordeon toggle faq",
            settings:  true
        }
    }

    static insert() {


        const text_title = blockeeEditor.i18n('title');
        const text_my_text = blockeeEditor.i18n('my_text');

        let contents = `<details class="blockee-editor-block-element blockee-editor-block-element--details">
                               <summary contenteditable="true">${text_title}</summary>
                               <p class="details-contents" contenteditable="true">
                                    ${text_my_text}
                               </p>     
                                </details>`;
        blockeeEditor.blockInsert('details', contents, false);
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