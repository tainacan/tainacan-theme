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

    $('.comment-reply-link').on('click', function(){
        //$('#cancel-comment-reply-link').remove();
        $('.form-submit').prepend($('#cancel-comment-reply-link'));
        $('.comment-submit-link');
    });

    /**
     * Change the class of guttenberg button
     */
    $('.wp-block-button a').toggleClass().addClass('btn btn-jelly-bean');

    $('.tainacan-list-post .table .tainacan-list-collection td').click(function(){
        window.location = $('.tainacan-list-post .table .tainacan-list-collection').data("href");
    });

    $('.single-item-collection--attachments').slick({
        prevArrow: '<button type="button" data-role="none" class="single-item-collection--attachments-prev" aria-label="Previous" role="button" style="display: block;"><i class="mdi mdi-menu-left"></i></button>',
        nextArrow: '<button type="button" data-role="none" class="single-item-collection--attachments-next" aria-label="Next" role="button" style="display: block;"><i class="mdi mdi-menu-right"></i></button>',
        slidesToShow: 6,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 2000,
        responsive: [
            {
              breakpoint: 1024,
              settings: {
                slidesToShow: 5,
                slidesToScroll: 1
              }
            },
            {
              breakpoint: 768,
              settings: {
                slidesToShow: 4,
                slidesToScroll: 1
              }
            },
            {
              breakpoint: 576,
              settings: {
                slidesToShow: 2,
                slidesToScroll: 1
              }
            }
            // You can unslick at a given breakpoint now by adding:
            // settings: "unslick"
            // instead of a settings object
        ]
    });
    //$('.t-collection--info-description-text').attr('style', 'display: block; max-height: 4.5em;');
    /* var t = $(".elimore");
    t.dotdotdot({
        keep: ".toggle"
    });

    var n = t.data("dotdotdot");
    t.on("click", ".toggle", function(e) {
        t.preventDefault(), t.hasClass("ddd-truncated") ? (n.restore(), t.addClass("full-story")) : (t.removeClass("full-story"), n.truncate(), n.watch())
    }); */
});
jQuery(document).ready(function(e) {
        var t = e(".dotmore");
        t.dotdotdot({
            keep: ".toggle"
        });
        var n = t.data("dotdotdot");
        t.on("click", ".toggle", function(e) {
            e.preventDefault(), t.hasClass("ddd-truncated") ? (n.restore(), t.addClass("full-story")) : (t.removeClass("full-story"), n.truncate(), n.watch())
        });
        e(".trigger").click(function() {
            e(".collection-header--share").toggleClass("active"); 
        });
});