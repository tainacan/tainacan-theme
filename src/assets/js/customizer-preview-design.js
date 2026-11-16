/**
 * Preview of the design: applies at once the options that are CSS variables or
 * body classes, on the same names inc/design.php prints.
 */
(function (api, cfg) {
	'use strict';

	if (!api || !cfg) { return; }

	var body = document.body;

	function isDark(hex) {
		var value = String(hex || '').replace('#', '');
		if (3 === value.length) { value = value.replace(/./g, '$&$&'); }
		var number = parseInt(value, 16);
		return 0.2126 * (number >> 16 & 255) + 0.7152 * (number >> 8 & 255) + 0.0722 * (number & 255) < 150;
	}

	function css(name, value) { body.style.setProperty(name, value); }

	function read(key) {
		var setting = api('tainacan_interface_' + key);
		return setting ? setting() : '';
	}

	function band() {
		var colors = [read('band_1'), read('band_2'), read('band_3'), read('band_4')];
		switch (read('band_style')) {
			case 'bands':
				return 'linear-gradient(90deg, ' + colors[0] + ' 0 25%, ' + colors[1] + ' 25% 50%, ' + colors[2] + ' 50% 75%, ' + colors[3] + ' 75%)';
			case 'gradient':
				return 'linear-gradient(90deg, ' + colors.join(', ') + ')';
			case 'accent':
				return read('accent_color');
			default:
				return 'transparent';
		}
	}

	// The header and the footer take the main or the accent colour when their style says so:
	// inc/design.php derives these four tokens from the two colours, so the preview does too.
	function surfaces() {
		var tones = { brand: read('brand_color'), accent: read('accent_color') };
		[['header_style', '--tt2-header'], ['footer_tone', '--tt2-footer']].forEach(function (pair) {
			var tone = tones[read(pair[0])];
			if (!tone) { return; }
			css(pair[1] + '-bg', tone);
			css(pair[1] + '-fg', isDark(tone) ? '#ffffff' : '#111111');
		});
	}

	var kinds = {
		brand: function (value) {
			// --tt2-ink is var(--tt2-brand), var(--tt2-accent) or var(--tt2-text) by the heading colour option: it follows on its own.
			css('--tt2-brand', value);
			css('--tt2-on-brand', isDark(value) ? '#ffffff' : '#111111');
			surfaces();
		},
		accent: function (value) {
			['--tt2-accent', '--tt2-d-accent', '--tainacan-secondary', '--tainacan-interface--link-color', '--tainacan-block-primary'].forEach(function (name) { css(name, value); });
			css('--tt2-on-accent', isDark(value) ? '#ffffff' : '#111111');
			css('--tainacan-primary', 'color-mix(in srgb, ' + value + ' 14%, #fff)');
			css('--tainacan-interface--tooltip-color', 'color-mix(in srgb, ' + value + ' 18%, #fff)');
			if ('accent' === read('band_style')) { css('--tt2-band', value); }
			surfaces();
		},
		band: function () { css('--tt2-band', band()); }
	};

	function apply(key, how, value) {
		if (how.kind) {
			kinds[how.kind](value);
		} else if (how.vars) {
			var text = how.map ? how.map[value] : (how.divide ? Number(value) / how.divide : value) + (how.unit || '');
			how.vars.forEach(function (name) { css(name, text); });
		} else if (how.prefix) {
			how.values.forEach(function (one) { body.classList.remove(how.prefix + one); });
			body.classList.add(how.prefix + value);
		} else if (how.flag) {
			body.classList.toggle(how.flag, how.when ? value === how.when : !!value);
		}
	}

	Object.keys(cfg.live).forEach(function (key) {
		// A collection with its own value keeps it, as on the published page.
		if (cfg.pinned.indexOf(key) !== -1) { return; }
		api('tainacan_interface_' + key, function (setting) {
			setting.bind(function (value) { apply(key, cfg.live[key], value); });
		});
	});

	// The panel hears the preview only once the channel is active.
	api.bind('preview-ready', function () {
		api.preview.bind('active', function () {
			api.preview.send('tt2-context', cfg.kind);
		});
	});
}(window.wp && window.wp.customize, window.tainacanInterfacePreview));
