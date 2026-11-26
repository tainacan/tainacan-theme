/**
 * Rebuilds, in the block editor, the blocks whose saved HTML does not match
 * what the editor makes from their settings (the "Attempt block recovery" of
 * the block menu, for all of them at once).
 *
 * - Tainacan blocks whose HTML is made by the editor (carousels, lists, faceted
 *   search...): rebuilt by themselves, because their HTML is only a function of
 *   their settings and of the address of the site. Written by an AI, pasted or
 *   inserted from a pattern of another site, they would show nothing.
 * - Any other invalid block: a notice offers the same, so nobody loses text
 *   without choosing it.
 */
( function ( wp, settings ) {
	if ( ! wp || ! wp.data || ! wp.blocks || ! settings ) {
		return;
	}
	const { select, dispatch, subscribe } = wp.data;
	const seen = new Set();
	let offered = false;

	const invalidBlocks = ( blocks, out ) => {
		blocks.forEach( ( block ) => {
			if ( block.isValid === false && ! seen.has( block.clientId ) ) {
				out.push( block );
			}
			invalidBlocks( block.innerBlocks || [], out );
		} );
		return out;
	};

	const rebuild = ( blocks ) => {
		blocks.forEach( ( block ) => {
			seen.add( block.clientId );
			dispatch( 'core/block-editor' ).replaceBlock( block.clientId, wp.blocks.createBlock( block.name, block.attributes, block.innerBlocks ) );
		} );
	};

	const check = () => {
		const editor = select( 'core/block-editor' );
		if ( ! editor || ! editor.getBlocks ) {
			return;
		}
		const invalid = invalidBlocks( editor.getBlocks(), [] );
		if ( ! invalid.length ) {
			return;
		}
		const own = invalid.filter( ( block ) => settings.editorMade.indexOf( block.name ) !== -1 );
		const other = invalid.filter( ( block ) => settings.editorMade.indexOf( block.name ) === -1 );
		if ( own.length ) {
			rebuild( own );
			dispatch( 'core/notices' ).createNotice( 'info', settings.rebuilt.replace( '%d', own.length ), { id: 'tt2-rebuilt', isDismissible: true } );
		}
		if ( other.length && ! offered ) {
			offered = true;
			dispatch( 'core/notices' ).createNotice( 'warning', settings.invalid.replace( '%d', other.length ), {
				id: 'tt2-invalid',
				isDismissible: true,
				actions: [
					{
						label: settings.action,
						onClick: () => {
							rebuild( invalidBlocks( select( 'core/block-editor' ).getBlocks(), [] ) );
							dispatch( 'core/notices' ).removeNotice( 'tt2-invalid' );
						},
					},
				],
			} );
		}
	};

	let timer = null;
	subscribe( () => {
		if ( timer ) {
			return;
		}
		timer = window.setTimeout( () => {
			timer = null;
			check();
		}, 400 );
	} );
}( window.wp, window.tt2Rebuild ) );
