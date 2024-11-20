class BlockeePlugin__list {

    static mount(){}

    static info(){
        return {
                    name: 'List',
                    title: blockeeEditor.i18n('list')+"  <kbd>alt + l</kbd>",
                    keywords: "ul li list",
                    settings: true
        }
    }


    static insert() {

        let contents = `<ul data-blockee-type="list" class='blockee-editor-block-element' contenteditable='true'>
                                    <li>item 1</li>
                                    <li>item 2</li>
                                    <li>item 3</li>
                               </ul>`;

        blockeeEditor.blockInsert('list', contents);

    }

    static settingsRender()
    {
        const $node = blockeeEditor.blockGetNode();

        let ul_checked = '';
        let ol_checked = '';

        if($node[0].tagName === 'UL')ul_checked = 'checked';
        if($node[0].tagName === 'OL')ol_checked = 'checked';

        if(ul_checked === '' && ol_checked === '') ul_checked = 'checked';

        let form = `<div class="blockee-editor-form-row">        
                                <div class="blockee-editor-form-label">Style</div>
                                <label>
                                   <input type="radio" name="list_style" value="UL" ${ul_checked}> Unordered
                                </label>    
                                <label>
                                    <input type="radio" name="list_style" value="OL" ${ol_checked}> Ordered
                                </label>                                                                                                                                                                    
                           </div>
                            
`;

        let render =
            {
                tab_advanced: true,
                tabs:[
                    {
                        title: 'STYLE',
                        contents: form
                    }
                ]
            };

        return render;
    }


    static settingsValidate()
    {
        let $node = blockeeEditor.blockGetNode();

        let val = $('.blockee-editor-window:visible input[name="list_style"]:checked').val();

        let node_contents = $node[0].outerHTML;

        if ($node[0].tagName !== val)
        {
            node_contents = node_contents.replace('<'+$node[0].tagName.toLowerCase()+' ', '<'+val.toLowerCase()+' ');
            node_contents = node_contents.replace('</'+$node[0].tagName.toLowerCase()+'>', '</'+val.toLowerCase()+'>');

            $node[0].outerHTML = node_contents;
        }



    }

}