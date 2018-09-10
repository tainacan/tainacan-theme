jQuery(document).on('tainacan-collection-hook-reload', function(){
    var colorWell = jQuery('#colorpicker');
    var alternativeColor = jQuery('.custom-color');
    var label = jQuery('.color-text');
    if(colorWell != null){
        colorWell.on('input', function(event){
            var label = jQuery('.color-text');
            if(label){
                label.css('background-color', event.target.value);
            }
        });
    }
    if(alternativeColor != null){
        alternativeColor.change(function(e){
            if(alternativeColor.is(':checked')){
                if(label){
                    label.css('background-color', e.target.value);
                }
                colorWell.val(e.target.value);
            }
        });
    }
    label.css('background-color', colorWell.val());
});