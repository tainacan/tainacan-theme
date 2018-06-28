jQuery(document).ready(function( $ ) {
    $('#carouselExample').on('slide.bs.carousel', function (e) {

        var $e = $(e.relatedTarget);
        var idx = $e.index();
        var itemsPerSlide = 3;
        var totalItems = $('.carousel-item').length;
        
        if (idx >= totalItems-(itemsPerSlide-1)) {
            var it = itemsPerSlide - (totalItems - idx);
            for (var i=0; i<it; i++) {
                // append slides to end
                if (e.direction=="left") {
                    $('.carousel-item').eq(i).appendTo('.carousel-inner');
                }
                else {
                    $('.carousel-item').eq(0).appendTo('.carousel-inner');
                }
            }
        }
    });

    /**
     * Align the comments childrens
     */
    $('#comments ul.children').addClass('align-children');

    /**
     * Add the icon on previous and next pagination in comment list
     */
    $('<i class="mdi mdi-menu-left"></i>').insertBefore("#comment-nav-below .float-left a");
    $('<i class="mdi mdi-menu-right"></i>').insertAfter("#comment-nav-below .float-right a");

    /**
     * Change the class of guttenberg button
     */
    $('.wp-block-button a').toggleClass().addClass('btn btn-jelly-bean');

    // Instantiates Masonry;
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if ($('.tainacan-masonry-view')[0]) {
                $('.tainacan-masonry-view').masonry({
                    percentPosition: true,
                    itemSelector: '.grid-item',
                    /* columnWidth: '.grid-sizer', */
                    columnWidth: 277,
                    //gutter: '.gutter-sizer',
                    horizontalOrder: true,
                });
            }
        });
    });
    
    $('.showInput').on('click', function(){
        $('.tainacan-form-search').removeClass('d-none');
        $('.tainacan-form-search').addClass('d-flex');
        if($(window).width() > 768){
            $(this).addClass('d-none');
        }
        if($(window).width() < 768){
            $(this).addClass('active-button');
        }
        $(this).attr('type', 'submit');
        $('.tainacan-form-search').animate({opacity: '1'}, "slow", function(){
            $('.tainacan-input-search').select();
        });
        /* if($.trim($('.tainacan-input-search').val()).length){
            $('#btn-reset').removeClass('d-none');
        }else{
            $('#btn-reset').addClass('d-none');
        } */
    });
    $('.tainacan-input-search').on('blur', function(){
        $('.tainacan-form-search').animate({opacity: '0'}, "slow", function(){
            $('.showInput').removeClass('d-none');
            $('.showInput').removeAttr('type');
        });
        if($(window).width() < 768){
            $('.showInput').removeClass('active-button');
        }
    });

    var config = {
        attributes: true,
        childList: true,
        characterData: true
    };
    
    if(document.getElementById('items-list-area'))
        observer.observe(document.getElementById('items-list-area'), config);

    $('.tainacan-list-post .table .tainacan-list-collection td').click(function(){
        window.location = $('.tainacan-list-post .table .tainacan-list-collection').data("href");
    });
});
