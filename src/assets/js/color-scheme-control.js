/* global colorScheme, Color */
/**
 * Add a listener to the Color Scheme control to update other color controls to new values/defaults.
 * Also trigger an update of the Color Scheme CSS when a color is changed.
 */

( function( api ) {
	var cssTemplate = wp.template( 'tainacan-color-scheme' ),
		colorSchemeKeys = [
			'tainacan_link_color',
			'tainacan_tooltip_color',
			/* 'main_text_color',
			'secondary_text_color' */
		],
		colorSettings = [
			'tainacan_link_color',
			'tainacan_tooltip_color',
			/* 'main_text_color',
			'secondary_text_color' */
		];

	api.controlConstructor.select = api.Control.extend( {
		ready: function() {
			if ( 'tainacan_color_scheme' === this.id ) {
				this.setting.bind( 'change', function( value ) {
					var colors = colorScheme[value].colors;

					// Update Link Color.
					color = colors[2];
					api( 'tainacan_link_color' ).set( color );
					api.control( 'tainacan_link_color' ).container.find( '.color-picker-hex' )
						.data( 'data-default-color', color )
						.wpColorPicker( 'defaultColor', color );

					color = colors[3];
					api( 'tainacan_tooltip_color' ).set( color );
					api.control( 'tainacan_tooltip_color' ).container.find( '.color-picker-hex' )
						.data( 'data-default-color', color )
						.wpColorPicker( 'defaultColor', color );

					/* // Update Main Text Color.
					color = colors[3];
					api( 'main_text_color' ).set( color );
					api.control( 'main_text_color' ).container.find( '.color-picker-hex' )
						.data( 'data-default-color', color )
						.wpColorPicker( 'defaultColor', color );

					// Update Secondary Text Color.
					color = colors[4];
					api( 'secondary_text_color' ).set( color );
					api.control( 'secondary_text_color' ).container.find( '.color-picker-hex' )
						.data( 'data-default-color', color )
						.wpColorPicker( 'defaultColor', color ); */
				} );
			}
		}
	} );

	// Generate the CSS for the current Color Scheme.
	function updateCSS() {
		var scheme = api( 'tainacan_color_scheme' )(),
			css,
			colors = _.object( colorSchemeKeys, colorScheme[ scheme ].colors );

		// Merge in color scheme overrides.
		_.each( colorSettings, function( setting ) {
			colors[ setting ] = api( setting )();
		} );

		// Add additional color.
		// jscs:disable
		colors.backtransparent = Color( colors.link_color ).toCSS( 'rgba', 0.5 );
		// jscs:enable

		css = cssTemplate( colors );

		api.previewer.send( 'update-color-scheme-css', css );
	}

	// Update the CSS whenever a color setting is changed.
	_.each( colorSettings, function( setting ) {
		api( setting, function( setting ) {
			setting.bind( updateCSS );
		} );
	} );
} )( wp.customize );
