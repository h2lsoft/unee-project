class BlockeePlugin__slider {

    static mount(){

        $('body').on('click', '[data-blockee-type="slider"]', function(e){

            $('.blockee-editor-block').removeClass('active');
            $(this).parents('.blockee-editor-block').addClass('active');
            $('.blockee-editor__menu-block').data('blockee-type', 'slider');
            blockeeEditor.blockSettingsOpen();
        });

    }

    static info(){
        return {
            name: 'Slider',
            title: "Slider",
            settings:  true
        }
    }

    static insert() {

        let contents = `<x-slider data-blockee-type="slider" class="blockee-editor-block-element blockee-editor-block-element--xslider"></x-slider>`;
        blockeeEditor.blockInsert('slider', contents, true);

    }

    static settingsRender()
    {
        const $node = blockeeEditor.blockGetNode();

        let slider = $node.data('name') ?? '';

        let form = `<div class="blockee-editor-form-row">                                
                                <div class="blockee-editor-form-label">Slider</div>
                                <input type="text" name="slider" value="${slider}" placeholder="slider name - #id" list="datalist_sliders">
                                <button type="button" class="blockee-editor-form-button blockee-editor-form-button-filemanager" onclick="windowPopup('/'+APP_BACKEND_DIRNAME+'/slider/?target=slider&_popup=1')">Select...</button>
                             </div>`;

        form += `<datalist id="datalist_sliders"></datalist>`;

        // list blocks
        $.getJSON(`/${APP_BACKEND_DIRNAME}/slider/all/?_format=json`, function(data){

            let options = '';
            data.forEach(function(item){
                options += `<option value="${item.name} - #${item.id}"></option>`;
            });

            $('#datalist_sliders').html(options);
        });

        let render =
            {
                tab_advanced: false,
                tabs:[{
                    title: 'SLIDER',
                    contents: form,
                }]
            };

        return render;
    }

    static settingsValidate()
    {
        let $node = blockeeEditor.blockGetNode();
        let name =  $('.blockee-editor-window--settings input[name="slider"]').val();

        $node.attr("data-name", name);
    }

}