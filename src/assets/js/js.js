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
});