jQuery( document ).on( 'tainacan-collection-hook-reload', function() {
	jQuery( "#colorpicker" ).spectrum({
		showPalette: true,
		showInput: true,
		showButtons: false,
		preferredFormat: "hex",
		cancelText: tainacan_colorPickerVars.cancelText,
		chooseText: tainacan_colorPickerVars.chooseText,
		togglePaletteMoreText: tainacan_colorPickerVars.togglePaletteMoreText,
		togglePaletteLessText: tainacan_colorPickerVars.togglePaletteLessText,
		clearText: tainacan_colorPickerVars.clearText,
		noColorSelectedText: tainacan_colorPickerVars.noColorSelectedText,
		palette: [
			['#754E24', '#8c442c', '#A12B42', '#955ba5', '#4751a3'],
			['#D3A978', '#D88C74', '#DB7B8F', '#BE9BCA', '#A5ACDA'],
			['#1D5C86', '#185F6D', '#205E6F', '#255F56', '#2c2d2d'],
			['#6CADDF', '#6ECBDE', '#7CCED0', '#7ECDC4', '#cbcbcb']
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
