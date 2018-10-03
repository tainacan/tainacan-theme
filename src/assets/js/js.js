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


    $('.tainacan-single-post .tainacan-content table').addClass('table table-borderless');

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

    /*
     * ACESSIBILIDADE
    */

    accessibilityCounter = 0;

    jQuery('.button-text-minus').on('click',function() {
        if (accessibilityCounter > -3) {
            var _html = jQuery('html'),
                fonte = _html.css('font-size'),
                tamanho = fonte.split('px');

            _html.css('font-size', (parseInt(tamanho[0]) - 2));
            accessibilityCounter--;
        }
    });

    jQuery('.button-text-default').on('click',function() {
        jQuery('html').css('font-size','1rem');
        accessibilityCounter = 0;
    });

    jQuery('.button-text-plus').on('click',function() {
        if (accessibilityCounter < 3) {
            var _html = jQuery('html'),
                fonte = _html.css('font-size'),
                tamanho = fonte.split('px');

            _html.css('font-size', (parseInt(tamanho[0]) + 3));
            accessibilityCounter++;
        }
    });

    jQuery('.button-high-contrast').on('click',function() {
        jQuery('body').toggleClass('contraste');
    });

    /*
     * BOTÃO DE EXIBIÇÃO DO FORMULÁRIO DE BUSCA
    */
    jQuery('.tainacan-search-button').on('click',function() {
        var _elementoPai = jQuery(this).parents('.input-group');

        if (!_elementoPai.hasClass('hover')) {
            _elementoPai.addClass('hover');

            return false;
        } else {
            if (jQuery('#tainacan-search-header').val() == '') {
                _elementoPai.removeClass('hover');

                return false;
            }
        }
    });

    jQuery('#tainacan-search-header').on({
        'focus': function() {
            jQuery(this).parents('.input-group').addClass('hover');
        },
        'blur': function() {
            jQuery(this).parents('.input-group').removeClass('hover');
        }
    });

    /*
     * AO CLICAR EM QUALQUER LUGAR DA PÁGINA, O CAMPO DE BUSCA ABERTO É FECHADO
    */
    var _formBusca = jQuery('.tainacan-search-form'),
        _formBuscaFilho = _formBusca.find('.input-group');

    _formBusca.on('click',function(e) {
        e.stopPropagation();
    });

    jQuery('body').on('click',function() {
        if (_formBuscaFilho.hasClass('hover')) {
            _formBuscaFilho.removeClass('hover');
        }
    });
});
jQuery(document).ready(function(e) {
        var t = e(".dotmore");
        t.dotdotdot({
            keep: ".toggle",
            height: "watch"
        });
        var n = t.data("dotdotdot");
        t.on("click", ".toggle", function(e) {
            e.preventDefault(), t.hasClass("ddd-truncated") ? (n.restore(), t.addClass("full-story")) : (t.removeClass("full-story"), n.truncate(), n.watch())
        });
        e(".trigger").click(function() {
            e(".collection-header--share").toggleClass("active"); 
        });
});