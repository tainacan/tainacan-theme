(function($) {
	$.fn.tainacan_interface_truncate = function (settings) {
		if(window.innerWidth <= 576) {
			count = 150;
		}else if(window.innerWidth <= 360) {
			count = 87;
		} else {
			count = 350;
		}
		var config = {
			showChars: count,
			minChars: count,
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

(function($) {
	$.fn.tainacan_interface_truncate_term = function (settings) {
		count = 1400;
		const windowWidth = window.innerWidth;

		if (windowWidth <= 1860)
			count = 890;

		if (windowWidth <= 1365)
			count = 580;
	
		if (windowWidth <= 920)
			count = 500;
		
		if (windowWidth <= 768)
			count = 280;
		
		var config = {
			showChars: count,
			minChars: count,
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
				t.slice( 0,config.showChars ) + '<span>' + config.ellipsesText + ' </span><a href="#" class="tainacan-interface-more-term">[ ' + config.moreText + ' ]</a>' + '<span style="display:none;">' + t.slice( config.showChars,t.length ) + ' <a href="#" class="tainacan-interface-less-term">[ ' + config.lessText + ' ]</a></span>'
			);

		});

		$( 'a.tainacan-interface-more-term', minimized_elements ).click(function(event){
			event.preventDefault();
			minimized_elements.addClass( 'full-story' );
			$( this ).hide().prev().hide();
			$( this ).next().show();
		});

		$( 'a.tainacan-interface-less-term', minimized_elements ).click(function(event){
			event.preventDefault();
			minimized_elements.removeClass( 'full-story' );
			$( this ).parent().hide().prev().show().prev().show();
		});
		return minimized_elements;
	};
})(jQuery);
