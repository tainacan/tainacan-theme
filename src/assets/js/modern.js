/**
 * Small behaviors of the design of version 4: the field that finds a collection,
 * "Surprise me", the number of fields of each metadata section, the short values
 * of the record as capsules and the "How to cite" tabs.
 */
(function () {
	'use strict';

	// What assets/js/js.js does with jQuery on the classic design.
	document.body.classList.remove('loading-content');

	var top = document.getElementById('return-to-top');
	if (top) {
		var shown = false;
		var place = function () {
			var show = window.scrollY >= 100;
			if (show !== shown) {
				shown = show;
				top.classList.toggle('tt2-is-shown', show);
			}
		};
		window.addEventListener('scroll', place, { passive: true });
		place();
		top.addEventListener('click', function (event) {
			event.preventDefault();
			var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			window.scrollTo({ top: 0, behavior: still ? 'auto' : 'smooth' });
		});
	}

	// The theme's button on block buttons, unless the editor chose a color or the outline style.
	document.querySelectorAll('.wp-block-button:not(.is-style-outline) a:not(.has-background):not(.has-text-color):not(.has-link-color)').forEach(function (link) {
		link.classList.add('btn', 'btn-jelly-bean');
	});
	document.querySelectorAll('.tainacan-single-post .tainacan-content table').forEach(function (table) {
		table.classList.add('table', 'table-borderless');
	});
	document.querySelectorAll('#comments ul.children').forEach(function (list) {
		list.classList.add('align-children');
	});
	function icon(name) {
		var i = document.createElement('i');
		i.className = 'tainacan-icon tainacan-icon-' + name;
		return i;
	}
	document.querySelectorAll('#comment-nav-below .float-left a').forEach(function (link) { link.before(icon('arrowleft')); });
	document.querySelectorAll('#comment-nav-below .float-right a').forEach(function (link) { link.after(icon('arrowright')); });
	document.addEventListener('click', function (event) {
		var reply = event.target.closest('.comment-reply-link');
		var cancel = document.getElementById('cancel-comment-reply-link');
		var submit = document.querySelector('.form-submit');
		if (reply && cancel && submit) { submit.prepend(cancel); }

		var cell = event.target.closest('.tainacan-list-post .table .tainacan-list-collection td');
		var href = cell && cell.closest('.tainacan-list-collection').getAttribute('data-href');
		if (href && !event.target.closest('a')) { window.location = href; }
	});
	document.querySelectorAll('#menubelowHeader .dropdown-menu a.dropdown-toggle').forEach(function (link) {
		link.classList.add('dropdown-submenu');
	});
	// A submenu inside a submenu opens beside it, and the one above stays open.
	document.addEventListener('click', function (event) {
		var toggle = event.target.closest('.dropdown-menu a.dropdown-toggle');
		var sub = toggle && toggle.nextElementSibling;
		if (!sub || !sub.classList.contains('dropdown-menu')) { return; }
		event.preventDefault();
		event.stopImmediatePropagation();
		if (!sub.classList.contains('show')) {
			toggle.closest('.dropdown-menu').querySelectorAll('.show').forEach(function (open) { open.classList.remove('show'); });
		}
		sub.classList.toggle('show');
	}, true);
	document.querySelectorAll('.margin-pagination .navigation.pagination').forEach(function (nav) {
		nav.classList.add('justify-content-center', 'justify-content-md-end');
	});
	if (document.querySelector('.tainacan-single-item-heading')) {
		var navbar = document.getElementById('topNavbar');
		if (navbar) { navbar.classList.add('b-bottom-top'); }
		document.querySelectorAll('nav.menu-belowheader').forEach(function (nav) { nav.classList.remove('border-bottom'); });
		document.querySelectorAll('nav.menu-belowheader .max-large').forEach(function (box) { box.classList.add('b-bottom-bellow'); });
	}

	// A map that started inside a closed panel has no size until a resize.
	document.querySelectorAll('.tt2-section').forEach(function (section) {
		section.addEventListener('toggle', function () {
			if (section.open) { window.dispatchEvent(new Event('resize')); }
		});
	});

	var t = (window.tainacanInterfaceModern && window.tainacanInterfaceModern.i18n) || {};

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
		if (!button || !window.tainacanInterfaceDrawer) { return; }

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
