class BlockeePlugin__block {

    static info(){
        return {
            name: 'Block',
            title: "Block",
            keywords: "",
            settings:  true
        }
    }

    static insert() {

        let contents = `<x-block data-blockee-type="block" class="blockee-editor-block-element blockee-editor-block-element--xblock"></x-block>`;
        blockeeEditor.blockInsert('block', contents, true);

    }

    static settingsRender()
    {
        const $node = blockeeEditor.blockGetNode();

        let block = $node.data('name') ?? '';

        let form = `<div class="blockee-editor-form-row">                                
                                <div class="blockee-editor-form-label">Name</div>
                                <input type="text" name="block" value="${block}" placeholder="blockname - #id" list="datalist_blocks">
                                <button type="button" class="blockee-editor-form-button blockee-editor-form-button-filemanager" onclick="windowPopup('/'+APP_BACKEND_DIRNAME+'/block/?target=block&_popup=1')">Select...</button>
                             </div>`;

        form += `<datalist id="datalist_blocks"></datalist>`;

        // list blocks
        $.getJSON(`/${APP_BACKEND_DIRNAME}/block/all/?_format=json`, function(data){

            let options = '';
            data.forEach(function(item){
                options += `<option value="${item.name} - #${item.id}"></option>`;
            });

            $('#datalist_blocks').html(options);
        });





        let render =
            {
                tab_advanced: false,
                tabs:[{
                            title: 'BLOCK',
                            contents: form,
                }]
            };

        return render;
    }

    static settingsValidate()
    {
        let $node = blockeeEditor.blockGetNode();
        let name =  $('.blockee-editor-window--settings input[name="block"]').val();

        $node.attr("data-name", name);
    }

}