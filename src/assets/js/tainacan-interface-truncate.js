(function($) {
	$.fn.tainacan_interface_truncate = function (settings) {
		var config = {
			showChars: 350,
			minChars: 350,
			ellipsesText: "...",
			moreText: tainacan_trucanteVars.moreText,
			lessText: tainacan_trucanteVars.lessText
		};

		if (settings) {
			$.extend( config, settings );
		}

		var minimized_elements = $( this );

		minimized_elements.each(function(){
			var t = minimized_elements.html();
			if (t.length <= config.minChars) { return };

			$( this ).html(
				t.slice( 0,config.showChars ) + '<span>' + config.ellipsesText + ' </span><a href="#" class="tainacan-interface-more">[ ' + config.moreText + ' ]</a>' + '<span style="display:none;">' + t.slice( config.showChars,t.length ) + ' <a href="#" class="tainacan-interface-less">[ ' + config.lessText + ' ]</a></span>'
			);

		});

		$( 'a.tainacan-interface-more', minimized_elements ).click(function(event){
			event.preventDefault();
			minimized_elements.addClass( 'full-story' );
			$( this ).hide().prev().hide();
			$( this ).next().show();
		});

		$( 'a.tainacan-interface-less', minimized_elements ).click(function(event){
			event.preventDefault();
			minimized_elements.removeClass( 'full-story' );
			$( this ).parent().hide().prev().show().prev().show();
		});
		return minimized_elements;
	};
})(jQuery);
