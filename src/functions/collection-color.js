document.addEventListener('tainacan-collection-hook-reload', function() {
    var colorWell;
    colorWell = document.getElementById("colorpicker");
    if(colorWell != null){
        colorWell.addEventListener('input', function(event){
            var label = document.getElementsByClassName('color-text');
            if (label[0] && label[1]) {
                label[0].style.backgroundColor = event.target.value;
                label[1].style.backgroundColor = event.target.value;
            }
        });
        colorWell.select();
    }
});