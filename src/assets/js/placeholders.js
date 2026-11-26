/**
 * Pictures for items without an image (inc/placeholders.php), JavaScript side:
 * the filter of wp.hooks the lists and blocks of the Tainacan plugin call, and a
 * swap of the gray square some blocks of the plugin write straight in the page.
 */
( function () {
	'use strict';

	var data = window.tainacanInterfacePlaceholders;
	if ( ! data || ! data.url ) {
		return;
	}
	var own = Object.prototype.hasOwnProperty;
	var smallSizes = [ '_small', 'small', 'tainacan-small', 'thumbnail' ];

	function kindOf( mime ) {
		var value = String( mime || '' ).toLowerCase().trim();
		if ( own.call( data.exact, value ) ) {
			return data.exact[ value ];
		}
		for ( var start in data.prefix ) {
			if ( own.call( data.prefix, start ) && value.indexOf( start ) === 0 ) {
				return data.prefix[ start ];
			}
		}
		return 'attachment';
	}

	function address( kind, small ) {
		return data.url.replace( '__KIND__', kind ).replace( '__SIZE__', small ? 's' : 'm' );
	}

	if ( window.wp && window.wp.hooks ) {
		window.wp.hooks.addFilter( 'tainacan_get_the_mime_type_icon', 'tainacan-interface/placeholders', function ( src, mime, size ) {
			return address( kindOf( mime ), smallSizes.indexOf( size ) !== -1 );
		} );
	}

	// Blocks that write the picture of the plugin without the filter.
	var pattern = /\/plugins\/tainacan\/assets\/images\/placeholder_(square|image|audio|video|pdf|text|url|embed|attachment)(_medium|_small)?\.png(\?[^#]*)?$/;
	var names = { square: 'empty', embed: 'url' };

	function swap( img ) {
		var match = pattern.exec( img.getAttribute( 'src' ) || '' );
		if ( ! match ) {
			return;
		}
		img.setAttribute( 'src', address( names[ match[ 1 ] ] || match[ 1 ], match[ 2 ] === '_small' ) );
		img.removeAttribute( 'srcset' );
	}

	function scan( node ) {
		if ( node.nodeType !== 1 ) {
			return;
		}
		if ( node.tagName === 'IMG' ) {
			swap( node );
		} else if ( node.getElementsByTagName ) {
			var list = node.getElementsByTagName( 'img' );
			for ( var i = 0; i < list.length; i++ ) {
				swap( list[ i ] );
			}
		}
	}

	function start() {
		scan( document.body );
		if ( ! window.MutationObserver ) {
			return;
		}
		new window.MutationObserver( function ( records ) {
			records.forEach( function ( record ) {
				if ( record.type === 'attributes' ) {
					scan( record.target );
				} else {
					record.addedNodes.forEach( scan );
				}
			} );
		} ).observe( document.body, { childList: true, subtree: true, attributes: true, attributeFilter: [ 'src' ] } );
	}

	if ( document.body ) {
		start();
	} else {
		document.addEventListener( 'DOMContentLoaded', start );
	}
}() );
