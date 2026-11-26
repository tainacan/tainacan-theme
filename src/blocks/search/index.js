/**
 * Search the collection: editor side. No build step: plain wp.* globals.
 * The block is rendered by the server (render.php); the editor shows that
 * rendering and the settings.
 */
( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, SelectControl, TextControl } = wp.components;
	const ServerSideRender = wp.serverSideRender;
	const data = window.tt2SearchBlock || { collections: [], i18n: {} };
	const t = ( key, fallback ) => ( data.i18n && data.i18n[ key ] ) || fallback;

	registerBlockType( 'tainacan-interface/search', {
		edit( { attributes, setAttributes } ) {
			const options = [ { value: '0', label: t( 'allItems', 'All items' ) } ].concat( data.collections );
			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: t( 'search', 'Search' ) },
						el( SelectControl, {
							label: t( 'searchIn', 'Search in' ),
							value: String( attributes.collectionId || 0 ),
							options,
							onChange: ( value ) => setAttributes( { collectionId: parseInt( value, 10 ) || 0 } ),
						} ),
						el( TextControl, {
							label: t( 'placeholder', 'Text inside the box' ),
							value: attributes.placeholder,
							onChange: ( value ) => setAttributes( { placeholder: value } ),
						} ),
						el( TextControl, {
							label: t( 'button', 'Button text' ),
							value: attributes.buttonText,
							onChange: ( value ) => setAttributes( { buttonText: value } ),
						} ),
						el( TextControl, {
							label: t( 'label', 'Name for screen readers' ),
							value: attributes.label,
							onChange: ( value ) => setAttributes( { label: value } ),
						} ),
						el( SelectControl, {
							label: t( 'size', 'Size' ),
							value: attributes.size,
							options: [
								{ value: 'regular', label: t( 'regular', 'Regular' ) },
								{ value: 'large', label: t( 'large', 'Large' ) },
							],
							onChange: ( value ) => setAttributes( { size: value } ),
						} )
					)
				),
				el(
					'div',
					useBlockProps(),
					el( ServerSideRender, { block: 'tainacan-interface/search', attributes, skipBlockSupportAttributes: true } )
				)
			);
		},
		save: () => null,
	} );
}( window.wp ) );
