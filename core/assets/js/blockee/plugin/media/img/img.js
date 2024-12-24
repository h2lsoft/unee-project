class BlockeePlugin__img {

    static mount(){

        $('body').on('click', '[data-blockee-type="img"] img', function(e){

            $('.blockee-editor-block').removeClass('active');
            $(this).parents('.blockee-editor-block').addClass('active');
            $('.blockee-editor__menu-block').data('blockee-type', 'img');
            blockeeEditor.blockSettingsOpen();

        });



    }

    static info(){
        return {
            name: 'Img',
            title: "Image <kbd>alt + i</kbd>",
            keywords: "image figure",
            settings:  true
        }
    }

    static insert(open_settings=true) {

        const text_caption_here = blockeeEditor.i18n('caption_here');
        let contents = `<div data-blockee-type="img" class="blockee-editor-block-element blockee-editor-block-element--img">
                                    <figure>
                                        <img>
                                        <figcaption contenteditable="true">${text_caption_here}</figcaption>
                                    </figure>
                               </div>`;
        blockeeEditor.blockInsert('img', contents, open_settings);
    }


    static settingsRender()
    {
        let $node = blockeeEditor.blockGetNode();
        $node = $node.find('img');

        let src = $node.attr('src') ?? '';
        let width = $node.attr('width') ?? '';
        let height = $node.attr('height') ?? '';
        let alt = $node.attr('alt') ?? '';
        let title = $node.attr('title') ?? '';

        let img_id = $node.attr('id') ?? '';
        let img_class = $node.attr('class') ?? '';
        let img_style = $node.attr('style') ?? '';

        let url = $node.parent('a').attr('href') ?? '';
        let target = $node.parent('a').attr('target') ?? '';

        let a_id = $node.parent('a').attr('id') ?? '';
        let a_class = $node.parent('a').attr('class') ?? '';
        let a_style = $node.parent('a').attr('style') ?? '';

        let select_file = blockeeEditor.i18n('select_file');

        let render =
            {
                tab_advanced: true,
                tabs:[
                        {
                            title: "IMAGE",
                            contents: `<div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Src</div>
                                            <input type="text" name="src" value="${src}">
                                            <button type="button" class="blockee-editor-form-button blockee-editor-form-button-filemanager" onclick="blockeeEditor.fileManagerOpen('src', '&filter=image')">${select_file}...</button>
                                       </div>
                                       
                                       <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Alt</div>
                                            <input type="text" name="alt" value="${alt}">                                                                                                                                
                                       </div>
                                       
                                       <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Size</div>
                                            <small>Width</small> <input type="text" name="width" style="width: 80px; text-align: center; margin-left: 10px; margin-right: 10px" value="${width}"> 
                                            <small>Height</small> <input type="text" name="height" style="width: 80px; text-align: center; margin-left: 10px;" value="${height}">                                
                                       </div>
                                       
                                       <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Title</div>
                                            <input type="text" name="title" value="${title}">                                                                                                                                
                                       </div>
                                       
                                       <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Id</div>
                                            <input type="text" name="img_id" value="${img_id}">                                                                                                                                
                                       </div>
                                       
                                       <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Class</div>
                                            <input type="text" name="img_class" value="${img_class}">                                                                                                                                
                                       </div>
                                       
                                       <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Style</div>
                                            <input type="text" name="img_style" value="${img_style}">                                                                                                                                
                                       </div>


                                    `
                        },

                        {
                            title: "LINK",
                            contents: `<div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Href</div>
                                            <input type="text" name="url" value="${url}">                                                                                                                                
                                       </div>
                                       <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Target</div>
                                            <input type="text" name="target" value="${target}" list="datalist_link_target">
                                            <datalist id="datalist_link_target">
                                                <option value="_blank"></option>
                                                <option value="_parent"></option>
                                                <option value="#target"></option>
                                            </datalist>                                                                                                                                
                                       </div>
                                       
                                       <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Id</div>
                                            <input type="text" name="a_id" value="${a_id}">                                                                                                                                
                                       </div>
                                       
                                       <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Class</div>
                                            <input type="text" name="a_class" value="${a_class}">                                                                                                                                
                                       </div>
                                       
                                       <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Style</div>
                                            <input type="text" name="a_style" value="${a_style}">                                                                                                                                
                                       </div>
                                        `
                        }
                ]
            };

        return render;
    }


    static settingsValidate()
    {
        let $node = blockeeEditor.blockGetNode();
        $node = $node.find('img');

        let src =  $('.blockee-editor-window--settings input[name="src"]').val();
        let alt =  $('.blockee-editor-window--settings input[name="alt"]').val();
        let title =  $('.blockee-editor-window--settings input[name="title"]').val();
        let width =  $('.blockee-editor-window--settings input[name="width"]').val();
        let height =  $('.blockee-editor-window--settings input[name="height"]').val();

        let img_style =  $('.blockee-editor-window--settings input[name="img_style"]').val();
        let img_class =  $('.blockee-editor-window--settings input[name="img_class"]').val();
        let img_id =  $('.blockee-editor-window--settings input[name="img_id"]').val();

        let url =  $('.blockee-editor-window--settings input[name="url"]').val();
        let target =  $('.blockee-editor-window--settings input[name="target"]').val();

        let a_style =  $('.blockee-editor-window--settings input[name="a_style"]').val();
        let a_class =  $('.blockee-editor-window--settings input[name="a_class"]').val();
        let a_id =  $('.blockee-editor-window--settings input[name="a_id"]').val();


        if(src === '')
            $node.removeAttr("src");
        else
            $node.attr("src", src);

        $node.attr("width", width);
        $node.attr("height", height);
        $node.attr("alt", alt);
        $node.attr("title", title);

        $node.attr("style", img_style);
        $node.attr("class", img_class);
        $node.attr("id", img_id);


        // parent ?
        if(url === '' && $node.closest('a').length)
        {
            $node.closest('a').replaceWith($node);
        }

        // add link
        if(url !== '')
        {
            let $a_parent;

            if(!$node.closest('a').length)
            {
                $a_parent = $('<a></a>').attr('href', url).attr('target', target === '' ? null : target);
                $node.wrap($a_parent);
            }
            else
            {
                $node.closest('a').attr('href', url).attr('target', target === '' ? null : target);
            }

            $node.closest('a').attr("style", a_style);
            $node.closest('a').attr("class", a_class);
            $node.closest('a').attr("id", a_id);

        }

    }

}