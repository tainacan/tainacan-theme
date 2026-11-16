/**
 * The panel of the design: ready-made colors, options that leave when they would
 * do nothing, the warning about collections with their own value, and a preview
 * that goes to a page where the open section can be seen.
 */
(function (api, cfg, _) {
	'use strict';

	if (!api || !cfg) { return; }

	/** A checkbox holds true or '1'; everything is compared as text. */
	function asText(value) {
		if (true === value || 1 === value) { return '1'; }
		return false === value || null === value || undefined === value ? '' : String(value);
	}

	/** A condition on an option that some collection overrides cannot rule the control out. */
	function pinnedSetting(id) {
		var key = id.replace(/^tainacan_interface_/, '');
		return !!(cfg.pins[key] && cfg.pins[key].length);
	}

	function holds(condition) {
		var setting = api(condition[0]);
		if (!setting || pinnedSetting(condition[0])) { return true; }
		return condition[1].indexOf(asText(setting())) !== -1;
	}

	function applyRules(id, groups) {
		api.control(id, function (control) {
			var active = function () {
				return groups.every(function (group) { return group.some(holds); });
			};
			// The preview reports the state of each control after a reload: both must agree.
			var server = control.active();
			var sync = function () {
				control.active.set(server);
				// A section with no option left goes away with them.
				api.section(control.section(), function (section) {
					section.active.set(section.isContextuallyActive());
				});
			};
			control.active.validate = function (to) {
				server = to;
				return to && active();
			};
			groups.forEach(function (group) {
				group.forEach(function (condition) {
					api(condition[0], function (setting) { setting.bind(sync); });
				});
			});
			sync();
		});
	}

	function warnPinned(key, collections) {
		api.control('tainacan_interface_' + key, function (control) {
			var links = collections.map(function (collection) {
				return '<a href="' + _.escape(collection.u) + '" target="_blank" rel="noopener">' + _.escape(collection.n) + '</a>';
			}).join(', ');
			control.notifications.add(new api.Notification('tt2_pinned', {
				type: 'info',
				message: cfg.i18n.pinned.replace('%s', links)
			}));
		});
	}

	api.bind('ready', function () {
		api('tainacan_interface_preset_picker', function (setting) {
			setting.bind(function (slug) {
				var values = cfg.presets[slug];
				if (!values) { return; }
				Object.keys(values).forEach(function (key) {
					api('tainacan_interface_' + key, function (color) {
						color.set(values[key]);
					});
				});
			});
		});

		Object.keys(cfg.rules).forEach(function (id) { applyRules(id, cfg.rules[id]); });
		Object.keys(cfg.pins).forEach(function (key) { warnPinned(key, cfg.pins[key]); });

		var kind = '';
		api.previewer.bind('tt2-context', function (current) { kind = current; });

		Object.keys(cfg.sections).forEach(function (id) {
			api.section(id, function (section) {
				section.expanded.bind(function (open) {
					var wanted = cfg.sections[id];
					if (open && kind !== wanted && cfg.targets[wanted]) {
						api.previewer.previewUrl.set(cfg.targets[wanted]);
					}
				});
			});
		});
	});
}(window.wp && window.wp.customize, window.tainacanInterfaceCustomizer, window._));
