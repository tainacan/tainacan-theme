jQuery( document ).on( 'tainacan-collection-hook-reload', function() {
	jQuery( "#colorpicker" ).spectrum({
		showPalette: true,
		cancelText: tainacan_colorPickerVars.cancelText,
		chooseText: tainacan_colorPickerVars.chooseText,
		togglePaletteMoreText: tainacan_colorPickerVars.togglePaletteMoreText,
		togglePaletteLessText: tainacan_colorPickerVars.togglePaletteLessText,
		clearText: tainacan_colorPickerVars.clearText,
		noColorSelectedText: tainacan_colorPickerVars.noColorSelectedText,
		palette: [
			['#298596', '#a55032', '#af2e48', '#c58738', '#4ebfa7'],
			['#288698', '#2db4c1', '#499dd6', '#4751a3', '#955ba5'],
			['#2c2d2d']
		],
		move: function( color ) {
			jQuery( '.color-text' ).css( 'background-color', color.toHexString() );
			jQuery( '#colorpicker' ).attr( 'value', color.toHexString() );
		},
		change: function( color ) {
			jQuery( '.color-text' ).css( 'background-color', color.toHexString() );
			jQuery( '#colorpicker' ).attr( 'value', color.toHexString() );
		}
	});
	jQuery( '.color-text' ).css( 'background-color', jQuery( '.sp-preview-inner' ).css( "background-color" ) );
});
