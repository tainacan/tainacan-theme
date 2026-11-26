/**
 * Kit for AI page: copy buttons of the requests.
 */
( function () {
	const texts = window.tt2Kit || { copied: 'Copied', copy: 'Copy' };
	document.querySelectorAll( '[data-tt2kit-copy]' ).forEach( ( button ) => {
		button.addEventListener( 'click', () => {
			const field = document.getElementById( button.getAttribute( 'data-tt2kit-copy' ) );
			if ( ! field ) {
				return;
			}
			const done = () => {
				button.textContent = texts.copied;
				window.setTimeout( () => {
					button.textContent = texts.copy;
				}, 2000 );
			};
			if ( navigator.clipboard && window.isSecureContext ) {
				navigator.clipboard.writeText( field.value ).then( done );
			} else {
				field.select();
				document.execCommand( 'copy' );
				done();
			}
		} );
	} );
}() );
