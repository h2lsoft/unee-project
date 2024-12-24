class BlockeePlugin__video {

	static mount()
	{

	}

	static info(){
		return {
			name: 'Video',
			title: "Video",
			keywords: "video mp4 webm",
			settings:  true
		}
	}

	static insert() {
		let contents = `<div data-blockee-type="video" class="blockee-editor-block-element blockee-editor-block-element--video"><video src="" controls preload="auto">Your browser does not support the video element.</video></div>`;
		blockeeEditor.blockInsert('video', contents, true);
	}

	static settingsRender()
	{
		let $node = blockeeEditor.blockGetNode();
		$node = $node.find('video');


		// data
		let src = $node.attr('src') ?? '';
		let width = $node.attr('width') ?? '';
		let height = $node.attr('height') ?? '';
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
                                    <input type="button" value="..." class="input-file-manager" onclick="blockeeEditor.fileManagerOpen('src', '&filter=video')">                                                                                                                                                                    
                                </div>  
                                
                                <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Size</div>
                                            <small>Width</small> <input type="text" name="width" style="width: 80px; text-align: center; margin-left: 10px; margin-right: 10px" value="${width}"> 
                                            <small>Height</small> <input type="text" name="height" style="width: 80px; text-align: center; margin-left: 10px;" value="${height}">                                
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
		$node = $node.find('video');


		let src =  $('.blockee-editor-window:visible input[name="src"]').val();
		let width =  $('.blockee-editor-window--settings input[name="width"]').val();
		let height =  $('.blockee-editor-window--settings input[name="height"]').val();
		let preload =  $('.blockee-editor-window:visible select[name="preload"]').val();
		let muted =  $('.blockee-editor-window:visible input[name="muted"]').is(':checked');
		let loop =  $('.blockee-editor-window:visible input[name="loop"]').is(':checked');

		$node.attr("src", src);
		$node.attr("width", width);
		$node.attr("height", height);
		$node.prop("preload", preload);
		$node.attr("muted", !muted ? null : "muted");
		$node.prop("loop", !loop ? null : "loop");

	}


}