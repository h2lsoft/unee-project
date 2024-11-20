class BlockeePlugin__audio {

    static mount(){}

    static info(){
        return {
            name: 'Audio',
            title: "Audio",
            keywords: "audio sound",
            settings:  true
        }
    }

    static insert() {
        let contents = `<div data-blockee-type="audio" class="blockee-editor-block-element blockee-editor-block-element--audio"><audio src="" controls preload="auto">Your browser does not support the audio element.</audio></div>`;
        blockeeEditor.blockInsert('audio', contents, true);
    }

    static settingsRender()
    {
        let $node = blockeeEditor.blockGetNode();
        $node = $node.find('audio');


        // data
        let src = $node.attr('src') ?? '';
        let preload = $node.attr('preload') ?? '';
        let loop = $node.attr('loop') ?? false;
        let o_muted = $node.attr('muted') ?? false;

        // init
        let loop_checked = (loop !== false) ? 'checked' : '';

        let preload_auto = (preload === 'auto') ? 'selected' : '';
        let preload_metadata = (preload === 'metadata') ? 'selected' : '';
        let preload_none = (preload === 'none') ? 'selected' : '';

        let muted_checked = (o_muted !== false) ? 'checked' : '';


        let render =
            {
                tab_advanced: true,
                tabs:[
                        {
                            title: "GENERAL",
                            contents: `<div class="blockee-editor-form-row">                                                            
                                    <div class="blockee-editor-form-label">Src</div>                                
                                    <input type="text" name="src" value="${src}" class="input-file">
                                    <input type="button" value="..." class="input-file-manager" onclick="blockeeEditor.fileManagerOpen('src', '&filter=audio')">                                                                                                                                                                    
                                </div>  
                                
                                <div class="blockee-editor-form-row">                                
                                    <div class="blockee-editor-form-label">Preload</div>
                                    <select name="preload">
                                        <option value="auto" ${preload_auto}>auto</option>
                                        <option value="metadata" ${preload_metadata}>metadata</option>                                    
                                        <option value="none" ${preload_none}>none</option>
                                    </select>
                               </div>                                                     
                               
                               <div class="blockee-editor-form-row">                                
                                    <div class="blockee-editor-form-label">Loop</div>
                                    <input type="checkbox" name="loop" value="1" ${loop_checked}>
                               </div>
                               
                               <div class="blockee-editor-form-row">                                
                                    <div class="blockee-editor-form-label">Muted</div>
                                    <input type="checkbox" name="muted" value="1" ${muted_checked}>
                               </div>`
                        }
                ]
            };

        return render;



    }

    static settingsValidate()
    {
        let $node = blockeeEditor.blockGetNode();
        $node = $node.find('audio');


        let src =  $('.blockee-editor-window:visible input[name="src"]').val();
        let preload =  $('.blockee-editor-window:visible select[name="preload"]').val();
        let muted =  $('.blockee-editor-window:visible input[name="muted"]').is(':checked');
        let loop =  $('.blockee-editor-window:visible input[name="loop"]').is(':checked');

        $node.attr("src", src);
        $node.prop("preload", preload);
        $node.attr("muted", !muted ? null : "muted");
        $node.prop("loop", !loop ? null : "loop");

    }


}