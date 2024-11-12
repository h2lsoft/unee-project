class BlockeePlugin__plugin {

    static info(){
        return {
            name: 'Plugin',
            title: "Plugin",
            keywords: "",
            settings: true
        }
    }

    static insert() {

        let contents = `<x-plugin data-blockee-type="plugin" class="blockee-editor-block-element blockee-editor-block-element--xplugin"></x-plugin>`;
        blockeeEditor.blockInsert('plugin', contents, true);
    }

    static settingsRender()
    {
        const $node = blockeeEditor.blockGetNode();

        let name = $node.data('name') ?? '';
        let parameters = $node.html();

        if(parameters === '')
        {
            parameters = '{\n\t\n}';
        }


        let form = `<div class="blockee-editor-form-row">                                
                                <div class="blockee-editor-form-label">Name</div>
                                <input type="text" name="name" value="${name}" placeholder="plugin::method">                                
                             </div>
                             <div class="blockee-editor-form-row">                                
                                <div class="blockee-editor-form-label">Parameters</div>
                                <textarea name="parameters" placeholder='{\n\t"param_name":""\n}'>${parameters}</textarea>                                
                             </div>
                            `;

        let render =
            {
                tab_advanced: false,
                tabs:[{
                    title: 'PLUGIN',
                    contents: form,
                }]
            };

        return render;
    }

    static settingsValidate()
    {
        let $node = blockeeEditor.blockGetNode();
        let name =  $('.blockee-editor-window--settings:visible input[name="name"]').val();
        let parameters =  $('.blockee-editor-window--settings:visible textarea[name="parameters"]').val();

        $node.attr("data-name", name);
        $node.html(parameters);

    }

}