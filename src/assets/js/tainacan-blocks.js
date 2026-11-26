/**
 * Tainacan blocks with the look of the theme (inc/tainacan-blocks.php).
 *
 * Facets as labels: the number of items, which the plugin writes as " (12) ",
 * goes into the badge without the parentheses; in the cloud each label gets a
 * weight from 1 to 5 by its number of items (data-tt2-w), read by the
 * stylesheet. The blocks are drawn by Vue after the page loads and again when
 * "load more" brings more terms, so each block is watched while it changes.
 */
( function () {
	'use strict';

	var COUNT = /^\s*\(\s*([\d.,\s]+?)\s*\)\s*$/;

	function numberOf( text ) {
		var n = parseInt( String( text ).replace( /[^\d]/g, '' ), 10 );
		return isNaN( n ) ? 0 : n;
	}

	function tidy( root ) {
		var items = root.querySelectorAll( 'li.facet-list-item' );
		var counts = [];
		var max = 0;
		Array.prototype.forEach.call( items, function ( li ) {
			var badge = li.querySelector( '.facet-item-count' );
			var n = 0;
			if ( badge ) {
				var text = badge.textContent.replace( / /g, ' ' );
				var found = COUNT.exec( text );
				if ( found ) {
					badge.textContent = found[ 1 ];
				}
				n = numberOf( badge.textContent );
			}
			counts.push( n );
			max = Math.max( max, n );
		} );
		if ( ! root.classList.contains( 'tt2-tb-cloud' ) ) {
			return;
		}
		Array.prototype.forEach.call( items, function ( li, i ) {
			// Logarithmic, so one big term does not leave all the others at the smallest size.
			var w = max > 1 && counts[ i ] > 0 ? 1 + Math.round( 4 * Math.log( counts[ i ] ) / Math.log( max ) ) : 1;
			if ( li.getAttribute( 'data-tt2-w' ) !== String( w ) ) {
				li.setAttribute( 'data-tt2-w', String( w ) );
			}
		} );
	}

	function watch( root ) {
		var pending = false;
		function run() {
			pending = false;
			observer.disconnect();
			tidy( root );
			observer.observe( root, { childList: true, subtree: true, characterData: true } );
		}
		var observer = new MutationObserver( function () {
			if ( ! pending ) {
				pending = true;
				window.requestAnimationFrame( run );
			}
		} );
		run();
	}

	function start() {
		var roots = document.querySelectorAll( '.wp-block-tainacan-facets-list.tt2-tb.tt2-tb-chips, .wp-block-tainacan-facets-list.tt2-tb.tt2-tb-cloud' );
		Array.prototype.forEach.call( roots, watch );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
