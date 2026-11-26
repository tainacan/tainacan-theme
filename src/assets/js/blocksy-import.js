/**
 * Import from Blocksy: the drop zone, the boxes of the topics and the buttons
 * that check, uncheck and open them all. The page works without this script.
 */
( function () {
	'use strict';

	document.querySelectorAll( '.tib-drop' ).forEach( function ( drop ) {
		var input = drop.querySelector( 'input[type="file"]' );
		var text = drop.querySelector( '.tib-drop-text' );
		if ( ! input || ! text ) {
			return;
		}
		var show = function () {
			if ( input.files && input.files.length ) {
				text.textContent = input.files[ 0 ].name;
				drop.classList.add( 'has-file' );
			}
		};
		input.addEventListener( 'change', show );
		[ 'dragenter', 'dragover' ].forEach( function ( type ) {
			drop.addEventListener( type, function ( event ) {
				event.preventDefault();
				drop.classList.add( 'is-over' );
			} );
		} );
		[ 'dragleave', 'drop' ].forEach( function ( type ) {
			drop.addEventListener( type, function () {
				drop.classList.remove( 'is-over' );
			} );
		} );
		drop.addEventListener( 'drop', function ( event ) {
			event.preventDefault();
			if ( event.dataTransfer && event.dataTransfer.files.length ) {
				input.files = event.dataTransfer.files;
				show();
			}
		} );
	} );

	var mark = function ( box ) {
		var group = box.closest( '.tib-group' );
		if ( group ) {
			group.classList.toggle( 'is-off', ! box.checked );
		}
	};
	var boxes = document.querySelectorAll( '.tib-group input[name="groups[]"]' );
	boxes.forEach( function ( box ) {
		box.addEventListener( 'change', function () {
			mark( box );
		} );
		// A click on the box does not open or close the topic.
		box.addEventListener( 'click', function ( event ) {
			event.stopPropagation();
		} );
	} );

	document.querySelectorAll( '[data-tib-all]' ).forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			var on = '1' === button.getAttribute( 'data-tib-all' );
			boxes.forEach( function ( box ) {
				box.checked = on;
				mark( box );
			} );
		} );
	} );

	document.querySelectorAll( '[data-tib-open]' ).forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			document.querySelectorAll( '.tib-group' ).forEach( function ( group ) {
				group.open = true;
			} );
		} );
	} );

	// Nothing checked: the import would do nothing.
	var form = document.querySelector( '.tib-plan' );
	if ( form ) {
		form.addEventListener( 'submit', function ( event ) {
			var any = Array.prototype.some.call( boxes, function ( box ) {
				return box.checked;
			} );
			if ( ! any ) {
				event.preventDefault();
				boxes[ 0 ] && boxes[ 0 ].focus();
			}
		} );
	}
}() );
