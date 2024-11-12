class BlockeePlugin__iframe {

    static info(){
        return {
                    name: 'Iframe',
                    title: "Iframe",
                    keywords: "iframe",
                    settings:  true
        }
    }

    static insert() {

        let contents = `<div data-blockee-type="iframe" class="blockee-editor-block-element blockee-editor-block-element--iframe"><iframe src="about:blank" width="100%" height="150px"></iframe></div>`;
        blockeeEditor.blockInsert('iframe', contents, true);
    }

    static settingsRender()
    {
       let $node = blockeeEditor.blockGetNode();
        $node.find('iframe');

        // data
        let src = $node.attr('src') ?? '';
        let width = $node.attr('width') ?? '';
        let height = $node.attr('height') ?? '';

        let render =
            {
                tab_advanced: true,
                tabs:[
                    {
                        title: "GENERAL",
                        contents: `<div class="blockee-editor-form-row">                                
                                        <div class="blockee-editor-form-label">Src</div>
                                        <input type="text" name="src" value="${src}">                                                                                                                                
                                   </div>
                                   <div class="blockee-editor-form-row">                                
                                        <div class="blockee-editor-form-label">Size</div>
                                        Width <input type="text" name="width" style="width: 80px; text-align: center; margin-left: 10px; margin-right: 10px" value="${width}"> 
                                        Height <input type="text" name="height" style="width: 80px; text-align: center; margin-left: 10px;" value="${height}">                                
                                   </div>`
                    }
                ]
            };

        return render;


    }


    static settingsValidate()
    {
        let $node = blockeeEditor.blockGetNode();
        $node = $node.find('iframe');

        let src =  $('.blockee-editor-window input[name="src"]').val();
        let width =  $('.blockee-editor-window input[name="width"]').val();
        let height =  $('.blockee-editor-window input[name="height"]').val();


        if(src === '')src = 'about:blank;'

        $node.attr("src", src);
        $node.attr("width", width);
        $node.attr("height", height);

    }






}