var blockeeEditorUrl = "";
var blockeeEditorFileManagerUrl = false;
var blockeeEditorInstances = [];
var blockeeEditorPlugins = {

    'heading': ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
    'text': ['p', 'list', 'table', 'hr',  'blockquote', 'details', 'html', 'code', 'pre'],
    'media' : ['img', 'audio', 'video', 'embed', 'iframe'],
    'unee' : ['block', 'slider', 'gallery', 'thumbpage', 'plugin'],
};

var blockeeEditorPluginsLoaded = []

var blockeeEditorMouseX = 0;
var blockeeEditorMouseY = 0;


class blockeeEditor {

    static undoStack = [];
    static redoStack = [];

    static savedSelection;
    static $toolbar;
    static selectionRange;
    static isFullScreened = false;

    static ScrollviewAfterInsert = true;

    static init(){

        // blockeeEditor.version();

        // init textarea
        $('textarea.blockee-editor').each(function(){
            name = $(this).attr('name');
            blockeeEditorInstances[blockeeEditorInstances.length] = new blockeeEditor(name, $(this));
        });

        $(window).scrollTop(0);

    }

    static version()
    {
        const version = "0.4.0";
        console.log("***** BlockeeEditor v"+version+' *****');
        return version;
    }

    static i18n(key) {

        let language = $('html').attr('lang');
        return (blockeeEditorI18n[key] && blockeeEditorI18n[key][language]) ? blockeeEditorI18n[key][language] : key.replace(' ', ' ');
    }

    constructor(name, node) {

        this.name = name;
        this.node = node;

        if(!blockeeEditorFileManagerUrl  && $(node).data('blockee-filemanager-url') !== undefined && $(node).data('blockee-filemanager-url') !== '')
            blockeeEditorFileManagerUrl = $(node).data('blockee-filemanager-url');


        let contents = $.trim($(this.node).html());


        const text_view_source = blockeeEditor.i18n('view_source');
        const text_clear_all = blockeeEditor.i18n('clear_all');
        const text_save = blockeeEditor.i18n('save');
        const text_cancel = blockeeEditor.i18n('cancel');
        const text_fullscreen = blockeeEditor.i18n('fullscreen');

        let render = `
 <div class="blockee-editor blockee-editor-container-__${this.name}" data-source="${this.name}" spellcheck="false">
     <div class="blockee-editor__toolbar">        
            <button type="button" class="blockee-editor__button-add" onclick="blockeeEditor.actionMenuShow('toolbar')"></button>
            <button type="button" title="Template" class="blockee-editor__button-template" onclick="blockeeEditor.actionTemplateOpen()"></button>
            
            <button type="button" title="Plan" class="blockee-editor__button-plan" onclick="blockeeEditor.actionPlanOpen()"></button>
            
            <button type="button" title="Undo - ctrl + z" class="blockee-editor__button-undo" onclick="blockeeEditor.actionUndo()"></button>
            <button type="button" title="Redo - ctrl + y" class="blockee-editor__button-redo" onclick="blockeeEditor.actionRedo()"></button>
            
            <button type="button" title="${text_view_source}" class="blockee-editor__button-source" onclick="blockeeEditor.actionSourceOpen()"></button>
            <button type="button" title="${text_clear_all}" class="blockee-editor__button-clear" onclick="blockeeEditor.actionClearAll()"></button>
            <button type="button" title="${text_save} - ctrl + s" class="blockee-editor__button-save" onclick="blockeeEditor.actionSave()"></button>            
            <button type="button" title="${text_fullscreen} - alt + f" class="blockee-editor__button-fullscreen" onclick="blockeeEditor.actionFullscreen(this)"></button>
        </div>    
    <div class="blockee-editor__content"></div>
</div>`;

        $(this.node).after(render);


        // add element
        const canvas = `<div class="blockee-editor-canvas" onclick="blockeeEditor.actionMenuHide()"></div>`;
        $('div.blockee-editor').append(canvas);

        // init menu
        let search_label = blockeeEditor.i18n('search');

        render = `<div class="blockee-editor__menu blockee-editor__menu-plugin">`;
        render += `<input type="search" placeholder="${search_label}...">`;

        Object.keys(blockeeEditorPlugins).forEach(function(group) {


            let groupX = blockeeEditor.i18n(group).toUpperCase();
            // let groupX = group.charAt(0).toUpperCase() + group.slice(1);


            render += `<div class="blockee-editor__menu-group">${groupX}</div>`;
            render += `<ul>`;

            Object.keys(blockeeEditorPlugins[group]).forEach(function(plugin) {

                let plugin_name = blockeeEditorPlugins[group][plugin];
                let plugin_path = "/plugin/"+group+"/"+plugin_name;

                const signature = 'BlockeePlugin__'+plugin_name;
                let info = eval(signature+".info()");

                let plugin_keywords = info.keywords;

                // mount detected
                let mount = eval(signature+".mount()");

                render += `<li data-blockee-group="${group}" data-blockee-plugin-keywords="${plugin_keywords}" data-blockee-plugin="${plugin_name}" onclick="BlockeePlugin__${plugin_name}.insert(); "><img src="${blockeeEditorUrl}/${plugin_path}/icon.svg"> ${info.title}</li>`;

            });

            render += `</ul>`;
        });

        render += `</div>`;

        // init block menu
        const text_add = blockeeEditor.i18n('add');
        const text_duplicate = blockeeEditor.i18n('duplicate');
        const text_configure = blockeeEditor.i18n('configure');
        const text_up = blockeeEditor.i18n('up');
        const text_down = blockeeEditor.i18n('down');
        const text_delete = blockeeEditor.i18n('delete');

        render += `
                    <div class="blockee-editor__menu blockee-editor__menu-block">
                        <div class="blockee-editor__menu--header"></div>
                        <ul>
                            <li class="blockee-editor__menu-block--li-add" onclick="blockeeEditor.blockAdd()">${text_add}</li>
                            <li class="blockee-editor__menu-block--li-duplicate" onclick="blockeeEditor.blockDuplicate()">${text_duplicate}</li>
                            <li class="divider"></li>
                            <li class="blockee-editor__menu-block--li-configure" onclick="blockeeEditor.blockSettingsOpen()">${text_configure}</li>
                            <li class="divider"></li>
                            <li class="blockee-editor__menu-block--li-up"  onclick="blockeeEditor.blockUp()">${text_up}</li>
                            <li class="blockee-editor__menu-block--li-down"  onclick="blockeeEditor.blockDown()">${text_down}</li>
                            <li class="divider"></li>                            
                            <li class="blockee-editor__menu-block--li-delete" onclick="blockeeEditor.blockDelete()">${text_delete}</li>
                        </ul>
                    </div>`;

        // init window settings
        render += `<div class="blockee-editor-window-canvas"></div>
        <div class="blockee-editor-window blockee-editor-window--settings">
            <form onsubmit="blockeeEditor.blockSettingsValidate(); return false;">
        
            <div class="blockee-editor-window-header">Configuration</div>
            <div class="blockee-editor-window-body">
            </div>
            <div class="blockee-editor-window-footer">
                <input type="button" value="${text_cancel}" onclick="blockeeEditor.blockSettingsClose()">
                <input type="submit" value="${text_save}">
            </div>
            
            </form>            
        </div>`;

        // init erase all
        const text_msg_confirmation = blockeeEditor.i18n('clear_all_confirmation_msg');
        render += `
        <div class="blockee-editor-window blockee-editor-window--confirm">
            <form onsubmit="blockeeEditor.blockConfirmExecute(); return false;">        
                <div class="blockee-editor-window-header">Confirmation</div>
                <div class="blockee-editor-window-body">
                    <p>${text_msg_confirmation}</p>
                </div>
                <div class="blockee-editor-window-footer">
                    <input type="button" value="${text_cancel}" onclick="blockeeEditor.blockConfirmClose()">
                    <input type="submit" value="${text_save}">
                </div>                        
            </form>            
        </div>`;

        // init window source
        render += `
        <div class="blockee-editor-window blockee-editor-window--source">
            <form onsubmit="blockeeEditor.blockSourceExecute(); return false;">        
                <div class="blockee-editor-window-header">Code</div>
                <div class="blockee-editor-window-body">
                    <textarea></textarea>
                </div>
                <div class="blockee-editor-window-footer">
                    <input type="button" value="${text_cancel}" onclick="blockeeEditor.blockConfirmClose()">
                    <input type="submit" value="${text_save}">
                </div>                        
            </form>            
        </div>`;

        // init window filebrowser
        render += `
        <div class="blockee-editor-window blockee-editor-window--file-browser">
            <form onsubmit="return false;">        
                <div class="blockee-editor-window-header">File browser</div>
                <div class="blockee-editor-window-body">
                    <iframe src=""></iframe>
                </div>                         
            </form>            
        </div>`;

        // init window plan
        render += `
        <div class="blockee-editor-window blockee-editor-window--plan">
            <form onsubmit="blockeeEditor.actionPlanExecute(); return false;">        
                <div class="blockee-editor-window-header">Plan</div>
                <div class="blockee-editor-window-body">                                        
                </div>
                <div class="blockee-editor-window-footer">
                    <input type="button" value="${text_cancel}" onclick="blockeeEditor.blockConfirmClose()">
                    <input type="submit" value="${text_save}">                    
                </div>                         
            </form>            
        </div>`;

        // init window template
        render += `
        <div class="blockee-editor-window blockee-editor-window--template">
            <form onsubmit="blockeeEditor.actionPlanExecute(); return false;">        
                <div class="blockee-editor-window-header">Template</div>
                <div class="blockee-editor-window-body">                                        
                </div>
                <div class="blockee-editor-window-footer">
                    <input type="button" value="${text_cancel}" onclick="blockeeEditor.blockConfirmClose()">                   
                </div>                         
            </form>            
        </div>`;


        // init link text toolbar
        let text_link_properties = blockeeEditor.i18n('link_properties');
        let select_file = blockeeEditor.i18n('select_file');

        render += `
        <div class="blockee-editor-window blockee-editor-window--link">
            <form onsubmit="blockeeEditor.textToolbarWindowLinkExecute(); return false;">
        
                <div class="blockee-editor-window-header">${text_link_properties}</div>
                <div class="blockee-editor-window-body">
                    
                    <div class="blockee-editor-form-row">
                        <div class="blockee-editor-form-label">Href</div>                        
                        <input type="text" name="text_link_href" value="">
                        <button type="button" class="blockee-editor-form-button blockee-editor-form-button-filemanager" onclick="blockeeEditor.fileManagerOpen('text_link_href')">${select_file}...</button>                                                                              
                   </div>
                   
                   <div class="blockee-editor-form-row">
                        <div class="blockee-editor-form-label">Target</div>                        
                        <input type="text" name="text_link_target" list="data_text_link_target" value="">
                        <datalist id="data_text_link_target">
                            <option value="_blank"></option>
                            <option value="_top"></option>
                            <option value="_self"></option>
                            <option value="#my_anchor"></option>
                            <option value="my_iframe"></option>
                        </datalist>                                                                                                      
                   </div>
                   
                   <div class="blockee-editor-form-row">
                        <div class="blockee-editor-form-label">Id</div>                        
                        <input type="text" name="text_link_id" value="">
                   </div>
                   
                   <div class="blockee-editor-form-row">
                        <div class="blockee-editor-form-label">Class</div>                        
                        <input type="text" name="text_link_class" value="">                        
                   </div>
                   
                   <div class="blockee-editor-form-row">
                        <div class="blockee-editor-form-label">Style</div>                        
                        <input type="text" name="text_link_style" value="">
                   </div>                   
                    
                </div>
                <div class="blockee-editor-window-footer">
                    <input type="button" value="${text_cancel}" onclick="blockeeEditor.textToolbarWindowLinkClose()">
                    <input type="submit" value="${text_save}">
                </div>
                        
            </form>            
        </div>`;


        // int toolbar
        render += `    <div class="blockeditor-text-toolbar">
                            <button data-command="bold" type="button"></button>
                            <button data-command="italic" type="button"></button>
                            <button data-command="underline" type="button"></button>
                            <div class="divider"></div>
                            <button data-command="strikeThrough" type="button"></button>
                            <button data-command="hiliteColor" type="button"></button>

                            <div class="divider"></div>
                            <button data-command="createLink" type="button"></button>
                            <button data-command="unlink" type="button"></button>
                            
                            <div class="divider"></div>
                            <button data-command="indent" type="button"></button>
                            <button data-command="outdent" type="button"></button>
                            
                            <div class="divider"></div>
                            <button data-command="subscript" type="button"></button>                           
                            <button data-command="superscript" type="button"></button>                           
                                                        
                            <div class="divider"></div>
                            <button data-command="removeFormat" type="button"></button>
                        </div>`;

        $('div.blockee-editor').append(render);

        var tempElement = $('<div></div>');  // Crée une <div> vide
        if(contents !== '')
        {
            contents = contents.replaceAll('&lt;', '<');
            contents = contents.replaceAll('&gt;', '>');
            contents = contents.replaceAll('&amp;amp;nbsp;', ' ');
            contents = contents.replaceAll('&amp;nbsp;', ' ');
            contents = contents.replaceAll('&nbsp;', ' ');
            contents = contents.replaceAll('&amp;amp;', '&amp;');


            let $html = $('<div></div>').html(contents);

            blockeeEditor.ScrollviewAfterInsert = false;
            $html.find('.blockee-editor-block-element').each(function(index, element){

                let block = $(element)[0].outerHTML;
                let block_type = $(element).data('blockee-type');



                if(block_type === undefined)
                {
                    block_type = $(element).prop('tagName').toLowerCase();
                }

                blockeeEditor.blockInsert(block_type, block);
            });

            blockeeEditor.ScrollviewAfterInsert = true;

            $('.blockee-editor .blockee-editor__content').scrollTop(0);


        }

        // custom-css
        let custom_css_file = $("textarea.blockee-editor").data('blockee-css-file');
        if(typeof custom_css_file !== 'undefined' && custom_css_file !== '')
        {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = custom_css_file;
            link.onload = () => {
                console.info(`Successfully to load ${custom_css_file}`);
            };
            link.onerror = () => {
                console.error(`Failed to load ${custom_css_file}`);
                reject(new Error(`Failed to load ${custom_css_file}`));
            };
            document.head.appendChild(link);


        }



    }

    static loadPlugin(pluginPath) {

        return new Promise((resolve, reject) => {

            const extension = pluginPath.split('.').pop().toLowerCase();

            if (extension === 'css') {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = pluginPath;
                link.onload = () => {
                    // console.log(`${pluginPath} loaded`);
                    resolve();
                };
                link.onerror = () => {
                    console.error(`Failed to load ${pluginPath}`);
                    reject(new Error(`Failed to load ${pluginPath}`));
                };
                document.head.appendChild(link);
            }
            else
            {
                const script = document.createElement('script');
                script.src = pluginPath;
                script.async = false;
                document.head.appendChild(script);

                script.onload = () => {
                    // console.log(`${pluginPath} loaded`);
                    resolve();
                };
                script.onerror = () => {
                    console.error(`Failed to load ${pluginPath}`);
                    reject(new Error(`Failed to load ${pluginPath}`));
                };
            }

        });
    }

    static blockAdd(){
        blockeeEditor.actionMenuShow('block');
    }

    static blockDuplicate(){

        let html = $(".blockee-editor-block.active")[0].outerHTML;
        html = html.replace('active', 'pre-active');

        $(".blockee-editor-block.active").after(html);
        blockeeEditor.actionMenuHide();
    }

    static blockDelete(){
        $(".blockee-editor-block.active").remove();
        blockeeEditor.actionMenuHide();
    }

    static blockUp(){

        let $block = $(".blockee-editor-block.active");
        let $prevChild = $block.prev('.blockee-editor-block');

        if($(".blockee-editor-block.active").index() === 0)
        {
            blockeeEditor.actionMenuHide();
            return;
        }

        $block.insertBefore($prevChild);
        blockeeEditor.actionMenuHide();

    }

    static blockDown()
    {
        let $block = $(".blockee-editor-block.active");
        let $nextChild = $block.next('.blockee-editor-block');


        $block.insertAfter($nextChild);
        blockeeEditor.actionMenuHide();

    }


    static insertBlockHtml(contents)
    {
        contents = `<div data-blockee-type="html" class="blockee-editor-block-element blockee-editor-block-element--html">${contents}</div>`
        blockeeEditor.blockInsert('html', contents, false);
    }

    static insertHtmlAtCaret(html)
    {
        let selection = window.getSelection();
        if (!selection.rangeCount) return false;
        let range = selection.getRangeAt(0);
        range.deleteContents();

        let div = document.createElement('div');
        div.innerHTML = html;
        let frag = document.createDocumentFragment(), node, last_node;
        while ((node = div.firstChild)) {
            last_node = frag.appendChild(node);
        }
        range.insertNode(frag);

        if (last_node) {
            range = range.cloneRange();
            range.setStartAfter(last_node);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
        }

        blockeeEditor.update();
    }


    static blockInsert(type, contents, open_settings=false)
    {
        let contents2 = `<div class="blockee-editor-block" data-type="${type}">
                                        <div class="blockee-editor-block__option" onclick="blockeeEditor.actionBlockMenuShow(this)"></div>
                                        <div class="blockee-editor-block__contents">${contents}</div>
                                 </div>`;

        if(!$('.blockee-editor-block.active').length)
        {
            $('.blockee-editor__content').append(contents2);


            $('.blockee-editor__content').scrollTop($('.blockee-editor__content')[0].scrollHeight);

            if($('.blockee-editor__content .blockee-editor-block:last [contenteditable]').length)
            {
                $('.blockee-editor__content .blockee-editor-block:last [contenteditable]:eq(0)').focus().select();
            }

            // scroll into view
            if(blockeeEditor.ScrollviewAfterInsert)
            {
                setTimeout(() => {

                    $('.blockee-editor__content .blockee-editor-block:last')[0].scrollIntoView({
                        behavior: 'smooth',  // Optional: makes the scrolling smooth
                        block: 'center',     // Options: 'start', 'center', 'end', 'nearest' (where it should appear)
                        inline: 'nearest'    // Options: 'start', 'center', 'end', 'nearest' (horizontal alignment)
                    });

                }, 250);

            }





            if(open_settings)
            {
                $('.blockee-editor-block').removeClass('active');

                setTimeout(function(){
                    $('.blockee-editor-block:last').addClass('active');
                    $('.blockee-editor__menu-block').data('blockee-type', type);
                    blockeeEditor.blockSettingsOpen();
                } , 300);
            }


            blockeeEditor.update();



        }
        else
        {
            contents2 = contents2.replace("class=\"blockee-editor-block\"", "class=\"blockee-editor-block pre-active\"");

            $('.blockee-editor-block.active').after(contents2).removeClass('active');
            blockeeEditor.actionMenuHide();

            $('.blockee-editor__content .blockee-editor-block.pre-active').removeClass('pre-active').addClass('active');

            if($('.blockee-editor__content .blockee-editor-block.active [contenteditable]').length)
                $('.blockee-editor__content .blockee-editor-block.active [contenteditable]:eq(0)').focus().select();


            if(blockeeEditor.ScrollviewAfterInsert)
            {
                setTimeout(function(){
                    // scroll into view
                    if($('.blockee-editor__content .blockee-editor-block.active').length)
                    {
                        $('.blockee-editor__content .blockee-editor-block.active')[0].scrollIntoView({
                            behavior: 'smooth',  // Optional: makes the scrolling smooth
                            block: 'center',     // Options: 'start', 'center', 'end', 'nearest' (where it should appear)
                            inline: 'nearest'    // Options: 'start', 'center', 'end', 'nearest' (horizontal alignment)
                        });
                    }

                }, 500);
            }

            blockeeEditor.update();

        }
    }


    static actionSourceOpen()
    {
        $('.blockee-editor-window-canvas').show();

        let source = $('textarea.blockee-editor').val();
        $('.blockee-editor-window--source textarea').val(source);

        let c_top = 20 + $(window).scrollTop();

        $('.blockee-editor-window--source').show().css('top', c_top);
    }

    static blockSourceExecute()
    {
        let contents = $('.blockee-editor-window--source textarea').val();

        $('textarea.blockee-editor').val(contents);

        // update editor
        $('.blockee-editor-block__contents').html('');

        blockeeEditor.ScrollviewAfterInsert = false;

        contents = contents.replaceAll('&lt;', '<');
        contents = contents.replaceAll('&gt;', '>');
        contents = contents.replaceAll('&nbsp;', ' ');
        let $html = $('<div></div>').html(contents);
        $html.find('.blockee-editor-block-element').each(function(index, element){

            let block = $(element)[0].outerHTML;
            let block_type = $(element).data('blockee-type');

            if(block_type === undefined)
            {
                block_type = $(element).prop('tagName').toLowerCase();
            }

            blockeeEditor.blockInsert(block_type, block);
        });

        blockeeEditor.ScrollviewAfterInsert = true;


        blockeeEditor.update();

        blockeeEditor.blockConfirmClose();
    }


    static actionFullscreen(node)
    {
        if(!document.fullscreenElement)
        {
            $(node).parents('.blockee-editor')[0].requestFullscreen();
            $('body').addClass('fullscreen');
            blockeeEditor.isFullScreened = true;
        }
        else
        {
            document.exitFullscreen();
            $('body').removeClass('fullscreen');
            blockeeEditor.isFullScreened = false;
        }
    }

    static actionSave()
    {
        blockeeEditor.update();
        $('.blockee-editor').parents('form').submit();
    }

    static update(state_cancel=true){

        let state_old = $('textarea.blockee-editor').val();

        let v = "";
        $('.blockee-editor-block-element').each(function(){
            v += $(this)[0].outerHTML+"\n";
        });

        v = v.replaceAll('id=""', '');
        v = v.replaceAll('width=""', '');
        v = v.replaceAll('height=""', '');
        v = v.replaceAll('class=""', '');

        if(state_old === v)return;


        $('textarea.blockee-editor').val(v);

        if(state_cancel)
        {
            blockeeEditor.undoStack.push($('.blockee-editor__content').html());
            blockeeEditor.redoStack = [];
        }

    }

    static actionUndo()
    {
        if(!blockeeEditor.undoStack.length)
        {
            $('.blockee-editor__content').html('');
            blockeeEditor.redoStack = [];
            blockeeEditor.update(false);
            return;
        }


        let contents = $('.blockee-editor__content').html();


        blockeeEditor.redoStack.push(contents);
        const previousState = blockeeEditor.undoStack.pop();
        $('.blockee-editor__content').html(previousState);

        blockeeEditor.update(false);

    }

    static actionRedo()
    {
        if(!blockeeEditor.redoStack.length)return;

        blockeeEditor.undoStack.push($('.blockee-editor__content').html());
        const nextState = blockeeEditor.redoStack.pop();
        $('.blockee-editor__content').html(nextState);

        blockeeEditor.update(false);

    }


    static actionTemplateOpen()
    {
        const name = blockeeEditorInstances[0].name;
        let uri = `/${APP_BACKEND_DIRNAME}/template/all/`;

        $.get(uri, function(contents){

            $('.blockee-editor-window--template .blockee-editor-window-body').html(contents);


            $('.blockee-editor-window--template').show();
            $('.blockee-editor-window-canvas').show();
            blockeeEditor.windowCenterY();

        });

    }



    static windowCenterY()
    {
        let ctop = ($(window).height() * 5 / 100) + $(window).scrollTop();
        $('.blockee-editor-window:visible').css('top', ctop + 'px');
    }


    static actionPlanOpen()
    {
        $('.blockee-editor-window-canvas').show();


        let str = '<ul>';
        $('.blockee-editor-block-element').each(function(index, element){

            let block_type = $(this).data('blockee-type');
            if (!block_type) {
                block_type = $(this)[0].nodeName.toLowerCase();
            }


            str += `<li data-index="${index}">
                         ${block_type}
                    </li>`;

        });


        str += '</ul>';

        $('.blockee-editor-window--plan .blockee-editor-window-body').html(str);

        $('.blockee-editor-window--plan').show();
        blockeeEditor.windowCenterY();

        $('.blockee-editor-window--plan .blockee-editor-window-body ul').sortable({
            axis: "y"
        });


    }

    static actionPlanExecute()
    {
        const $lis = $('.blockee-editor-window--plan li');
        if(!$lis.length)
            blockeeEditor.blockConfirmClose();

        let contents = '';
        $lis.each(function(){

            if(!empty(str))contents += '\n';

            contents += $('.blockee-editor-block-element').eq($(this).data('index'))[0].outerHTML;

        });

        // update textarea
        $('textarea.blockee-editor').val(contents);
        $('.blockee-editor-block__contents').html('');
        blockeeEditor.ScrollviewAfterInsert = false;

        contents = contents.replaceAll('&lt;', '<');
        contents = contents.replaceAll('&gt;', '>');
        contents = contents.replaceAll('&nbsp;', ' ');
        let $html = $('<div></div>').html(contents);
        $html.find('.blockee-editor-block-element').each(function(index, element){

            let block = $(element)[0].outerHTML;
            let block_type = $(element).data('blockee-type');

            if(block_type === undefined)
            {
                block_type = $(element).prop('tagName').toLowerCase();
            }

            blockeeEditor.blockInsert(block_type, block);
        });

        blockeeEditor.ScrollviewAfterInsert = true;
        blockeeEditor.update();
        blockeeEditor.blockConfirmClose();
    }

    static actionClearAll()
    {
        $('.blockee-editor-window-canvas').show();
        $('.blockee-editor-window--confirm').show();

        blockeeEditor.windowCenterY();

    }

    static actionMenuShow(source="")
    {
        let pos;
        let top = '30%';
        let left = '50%';
        let cur_scroll = $(window).scrollTop();

        if(source.toLowerCase() === "toolbar")
        {
            pos = $('.blockee-editor__button-add').offset();
            top = pos.top + 130;
            left = pos.left + 80;

            if($('body').hasClass('fullscreen'))
            {
                top = 60 + 130;
                left -= 80;
            }
        }

        if(source.toLowerCase() === "block")
        {
            pos = $('.blockee-editor__menu-block').offset();
            top = pos.top + 100;
            left = pos.left + 75;
            $('.blockee-editor__menu-block').hide();

            if($('body').hasClass('fullscreen'))
            {
                top = pos.top + 50 + cur_scroll;
                left = pos.left + 125;
            }

            if(top < 200)
                top = 200;
        }

        $('.blockee-editor__menu-plugin').css({top: top, left: left});


        $('.blockee-editor__menu-plugin').show();
        $('.blockee-editor-canvas').show();
        $('.blockee-editor__menu-plugin')[0].scrollTop = 0;
        $('.blockee-editor__menu-plugin input[type=search]').val('').change().focus();
    }

    static actionMenuHide()
    {
        $('.blockee-editor-block').removeClass('active');

        $('.blockee-editor__menu').hide();
        $('.blockee-editor-canvas').hide();

        blockeeEditor.update();

        event.stopImmediatePropagation();

    }

    static actionBlockMenuShow(node)
    {
        let cur_scroll;
        let top;
        let left;


        let current_block_type = $(node).parent('.blockee-editor-block').data('type');
        const signature = 'BlockeePlugin__'+current_block_type;
        let info = eval(signature+".info()");

        $('.blockee-editor__menu.blockee-editor__menu-block .blockee-editor__menu--header').text(info.name);

        if(info.settings === true)
            $('.blockee-editor__menu-block--li-configure').removeClass('disabled');
        else
            $('.blockee-editor__menu-block--li-configure').addClass('disabled');


        $('.blockee-editor__menu-block').data('blockee-type', current_block_type);
        $('.blockee-editor-block.active').removeClass('active');
        $(node).parent('.blockee-editor-block').addClass('active');

        // position
        const pos = $('.blockee-editor-block.active').offset();
        top = pos.top + 60;
        left = pos.left + 35;

        if($('body').hasClass('fullscreen'))
        {
            // top += (-1 * $(window).scrollTop()) + 50;
            top = blockeeEditorMouseY + 80;
            left += 50;
        }
        else
        {
            top = blockeeEditorMouseY + $(window).scrollTop() + 40;
        }


        $('.blockee-editor__menu-block').css({top: top, left:left}).show();
        $('.blockee-editor-canvas').show();



        // reposition outside boundaries
        let menuHeight = $('.blockee-editor__menu:visible').height();
        let screenHeight = window.innerHeight;
        let posY = blockeeEditorMouseY;

        if((posY + menuHeight) > screenHeight)
        {
            posY = screenHeight - (menuHeight / 2);
            $('.blockee-editor__menu').css({top: posY});
        }

    }

    static textToolbarLinkOpen(l_href, l_target, l_id, l_class, l_style)
    {
        const selection = window.getSelection();
        blockeeEditor.savedSelection = window.getSelection().getRangeAt(0);

        const $parent = $('.blockee-editor-window--link');

        $parent.find('input[name="text_link_href"]').val(l_href);
        $parent.find('input[name="text_link_target"]').val(l_target);
        $parent.find('input[name="text_link_id"]').val(l_id);
        $parent.find('input[name="text_link_class"]').val(l_class);
        $parent.find('input[name="text_link_style"]').val(l_style);

        $('.blockee-editor-window--link').show();
        $('.blockee-editor-window-canvas').show();
    }


    static blockSettingsOpen(){

        $('.blockee-editor__menu').hide();
        $('.blockee-editor-canvas').hide();

        let current_block_type = $('.blockee-editor__menu-block').data('blockee-type');
        const signature = 'BlockeePlugin__'+current_block_type;
        let info = eval(signature+".info()");
        let render;
        const advanced_label = blockeeEditor.i18n('advanced');
        const select_file = blockeeEditor.i18n('select_file');


        try {
            render = eval(signature+".settingsRender()");
        } catch (error) {
            console.error("An error occurred while executing the code :", error);
            render = "";
        }


        // build tabs
        let contents = `<ul class="blockee-editor-tabs">`;
        for(let i = 0; i < render.tabs.length; i++)
        {
            contents += `<li><a href="">${render.tabs[i].title}</a></li>`;
        }

        // tab advanced
        if(render.tab_advanced)
        {
            contents += `<li><a href="#">${advanced_label}</a></li>`;
        }

        contents += `</ul>`;

        // add tabs
        for(let i = 0; i < render.tabs.length; i++)
        {
            contents += `<div class="blockee-editor-tab--content">${render.tabs[i].contents}</div>`;
        }

        let text_color ='';
        let bg_color ='';
        if(render.tab_advanced)
        {
            // advanced
            let $node = blockeeEditor.blockGetNode();

            let id = $node.attr('id') ?? '';
            let o_class = blockeeEditor.removeSpecialClass($node.attr('class')) ?? '';
            let style = $node.attr('style') ?? '';

            //  get bg_image
            let bg_image = $node.attr('data-bg-image') ?? '';
            if(bg_image !== '')
            {
                style = style.replace(`background-image:url('${bg_image}');`, '');
            }

            // margin
            let margin = $node.attr('data-margin') ?? '';
            let margin_top = margin.indexOf('mt-') === -1 ? '' : margin.match(/mt-(\d+)/)[1];
            let margin_bottom = margin.indexOf('mb-') === -1 ? '' : margin.match(/mb-(\d+)/)[1];
            let margin_left = margin.indexOf('ms-') === -1 ? '' : margin.match(/ms-(\d+)/)[1];
            let margin_right = margin.indexOf('me-') === -1 ? '' : margin.match(/me-(\d+)/)[1];

            // padding
            let padding = $node.attr('data-padding') ?? '';
            let padding_top = padding.indexOf('pt-') === -1 ? '' : padding.match(/pt-(\d+)/)[1];
            let padding_bottom = padding.indexOf('pb-') === -1 ? '' : padding.match(/pb-(\d+)/)[1];
            let padding_left = padding.indexOf('ps-') === -1 ? '' : padding.match(/ps-(\d+)/)[1];
            let padding_right = padding.indexOf('pe-') === -1 ? '' : padding.match(/pe-(\d+)/)[1];

            let h_align_left_selected = $node.attr('data-align') !== 'left' ? '' : 'selected';
            let h_align_center_selected = $node.attr('data-align') !== 'center' ? '' : 'selected';
            let h_align_right_selected = $node.attr('data-align') !== 'right' ? '' : 'selected';
            let h_align_justify_selected = $node.attr('data-align') !== 'justify' ? '' : 'selected';

            text_color = $node.attr('data-color') ?? '';
            bg_color = $node.attr('data-bg-color') ?? '';


            contents += `<div class="blockee-editor-tab--content blockee-editor-tab--content-advanced">
                   
                   <div class="blockee-editor-form-row">                                
                        <div class="blockee-editor-form-label">H align</div>
                        <select name="h_align">
                            <option value=""></option>
                            <option value="left" ${h_align_left_selected}>left</option>
                            <option value="center" ${h_align_center_selected}>center</option>
                            <option value="right" ${h_align_right_selected}>right</option>
                            <option value="justify" ${h_align_justify_selected}>justify</option>
                        </select>
                   </div>                                     
                   
                   <div class="blockee-editor-form-row">                                
                        <div class="blockee-editor-form-label">Margin</div>
                        <span class="label-group">↑</span><input type="number" name="margin_top" value="${margin_top}" step="1" min="0" max="10">
                        <span class="label-group">↓</span><input type="number" name="margin_bottom" value="${margin_bottom}" step="1" min="0" max="10">
                        <span class="label-group">←</span><input type="number" name="margin_left" value="${margin_left}" step="1" min="0" max="10">
                        <span class="label-group">→</span><input type="number" name="margin_right" value="${margin_right}" step="1" min="0" max="10">
                   </div>
                   
                   <div class="blockee-editor-form-row">                                
                        <div class="blockee-editor-form-label">Padding</div>
                        <span class="label-group">↑</span><input type="number" name="padding_top" value="${padding_top}" step="1" min="0" max="10">
                        <span class="label-group">↓</span><input type="number" name="padding_bottom" value="${padding_bottom}" step="1" min="0" max="10">
                        <span class="label-group">←</span><input type="number" name="padding_left" value="${padding_left}" step="1" min="0" max="10">
                        <span class="label-group">→</span><input type="number" name="padding_right" value="${padding_right}" step="1" min="0" max="10">
                   </div>
                   
                   <div class="blockee-editor-form-row">                                
                        <div class="blockee-editor-form-label">Text color</div>
                        <select name="color">
                            <option></option>    
                            <option value="aliceblue" style="background: aliceblue; color:black">AliceBlue</option>
                            <option value="antiquewhite" style="background: antiquewhite; color:black">AntiqueWhite</option>
                            <option value="aqua" style="background: aqua; color:black">Aqua</option>
                            <option value="aquamarine" style="background: aquamarine; color:black">Aquamarine</option>
                            <option value="azure" style="background: azure; color:black">Azure</option>
                            <option value="beige" style="background: beige; color:black">Beige</option>
                            <option value="bisque" style="background: bisque; color:black">Bisque</option>
                            <option value="black" style="background: black; color:white">Black</option>
                            <option value="blanchedalmond" style="background: blanchedalmond; color:black">BlanchedAlmond</option>
                            <option value="blue" style="background: blue; color:white">Blue</option>
                            <option value="blueviolet" style="background: blueviolet; color:white">BlueViolet</option>
                            <option value="brown" style="background: brown; color:white">Brown</option>
                            <option value="burlywood" style="background: burlywood; color:black">BurlyWood</option>
                            <option value="cadetblue" style="background: cadetblue; color:white">CadetBlue</option>
                            <option value="chartreuse" style="background: chartreuse; color:black">Chartreuse</option>
                            <option value="chocolate" style="background: chocolate; color:white">Chocolate</option>
                            <option value="coral" style="background: coral; color:white">Coral</option>
                            <option value="cornflowerblue" style="background: cornflowerblue; color:white">CornflowerBlue</option>
                            <option value="cornsilk" style="background: cornsilk; color:black">Cornsilk</option>
                            <option value="crimson" style="background: crimson; color:white">Crimson</option>
                            <option value="cyan" style="background: cyan; color:black">Cyan</option>
                            <option value="darkblue" style="background: darkblue; color:white">DarkBlue</option>
                            <option value="darkcyan" style="background: darkcyan; color:white">DarkCyan</option>
                            <option value="darkgoldenrod" style="background: darkgoldenrod; color:white">DarkGoldenRod</option>
                            <option value="darkgray" style="background: darkgray; color:black">DarkGray</option>
                            <option value="darkgreen" style="background: darkgreen; color:white">DarkGreen</option>
                            <option value="darkkhaki" style="background: darkkhaki; color:black">DarkKhaki</option>
                            <option value="darkmagenta" style="background: darkmagenta; color:white">DarkMagenta</option>
                            <option value="darkolivegreen" style="background: darkolivegreen; color:white">DarkOliveGreen</option>
                            <option value="darkorange" style="background: darkorange; color:white">DarkOrange</option>
                            <option value="darkorchid" style="background: darkorchid; color:white">DarkOrchid</option>
                            <option value="darkred" style="background: darkred; color:white">DarkRed</option>
                            <option value="darksalmon" style="background: darksalmon; color:black">DarkSalmon</option>
                            <option value="darkseagreen" style="background: darkseagreen; color:black">DarkSeaGreen</option>
                            <option value="darkslateblue" style="background: darkslateblue; color:white">DarkSlateBlue</option>
                            <option value="darkslategray" style="background: darkslategray; color:white">DarkSlateGray</option>
                            <option value="darkturquoise" style="background: darkturquoise; color:black">DarkTurquoise</option>
                            <option value="darkviolet" style="background: darkviolet; color:white">DarkViolet</option>
                            <option value="deeppink" style="background: deeppink; color:white">DeepPink</option>
                            <option value="deepskyblue" style="background: deepskyblue; color:black">DeepSkyBlue</option>
                            <option value="dimgray" style="background: dimgray; color:white">DimGray</option>
                            <option value="dodgerblue" style="background: dodgerblue; color:white">DodgerBlue</option>
                            <option value="firebrick" style="background: firebrick; color:white">FireBrick</option>
                            <option value="floralwhite" style="background: floralwhite; color:black">FloralWhite</option>
                            <option value="forestgreen" style="background: forestgreen; color:white">ForestGreen</option>
                            <option value="fuchsia" style="background: fuchsia; color:black">Fuchsia</option>
                            <option value="gainsboro" style="background: gainsboro; color:black">Gainsboro</option>
                            <option value="ghostwhite" style="background: ghostwhite; color:black">GhostWhite</option>
                            <option value="gold" style="background: gold; color:black">Gold</option>
                            <option value="goldenrod" style="background: goldenrod; color:black">GoldenRod</option>
                            <option value="gray" style="background: gray; color:white">Gray</option>
                            <option value="green" style="background: green; color:white">Green</option>
                            <option value="greenyellow" style="background: greenyellow; color:black">GreenYellow</option>
                            <option value="honeydew" style="background: honeydew; color:black">HoneyDew</option>
                            <option value="hotpink" style="background: hotpink; color:black">HotPink</option>
                            <option value="indianred" style="background: indianred; color:white">IndianRed</option>
                            <option value="indigo" style="background: indigo; color:white">Indigo</option>
                            <option value="ivory" style="background: ivory; color:black">Ivory</option>
                            <option value="khaki" style="background: khaki; color:black">Khaki</option>
                            <option value="lavender" style="background: lavender; color:black">Lavender</option>
                            <option value="lavenderblush" style="background: lavenderblush; color:black">LavenderBlush</option>
                            <option value="lawngreen" style="background: lawngreen; color:black">LawnGreen</option>
                            <option value="lemonchiffon" style="background: lemonchiffon; color:black">LemonChiffon</option>
                            <option value="lightblue" style="background: lightblue; color:black">LightBlue</option>
                            <option value="lightcoral" style="background: lightcoral; color:black">LightCoral</option>
                            <option value="lightcyan" style="background: lightcyan; color:black">LightCyan</option>
                            <option value="lightgoldenrodyellow" style="background: lightgoldenrodyellow; color:black">LightGoldenRodYellow</option>
                            <option value="lightgray" style="background: lightgray; color:black">LightGray</option>
                            <option value="lightgreen" style="background: lightgreen; color:black">LightGreen</option>
                            <option value="lightpink" style="background: lightpink; color:black">LightPink</option>
                            <option value="lightsalmon" style="background: lightsalmon; color:black">LightSalmon</option>
                            <option value="lightseagreen" style="background: lightseagreen; color:black">LightSeaGreen</option>
                            <option value="lightskyblue" style="background: lightskyblue; color:black">LightSkyBlue</option>
                            <option value="lightslategray" style="background: lightslategray; color:black">LightSlateGray</option>
                            <option value="lightsteelblue" style="background: lightsteelblue; color:black">LightSteelBlue</option>
                            <option value="lightyellow" style="background: lightyellow; color:black">LightYellow</option>
                            <option value="lime" style="background: lime; color:black">Lime</option>
                            <option value="limegreen" style="background: limegreen; color:black">LimeGreen</option>
                            <option value="linen" style="background: linen; color:black">Linen</option>
                            <option value="magenta" style="background: magenta; color:black">Magenta</option>
                            <option value="maroon" style="background: maroon; color:white">Maroon</option>
                            <option value="mediumaquamarine" style="background: mediumaquamarine; color:black">MediumAquaMarine</option>
                            <option value="mediumblue" style="background: mediumblue; color:white">MediumBlue</option>
                            <option value="mediumorchid" style="background: mediumorchid; color:white">MediumOrchid</option>
                            <option value="mediumpurple" style="background: mediumpurple; color:white">MediumPurple</option>
                            <option value="mediumseagreen" style="background: mediumseagreen; color:black">MediumSeaGreen</option>
                            <option value="mediumslateblue" style="background: mediumslateblue; color:white">MediumSlateBlue</option>
                            <option value="mediumspringgreen" style="background: mediumspringgreen; color:black">MediumSpringGreen</option>
                            <option value="mediumturquoise" style="background: mediumturquoise; color:black">MediumTurquoise</option>
                            <option value="mediumvioletred" style="background: mediumvioletred; color:white">MediumVioletRed</option>
                            <option value="midnightblue" style="background: midnightblue; color:white">MidnightBlue</option>
                            <option value="mintcream" style="background: mintcream; color:black">MintCream</option>
                            <option value="mistyrose" style="background: mistyrose; color:black">MistyRose</option>
                            <option value="moccasin" style="background: moccasin; color:black">Moccasin</option>
                            <option value="navajowhite" style="background: navajowhite; color:black">NavajoWhite</option>
                            <option value="navy" style="background: navy; color:white">Navy</option>
                            <option value="oldlace" style="background: oldlace; color:black">OldLace</option>
                            <option value="olive" style="background: olive; color:white">Olive</option>
                            <option value="olivedrab" style="background: olivedrab; color:white">OliveDrab</option>
                            <option value="orange" style="background: orange; color:black">Orange</option>
                            <option value="orangered" style="background: orangered; color:white">OrangeRed</option>
                            <option value="orchid" style="background: orchid; color:black">Orchid</option>
                            <option value="palegoldenrod" style="background: palegoldenrod; color:black">PaleGoldenRod</option>
                            <option value="palegreen" style="background: palegreen; color:black">PaleGreen</option>
                            <option value="paleturquoise" style="background: paleturquoise; color:black">PaleTurquoise</option>
                            <option value="palevioletred" style="background: palevioletred; color:black">PaleVioletRed</option>
                            <option value="papayawhip" style="background: papayawhip; color:black">PapayaWhip</option>
                            <option value="peachpuff" style="background: peachpuff; color:black">PeachPuff</option>
                            <option value="peru" style="background: peru; color:white">Peru</option>
                            <option value="pink" style="background: pink; color:black">Pink</option>
                            <option value="plum" style="background: plum; color:black">Plum</option>
                            <option value="powderblue" style="background: powderblue; color:black">PowderBlue</option>
                            <option value="purple" style="background: purple; color:white">Purple</option>
                            <option value="rebeccapurple" style="background: rebeccapurple; color:white">RebeccaPurple</option>
                            <option value="red" style="background: red; color:white">Red</option>
                            <option value="rosybrown" style="background: rosybrown; color:white">RosyBrown</option>
                            <option value="royalblue" style="background: royalblue; color:white">RoyalBlue</option>
                            <option value="saddlebrown" style="background: saddlebrown; color:white">SaddleBrown</option>
                            <option value="salmon" style="background: salmon; color:black">Salmon</option>
                            <option value="sandybrown" style="background: sandybrown; color:black">SandyBrown</option>
                            <option value="seagreen" style="background: seagreen; color:white">SeaGreen</option>
                            <option value="seashell" style="background: seashell; color:black">SeaShell</option>
                            <option value="sienna" style="background: sienna; color:white">Sienna</option>
                            <option value="silver" style="background: silver; color:black">Silver</option>
                            <option value="skyblue" style="background: skyblue; color:black">SkyBlue</option>
                            <option value="slateblue" style="background: slateblue; color:white">SlateBlue</option>
                            <option value="slategray" style="background: slategray; color:white">SlateGray</option>
                            <option value="snow" style="background: snow; color:black">Snow</option>
                            <option value="springgreen" style="background: springgreen; color:black">SpringGreen</option>
                            <option value="steelblue" style="background: steelblue; color:white">SteelBlue</option>
                            <option value="tan" style="background: tan; color:black">Tan</option>
                            <option value="teal" style="background: teal; color:white">Teal</option>
                            <option value="thistle" style="background: thistle; color:black">Thistle</option>
                            <option value="tomato" style="background: tomato; color:white">Tomato</option>
                            <option value="turquoise" style="background: turquoise; color:black">Turquoise</option>
                            <option value="violet" style="background: violet; color:black">Violet</option>
                            <option value="wheat" style="background: wheat; color:black">Wheat</option>
                            <option value="white" style="background: white; color:black">White</option>
                            <option value="whitesmoke" style="background: whitesmoke; color:black">WhiteSmoke</option>
                            <option value="yellow" style="background: yellow; color:black">Yellow</option>
                            <option value="yellowgreen" style="background: yellowgreen; color:black">YellowGreen</option>
                        </select>
                   </div>
                   
                   <div class="blockee-editor-form-row">                                
                        <div class="blockee-editor-form-label">Bg color</div>
                        <select name="bg_color">
                            <option></option>    
                            <option value="aliceblue" style="background: aliceblue; color:black">AliceBlue</option>
                            <option value="antiquewhite" style="background: antiquewhite; color:black">AntiqueWhite</option>
                            <option value="aqua" style="background: aqua; color:black">Aqua</option>
                            <option value="aquamarine" style="background: aquamarine; color:black">Aquamarine</option>
                            <option value="azure" style="background: azure; color:black">Azure</option>
                            <option value="beige" style="background: beige; color:black">Beige</option>
                            <option value="bisque" style="background: bisque; color:black">Bisque</option>
                            <option value="black" style="background: black; color:white">Black</option>
                            <option value="blanchedalmond" style="background: blanchedalmond; color:black">BlanchedAlmond</option>
                            <option value="blue" style="background: blue; color:white">Blue</option>
                            <option value="blueviolet" style="background: blueviolet; color:white">BlueViolet</option>
                            <option value="brown" style="background: brown; color:white">Brown</option>
                            <option value="burlywood" style="background: burlywood; color:black">BurlyWood</option>
                            <option value="cadetblue" style="background: cadetblue; color:white">CadetBlue</option>
                            <option value="chartreuse" style="background: chartreuse; color:black">Chartreuse</option>
                            <option value="chocolate" style="background: chocolate; color:white">Chocolate</option>
                            <option value="coral" style="background: coral; color:white">Coral</option>
                            <option value="cornflowerblue" style="background: cornflowerblue; color:white">CornflowerBlue</option>
                            <option value="cornsilk" style="background: cornsilk; color:black">Cornsilk</option>
                            <option value="crimson" style="background: crimson; color:white">Crimson</option>
                            <option value="cyan" style="background: cyan; color:black">Cyan</option>
                            <option value="darkblue" style="background: darkblue; color:white">DarkBlue</option>
                            <option value="darkcyan" style="background: darkcyan; color:white">DarkCyan</option>
                            <option value="darkgoldenrod" style="background: darkgoldenrod; color:white">DarkGoldenRod</option>
                            <option value="darkgray" style="background: darkgray; color:black">DarkGray</option>
                            <option value="darkgreen" style="background: darkgreen; color:white">DarkGreen</option>
                            <option value="darkkhaki" style="background: darkkhaki; color:black">DarkKhaki</option>
                            <option value="darkmagenta" style="background: darkmagenta; color:white">DarkMagenta</option>
                            <option value="darkolivegreen" style="background: darkolivegreen; color:white">DarkOliveGreen</option>
                            <option value="darkorange" style="background: darkorange; color:white">DarkOrange</option>
                            <option value="darkorchid" style="background: darkorchid; color:white">DarkOrchid</option>
                            <option value="darkred" style="background: darkred; color:white">DarkRed</option>
                            <option value="darksalmon" style="background: darksalmon; color:black">DarkSalmon</option>
                            <option value="darkseagreen" style="background: darkseagreen; color:black">DarkSeaGreen</option>
                            <option value="darkslateblue" style="background: darkslateblue; color:white">DarkSlateBlue</option>
                            <option value="darkslategray" style="background: darkslategray; color:white">DarkSlateGray</option>
                            <option value="darkturquoise" style="background: darkturquoise; color:black">DarkTurquoise</option>
                            <option value="darkviolet" style="background: darkviolet; color:white">DarkViolet</option>
                            <option value="deeppink" style="background: deeppink; color:white">DeepPink</option>
                            <option value="deepskyblue" style="background: deepskyblue; color:black">DeepSkyBlue</option>
                            <option value="dimgray" style="background: dimgray; color:white">DimGray</option>
                            <option value="dodgerblue" style="background: dodgerblue; color:white">DodgerBlue</option>
                            <option value="firebrick" style="background: firebrick; color:white">FireBrick</option>
                            <option value="floralwhite" style="background: floralwhite; color:black">FloralWhite</option>
                            <option value="forestgreen" style="background: forestgreen; color:white">ForestGreen</option>
                            <option value="fuchsia" style="background: fuchsia; color:black">Fuchsia</option>
                            <option value="gainsboro" style="background: gainsboro; color:black">Gainsboro</option>
                            <option value="ghostwhite" style="background: ghostwhite; color:black">GhostWhite</option>
                            <option value="gold" style="background: gold; color:black">Gold</option>
                            <option value="goldenrod" style="background: goldenrod; color:black">GoldenRod</option>
                            <option value="gray" style="background: gray; color:white">Gray</option>
                            <option value="green" style="background: green; color:white">Green</option>
                            <option value="greenyellow" style="background: greenyellow; color:black">GreenYellow</option>
                            <option value="honeydew" style="background: honeydew; color:black">HoneyDew</option>
                            <option value="hotpink" style="background: hotpink; color:black">HotPink</option>
                            <option value="indianred" style="background: indianred; color:white">IndianRed</option>
                            <option value="indigo" style="background: indigo; color:white">Indigo</option>
                            <option value="ivory" style="background: ivory; color:black">Ivory</option>
                            <option value="khaki" style="background: khaki; color:black">Khaki</option>
                            <option value="lavender" style="background: lavender; color:black">Lavender</option>
                            <option value="lavenderblush" style="background: lavenderblush; color:black">LavenderBlush</option>
                            <option value="lawngreen" style="background: lawngreen; color:black">LawnGreen</option>
                            <option value="lemonchiffon" style="background: lemonchiffon; color:black">LemonChiffon</option>
                            <option value="lightblue" style="background: lightblue; color:black">LightBlue</option>
                            <option value="lightcoral" style="background: lightcoral; color:black">LightCoral</option>
                            <option value="lightcyan" style="background: lightcyan; color:black">LightCyan</option>
                            <option value="lightgoldenrodyellow" style="background: lightgoldenrodyellow; color:black">LightGoldenRodYellow</option>
                            <option value="lightgray" style="background: lightgray; color:black">LightGray</option>
                            <option value="lightgreen" style="background: lightgreen; color:black">LightGreen</option>
                            <option value="lightpink" style="background: lightpink; color:black">LightPink</option>
                            <option value="lightsalmon" style="background: lightsalmon; color:black">LightSalmon</option>
                            <option value="lightseagreen" style="background: lightseagreen; color:black">LightSeaGreen</option>
                            <option value="lightskyblue" style="background: lightskyblue; color:black">LightSkyBlue</option>
                            <option value="lightslategray" style="background: lightslategray; color:black">LightSlateGray</option>
                            <option value="lightsteelblue" style="background: lightsteelblue; color:black">LightSteelBlue</option>
                            <option value="lightyellow" style="background: lightyellow; color:black">LightYellow</option>
                            <option value="lime" style="background: lime; color:black">Lime</option>
                            <option value="limegreen" style="background: limegreen; color:black">LimeGreen</option>
                            <option value="linen" style="background: linen; color:black">Linen</option>
                            <option value="magenta" style="background: magenta; color:black">Magenta</option>
                            <option value="maroon" style="background: maroon; color:white">Maroon</option>
                            <option value="mediumaquamarine" style="background: mediumaquamarine; color:black">MediumAquaMarine</option>
                            <option value="mediumblue" style="background: mediumblue; color:white">MediumBlue</option>
                            <option value="mediumorchid" style="background: mediumorchid; color:white">MediumOrchid</option>
                            <option value="mediumpurple" style="background: mediumpurple; color:white">MediumPurple</option>
                            <option value="mediumseagreen" style="background: mediumseagreen; color:black">MediumSeaGreen</option>
                            <option value="mediumslateblue" style="background: mediumslateblue; color:white">MediumSlateBlue</option>
                            <option value="mediumspringgreen" style="background: mediumspringgreen; color:black">MediumSpringGreen</option>
                            <option value="mediumturquoise" style="background: mediumturquoise; color:black">MediumTurquoise</option>
                            <option value="mediumvioletred" style="background: mediumvioletred; color:white">MediumVioletRed</option>
                            <option value="midnightblue" style="background: midnightblue; color:white">MidnightBlue</option>
                            <option value="mintcream" style="background: mintcream; color:black">MintCream</option>
                            <option value="mistyrose" style="background: mistyrose; color:black">MistyRose</option>
                            <option value="moccasin" style="background: moccasin; color:black">Moccasin</option>
                            <option value="navajowhite" style="background: navajowhite; color:black">NavajoWhite</option>
                            <option value="navy" style="background: navy; color:white">Navy</option>
                            <option value="oldlace" style="background: oldlace; color:black">OldLace</option>
                            <option value="olive" style="background: olive; color:white">Olive</option>
                            <option value="olivedrab" style="background: olivedrab; color:white">OliveDrab</option>
                            <option value="orange" style="background: orange; color:black">Orange</option>
                            <option value="orangered" style="background: orangered; color:white">OrangeRed</option>
                            <option value="orchid" style="background: orchid; color:black">Orchid</option>
                            <option value="palegoldenrod" style="background: palegoldenrod; color:black">PaleGoldenRod</option>
                            <option value="palegreen" style="background: palegreen; color:black">PaleGreen</option>
                            <option value="paleturquoise" style="background: paleturquoise; color:black">PaleTurquoise</option>
                            <option value="palevioletred" style="background: palevioletred; color:black">PaleVioletRed</option>
                            <option value="papayawhip" style="background: papayawhip; color:black">PapayaWhip</option>
                            <option value="peachpuff" style="background: peachpuff; color:black">PeachPuff</option>
                            <option value="peru" style="background: peru; color:white">Peru</option>
                            <option value="pink" style="background: pink; color:black">Pink</option>
                            <option value="plum" style="background: plum; color:black">Plum</option>
                            <option value="powderblue" style="background: powderblue; color:black">PowderBlue</option>
                            <option value="purple" style="background: purple; color:white">Purple</option>
                            <option value="rebeccapurple" style="background: rebeccapurple; color:white">RebeccaPurple</option>
                            <option value="red" style="background: red; color:white">Red</option>
                            <option value="rosybrown" style="background: rosybrown; color:white">RosyBrown</option>
                            <option value="royalblue" style="background: royalblue; color:white">RoyalBlue</option>
                            <option value="saddlebrown" style="background: saddlebrown; color:white">SaddleBrown</option>
                            <option value="salmon" style="background: salmon; color:black">Salmon</option>
                            <option value="sandybrown" style="background: sandybrown; color:black">SandyBrown</option>
                            <option value="seagreen" style="background: seagreen; color:white">SeaGreen</option>
                            <option value="seashell" style="background: seashell; color:black">SeaShell</option>
                            <option value="sienna" style="background: sienna; color:white">Sienna</option>
                            <option value="silver" style="background: silver; color:black">Silver</option>
                            <option value="skyblue" style="background: skyblue; color:black">SkyBlue</option>
                            <option value="slateblue" style="background: slateblue; color:white">SlateBlue</option>
                            <option value="slategray" style="background: slategray; color:white">SlateGray</option>
                            <option value="snow" style="background: snow; color:black">Snow</option>
                            <option value="springgreen" style="background: springgreen; color:black">SpringGreen</option>
                            <option value="steelblue" style="background: steelblue; color:white">SteelBlue</option>
                            <option value="tan" style="background: tan; color:black">Tan</option>
                            <option value="teal" style="background: teal; color:white">Teal</option>
                            <option value="thistle" style="background: thistle; color:black">Thistle</option>
                            <option value="tomato" style="background: tomato; color:white">Tomato</option>
                            <option value="turquoise" style="background: turquoise; color:black">Turquoise</option>
                            <option value="violet" style="background: violet; color:black">Violet</option>
                            <option value="wheat" style="background: wheat; color:black">Wheat</option>
                            <option value="white" style="background: white; color:black">White</option>
                            <option value="whitesmoke" style="background: whitesmoke; color:black">WhiteSmoke</option>
                            <option value="yellow" style="background: yellow; color:black">Yellow</option>
                            <option value="yellowgreen" style="background: yellowgreen; color:black">YellowGreen</option>
                        </select>

                        
                   </div>
                   
                   <div class="blockee-editor-form-row">                                
                        <div class="blockee-editor-form-label">Bg image</div>
                        <input type="text" name="bg_image" value="${bg_image}"> 
                        <button type="button" class="blockee-editor-form-button blockee-editor-form-button-filemanager" onclick="blockeeEditor.fileManagerOpen('bg_image', '&filter=image')">${select_file}...</button> 
                   </div>                                                         
                                   
                   <div class="blockee-editor-form-row">                                
                        <div class="blockee-editor-form-label">Id</div>
                        <input type="text" name="id" value="${id}">
                   </div>
                   
                   <div class="blockee-editor-form-row">
                        <div class="blockee-editor-form-label">Class</div>
                        <input type="text" name="class" value="${o_class}">
                   </div>
                   
                   <div class="blockee-editor-form-row">                                
                        <div class="blockee-editor-form-label">Style</div>
                        <input type="text" name="style" value="${style}">
                   </div>                                   

            </div>`;
        }

        $('.blockee-editor-window--settings .blockee-editor-window-body').html(contents);


        // select color
        if(render.tab_advanced)
        {
            if(text_color !== '')
            {
                $('.blockee-editor-window--settings .blockee-editor-window-body select[name="color"]').val(text_color);
            }

            if(bg_color !== '')
            {
                $('.blockee-editor-window--settings .blockee-editor-window-body select[name="bg_color"]').val(bg_color);
            }
        }


        // allow tab in textaera
        $('.blockee-editor-window--settings textarea').on('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = this.selectionStart;
                const end = this.selectionEnd;

                if (e.shiftKey)return;

                this.value = this.value.substring(0, start) + "\t" + this.value.substring(end);
                this.selectionStart = this.selectionEnd = start + 1;

            }
        });

        $('.blockee-editor-window-canvas').show();
        $('.blockee-editor-window--settings').show();

        blockeeEditor.windowCenterY();




        $('.blockee-editor-window-body .blockee-editor-tabs a:eq(0)').click();


    }

    static blockSettingsClose()
    {
        $('.blockee-editor-window-canvas').hide();
        $('.blockee-editor-window').hide();

    }

    static blockConfirmExecute()
    {
        $('.blockee-editor__content').html('');
        blockeeEditor.update();
        blockeeEditor.blockSettingsClose();
    }

    static blockConfirmClose()
    {
        $('.blockee-editor-window-canvas').hide();
        $('.blockee-editor-window').hide();
    }




    static textToolbarWindowLinkExecute()
    {
        const $parent = $('.blockee-editor-window--link');

        let link_href = $parent.find('input[name="text_link_href"]').val();
        let link_target = $parent.find('input[name="text_link_target"]').val();
        let link_id = $parent.find('input[name="text_link_id"]').val();
        let link_class = $parent.find('input[name="text_link_class"]').val();
        let link_style = $parent.find('input[name="text_link_style"]').val();

        let $node = $('<a>');
        $node.attr('href', link_href);

        if(link_target !== '')$node.attr('target', link_target);
        if(link_id !== '')$node.attr('id', link_id);
        if(link_class !== '')$node.attr('class', link_class);
        if(link_style !== '')$node.attr('style', link_style);


        blockeeEditor.textToolbarWindowLinkClose();

        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(blockeeEditor.savedSelection);

        let selectedText = selection.toString();
        $node.text(selectedText);

        blockeeEditor.insertHtmlAtCaret($node[0].outerHTML);

    }

    static textToolbarWindowLinkClose()
    {
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(blockeeEditor.savedSelection);

        $('.blockee-editor-window-canvas').hide();
        $('.blockee-editor-window').hide();
    }


    static blockGetNode()
    {
        return $(".blockee-editor-block.active .blockee-editor-block-element");
    }

    static blockSettingsValidate()
    {
        let current_block_type = $(".blockee-editor-block.active").data('type');
        const signature = 'BlockeePlugin__'+current_block_type;
        let render = eval(signature+".settingsValidate()");
        let info = eval(signature+".info()");

        let $node = blockeeEditor.blockGetNode();

        // special
        if($('.blockee-editor-tab--content-advanced').length)
        {
            let id =  $('.blockee-editor-window--settings input[name="id"]').val();
            let o_class =  $('.blockee-editor-window--settings input[name="class"]').val();
            let style =  $('.blockee-editor-window--settings input[name="style"]').val();

            if(id === '')
                $node.removeAttr("id");
            else
                $node.attr("id", id);

            // add style
            let bg_image =  $('.blockee-editor-window--settings input[name="bg_image"]').val();
            if(bg_image !== '')
            {
                style +=  ` background-image:url('${bg_image}'); `;
            }
            style = $.trim(style);

            if(style === '')
                $node.removeAttr("style");
            else
                $node.attr("style", style);

            o_class = $.trim(o_class);

            // get original class
            let class_original = $node.attr('class') ?? '';
            class_original = class_original.replaceAll('  ', ' ');
            class_original = class_original.trim();

            let class_parsed = "";
            class_original.split(" ").forEach((elem) => {

                if(class_parsed !== '')class_parsed += " ";

                if(elem.indexOf("blockee") !== -1)
                    class_parsed += elem;
            });

            let class_final = class_parsed+" "+o_class;
            class_final = class_final.trim();
            $node.attr("class", class_final);

            // bg_image
            if(bg_image === '')
                $node.removeAttr("data-bg-image");
            else
                $node.attr("data-bg-image", bg_image);

            // h_align
            let h_align =  $('.blockee-editor-window--settings select[name="h_align"]').val();
            if(h_align === '')
                $node.removeAttr("data-align");
            else
                $node.attr("data-align", h_align);

            // margin
            let margin = "";
            margin += ($('.blockee-editor-window--settings input[name="margin_top"]').val() === '') ? '' : 'mt-'+$('.blockee-editor-window--settings input[name="margin_top"]').val()+" ";
            margin += ($('.blockee-editor-window--settings input[name="margin_bottom"]').val() === '') ? '' : 'mb-'+$('.blockee-editor-window--settings input[name="margin_bottom"]').val()+" ";
            margin += ($('.blockee-editor-window--settings input[name="margin_left"]').val() === '') ? '' : 'ms-'+$('.blockee-editor-window--settings input[name="margin_left"]').val()+" ";
            margin += ($('.blockee-editor-window--settings input[name="margin_right"]').val() === '') ? '' : 'me-'+$('.blockee-editor-window--settings input[name="margin_right"]').val()+" ";
            margin = $.trim(margin);
            if(margin === '')
                $node.removeAttr("data-margin");
            else
                $node.attr("data-margin", margin);

            // padding
            let padding = "";
            padding += ($('.blockee-editor-window--settings input[name="padding_top"]').val() === '') ? '' : 'pt-'+$('.blockee-editor-window--settings input[name="padding_top"]').val()+" ";
            padding += ($('.blockee-editor-window--settings input[name="padding_bottom"]').val() === '') ? '' : 'pb-'+$('.blockee-editor-window--settings input[name="padding_bottom"]').val()+" ";
            padding += ($('.blockee-editor-window--settings input[name="padding_left"]').val() === '') ? '' : 'ps-'+$('.blockee-editor-window--settings input[name="padding_left"]').val()+" ";
            padding += ($('.blockee-editor-window--settings input[name="padding_right"]').val() === '') ? '' : 'pe-'+$('.blockee-editor-window--settings input[name="padding_right"]').val()+" ";
            padding = $.trim(padding);
            if(padding === '')
                $node.removeAttr("data-padding");
            else
                $node.attr("data-padding", padding);

            // color
            let color =  $('.blockee-editor-window--settings select[name="color"]').val();
            if(color === '')
                $node.removeAttr("data-color");
            else
                $node.attr("data-color", color);

            // bg_color
            let bg_color =  $('.blockee-editor-window--settings select[name="bg_color"]').val();
            if(bg_color === '')
                $node.removeAttr("data-bg-color");
            else
                $node.attr("data-bg-color", bg_color);



        }



        blockeeEditor.update();
        blockeeEditor.blockSettingsClose();

        return false;
    }

    static fileManagerOpen(name, query_added=''){

        if(!blockeeEditorFileManagerUrl)
        {
            let msg = "Please add parameter `data-blockee-filemanager-url` to open your file manager";
            alert(msg);
            return false;
        }


        const w_width = 1100;
        const w_height = 960;
        const w_top =( window.top.outerHeight / 2) + (window.top.screenY) - ( w_height / 2);
        const w_left = (window.top.outerWidth / 2) + (window.top.screenX) - ( w_width / 2);

        const attributes = "toolbar=yes,status=yes,scrollbars=yes,resizable=yes,width="+w_width+",height="+w_height+",top="+w_top+",left="+w_left;

        if(!blockeeEditor.isFullScreened)
        {
            $('.blockee-editor__button-fullscreen').click();
        }


        // iframe open
        let uri = blockeeEditorFileManagerUrl+'?target='+name+query_added;
        $('.blockee-editor-window--file-browser iframe').attr("src", uri);
        $('.blockee-editor-window--file-browser').show();
        // $('.blockee-editor-window-canvas').show();
    }

    static fileManagerClose()
    {
        $('.blockee-editor-window--file-browser').hide();
        // $('.blockee-editor-window-canvas').hide();



    }

    static removeSpecialClass(o_class)
    {
        if(typeof o_class == 'undefined')
            return "";

        let classes = o_class.split(' ');
        let filtered_classes = [];
        for (var i = 0; i < classes.length; i++) {
            if (!classes[i].startsWith('blockee')) {
                filtered_classes.push(classes[i]);
            }
        }
        let result = filtered_classes.join(' ');
        o_class = $.trim(result);
        return o_class;
    }


    static toolbarPosition()
    {
        let selectionRange = blockeeEditor.selectionRange;
        let $toolbar = blockeeEditor.$toolbar;

        if (!selectionRange) return;

        let rect = selectionRange.getBoundingClientRect();

        $toolbar.css({
            top: rect.top - $toolbar.outerHeight() - 10 + 'px',
            left: rect.left - 200 + 'px'
        });
    }

}



// $(function(){

    // blockee url
    const scripts = document.getElementsByTagName('script');
    for (let script of scripts) {
        if (script.src.endsWith('blockee.js')) {
            blockeeEditorUrl = script.src;
            blockeeEditorUrl = blockeeEditorUrl.replace('/blockee.js', '');
        }
    }

    // register mouse position
    document.addEventListener('mousemove', function(event) {
        blockeeEditorMouseX = event.clientX;
        blockeeEditorMouseY = event.clientY;
    });


    // load plugins files
    let blockee_plugins_dirs = [];
    Object.keys(blockeeEditorPlugins).forEach(function(group) {
        Object.keys(blockeeEditorPlugins[group]).forEach(function(plugin_name) {
            blockee_plugins_dirs[blockee_plugins_dirs.length] = `${blockeeEditorUrl}/plugin/${group}/${blockeeEditorPlugins[group][plugin_name]}/style.css`;
            blockee_plugins_dirs[blockee_plugins_dirs.length] = `${blockeeEditorUrl}/plugin/${group}/${blockeeEditorPlugins[group][plugin_name]}/${blockeeEditorPlugins[group][plugin_name]}.js`;
            blockeeEditorPluginsLoaded[blockeeEditorPluginsLoaded.length] = blockeeEditorPlugins[group][plugin_name];
        });
    });

    // load all plugins
    Promise.all(blockee_plugins_dirs.map(blockeeEditor.loadPlugin))
        .then(() => {
            blockeeEditor.init();
        })
        .catch(error => {
            console.error('Error loading plugins:', error);
    });


    // change
    $('body').on('input', '.blockee-editor-block-element', function(e){
        blockeeEditor.update();
    });

    // paste cleaner
    $('body').on('paste', '.blockee-editor-block-element', function(e){

        e.preventDefault();
        let text = (e.originalEvent || e).clipboardData.getData('text/plain');
        // text = $.trim(text);
        text = text.replaceAll("\r", "");
        text = text.replaceAll("\n", "<br>");

        const parentListItem = window.getSelection().focusNode.parentNode;
        if(parentListItem.tagName === 'LI')
        {
            text = text.replaceAll('<br><br>', '<br>');
            text = text.replaceAll("<br>", "</li><li>");
            text = "<li>"+text+"</li>";
        }


        // document.execCommand('insertText', true, text);
        blockeeEditor.insertHtmlAtCaret(text);

    });

    // register text-toolbar
    $('body').on('mouseup', '[contenteditable]', function(e){



        e.stopImmediatePropagation();
        blockeeEditor.$toolbar = $('.blockeditor-text-toolbar');

        let selection = window.getSelection();
        if (selection.rangeCount > 0 && !selection.isCollapsed) {
            blockeeEditor.selectionRange = selection.getRangeAt(0);
            blockeeEditor.toolbarPosition();
            blockeeEditor.$toolbar.show();

            // Ajouter un écouteur d'événement pour le défilement et le redimensionnement
            $(window).on('scroll resize', blockeeEditor.toolbarPosition);
        } else {
            blockeeEditor.$toolbar.hide();
            $(window).off('scroll resize', blockeeEditor.toolbarPosition);
        }


    });

    $('body').on('mouseup', function(){
        const $toolbar = $('.blockeditor-text-toolbar');
        $toolbar.hide();
    });

    $('body').on('click', '.blockeditor-text-toolbar button', function(e){

        const $toolbar = $('.blockeditor-text-toolbar');
        const command = $(this).data('command');

        if(command === 'createLink') {

            const selection = window.getSelection();
            const selectedNode = selection.anchorNode.parentNode;

            let l_href = "";
            let l_target = "";
            let l_class = "";
            let l_id = "";
            let l_style = "";

            if (selectedNode && selectedNode.tagName === 'A')
            {
                l_href = $(selectedNode).attr('href') ?? '';
                l_target = $(selectedNode).attr('target') ?? '';
                l_class = $(selectedNode).attr('class') ?? '';
                l_id = $(selectedNode).attr('id') ?? '';
                l_style = $(selectedNode).attr('style') ?? '';
            }

            blockeeEditor.textToolbarLinkOpen(l_href, l_target, l_id, l_class, l_style);

        }
        else if (command === 'hiliteColor')
        {
            document.execCommand(command, false, 'yellow');
        }
        else
        {
            document.execCommand(command, false, null);
        }

    });

    // detect enter
    $('body').on('keydown', '.blockee-editor-block-element[contenteditable]', function(e){
        if(e.which === 13)
        {
            e.preventDefault();

            const parentListItem = window.getSelection().focusNode.parentNode;
            if(parentListItem.tagName === 'LI')
            {
                let $cur_li = $(parentListItem);
                let $new_li = $("<li>Item</li>");

                $new_li.insertAfter($cur_li);
                $new_li.focus();

                // Move the selection cursor to the beginning of the newly created list item
                let selection = window.getSelection();
                let range = document.createRange();
                range.selectNodeContents($new_li[0]);
                selection.removeAllRanges();
                selection.addRange(range);

            }
            else
            {
                blockeeEditor.insertHtmlAtCaret("<br>");
            }

        }
    });

    $('body').on('keydown', function(e){

        if($('.blockee-editor__menu-plugin').is(':visible'))
        {
            let $menuItems = $('.blockee-editor__menu-plugin li:visible');
            let $selectedItem = $menuItems.filter('.active');
            let currentIndex = $menuItems.index($selectedItem);

            // detect down
            if(e.key === "ArrowDown")
            {
                e.preventDefault();

                if(currentIndex < $menuItems.length - 1)
                {
                    $selectedItem.removeClass('active').blur();
                    let $newSelectedItem = $menuItems.eq(currentIndex + 1);
                    $newSelectedItem.addClass('active').focus();
                    $newSelectedItem[0].scrollIntoView({ block: "nearest"});
                }
            }

            // detect up and down
            if(e.key === "ArrowUp")
            {
                e.preventDefault();

                if (currentIndex > 0)
                {
                    $selectedItem.removeClass('active').blur();
                    let $newSelectedItem = $menuItems.eq(currentIndex - 1);
                    $newSelectedItem.addClass('active').focus();
                    $newSelectedItem[0].scrollIntoView({ block: "nearest"});

                    if(currentIndex - 1 < 2)
                    {
                        $('.blockee-editor__menu-plugin').scrollTop(0);
                    }
                }
            }


            // detect up and down
            if(e.key === "Enter")
            {
                e.preventDefault();
                $menuItems.filter('.active').click();
            }
        }

        // escape
        if(e.key === "Escape" || e.keyCode === 27)
        {
            if($(e.target).is('input') && $(e.target).val() !== '')return;

            blockeeEditor.actionMenuHide();
            blockeeEditor.blockSettingsClose();
            return;
        }

        // touch /
        if ((e.key === "/" || e.keyCode === 191) && !$(e.target).is('input, textarea, select') && !$(e.target).attr('contenteditable'))
        {
            blockeeEditor.actionMenuShow('shortcut');
            e.preventDefault();
            return;
        }

        // ctrl + S
        if (e.ctrlKey && e.keyCode === 83) {
            e.preventDefault();
            blockeeEditor.actionSave();
            return;
        }

        // ctrl + z
        if (e.ctrlKey && e.key === 'z' && !$(e.target).is('input, textarea') && !$(e.target).attr('contenteditable')) {
            e.preventDefault();
            blockeeEditor.actionUndo();
            return;
        }

        // ctrl + y
        if (e.ctrlKey && e.key === 'y' && !$(e.target).is('input, textarea') && !$(e.target).attr('contenteditable')) {
            e.preventDefault();
            blockeeEditor.actionRedo();
            return;
        }

        // alt + F => fullscreen
        if (e.altKey && e.key === 'f') {
            e.preventDefault();
            blockeeEditor.actionFullscreen($('.blockee-editor__content')[0]);
            return;
        }


        // alt + I => image
        if (e.altKey && e.key === 'i') {
            e.preventDefault();
            BlockeePlugin__img.insert(false);
            return;
        }

        // alt + T => text
        if ((e.altKey && e.key === 't') || (e.altKey && e.key === 'p')) {
            e.preventDefault();
            BlockeePlugin__p.insert();
            return;
        }

        // alt + L => text
        if (e.altKey && e.key === 'l') {
            e.preventDefault();
            BlockeePlugin__list.insert();
            return;
        }

        // alt + H + 1 => text
        if(e.altKey && e.key === '1') {
            e.preventDefault();
            BlockeePlugin__h1.insert();
            return;
        }

        if(e.altKey && e.key === '2') {
            e.preventDefault();
            BlockeePlugin__h2.insert();
            return;
        }

        if(e.altKey && e.key === '3') {
            e.preventDefault();
            BlockeePlugin__h3.insert();
            return;
        }

        if(e.altKey && e.key === '4') {
            e.preventDefault();
            BlockeePlugin__h4.insert();
            return;
        }

        if(e.altKey && e.key === '5') {
            e.preventDefault();
            BlockeePlugin__h5.insert();
            return;
        }

        if(e.altKey && e.key === '6') {
            e.preventDefault();
            BlockeePlugin__h6.insert();
            return;
        }

        // insert hr
        if(e.altKey && e.key === '-') {
            e.preventDefault();
            BlockeePlugin__hr.insert();
            return;
        }

    });

    $('body').on('mouseover', '.blockee-editor__menu li', function(){
        $('.blockee-editor__menu li').removeClass('active');
        $(this).addClass('active')
    });

    $('body').on('mouseout', '.blockee-editor__menu li', function(){
        $(this).removeClass('active');
    });

    // detect image editable
    $('body').on('click', 'img[x-image-editable="true"]', function(){

        const id = $(this).attr('id');

        blockeeEditor.fileManagerOpen('@'+id, "");



    });


    // tabs
    $('body').on('click', '.blockee-editor-tabs a', function(e){
        e.preventDefault();
        $(this).parents('.blockee-editor-tabs').find('a').removeClass('active');
        $(this).addClass('active');

        let index = $(this).parents('li').index();
        $('.blockee-editor-tab--content').removeClass('active');
        $('.blockee-editor-tab--content').eq(index).addClass('active');
    });

    // attach menu event
    $('body').on('click', '.blockee-editor__menu-plugin li', function(e){
        blockeeEditor.actionMenuHide();
    });

    $('body').on('input change', '.blockee-editor__menu-plugin input[type="search"]', function(e){

        $('.blockee-editor__menu-plugin li').removeClass('active');

        let v = $(this).val().toLowerCase();
        v = $.trim(v);

        if($(this).val() == '')
        {
            $(this).parent().find('li').show();
        }
        else
        {
            $(this).parent().find('li').hide();

            let lis = $(this).parent().find('li');
            lis.each(function(){

                let li_text = $(this).text();
                li_text = $.trim(li_text.toLowerCase());
                li_keywords = $(this).data('blockee-plugin-keywords');

                if(li_text.indexOf(v) !== -1 || $(this).data('blockee-plugin').indexOf(v) != -1 || (li_keywords !== '' && li_keywords.indexOf(v) !== -1))
                    $(this).show();

            });

        }
    });

    document.addEventListener('fullscreenchange', (event) => {
        if(!document.fullscreenElement) {
            $('body').removeClass('fullscreen');
        }
    });

    // file-manager
    window.addEventListener('message', function(event) {

        if(event.data === 'closeFileManager') {
            if (typeof blockeeEditor !== 'undefined' && typeof blockeeEditor.fileManagerClose === 'function') {
                blockeeEditor.fileManagerClose();
            } else {
                console.error("function blockeeEditor.fileManagerClose not available");
            }
        }
    }, false);


// });
