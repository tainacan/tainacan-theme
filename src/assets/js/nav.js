/**
 * Dropdowns and collapses of the theme, without a framework. Answers to the
 * data-toggle="dropdown" and data-toggle="collapse" markup of the templates, the
 * menu walker and child themes written for 2.x: the same classes (.show,
 * .collapsed) and the same aria-expanded.
 */
(function () {
	'use strict';

	function closeDropdowns(except) {
		document.querySelectorAll('[data-toggle="dropdown"][aria-expanded="true"]').forEach(function (toggle) {
			if (toggle === except || (except && toggle.parentNode.contains(except))) { return; }
			setDropdown(toggle, false);
		});
	}

	function menuOf(toggle) {
		var parent = toggle.parentNode;
		return parent ? parent.querySelector('.dropdown-menu') : null;
	}

	function setDropdown(toggle, open) {
		var menu = menuOf(toggle);
		if (!menu) { return; }
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		toggle.parentNode.classList.toggle('show', open);
		menu.classList.toggle('show', open);
	}

	function toggleCollapse(toggle) {
		var selector = toggle.getAttribute('data-target') || toggle.getAttribute('href');
		var target = null;
		try { target = selector ? document.querySelector(selector) : null; } catch (err) { target = null; }
		if (!target) { return; }
		var open = !target.classList.contains('show');
		target.classList.toggle('show', open);
		toggle.classList.toggle('collapsed', !open);
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
	}

	document.addEventListener('click', function (event) {
		var toggle = event.target.closest ? event.target.closest('[data-toggle]') : null;
		var kind = toggle ? toggle.getAttribute('data-toggle') : '';

		if ('collapse' === kind) {
			event.preventDefault();
			toggleCollapse(toggle);
			return;
		}

		if ('dropdown' === kind) {
			event.preventDefault();
			var open = 'true' !== toggle.getAttribute('aria-expanded');
			closeDropdowns(toggle);
			setDropdown(toggle, open);
			if (open) {
				var field = menuOf(toggle).querySelector('input[type="search"], input[type="text"]');
				if (field) { field.focus(); }
			}
			return;
		}

		// A click inside an open menu's form keeps it open; anywhere else closes.
		if (!event.target.closest || !event.target.closest('.dropdown-menu form')) {
			closeDropdowns(null);
		}
	});

	document.addEventListener('keydown', function (event) {
		var toggle;
		if ('Escape' === event.key) {
			toggle = document.querySelector('[data-toggle="dropdown"][aria-expanded="true"]');
			if (toggle) {
				closeDropdowns(null);
				toggle.focus();
			}
			return;
		}

		if ('ArrowDown' !== event.key && 'ArrowUp' !== event.key) { return; }

		var menu = event.target.closest ? event.target.closest('.dropdown-menu.show') : null;
		toggle = event.target.closest ? event.target.closest('[data-toggle="dropdown"]') : null;
		if (toggle && !menu) {
			setDropdown(toggle, true);
			menu = menuOf(toggle);
		}
		if (!menu) { return; }

		var links = Array.prototype.slice.call(menu.querySelectorAll('a[href], button:not([disabled])'));
		if (!links.length) { return; }
		event.preventDefault();
		var index = links.indexOf(document.activeElement) + ('ArrowDown' === event.key ? 1 : -1);
		links[(index + links.length) % links.length].focus();
	});
}());
