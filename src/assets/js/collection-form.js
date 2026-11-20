/**
 * Tainacan Interface: the panel "Look of this collection on the site" on the
 * collection form of Tainacan (inc/collection-form.php).
 *
 * Tainacan prints the panel and fills its named fields; this only adds the
 * color picker, the colors of the drawings and the counters of each group.
 * The panel may be printed again by Vue at any time, so everything is
 * delegated and read from the page when needed.
 */
( function () {
	'use strict';

	var HEX = /^#(?:[0-9a-f]{3}){1,2}$/i;

	function expand( color ) {
		return 4 === color.length ? '#' + color[ 1 ] + color[ 1 ] + color[ 2 ] + color[ 2 ] + color[ 3 ] + color[ 3 ] : color;
	}

	function colorOf( box ) {
		var hex = box.querySelector( '.tt2-color__hex' );
		var value = hex ? hex.value.trim() : '';
		return HEX.test( value ) ? value : '';
	}

	function syncColor( box ) {
		var hex = box.querySelector( '.tt2-color__hex' );
		var pick = box.querySelector( '.tt2-color__pick' );
		var own = colorOf( box );
		var shown = expand( own ) || box.getAttribute( 'data-site' );
		if ( pick && pick.value.toLowerCase() !== shown.toLowerCase() ) {
			pick.value = shown;
		}
		box.classList.toggle( 'is-own', '' !== own );
		box.classList.toggle( 'is-invalid', !! hex && '' !== hex.value.trim() && '' === own );
		var panel = box.closest( '.tt2-cform' );
		if ( panel ) {
			var name = 'brand_color' === box.getAttribute( 'data-option' ) ? '--tt2f-brand' : '--tt2f-accent';
			panel.style.setProperty( name, shown );
		}
	}

	function syncGroup( group ) {
		var changed = 0;
		group.querySelectorAll( '.tt2-color' ).forEach( function ( box ) {
			if ( colorOf( box ) ) {
				changed++;
			}
		} );
		group.querySelectorAll( '.tt2-cform__opt input[type="radio"]:checked' ).forEach( function ( radio ) {
			if ( '' !== radio.value ) {
				changed++;
			}
		} );
		var state = group.querySelector( '.tt2-cform__state' );
		if ( ! state ) {
			return;
		}
		var text = state.getAttribute( 'data-site' );
		if ( 1 === changed ) {
			text = state.getAttribute( 'data-one' );
		} else if ( changed > 1 ) {
			text = state.getAttribute( 'data-many' ).replace( '%d', String( changed ) );
		}
		state.textContent = text;
		state.classList.toggle( 'is-changed', changed > 0 );
	}

	function syncAll() {
		document.querySelectorAll( '.tt2-cform' ).forEach( function ( panel ) {
			panel.querySelectorAll( '.tt2-color' ).forEach( syncColor );
			panel.querySelectorAll( '.tt2-cform__group' ).forEach( syncGroup );
		} );
	}

	document.addEventListener( 'input', function ( event ) {
		var target = event.target;
		var box = target.closest ? target.closest( '.tt2-color' ) : null;
		if ( box && target.classList.contains( 'tt2-color__pick' ) ) {
			box.querySelector( '.tt2-color__hex' ).value = target.value;
		}
		if ( target.closest && target.closest( '.tt2-cform' ) ) {
			syncAll();
		}
	} );

	document.addEventListener( 'change', function ( event ) {
		if ( event.target.closest && event.target.closest( '.tt2-cform' ) ) {
			syncAll();
		}
	} );

	document.addEventListener( 'click', function ( event ) {
		var reset = event.target.closest ? event.target.closest( '.tt2-color__reset' ) : null;
		if ( ! reset ) {
			return;
		}
		var box = reset.closest( '.tt2-color' );
		var hex = box.querySelector( '.tt2-color__hex' );
		hex.value = '';
		syncAll();
		hex.focus();
	} );

	// Tainacan fires this after every update of its form, the filling with the saved values included.
	document.addEventListener( 'tainacan-collection-hook-reload', function () {
		window.requestAnimationFrame( syncAll );
	} );

	// The filling sets the values without events: look again when the panel is opened.
	document.addEventListener( 'toggle', function ( event ) {
		if ( event.target.classList && event.target.classList.contains( 'tt2-cform__group' ) ) {
			syncAll();
		}
	}, true );
}() );
