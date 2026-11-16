/**
 * Item preview drawer on the Tainacan lists.
 *
 * A plain click on an item of a list opens the drawer on the right; the
 * buttons and the arrow keys (with the focus in the drawer) walk the items of
 * the page, Esc closes and gives the focus back to the item being shown.
 * Ctrl/Cmd/Shift/Alt and the middle button open the item page as always.
 * Every text from the server goes in with textContent; metadata values were
 * filtered by wp_kses_post() on the server and are parsed in an inert document.
 * When TainacanBiblio's or Acervo em Cena's drawer is on the page, theirs wins.
 */
(function () {
	'use strict';

	var cfg = window.tt2Drawer;
	if (!cfg || !cfg.endpoint || !window.fetch) { return; }

	var t = cfg.i18n || {};
	var LIST_ROOT = '#tainacan-items-page, .theme-items-list, .wp-block-tainacan-faceted-search';
	var SMALL = window.matchMedia('(max-width: 600px)');
	var drawer = null;
	var live = null;
	var backdrop = null;
	var OVERLAY = cfg.mode === 'overlay';
	var state = { id: null, list: [], trigger: null };
	var cache = Object.create(null);

	function fmt(s, args) {
		var i = 0;
		return String(s || '').replace(/%(\d+\$)?[sd]/g, function (_, n) {
			var k = n ? parseInt(n, 10) - 1 : i++;
			return args[k] == null ? '' : String(args[k]);
		});
	}

	function h(tag, attrs, kids) {
		var el = document.createElement(tag);
		Object.keys(attrs || {}).forEach(function (k) {
			var v = attrs[k];
			if (v == null || v === false) { return; }
			if (k === 'text') { el.textContent = v; } else if (k === 'class') { el.className = v; } else { el.setAttribute(k, v === true ? '' : v); }
		});
		(kids || []).forEach(function (c) { if (c) { el.appendChild(c); } });
		return el;
	}

	function iconButton(cls, label, glyph, onClick) {
		var b = h('button', { type: 'button', class: 'tt2-drawer__btn ' + cls, 'aria-label': label, title: label });
		b.appendChild(h('span', { 'aria-hidden': 'true', text: glyph }));
		b.addEventListener('click', function () {
			if (b.getAttribute('aria-disabled') !== 'true') { onClick(); }
		});
		return b;
	}

	// The metadata the list is showing (Tainacan keeps fetch_only_meta in the URL).
	function shownMeta() {
		var m = /[?&]fetch_only_meta=([^&#]*)/.exec(window.location.search);
		var v = '';
		try { v = m ? decodeURIComponent(m[1].replace(/\+/g, ' ')) : ''; } catch (e) { v = ''; }
		return /^\d+(,\d+)*$/.test(v) ? v.split(',').slice(0, 60).join(',') : '';
	}

	function fetchItem(id) {
		var meta = shownMeta();
		var key = id + '|' + meta;
		if (!cache[key]) {
			var headers = cfg.nonce ? { 'X-WP-Nonce': cfg.nonce } : {};
			var url = cfg.endpoint + encodeURIComponent(id) + (meta ? '?meta=' + encodeURIComponent(meta) : '');
			cache[key] = fetch(url, { credentials: 'same-origin', headers: headers })
				.then(function (r) {
					if (!r.ok) { var err = new Error(String(r.status)); err.status = r.status; throw err; }
					return r.json();
				})
				.catch(function (e) { delete cache[key]; throw e; });
		}
		return cache[key];
	}

	// HTML already filtered by wp_kses_post() on the server: parsed in an inert document, never innerHTML.
	function valueNodes(html) {
		var doc = new DOMParser().parseFromString('<div>' + String(html || '') + '</div>', 'text/html');
		var frag = document.createDocumentFragment();
		Array.prototype.forEach.call(doc.body.firstChild.childNodes, function (n) { frag.appendChild(document.importNode(n, true)); });
		return frag;
	}

	document.addEventListener('DOMContentLoaded', function () {
		if (!otherDrawer()) { document.documentElement.classList.add('tt2-drawer-ready'); }
	});

	function build() {
		drawer = h('aside', { class: 'tt2-drawer', role: 'dialog', 'aria-modal': 'false', 'aria-label': t.label || '', hidden: true, tabindex: '-1' });
		live = h('p', { class: 'screen-reader-text', 'aria-live': 'polite' });
		if (cfg.side === 'left') { drawer.classList.add('tt2-drawer--left'); document.body.classList.add('tt2-drawer-side-left'); }
		if (cfg.media === 'cover') { drawer.classList.add('tt2-drawer--cover'); }
		if (cfg.modern) { drawer.classList.add('tt2-drawer--modern'); }
		if (OVERLAY) {
			drawer.classList.add('tt2-drawer--overlay');
			backdrop = h('div', { class: 'tt2-drawer-backdrop' + (cfg.backdrop ? ' is-dim' : ''), hidden: true });
			backdrop.addEventListener('click', close);
			document.body.appendChild(backdrop);
		}
		document.body.appendChild(drawer);
		document.body.appendChild(live);
		drawer.addEventListener('keydown', function (e) {
			var typing = /input|textarea|select/i.test(e.target.tagName);
			if (e.key === 'Escape') { e.preventDefault(); close(); }
			else if (e.key === 'ArrowLeft' && !typing) { e.preventDefault(); step(-1); }
			else if (e.key === 'ArrowRight' && !typing) { e.preventDefault(); step(1); }
		});
	}

	function head(withNav) {
		var pos = state.list.indexOf(state.id);
		var many = withNav && pos >= 0 && state.list.length > 1;
		var prev = many ? iconButton('tt2-drawer__prev', t.prev || '', '‹', function () { step(-1); }) : null;
		var next = many ? iconButton('tt2-drawer__next', t.next || '', '›', function () { step(1); }) : null;
		if (prev && pos === 0) { prev.setAttribute('aria-disabled', 'true'); }
		if (next && pos === state.list.length - 1) { next.setAttribute('aria-disabled', 'true'); }
		return h('div', { class: 'tt2-drawer__head' }, [
			h('span', { class: 'tt2-drawer__pos', text: many ? fmt(t.pos, [pos + 1, state.list.length]) : '' }),
			h('div', { class: 'tt2-drawer__nav' }, [prev, next, iconButton('tt2-drawer__close', t.close || '', '×', close)])
		]);
	}

	// A detached player keeps playing: pause it before the drawer content goes.
	function stopMedia() {
		if (!drawer) { return; }
		drawer.querySelectorAll('video, audio').forEach(function (m) { try { m.pause(); } catch (err) { /* gone */ } });
	}

	// The item's document: image, video and audio play here (preload="none": nothing
	// is downloaded until the visitor presses play); a PDF shows its cover and a link.
	function mediaBox(d) {
		var m = d.media;
		var box = h('div', { class: 'tt2-drawer__media' });
		if (m && m.kind === 'image') {
			box.appendChild(h('img', { class: 'tt2-drawer__cover', src: m.src, alt: '', decoding: 'async' }));
		} else if (m && (m.kind === 'video' || m.kind === 'audio')) {
			if (m.kind === 'audio' && d.cover) { box.appendChild(h('img', { class: 'tt2-drawer__cover', src: d.cover, alt: '', decoding: 'async' })); }
			var player = h(m.kind, { class: 'tt2-drawer__player', src: m.src, controls: true, preload: 'none', playsinline: true, poster: m.kind === 'video' ? d.cover || null : null });
			player.setAttribute('controlslist', 'nodownload');
			player.addEventListener('error', function () {
				player.replaceWith(h('p', { class: 'tt2-drawer__status' }, [
					h('span', { text: (t.unplayable || '') + ' ' }),
					h('a', { href: m.src, download: true, text: t.download || '' })
				]));
			});
			box.appendChild(player);
		} else if (m && m.kind === 'pdf') {
			box.appendChild(h('a', { class: 'tt2-drawer__pdf', href: listHref(d.id) || d.url }, [
				h('img', { class: 'tt2-drawer__cover', src: d.cover, alt: '', decoding: 'async' }),
				h('span', { class: 'tt2-drawer__badge', text: 'PDF' })
			]));
		} else if (d.cover) {
			box.appendChild(h('img', { class: 'tt2-drawer__cover', src: d.cover, alt: '', decoding: 'async' }));
		}
		return box;
	}

	function render(d) {
		stopMedia();
		drawer.textContent = '';
		drawer.appendChild(head(true));

		var body = h('div', { class: 'tt2-drawer__body' });
		if (cfg.media !== 'none') { body.appendChild(mediaBox(d)); }
		if (cfg.metaLine && d.meta_line && d.meta_line.length) {
			body.appendChild(h('p', { class: 'tt2-drawer__meta-line', text: d.meta_line.join(' · ') }));
		} else if (d.collection && !cfg.modern) {
			body.appendChild(h('p', { class: 'tt2-drawer__eyebrow', text: d.collection }));
		}
		body.appendChild(h('h2', { id: 'tt2-drawer-title', class: 'tt2-drawer__title', tabindex: '-1', text: d.title }));
		var description = d.description && !cfg.noDesc ? h('p', { class: 'tt2-drawer__description', text: d.description }) : null;
		if (description && !cfg.modern) { body.appendChild(description); }

		if (d.fields && d.fields.length) {
			var dl = h('dl', { class: 'tt2-drawer__fields' });
			d.fields.forEach(function (f) {
				var dd = h('dd', {}, [valueNodes(f.html)]);
				// A short plain value reads as the capsules of the terms beside it.
				if (cfg.modern && dd.textContent.trim().length <= 48 && !dd.querySelector('a, img, ul, table, iframe')) { dd.classList.add('tt2-chip-value'); }
				dl.appendChild(h('dt', { text: f.label }));
				dl.appendChild(dd);
			});
			body.appendChild(dl);
		}
		if (description && cfg.modern) { body.appendChild(description); }
		drawer.appendChild(body);
		drawer.setAttribute('aria-labelledby', 'tt2-drawer-title');

		drawer.appendChild(h('div', { class: 'tt2-drawer__foot' }, [
			h('a', { class: 'tt2-drawer__open', href: listHref(d.id) || d.url, target: cfg.openNew ? '_blank' : null, rel: cfg.openNew ? 'noopener' : null, text: t.open || '' })
		]));
	}

	function renderError(href) {
		stopMedia();
		drawer.textContent = '';
		drawer.removeAttribute('aria-labelledby');
		drawer.appendChild(head(true));
		var box = h('div', { class: 'tt2-drawer__body' }, [h('p', { class: 'tt2-drawer__status', text: t.error || '' })]);
		if (href) { box.appendChild(h('a', { class: 'tt2-drawer__open', href: href, text: t.open || '' })); }
		drawer.appendChild(box);
	}

	function itemEls(id) {
		return document.querySelectorAll('[data-tainacan-item-id="' + String(id).replace(/[^0-9]/g, '') + '"]');
	}

	// The item link as the list built it: it carries the search position for the item page's previous/next.
	function mainLink(el) {
		if (el.matches('a[href]')) { return el; }
		var links = el.querySelectorAll('a[href]');
		for (var i = 0; i < links.length; i++) {
			if (!links[i].closest('[class*="faceted-search-hook"]')) { return links[i]; }
		}
		return null;
	}

	function itemLink(id) {
		var el = itemEls(id)[0];
		return el ? mainLink(el) : null;
	}

	function listHref(id) {
		var a = itemLink(id);
		return a ? a.href : '';
	}

	function markOpen(id) {
		document.querySelectorAll('.tt2-is-previewed').forEach(function (el) { el.classList.remove('tt2-is-previewed'); });
		itemEls(id).forEach(function (el) { el.classList.add('tt2-is-previewed'); });
	}

	// On a phone the drawer covers the page: it becomes modal, the rest inert.
	function setModal(on) {
		drawer.setAttribute('aria-modal', on ? 'true' : 'false');
		Array.prototype.forEach.call(document.body.children, function (el) {
			if (el === drawer || el === live || el === backdrop || el.tagName === 'SCRIPT') { return; }
			if (on) { el.setAttribute('inert', ''); el.setAttribute('data-tt2-inert', ''); }
			else if (el.hasAttribute('data-tt2-inert')) { el.removeAttribute('inert'); el.removeAttribute('data-tt2-inert'); }
		});
	}

	function resizeSoon() {
		// Masonry and records measure their columns: let them see the new width.
		// Over the list its width does not change.
		if (OVERLAY) { return; }
		setTimeout(function () { window.dispatchEvent(new Event('resize')); }, 260);
	}

	function open(id, opts) {
		if (!drawer) { build(); }
		opts = opts || {};
		state.id = String(id);
		if (opts.list && opts.list.length) { state.list = opts.list; }
		if (opts.trigger) { state.trigger = opts.trigger; }

		var wasHidden = drawer.hidden;
		drawer.hidden = false;
		document.body.classList.add('tt2-drawer-open');
		if (OVERLAY) { document.body.classList.add('tt2-drawer-over'); }
		if (backdrop) { backdrop.hidden = false; }
		setModal(OVERLAY || SMALL.matches);
		if (wasHidden) { resizeSoon(); }

		// Walking with the buttons keeps the focus on the button pressed; the
		// drawer holds it meanwhile, so it never falls to the page body.
		var keep = opts.keepFocus || '';
		if (keep) { drawer.focus({ preventScroll: true }); }

		stopMedia();
		drawer.textContent = '';
		drawer.appendChild(head(false));
		drawer.appendChild(h('p', { class: 'tt2-drawer__status', role: 'status', text: t.loading || '' }));
		markOpen(id);

		fetchItem(id).then(function (d) {
			if (state.id !== String(id)) { return; }
			render(d);
			var pos = state.list.indexOf(state.id);
			live.textContent = pos >= 0 && state.list.length > 1 ? d.title + '. ' + fmt(t.pos, [pos + 1, state.list.length]) : d.title;
			var target = keep ? drawer.querySelector('.' + keep) : null;
			if (target && target.getAttribute('aria-disabled') === 'true') { target = null; }
			(target || document.getElementById('tt2-drawer-title')).focus({ preventScroll: true });
			[pos - 1, pos + 1].forEach(function (k) {
				if (k >= 0 && k < state.list.length) { fetchItem(state.list[k]).catch(function () {}); }
			});
		}).catch(function (err) {
			if (state.id !== String(id)) { return; }
			var href = listHref(id);
			if (err && err.status === 404 && href) {
				// Not something the visitor may preview: open the page as before.
				close();
				window.location.href = href;
				return;
			}
			renderError(href);
		});
	}

	function step(dir) {
		var p = state.list.indexOf(state.id) + dir;
		if (p < 0 || p >= state.list.length) { return; }
		var link = itemLink(state.list[p]);
		if (link) { state.trigger = link; }
		open(state.list[p], { keepFocus: dir < 0 ? 'tt2-drawer__prev' : 'tt2-drawer__next' });
	}

	function close() {
		if (!drawer || drawer.hidden) { return; }
		stopMedia();
		drawer.hidden = true;
		if (backdrop) { backdrop.hidden = true; }
		setModal(false);
		document.body.classList.remove('tt2-drawer-open', 'tt2-drawer-over');
		document.querySelectorAll('.tt2-is-previewed').forEach(function (el) { el.classList.remove('tt2-is-previewed'); });
		resizeSoon();
		if (state.trigger && document.contains(state.trigger)) { state.trigger.focus({ preventScroll: true }); }
	}

	function pageList(from) {
		var root = (from && from.closest(LIST_ROOT)) || document;
		var ids = [];
		root.querySelectorAll('[data-tainacan-item-id]').forEach(function (el) {
			var v = el.getAttribute('data-tainacan-item-id');
			if (v && /^\d+$/.test(v) && ids.indexOf(v) < 0) { ids.push(v); }
		});
		return ids;
	}

	function otherDrawer() {
		return !!(window.tainacanBiblioPeek || (window.AEC && window.AEC.drawer));
	}

	document.addEventListener('click', function (e) {
		if (otherDrawer() || e.defaultPrevented) { return; }
		var target = e.target;
		var el = target.closest && target.closest('[data-tainacan-item-id]');
		if (!el || !el.closest(LIST_ROOT)) { return; }
		if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) { return; }
		var main = mainLink(el);
		var a = target.closest('a[href]');
		if (target.closest('button, [role="button"], .slideshow-icon')) { return; }
		if (a && el.contains(a)) {
			if (a.target === '_blank' || a.hasAttribute('download') || a.origin !== window.location.origin) { return; }
			// Only the item's own link previews; a term, author or other link in a card or a table cell
			// is followed, and so are controls such as the slideshow icon or same-page query links.
			if (!main || a.pathname !== main.pathname || a.closest('.slideshow-icon') || /^\?/.test(a.getAttribute('href') || '')) { return; }
		} else {
			// A click on the card or on the table row outside any link (cards, records, table, masonry...).
			if (target.closest('input, select, textarea, label, [contenteditable]')) { return; }
			if (window.getSelection && String(window.getSelection()).length > 0) { return; }
			a = main;
		}
		e.preventDefault();
		var trigger = a || el;
		if (!a && !el.hasAttribute('tabindex')) { el.setAttribute('tabindex', '-1'); }
		open(el.getAttribute('data-tainacan-item-id'), { list: pageList(el), trigger: trigger });
	}, true);

	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape' || !drawer || drawer.hidden || drawer.contains(e.target)) { return; }
		// Another dialog of the page (Tainacan modal, lightbox) closes first.
		if (document.querySelector('dialog[open], .modal.is-active, .pswp--open')) { return; }
		close();
	});

	SMALL.addEventListener('change', function () {
		if (drawer && !drawer.hidden) { setModal(OVERLAY || SMALL.matches); }
	});

	window.tt2Drawer.open = open;
	window.tt2Drawer.close = close;
}());
