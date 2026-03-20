/**
 * Tainacan Interface - Dark Mode Toggle
 *
 * Manages dark/light theme switching with localStorage persistence.
 *
 * @package Tainacan_Interface
 * @since 2.9.0
 */
(function () {
	'use strict';

	var STORAGE_KEY = 'tainacan_dark_mode';
	var toggle, html;

	function getPreference() {
		var stored = localStorage.getItem(STORAGE_KEY);
		if (stored !== null) return stored === 'true';

		// Respect system preference
		if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
			return true;
		}
		return false;
	}

	function applyTheme(isDark) {
		html = document.documentElement;
		html.setAttribute('data-theme', isDark ? 'dark' : 'light');

		var toggles = document.querySelectorAll('.tainacan-dark-mode-toggle');
		toggles.forEach(function (t) {
			t.setAttribute('aria-pressed', isDark ? 'true' : 'false');
		});
	}

	function savePreference(isDark) {
		try {
			localStorage.setItem(STORAGE_KEY, isDark ? 'true' : 'false');
		} catch (e) {
			// localStorage unavailable
		}
	}

	function init() {
		var isDark = getPreference();
		applyTheme(isDark);

		document.addEventListener('click', function (e) {
			if (e.target.closest('.tainacan-dark-mode-toggle')) {
				isDark = !isDark;
				applyTheme(isDark);
				savePreference(isDark);
			}
		});

		// Listen for system preference changes
		if (window.matchMedia) {
			window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
				if (localStorage.getItem(STORAGE_KEY) === null) {
					isDark = e.matches;
					applyTheme(isDark);
				}
			});
		}
	}

	// Apply immediately to prevent flash
	(function () {
		var isDark = false;
		try {
			var stored = localStorage.getItem(STORAGE_KEY);
			if (stored !== null) {
				isDark = stored === 'true';
			} else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
				isDark = true;
			}
		} catch (e) {}
		document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
	})();

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
