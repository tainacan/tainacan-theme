jQuery( document ).ready(function( $ ) {

	$( 'body' ).removeClass('loading-content');

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
	$( window ).on('scroll', function() {
		if ( $( this ).scrollTop() >= 100 ) {        // If page is scrolled more than 50px
			$( '#return-to-top' ).fadeIn( 200 );    // Fade in the arrow
		} else {
			$( '#return-to-top' ).fadeOut( 200 );   // Else fade out the arrow
		}
	} );
	$( '#return-to-top' ).on('click', function() {      // When arrow is clicked
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
	$( '<i class="tainacan-icon tainacan-icon-arrowleft"></i>' ).insertBefore( "#comment-nav-below .float-left a" );
	$( '<i class="tainacan-icon tainacan-icon-arrowright"></i>' ).insertAfter( "#comment-nav-below .float-right a" );

	$( '.comment-reply-link' ).on( 'click', function(){
		//$('#cancel-comment-reply-link').remove();
		$( '.form-submit' ).prepend( $( '#cancel-comment-reply-link' ) );
		$( '.comment-submit-link' );
	});

	/**
	 * Change the class of guttenberg button
	 */
	$( '.wp-block-button a' ).addClass( 'btn btn-jelly-bean' );

	$( '.tainacan-list-post .table .tainacan-list-collection td' ).on('click', function(){
		window.location = $( '.tainacan-list-post .table .tainacan-list-collection' ).data( "href" );
	});

	$( '.tainacan-interface-truncate' ).tainacan_interface_truncate();
	$( '.tainacan-interface-truncate-term' ).tainacan_interface_truncate_term();
	$( ".trigger" ).on('click', function() {
		$( ".collection-header--share" ).toggleClass( "active" );
	});
	$('#menubelowHeader .dropdown-menu a.dropdown-toggle').addClass('dropdown-submenu');
	$('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
		if (!$(this).next().hasClass('show')) {
		  $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
		}
		var $subMenu = $(this).next(".dropdown-menu");
		$subMenu.toggleClass('show');

		return false;
	});
	
	$('.margin-pagination .navigation.pagination').addClass('justify-content-center justify-content-md-end');
});
