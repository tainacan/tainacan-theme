/**
 * Small behaviors of the design of version 4: the field that finds a collection,
 * "Surprise me", the number of fields of each metadata section, the short values
 * of the record as capsules and the "How to cite" tabs.
 */
(function () {
	'use strict';

	var t = (window.tt2Modern && window.tt2Modern.i18n) || {};

	function fold(text) {
		var s = String(text || '').toLowerCase();
		return s.normalize ? s.normalize('NFD').replace(/[̀-ͯ]/g, '') : s;
	}

	function collectionsFilter() {
		var field = document.querySelector('[data-tt2-collections-filter]');
		if (!field) { return; }
		var cards = Array.prototype.slice.call(document.querySelectorAll('[data-tt2-collection-name]'));
		var none = document.querySelector('[data-tt2-collections-none]');
		if (none) { none.textContent = t.nothingFound || ''; }

		field.addEventListener('input', function () {
			var query = fold(field.value.trim());
			var shown = 0;
			cards.forEach(function (card) {
				var match = !query || fold(card.textContent).indexOf(query) >= 0;
				card.hidden = !match;
				if (match) { shown++; }
			});
			if (none) { none.hidden = shown > 0; }
		});
	}

	// The list is drawn by Tainacan after the page loads: the button appears when there are items to choose from.
	function surprise() {
		var button = document.querySelector('[data-tt2-surprise]');
		if (!button || !window.tt2Drawer) { return; }

		function items() {
			return document.querySelectorAll('[data-tainacan-item-id]');
		}

		var tries = 0;
		var timer = setInterval(function () {
			tries++;
			if (items().length) {
				button.hidden = false;
				clearInterval(timer);
			} else if (tries > 40) {
				clearInterval(timer);
			}
		}, 500);

		button.addEventListener('click', function () {
			var list = items();
			if (!list.length) { return; }
			var element = list[Math.floor(Math.random() * list.length)];
			var link = element.querySelector('a[href]') || element;
			link.click();
		});
	}

	function sectionCounts() {
		document.querySelectorAll('.tt2-section').forEach(function (section) {
			var slot = section.querySelector('.tt2-section__count');
			if (!slot) { return; }
			var n = section.querySelectorAll('.s-item-collection--metadata .tainacan-metadata-value').length;
			if (!n) { return; }
			slot.textContent = (1 === n ? t.fieldsOne : t.fieldsMany || '').replace('%d', String(n));
		});
	}

	// Terms are links and become capsules by CSS; a short text value gets the same reading.
	function chips() {
		if (document.body.classList.contains('tt2-chips-plain')) { return; }
		document.querySelectorAll('.s-item-collection--metadata .tainacan-metadata-value').forEach(function (value) {
			var text = value.textContent.trim();
			if (!text || text.length > 48) { return; }
			if (value.querySelector('a:not([data-linkto]), img, iframe, p + p, ul, table, .leaflet-container, [class*="map"]')) { return; }
			if (value.querySelector('a[data-linkto]')) { return; }
			value.classList.add('tt2-chip-value');
		});
	}

	function citation() {
		var box = document.querySelector('[data-tt2-cite]');
		if (!box) { return; }
		var tabs = Array.prototype.slice.call(box.querySelectorAll('[role="tab"]'));
		var panels = Array.prototype.slice.call(box.querySelectorAll('[role="tabpanel"]'));
		var copy = box.querySelector('[data-tt2-cite-copy]');
		var copyLabel = copy ? copy.textContent : '';

		function select(tab, focus) {
			tabs.forEach(function (other) {
				var on = other === tab;
				other.setAttribute('aria-selected', on ? 'true' : 'false');
				other.tabIndex = on ? 0 : -1;
			});
			panels.forEach(function (panel) {
				panel.hidden = panel.id !== tab.getAttribute('aria-controls');
			});
			if (focus) { tab.focus(); }
		}

		select(tabs[0], false);

		tabs.forEach(function (tab, index) {
			tab.addEventListener('click', function () { select(tab, false); });
			tab.addEventListener('keydown', function (event) {
				var step = 'ArrowRight' === event.key ? 1 : ('ArrowLeft' === event.key ? -1 : 0);
				if (!step) { return; }
				event.preventDefault();
				select(tabs[(index + step + tabs.length) % tabs.length], true);
			});
		});

		if (!copy) { return; }

		function report(message) {
			copy.textContent = message;
			setTimeout(function () { copy.textContent = copyLabel; }, 2200);
		}

		copy.addEventListener('click', function () {
			var panel = panels.filter(function (p) { return !p.hidden; })[0];
			if (!panel) { return; }
			if (!navigator.clipboard) {
				report(t.copyFailed || '');
				return;
			}
			navigator.clipboard.writeText(panel.textContent).then(function () {
				report(t.copied || '');
			}, function () {
				report(t.copyFailed || '');
			});
		});
	}

	function start() {
		collectionsFilter();
		surprise();
		sectionCounts();
		chips();
		citation();
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
}());
