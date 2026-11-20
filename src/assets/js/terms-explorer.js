/**
 * Terms explorer of the Tainacan taxonomy page: tree and columns views of the
 * whole hierarchy, an A–Z index and a search, pages of 100 terms per level,
 * and a panel for the chosen term. No library; every text from the data goes
 * through textContent.
 *
 * @package tainacan-interface
 */
( function () {
	'use strict';

	var root = document.querySelector( '.tt2-tx' );
	var cfg = window.tainacanInterfaceTerms;
	if ( ! root || ! cfg ) {
		return;
	}
	var dataEl = root.querySelector( '.tt2-tx-data' );
	var raw;
	try {
		raw = JSON.parse( dataEl.textContent );
	} catch ( e ) {
		return;
	}
	if ( ! Array.isArray( raw ) || ! raw.length ) {
		return;
	}

	var t = cfg.i18n;
	var SVG = 'http://www.w3.org/2000/svg';
	var MODES = [ 'tree', 'columns' ];
	var PAGE = 100;

	/* ---------- Helpers ---------- */

	function el( tag, cls, text ) {
		var n = document.createElement( tag );
		if ( cls ) {
			n.className = cls;
		}
		if ( text !== undefined && text !== null ) {
			n.textContent = String( text );
		}
		return n;
	}
	function button( cls, text ) {
		var b = el( 'button', cls, text );
		b.type = 'button';
		return b;
	}
	function plural( forms, n ) {
		var f = n === 1 ? forms.singular : forms.plural;
		return String( f ).replace( '%d', n.toLocaleString() );
	}
	function fill( s, n ) {
		return String( s ).replace( '%d', n.toLocaleString() );
	}
	function norm( s ) {
		return String( s ).toLowerCase().normalize( 'NFD' ).replace( /[̀-ͯ]/g, '' );
	}
	function letterOf( key ) {
		var c = key.charAt( 0 );
		return /[a-z]/.test( c ) ? c : '#';
	}
	function safeUrl( u ) {
		return /^https?:\/\//i.test( String( u || '' ) ) ? String( u ) : '';
	}
	function icon( paths ) {
		var s = document.createElementNS( SVG, 'svg' );
		s.setAttribute( 'viewBox', '0 0 24 24' );
		s.setAttribute( 'width', '18' );
		s.setAttribute( 'height', '18' );
		s.setAttribute( 'aria-hidden', 'true' );
		s.setAttribute( 'focusable', 'false' );
		paths.forEach( function ( d ) {
			var p = document.createElementNS( SVG, 'path' );
			p.setAttribute( 'd', d );
			s.appendChild( p );
		} );
		return s;
	}
	var ICONS = {
		tree: [ 'M4 4h6v4H4zM14 10h6v4h-6zM14 17h6v4h-6zM7 8v11h7M7 12h7' ],
		columns: [ 'M3 4h5v16H3zM10 4h5v16h-5zM17 4h4v16h-4z' ]
	};

	/* ---------- Model ---------- */

	var nodes = {};
	var top = { id: 0, n: t.root, c: 0, d: '', i: '', u: '', kids: [], parent: null, depth: 0, key: '' };
	nodes[ 0 ] = top;
	raw.forEach( function ( r ) {
		nodes[ r.id ] = { id: r.id, p: r.p, n: r.n, c: r.c, d: r.d, i: r.i, u: r.u, kids: [], parent: null, depth: 0, key: norm( r.n ) };
	} );
	raw.forEach( function ( r ) {
		var node = nodes[ r.id ];
		var parent = nodes[ r.p ] || top; // Orphans (parent outside the list) go to the top.
		node.parent = parent;
		parent.kids.push( node );
	} );

	var maxDepth = 0;
	var letters = {};
	( function walk( node, depth ) {
		node.depth = depth;
		node.terms = 0;
		node.items = node.c || 0;
		maxDepth = Math.max( maxDepth, depth );
		if ( node !== top ) {
			letters[ letterOf( node.key ) ] = true;
		}
		node.kids.forEach( function ( k ) {
			walk( k, depth + 1 );
			node.terms += 1 + k.terms;
			node.items += k.items;
		} );
	} )( top, 0 );

	var state = {
		mode: MODES.indexOf( root.dataset.mode ) > -1 ? root.dataset.mode : 'tree',
		sort: 'name',
		query: '',
		letter: '',
		selected: null,
		expanded: {},
		page: 1, // Page of the top level in the tree.
		shown: {}, // Terms shown under a parent, beyond the first 100.
		matches: null
	};

	function sorted( list ) {
		var copy = list.slice();
		if ( state.sort === 'count' ) {
			copy.sort( function ( a, b ) {
				return b.items - a.items || b.terms - a.terms || a.n.localeCompare( b.n );
			} );
		} else {
			copy.sort( function ( a, b ) {
				return a.n.localeCompare( b.n, undefined, { sensitivity: 'base', numeric: true } );
			} );
		}
		return copy;
	}
	function pathOf( node ) {
		var out = [];
		for ( var n = node; n; n = n.parent ) {
			out.unshift( n );
		}
		return out;
	}
	function visibleKids( parent ) {
		var list = sorted( parent.kids );
		if ( state.matches ) {
			list = list.filter( function ( k ) {
				return state.matches.visible[ k.id ];
			} );
		}
		return list;
	}

	/* Search and letter: the terms that match and every ancestor of them. */
	function runFilter() {
		var q = norm( state.query.trim() );
		var letter = state.letter;
		if ( ! q && ! letter ) {
			state.matches = null;
			return;
		}
		var hit = {};
		var visible = {};
		var count = 0;
		Object.keys( nodes ).forEach( function ( id ) {
			var n = nodes[ id ];
			if ( n === top ) {
				return;
			}
			if ( ( ! q || n.key.indexOf( q ) > -1 ) && ( ! letter || letterOf( n.key ) === letter ) ) {
				hit[ id ] = true;
				count++;
				for ( var a = n; a; a = a.parent ) {
					visible[ a.id ] = true;
				}
			}
		} );
		state.matches = { hit: hit, visible: visible, count: count, q: q };
		state.page = 1;
		state.shown = {};
	}
	function marked( name ) {
		var frag = document.createDocumentFragment();
		var q = state.matches && state.matches.q;
		var i = q ? norm( name ).indexOf( q ) : -1;
		if ( i < 0 ) {
			frag.appendChild( document.createTextNode( name ) );
			return frag;
		}
		frag.appendChild( document.createTextNode( name.slice( 0, i ) ) );
		frag.appendChild( el( 'mark', '', name.slice( i, i + q.length ) ) );
		frag.appendChild( document.createTextNode( name.slice( i + q.length ) ) );
		return frag;
	}

	/* Makes a term reachable: its ancestors open and the pages that hold it. */
	function makeReachable( n ) {
		var path = pathOf( n );
		for ( var i = 1; i < path.length; i++ ) {
			var parent = path[ i - 1 ];
			var idx = visibleKids( parent ).indexOf( path[ i ] );
			if ( idx < 0 ) {
				continue;
			}
			if ( parent === top ) {
				state.page = Math.floor( idx / PAGE ) + 1;
			}
			// The columns show the top level by "show more", like any other.
			if ( idx >= ( state.shown[ parent.id ] || PAGE ) ) {
				state.shown[ parent.id ] = ( Math.floor( idx / PAGE ) + 1 ) * PAGE;
			}
			if ( i < path.length - 1 ) {
				state.expanded[ path[ i ].id ] = true;
			}
		}
	}

	/* ---------- Shell ---------- */

	root.textContent = '';
	root.classList.add( 'is-ready' );

	var bar = el( 'div', 'tt2-tx-bar' );
	var tabs = el( 'div', 'tt2-tx-modes' );
	tabs.setAttribute( 'role', 'tablist' );
	tabs.setAttribute( 'aria-label', t.views );
	var tabButtons = {};
	MODES.forEach( function ( m ) {
		var b = button( 'tt2-tx-mode' );
		b.setAttribute( 'role', 'tab' );
		b.appendChild( icon( ICONS[ m ] ) );
		b.appendChild( el( 'span', '', t[ m ] ) );
		b.addEventListener( 'click', function () {
			setMode( m );
		} );
		tabButtons[ m ] = b;
		tabs.appendChild( b );
	} );
	tabs.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'ArrowRight' || e.key === 'ArrowLeft' ) {
			e.preventDefault();
			var i = MODES.indexOf( state.mode );
			var next = MODES[ ( i + ( e.key === 'ArrowRight' ? 1 : MODES.length - 1 ) ) % MODES.length ];
			setMode( next );
			tabButtons[ next ].focus();
		}
	} );

	var search = el( 'label', 'tt2-tx-search' );
	var searchInput = el( 'input' );
	searchInput.type = 'search';
	searchInput.placeholder = t.search;
	searchInput.setAttribute( 'aria-label', t.search );
	search.appendChild( icon( [ 'M10.5 4a6.5 6.5 0 1 0 0 13a6.5 6.5 0 0 0 0-13zM15.5 15.5L20 20' ] ) );
	search.appendChild( searchInput );

	var sortSel = el( 'select', 'tt2-tx-sort' );
	sortSel.setAttribute( 'aria-label', t.sort );
	[ [ 'name', t.sortName ], [ 'count', t.sortCount ] ].forEach( function ( o ) {
		var op = el( 'option', '', o[ 1 ] );
		op.value = o[ 0 ];
		sortSel.appendChild( op );
	} );

	bar.appendChild( tabs );
	bar.appendChild( search );
	bar.appendChild( sortSel );
	root.appendChild( bar );

	// A–Z index: only the letters that start some term are active.
	var az = el( 'nav', 'tt2-tx-az' );
	az.setAttribute( 'aria-label', t.az );
	var azButtons = {};
	var azAll = button( 'tt2-tx-az-all', t.all );
	azAll.addEventListener( 'click', function () {
		setLetter( '' );
	} );
	az.appendChild( azAll );
	'abcdefghijklmnopqrstuvwxyz#'.split( '' ).forEach( function ( l ) {
		var b = button( 'tt2-tx-letter', l.toUpperCase() );
		if ( ! letters[ l ] ) {
			b.disabled = true;
		}
		b.addEventListener( 'click', function () {
			setLetter( state.letter === l ? '' : l );
		} );
		azButtons[ l ] = b;
		az.appendChild( b );
	} );
	root.appendChild( az );

	var stats = el( 'p', 'tt2-tx-stats' );
	stats.setAttribute( 'aria-live', 'polite' );
	root.appendChild( stats );

	var body = el( 'div', 'tt2-tx-body' );
	var view = el( 'div', 'tt2-tx-view' );
	view.setAttribute( 'role', 'tabpanel' );
	var panel = el( 'aside', 'tt2-tx-panel' );
	panel.setAttribute( 'aria-live', 'polite' );
	body.appendChild( view );
	body.appendChild( panel );
	root.appendChild( body );

	function renderStats() {
		var parts = [ plural( t.terms, top.terms ), plural( t.levels, maxDepth ), plural( t.items, top.items ) ];
		if ( state.matches ) {
			parts.unshift( state.matches.count ? plural( t.found, state.matches.count ) : t.nothing );
		}
		stats.textContent = parts.join( ' · ' );
		azAll.setAttribute( 'aria-pressed', state.letter ? 'false' : 'true' );
		Object.keys( azButtons ).forEach( function ( l ) {
			azButtons[ l ].setAttribute( 'aria-pressed', state.letter === l ? 'true' : 'false' );
		} );
	}

	function countBadges( node ) {
		var meta = el( 'span', 'tt2-tx-meta' );
		if ( node.kids.length ) {
			var sub = el( 'span', 'tt2-tx-sub', node.terms );
			sub.title = plural( t.below, node.terms );
			meta.appendChild( sub );
		}
		var count = el( 'span', 'tt2-tx-count', node.items.toLocaleString() );
		count.title = plural( t.items, node.items );
		meta.appendChild( count );
		return meta;
	}

	/* Numbered pages of the top level. */
	function pager( total, onPage ) {
		var pages = Math.ceil( total / PAGE );
		var nav = el( 'nav', 'tt2-tx-pager' );
		nav.setAttribute( 'aria-label', t.pages );
		var from = ( state.page - 1 ) * PAGE + 1;
		nav.appendChild( el( 'span', 'tt2-tx-pager-info', t.range
			.replace( '%1$d', from.toLocaleString() )
			.replace( '%2$d', Math.min( total, state.page * PAGE ).toLocaleString() )
			.replace( '%3$d', total.toLocaleString() ) ) );
		function add( label, page, current, aria ) {
			var b = button( 'tt2-tx-page', label );
			if ( aria ) {
				b.setAttribute( 'aria-label', aria );
			}
			if ( current ) {
				b.setAttribute( 'aria-current', 'page' );
			}
			if ( page < 1 || page > pages ) {
				b.disabled = true;
			}
			b.addEventListener( 'click', function () {
				onPage( page );
			} );
			nav.appendChild( b );
		}
		add( '‹', state.page - 1, false, t.prev );
		var shown = {};
		[ 1, state.page - 2, state.page - 1, state.page, state.page + 1, state.page + 2, pages ].forEach( function ( p ) {
			if ( p < 1 || p > pages || shown[ p ] ) {
				return;
			}
			var last = Object.keys( shown ).map( Number ).pop();
			if ( last && p - last > 1 ) {
				nav.appendChild( el( 'span', 'tt2-tx-gap', '…' ) );
			}
			shown[ p ] = true;
			add( String( p ), p, p === state.page );
		} );
		add( '›', state.page + 1, false, t.next );
		return nav;
	}

	function moreButton( parent, rest, rerender ) {
		var b = button( 'tt2-tx-more-btn', fill( t.showMore, Math.min( PAGE, rest ) ) + ( rest > PAGE ? ' · ' + plural( t.remaining, rest ) : '' ) );
		b.addEventListener( 'click', function () {
			state.shown[ parent.id ] = ( state.shown[ parent.id ] || PAGE ) + PAGE;
			rerender();
		} );
		return b;
	}

	/* ---------- Tree ---------- */

	var maxTopItems = 1;
	function treeView() {
		view.textContent = '';
		var tools = el( 'div', 'tt2-tx-tools' );
		[ [ t.expandAll, true ], [ t.collapseAll, false ] ].forEach( function ( b ) {
			var btn = button( 'tt2-tx-tool', b[ 0 ] );
			btn.addEventListener( 'click', function () {
				state.expanded = {};
				if ( b[ 1 ] ) {
					Object.keys( nodes ).forEach( function ( id ) {
						if ( nodes[ id ].kids.length ) {
							state.expanded[ id ] = true;
						}
					} );
				}
				treeView();
			} );
			tools.appendChild( btn );
		} );
		view.appendChild( tools );

		maxTopItems = 1;
		top.kids.forEach( function ( k ) {
			maxTopItems = Math.max( maxTopItems, k.items );
		} );

		var list = visibleKids( top );
		if ( ! list.length ) {
			view.appendChild( el( 'p', 'tt2-tx-none', t.nothing ) );
			return;
		}
		var pages = Math.ceil( list.length / PAGE );
		state.page = Math.min( Math.max( 1, state.page ), pages );
		var ul = el( 'ul', 'tt2-tx-tree' );
		ul.setAttribute( 'role', 'tree' );
		ul.setAttribute( 'aria-label', t.tree );
		buildLevel( ul, list.slice( ( state.page - 1 ) * PAGE, state.page * PAGE ) );
		view.appendChild( ul );
		if ( list.length > PAGE ) {
			view.appendChild( pager( list.length, function ( p ) {
				state.page = p;
				treeView();
				view.scrollIntoView( { block: 'start' } );
			} ) );
		}
		var current = ul.querySelector( '[aria-selected="true"]' ) || ul.querySelector( '[role="treeitem"]' );
		if ( current ) {
			current.tabIndex = 0;
		}
		ul.addEventListener( 'keydown', treeKeys );
	}
	function isOpen( node ) {
		if ( state.matches ) {
			return !! state.matches.visible[ node.id ] && node.kids.some( function ( k ) {
				return state.matches.visible[ k.id ];
			} );
		}
		return !! state.expanded[ node.id ];
	}
	function buildLevel( ul, list ) {
		list.forEach( function ( node ) {
			var li = el( 'li', 'tt2-tx-item' );
			li.setAttribute( 'role', 'treeitem' );
			li.setAttribute( 'aria-level', String( node.depth ) );
			li.setAttribute( 'aria-selected', state.selected === node ? 'true' : 'false' );
			li.tabIndex = -1;
			li.dataset.id = node.id;
			var open = isOpen( node );
			if ( node.kids.length ) {
				li.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			}

			var row = el( 'div', 'tt2-tx-row' );
			var caret = el( 'span', 'tt2-tx-caret' );
			caret.setAttribute( 'aria-hidden', 'true' );
			if ( node.kids.length ) {
				caret.addEventListener( 'click', function ( e ) {
					e.stopPropagation();
					toggle( node );
				} );
			}
			var name = el( 'span', 'tt2-tx-name' );
			name.appendChild( marked( node.n ) );
			var barEl = el( 'span', 'tt2-tx-bar-mini' );
			barEl.setAttribute( 'aria-hidden', 'true' );
			barEl.style.setProperty( '--w', Math.max( 2, Math.round( 100 * node.items / maxTopItems ) ) + '%' );

			row.appendChild( caret );
			row.appendChild( name );
			row.appendChild( barEl );
			row.appendChild( countBadges( node ) );
			row.addEventListener( 'click', function () {
				if ( node.kids.length && ! state.matches ) {
					state.expanded[ node.id ] = true;
				}
				select( node );
				focusTree( node );
			} );
			li.appendChild( row );

			if ( node.kids.length && open ) {
				var sub = el( 'ul', 'tt2-tx-group' );
				sub.setAttribute( 'role', 'group' );
				var kids = visibleKids( node );
				var shown = state.shown[ node.id ] || PAGE;
				buildLevel( sub, kids.slice( 0, shown ) );
				if ( kids.length > shown ) {
					var moreLi = el( 'li', 'tt2-tx-more-item' );
					moreLi.setAttribute( 'role', 'none' );
					moreLi.appendChild( moreButton( node, kids.length - shown, function () {
						treeView();
						focusTree( node );
					} ) );
					sub.appendChild( moreLi );
				}
				li.appendChild( sub );
			}
			ul.appendChild( li );
		} );
	}
	function toggle( node ) {
		if ( state.matches ) {
			return;
		}
		state.expanded[ node.id ] = ! state.expanded[ node.id ];
		treeView();
		focusTree( node );
	}
	function focusTree( node ) {
		var li = view.querySelector( '[role="treeitem"][data-id="' + node.id + '"]' );
		if ( ! li ) {
			return;
		}
		view.querySelectorAll( '[role="treeitem"]' ).forEach( function ( x ) {
			x.tabIndex = -1;
		} );
		li.tabIndex = 0;
		li.focus( { preventScroll: true } );
		var box = li.getBoundingClientRect();
		if ( box.top < 0 || box.bottom > window.innerHeight ) {
			li.scrollIntoView( { block: 'center' } );
		}
	}
	function treeKeys( e ) {
		var items = Array.prototype.slice.call( view.querySelectorAll( '[role="treeitem"]' ) );
		var li = e.target.closest( '[role="treeitem"]' );
		if ( ! li ) {
			return;
		}
		var i = items.indexOf( li );
		var node = nodes[ li.dataset.id ];
		var go = null;
		switch ( e.key ) {
			case 'ArrowDown':
				go = items[ i + 1 ];
				break;
			case 'ArrowUp':
				go = items[ i - 1 ];
				break;
			case 'Home':
				go = items[ 0 ];
				break;
			case 'End':
				go = items[ items.length - 1 ];
				break;
			case 'ArrowRight':
				if ( node.kids.length && li.getAttribute( 'aria-expanded' ) === 'false' ) {
					e.preventDefault();
					toggle( node );
					return;
				}
				go = li.querySelector( '[role="treeitem"]' );
				break;
			case 'ArrowLeft':
				e.preventDefault();
				if ( li.getAttribute( 'aria-expanded' ) === 'true' ) {
					toggle( node );
				} else if ( node.parent && node.parent !== top ) {
					focusTree( node.parent );
				}
				return;
			case 'Enter':
			case ' ':
				e.preventDefault();
				select( node );
				focusTree( node );
				return;
			default:
				return;
		}
		if ( go ) {
			e.preventDefault();
			li.tabIndex = -1;
			go.tabIndex = 0;
			go.focus();
		}
	}

	/* ---------- Columns ---------- */

	function columnsView() {
		view.textContent = '';
		var wrap = el( 'div', 'tt2-tx-cols' );
		var path = state.selected ? pathOf( state.selected ) : [ top ];
		var cols = [];
		path.forEach( function ( node, idx ) {
			var kids = visibleKids( node );
			if ( ! kids.length ) {
				return;
			}
			var next = path[ idx + 1 ] || null;
			var col = el( 'section', 'tt2-tx-col' );
			col.setAttribute( 'aria-label', node.n );
			var head = el( 'header', 'tt2-tx-col-head' );
			head.appendChild( el( 'span', 'tt2-tx-col-title', node.n ) );
			head.appendChild( el( 'span', 'tt2-tx-col-count', kids.length.toLocaleString() ) );
			col.appendChild( head );
			var list = el( 'ul', 'tt2-tx-col-list' );
			list.setAttribute( 'role', 'listbox' );
			list.setAttribute( 'aria-label', node.n );
			var shown = state.shown[ node.id ] || PAGE;
			kids.slice( 0, shown ).forEach( function ( k ) {
				var li = el( 'li', 'tt2-tx-col-item' + ( k === next ? ' is-path' : '' ) + ( k === state.selected ? ' is-selected' : '' ) );
				li.setAttribute( 'role', 'option' );
				li.setAttribute( 'aria-selected', k === next ? 'true' : 'false' );
				li.tabIndex = k === next ? 0 : -1;
				li.dataset.id = k.id;
				var name = el( 'span', 'tt2-tx-name' );
				name.appendChild( marked( k.n ) );
				li.appendChild( name );
				li.appendChild( countBadges( k ) );
				if ( k.kids.length ) {
					var chev = el( 'span', 'tt2-tx-chev' );
					chev.setAttribute( 'aria-hidden', 'true' );
					li.appendChild( chev );
				}
				li.addEventListener( 'click', function () {
					select( k );
					focusColumn( k );
				} );
				list.appendChild( li );
			} );
			if ( ! list.querySelector( '[tabindex="0"]' ) && list.firstChild ) {
				list.firstChild.tabIndex = 0;
			}
			col.appendChild( list );
			if ( kids.length > shown ) {
				col.appendChild( moreButton( node, kids.length - shown, function () {
					columnsView();
					var first = kids[ shown ];
					if ( first ) {
						focusColumn( first );
					}
				} ) );
			}
			list.addEventListener( 'keydown', columnKeys );
			cols.push( col );
			wrap.appendChild( col );
		} );
		if ( ! cols.length ) {
			view.appendChild( el( 'p', 'tt2-tx-none', t.nothing ) );
			return;
		}
		view.appendChild( wrap );
		// The newest column in sight.
		wrap.scrollLeft = wrap.scrollWidth;
	}
	function focusColumn( node ) {
		var li = view.querySelector( '.tt2-tx-col-item[data-id="' + node.id + '"]' );
		if ( li ) {
			li.focus( { preventScroll: true } );
		}
	}
	function columnKeys( e ) {
		var li = e.target.closest( '.tt2-tx-col-item' );
		if ( ! li ) {
			return;
		}
		var node = nodes[ li.dataset.id ];
		var items = Array.prototype.slice.call( li.parentNode.children );
		var i = items.indexOf( li );
		var go = null;
		switch ( e.key ) {
			case 'ArrowDown':
				go = items[ i + 1 ];
				break;
			case 'ArrowUp':
				go = items[ i - 1 ];
				break;
			case 'Home':
				go = items[ 0 ];
				break;
			case 'End':
				go = items[ items.length - 1 ];
				break;
			case 'ArrowRight':
				e.preventDefault();
				if ( node.kids.length ) {
					select( node );
					var first = visibleKids( node )[ 0 ];
					if ( first ) {
						focusColumn( first );
					}
				}
				return;
			case 'ArrowLeft':
				e.preventDefault();
				if ( node.parent && node.parent !== top ) {
					select( node.parent );
					focusColumn( node.parent );
				}
				return;
			case 'Enter':
			case ' ':
				e.preventDefault();
				select( node );
				focusColumn( node );
				return;
			default:
				return;
		}
		if ( go ) {
			e.preventDefault();
			li.tabIndex = -1;
			go.tabIndex = 0;
			go.focus();
		}
	}

	/* ---------- Panel ---------- */

	var itemsCache = {};
	function chip( text, cls ) {
		return el( 'span', 'tt2-tx-chip' + ( cls ? ' ' + cls : '' ), text );
	}
	function termButton( n ) {
		var b = button( 'tt2-tx-termlink', n.n );
		b.addEventListener( 'click', function () {
			reveal( n );
		} );
		return b;
	}

	function renderPanel() {
		panel.textContent = '';
		var n = state.selected;
		var close = button( 'tt2-tx-close' );
		close.setAttribute( 'aria-label', t.close );
		close.appendChild( icon( [ 'M6 6l12 12M18 6L6 18' ] ) );
		close.addEventListener( 'click', function () {
			root.classList.remove( 'is-panel-open' );
		} );
		panel.appendChild( close );

		if ( ! n ) {
			panel.appendChild( el( 'h2', 'tt2-tx-title label', t.root ) );
			var ov = el( 'div', 'tt2-tx-chips' );
			ov.appendChild( chip( plural( t.terms, top.terms ) ) );
			ov.appendChild( chip( plural( t.levels, maxDepth ) ) );
			ov.appendChild( chip( plural( t.items, top.items ) ) );
			panel.appendChild( ov );
			panel.appendChild( el( 'p', 'tt2-tx-hint', t.pick ) );
			var biggest = top.kids.slice().sort( function ( a, b ) {
				return b.items - a.items || b.terms - a.terms;
			} ).slice( 0, 8 );
			var list = el( 'div', 'tt2-tx-terms' );
			biggest.forEach( function ( k ) {
				list.appendChild( termButton( k ) );
			} );
			panel.appendChild( list );
			return;
		}

		var crumbs = el( 'nav', 'tt2-tx-path' );
		crumbs.setAttribute( 'aria-label', t.path );
		pathOf( n ).slice( 1, -1 ).forEach( function ( a ) {
			crumbs.appendChild( termButton( a ) );
		} );
		if ( crumbs.children.length ) {
			panel.appendChild( crumbs );
		}

		if ( safeUrl( n.i ) ) {
			var fig = el( 'figure', 'tt2-tx-figure' );
			var img = el( 'img' );
			img.src = n.i;
			img.alt = '';
			img.loading = 'lazy';
			fig.appendChild( img );
			panel.appendChild( fig );
		}

		panel.appendChild( el( 'h2', 'tt2-tx-title label', n.n ) );
		if ( n.d ) {
			panel.appendChild( el( 'p', 'tt2-tx-desc', n.d ) );
		}

		var chips = el( 'div', 'tt2-tx-chips' );
		var itemsChip = chip( plural( t.items, n.items ), 'is-accent' );
		chips.appendChild( itemsChip );
		if ( n.kids.length ) {
			chips.appendChild( chip( plural( t.children, n.kids.length ) ) );
			if ( n.terms !== n.kids.length ) {
				chips.appendChild( chip( plural( t.below, n.terms ) ) );
			}
		}
		panel.appendChild( chips );

		var actions = el( 'div', 'tt2-tx-actions' );
		if ( safeUrl( n.u ) ) {
			var see = el( 'a', 'tt2-tx-btn is-primary', t.seeItems );
			see.href = n.u;
			actions.appendChild( see );
		}
		var copy = button( 'tt2-tx-btn', t.copy );
		copy.addEventListener( 'click', function () {
			var url = location.href.split( '#' )[ 0 ] + '#termo-' + n.id;
			if ( navigator.clipboard ) {
				navigator.clipboard.writeText( url ).then( function () {
					copy.textContent = t.copied;
				} );
			}
		} );
		actions.appendChild( copy );
		panel.appendChild( actions );

		if ( n.kids.length ) {
			panel.appendChild( el( 'h3', 'tt2-tx-subtitle label', t.subterms ) );
			var kids = el( 'div', 'tt2-tx-terms' );
			sorted( n.kids ).slice( 0, 40 ).forEach( function ( k ) {
				kids.appendChild( termButton( k ) );
			} );
			if ( n.kids.length > 40 ) {
				kids.appendChild( el( 'span', 'tt2-tx-more', '+' + ( n.kids.length - 40 ) ) );
			}
			panel.appendChild( kids );
		}

		var sibs = n.parent ? n.parent.kids.filter( function ( k ) {
			return k !== n;
		} ) : [];
		if ( sibs.length && n.parent !== top ) {
			panel.appendChild( el( 'h3', 'tt2-tx-subtitle label', t.siblings ) );
			var sb = el( 'div', 'tt2-tx-terms is-quiet' );
			sorted( sibs ).slice( 0, 16 ).forEach( function ( k ) {
				sb.appendChild( termButton( k ) );
			} );
			panel.appendChild( sb );
		}

		panel.appendChild( el( 'h3', 'tt2-tx-subtitle label', t.itemsTitle ) );
		var grid = el( 'div', 'tt2-tx-items' );
		panel.appendChild( grid );
		loadItems( n, grid, itemsChip );
	}

	function loadItems( n, grid, itemsChip ) {
		function paint( res ) {
			if ( state.selected !== n ) {
				return;
			}
			grid.textContent = '';
			if ( res.error ) {
				grid.appendChild( el( 'p', 'tt2-tx-hint', t.error ) );
				return;
			}
			itemsChip.textContent = plural( t.items, res.total );
			if ( ! res.items.length ) {
				grid.appendChild( el( 'p', 'tt2-tx-hint', t.noItems ) );
				return;
			}
			res.items.forEach( function ( it ) {
				var href = safeUrl( it.url );
				var a = el( href ? 'a' : 'span', 'tt2-tx-thumb' );
				if ( href ) {
					a.href = href;
				}
				var th = it.thumbnail && ( it.thumbnail[ 'tainacan-medium' ] || it.thumbnail.thumbnail );
				var src = th && safeUrl( th[ 0 ] );
				if ( src ) {
					var img = el( 'img' );
					img.src = src;
					img.alt = '';
					img.loading = 'lazy';
					a.appendChild( img );
				} else {
					a.classList.add( 'is-empty' );
				}
				a.appendChild( el( 'span', 'tt2-tx-thumb-title', it.title ) );
				grid.appendChild( a );
			} );
		}
		if ( itemsCache[ n.id ] ) {
			paint( itemsCache[ n.id ] );
			return;
		}
		grid.appendChild( el( 'p', 'tt2-tx-hint', t.loading ) );
		var p = new URLSearchParams();
		p.set( 'perpage', '8' );
		p.set( 'fetch_only', 'title,thumbnail,url' );
		p.set( 'taxquery[0][taxonomy]', cfg.taxonomy );
		p.set( 'taxquery[0][terms][0]', String( n.id ) );
		p.set( 'taxquery[0][compare]', 'IN' );
		var headers = cfg.nonce ? { 'X-WP-Nonce': cfg.nonce } : {};
		fetch( cfg.items + ( cfg.items.indexOf( '?' ) > -1 ? '&' : '?' ) + p.toString(), { headers: headers, credentials: 'same-origin' } )
			.then( function ( r ) {
				if ( ! r.ok ) {
					throw new Error( r.status );
				}
				var total = parseInt( r.headers.get( 'X-WP-Total' ), 10 );
				return r.json().then( function ( j ) {
					var items = j && Array.isArray( j.items ) ? j.items : [];
					return { items: items, total: isNaN( total ) ? items.length : total };
				} );
			} )
			.then( function ( res ) {
				itemsCache[ n.id ] = res;
				paint( res );
			} )
			.catch( function () {
				paint( { error: true } );
			} );
	}

	/* ---------- State changes ---------- */

	function syncUrl() {
		try {
			var url = new URL( location.href );
			url.searchParams.set( 'tainacan_terms_viewmode', state.mode );
			url.searchParams.delete( 'termsparent' );
			url.hash = state.selected ? 'termo-' + state.selected.id : '';
			history.replaceState( null, '', url.toString() );
		} catch ( e ) {}
	}
	function select( n ) {
		state.selected = n === top ? null : n;
		if ( state.selected ) {
			makeReachable( state.selected );
			root.classList.add( 'is-panel-open' );
		}
		syncUrl();
		render();
	}
	function reveal( n ) {
		for ( var a = n.parent; a; a = a.parent ) {
			state.expanded[ a.id ] = true;
		}
		select( n );
		if ( state.mode === 'tree' ) {
			focusTree( n );
		} else {
			focusColumn( n );
		}
	}
	function setMode( m ) {
		state.mode = m;
		syncUrl();
		render();
	}
	function setLetter( l ) {
		state.letter = l;
		runFilter();
		render();
	}
	function render() {
		MODES.forEach( function ( m ) {
			var on = m === state.mode;
			tabButtons[ m ].setAttribute( 'aria-selected', on ? 'true' : 'false' );
			tabButtons[ m ].tabIndex = on ? 0 : -1;
		} );
		root.dataset.mode = state.mode;
		renderStats();
		if ( state.mode === 'columns' ) {
			columnsView();
		} else {
			treeView();
		}
		renderPanel();
	}

	var timer;
	searchInput.addEventListener( 'input', function () {
		clearTimeout( timer );
		timer = setTimeout( function () {
			state.query = searchInput.value;
			runFilter();
			render();
		}, 160 );
	} );
	sortSel.addEventListener( 'change', function () {
		state.sort = sortSel.value;
		state.page = 1;
		state.shown = {};
		if ( state.selected ) {
			makeReachable( state.selected );
		}
		render();
	} );

	/* ---------- Start ---------- */

	var start = null;
	var m = /^#termo-(\d+)$/.exec( location.hash );
	if ( m && nodes[ m[ 1 ] ] ) {
		start = nodes[ m[ 1 ] ];
	} else if ( parseInt( root.dataset.term, 10 ) && nodes[ root.dataset.term ] ) {
		start = nodes[ root.dataset.term ];
	}
	if ( root.dataset.search ) {
		searchInput.value = root.dataset.search;
		state.query = root.dataset.search;
		runFilter();
	}
	if ( start ) {
		state.selected = start;
		for ( var a = start; a; a = a.parent ) {
			state.expanded[ a.id ] = true;
		}
		makeReachable( start );
	}
	render();
	if ( start ) {
		if ( state.mode === 'tree' ) {
			focusTree( start );
		} else {
			focusColumn( start );
		}
	}
}() );
