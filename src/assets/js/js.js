jQuery( document ).ready(function( $ ) {
	$( '#carouselExample' ).on( 'slide.bs.carousel', function ( e ) {

		var $e = $( e.relatedTarget );
		var idx = $e.index();
		var itemsPerSlide = 3;
		var totalItems = $( '.carousel-item' ).length;

		if ( idx >= totalItems - ( itemsPerSlide - 1 ) ) {
			var it = itemsPerSlide - ( totalItems - idx );
			for ( var i = 0; i < it; i++ ) {
				// append slides to end
				if ( e.direction == "left" ) {
					$( '.carousel-item' ).eq( i ).appendTo( '.carousel-inner' );
				} else {
					$( '.carousel-item' ).eq( 0 ).appendTo( '.carousel-inner' );
				}
			}
		}
	});

	// ===== Scroll to Top ====
	$( window ).scroll( function() {
		if ( $( this ).scrollTop() >= 100 ) {        // If page is scrolled more than 50px
			$( '#return-to-top' ).fadeIn( 200 );    // Fade in the arrow
		} else {
			$( '#return-to-top' ).fadeOut( 200 );   // Else fade out the arrow
		}
	} );
	$( '#return-to-top' ).click( function() {      // When arrow is clicked
		$( 'body,html' ).animate( {
			scrollTop : 0                       // Scroll to top of body
		}, 500 );
	} );

	/**
	 * Align the comments childrens
	 */
	$( '#comments ul.children' ).addClass( 'align-children' );

	$( '.tainacan-single-post .tainacan-content table' ).addClass( 'table table-borderless' );

	/**
	 * Add the icon on previous and next pagination in comment list
	 */
	$( '<i class="mdi mdi-menu-left"></i>' ).insertBefore( "#comment-nav-below .float-left a" );
	$( '<i class="mdi mdi-menu-right"></i>' ).insertAfter( "#comment-nav-below .float-right a" );

	$( '.comment-reply-link' ).on( 'click', function(){
		//$('#cancel-comment-reply-link').remove();
		$( '.form-submit' ).prepend( $( '#cancel-comment-reply-link' ) );
		$( '.comment-submit-link' );
	});

	/**
	 * Change the class of guttenberg button
	 */
	$( '.wp-block-button a' ).toggleClass().addClass( 'btn btn-jelly-bean' );

	$( '.tainacan-list-post .table .tainacan-list-collection td' ).click( function(){
		window.location = $( '.tainacan-list-post .table .tainacan-list-collection' ).data( "href" );
	});

	$( '.single-item-collection--attachments' ).slick({
		prevArrow: '<button type="button" data-role="none" class="single-item-collection--attachments-prev" aria-label="Previous" role="button" style="display: block;"><i class="mdi mdi-menu-left"></i></button>',
		nextArrow: '<button type="button" data-role="none" class="single-item-collection--attachments-next" aria-label="Next" role="button" style="display: block;"><i class="mdi mdi-menu-right"></i></button>',
		slidesToShow: 6,
		slidesToScroll: 1,
		autoplay: true,
		autoplaySpeed: 2000,
		responsive: [ {
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

	$('.tainacan-interface-truncate').tainacan_interface_truncate();

});
