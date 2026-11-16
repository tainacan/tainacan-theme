/**
 * Document and image viewer on the item page.
 *
 * The server marks each slide of the Tainacan gallery with data-tt2-file
 * (the attachment id). A click on a marked slide opens a native <dialog>:
 * the browser gives it the top layer, the focus trap, the inert page behind
 * and Esc. Images zoom (buttons, wheel, double click, pinch) and pan; a swipe
 * or the arrow keys change file; video and audio play in the native player and
 * say so when the browser cannot play them; PDFs open in PDF.js, loaded only
 * now, pages drawn as they scroll into view and released when far away. The
 * phone's back button closes the viewer, and #tt2-file-<id> opens a file.
 * Every text goes in with textContent.
 */
(function () {
	'use strict';

	var cfg = window.tt2Viewer;
	if (!cfg || !cfg.files || typeof HTMLDialogElement === 'undefined') { return; }

	var t = cfg.i18n || {};
	var files = cfg.files;
	var HASH = /^#tt2-file-(\d+)$/;
	var dialog, stage, titleEl, captionEl, countEl, prevBtn, nextBtn, downloadA, originalA, zoomBar;
	var state = { seq: [], index: 0, opener: null, pushed: false, start: 0 };
	var view = null; // current view: { el, kind, destroy(), zoom?(f), reset?() }
	var pdfLib = null;
	var pressed = null;

	function has(id) { return Object.prototype.hasOwnProperty.call(files, String(id)); }

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

	function button(cls, label, glyph, onClick, base) {
		var b = h('button', { type: 'button', class: (base || 'tt2-viewer__btn') + ' ' + cls, 'aria-label': label, title: label }, [h('span', { 'aria-hidden': 'true', text: glyph })]);
		b.addEventListener('click', function () { if (b.getAttribute('aria-disabled') !== 'true') { onClick(); } });
		return b;
	}

	function build() {
		titleEl = h('p', { id: 'tt2-viewer-title', class: 'tt2-viewer__title' });
		countEl = h('span', { class: 'tt2-viewer__count', 'aria-live': 'polite' });
		downloadA = h('a', { class: 'tt2-viewer__link', download: true, text: t.download || '' });
		originalA = h('a', { class: 'tt2-viewer__link', target: '_blank', rel: 'noopener', text: t.original || '' });
		zoomBar = h('div', { class: 'tt2-viewer__zoom' }, [
			button('tt2-viewer__zoom-out', t.zoomOut || '', '−', function () { if (view && view.zoom) { view.zoom(1 / 1.5); } }),
			button('tt2-viewer__zoom-reset', t.zoomReset || '', '⤢', function () { if (view && view.reset) { view.reset(); } }),
			button('tt2-viewer__zoom-in', t.zoomIn || '', '+', function () { if (view && view.zoom) { view.zoom(1.5); } })
		]);
		prevBtn = button('tt2-viewer__prev', t.prev || '', '‹', function () { go(-1); });
		nextBtn = button('tt2-viewer__next', t.next || '', '›', function () { go(1); });
		stage = h('div', { class: 'tt2-viewer__stage' });
		captionEl = h('p', { class: 'tt2-viewer__caption' });

		dialog = h('dialog', { class: 'tt2-viewer', 'aria-labelledby': 'tt2-viewer-title' }, [
			h('div', { class: 'tt2-viewer__bar' }, [
				h('div', { class: 'tt2-viewer__meta' }, [titleEl, countEl]),
				zoomBar,
				h('div', { class: 'tt2-viewer__actions' }, [originalA, downloadA, button('tt2-viewer__close', t.close || '', '×', close)])
			]),
			h('div', { class: 'tt2-viewer__main' }, [prevBtn, stage, nextBtn]),
			captionEl
		]);
		document.body.appendChild(dialog);

		dialog.addEventListener('cancel', function (e) { e.preventDefault(); close(); });
		dialog.addEventListener('close', teardown);
		dialog.addEventListener('keydown', function (e) {
			if (/input|textarea|select/i.test(e.target.tagName)) { return; }
			if (e.key === 'ArrowLeft' && !(view && view.kind === 'pdf' && view.el.contains(e.target))) { e.preventDefault(); go(-1); }
			else if (e.key === 'ArrowRight' && !(view && view.kind === 'pdf' && view.el.contains(e.target))) { e.preventDefault(); go(1); }
			else if ((e.key === '+' || e.key === '=') && view && view.zoom) { e.preventDefault(); view.zoom(1.5); }
			else if (e.key === '-' && view && view.zoom) { e.preventDefault(); view.zoom(1 / 1.5); }
		});
		// A click on the backdrop (outside the content) closes.
		dialog.addEventListener('click', function (e) { if (e.target === dialog) { close(); } });
	}

	/* ---------- image ---------- */

	function imageView(file) {
		var img = h('img', { class: 'tt2-viewer__img', src: file.src, alt: file.title || '', draggable: 'false', decoding: 'async' });
		if (file.srcset) { img.setAttribute('srcset', file.srcset); img.setAttribute('sizes', '100vw'); }
		var box = h('div', { class: 'tt2-viewer__image', tabindex: '0' }, [img]);
		var z = { s: 1, x: 0, y: 0 };
		var pointers = {};
		var gesture = null;

		function apply() {
			img.style.transform = 'translate(' + z.x + 'px,' + z.y + 'px) scale(' + z.s + ')';
			box.classList.toggle('is-zoomed', z.s > 1.01);
		}
		function clamp() {
			var rx = Math.max(0, (img.offsetWidth * z.s - box.clientWidth) / 2);
			var ry = Math.max(0, (img.offsetHeight * z.s - box.clientHeight) / 2);
			z.x = Math.min(rx, Math.max(-rx, z.x));
			z.y = Math.min(ry, Math.max(-ry, z.y));
		}
		function zoom(f, cx, cy) {
			var s = Math.min(8, Math.max(1, z.s * f));
			var r = box.getBoundingClientRect();
			var ox = (cx == null ? r.width / 2 : cx - r.left) - r.width / 2;
			var oy = (cy == null ? r.height / 2 : cy - r.top) - r.height / 2;
			// Keep the point under the cursor in place.
			z.x = ox - (ox - z.x) * (s / z.s);
			z.y = oy - (oy - z.y) * (s / z.s);
			z.s = s;
			if (s === 1) { z.x = 0; z.y = 0; }
			clamp();
			apply();
		}
		function reset() { z.s = 1; z.x = 0; z.y = 0; apply(); }

		box.addEventListener('wheel', function (e) { e.preventDefault(); zoom(e.deltaY < 0 ? 1.2 : 1 / 1.2, e.clientX, e.clientY); }, { passive: false });
		box.addEventListener('dblclick', function (e) { if (z.s > 1.01) { reset(); } else { zoom(2.5, e.clientX, e.clientY); } });
		box.addEventListener('pointerdown', function (e) {
			box.setPointerCapture(e.pointerId);
			pointers[e.pointerId] = { x: e.clientX, y: e.clientY };
			var ids = Object.keys(pointers);
			if (ids.length === 2) {
				var a = pointers[ids[0]], b = pointers[ids[1]];
				gesture = { type: 'pinch', d: Math.hypot(a.x - b.x, a.y - b.y), s: z.s };
			} else {
				gesture = { type: z.s > 1.01 ? 'pan' : 'swipe', x0: e.clientX, y0: e.clientY, x: z.x, y: z.y };
			}
		});
		box.addEventListener('pointermove', function (e) {
			if (!pointers[e.pointerId] || !gesture) { return; }
			pointers[e.pointerId] = { x: e.clientX, y: e.clientY };
			if (gesture.type === 'pinch') {
				var ids = Object.keys(pointers);
				if (ids.length < 2) { return; }
				var a = pointers[ids[0]], b = pointers[ids[1]];
				var d = Math.hypot(a.x - b.x, a.y - b.y);
				zoom((gesture.s * d / gesture.d) / z.s, (a.x + b.x) / 2, (a.y + b.y) / 2);
			} else if (gesture.type === 'pan') {
				z.x = gesture.x + e.clientX - gesture.x0;
				z.y = gesture.y + e.clientY - gesture.y0;
				clamp();
				apply();
			}
		});
		function up(e) {
			if (gesture && gesture.type === 'swipe' && pointers[e.pointerId]) {
				var dx = e.clientX - gesture.x0, dy = e.clientY - gesture.y0;
				if (Math.abs(dx) > 60 && Math.abs(dx) > Math.abs(dy) * 1.5) { go(dx < 0 ? 1 : -1); }
			}
			delete pointers[e.pointerId];
			gesture = null;
		}
		box.addEventListener('pointerup', up);
		box.addEventListener('pointercancel', up);
		box.addEventListener('keydown', function (e) {
			var step = 60;
			if (z.s <= 1.01) { return; }
			if (e.key === 'ArrowUp') { z.y += step; } else if (e.key === 'ArrowDown') { z.y -= step; } else { return; }
			e.preventDefault(); clamp(); apply();
		});

		return { el: box, kind: 'image', zoom: function (f) { zoom(f); }, reset: reset, destroy: function () {} };
	}

	/* ---------- video / audio ---------- */

	function mediaView(file) {
		var tag = file.kind === 'audio' ? 'audio' : 'video';
		var media = h(tag, { class: 'tt2-viewer__media', src: file.url, controls: true, preload: 'metadata', playsinline: true });
		var box = h('div', { class: 'tt2-viewer__player tt2-viewer__player--' + tag }, [media]);
		media.addEventListener('error', function () {
			box.textContent = '';
			box.appendChild(h('p', { class: 'tt2-viewer__notice', text: t.unplayable || '' }));
			box.appendChild(h('a', { class: 'tt2-viewer__link tt2-viewer__link--solid', href: file.url, download: true, text: t.download || '' }));
		});
		return { el: box, kind: tag, destroy: function () { try { media.pause(); } catch (e) { /* already gone */ } } };
	}

	/* ---------- PDF ---------- */

	function loadPdf() {
		if (!pdfLib) {
			pdfLib = import(cfg.pdf.lib).then(function (mod) {
				mod.GlobalWorkerOptions.workerSrc = cfg.pdf.worker;
				return mod;
			});
			pdfLib.catch(function () { pdfLib = null; });
		}
		return pdfLib;
	}

	// One PDF.js document per file, shared by the page reader and the dialog.
	var docs = {};

	function acquire(file) {
		var d = docs[file.url];
		if (!d) {
			d = docs[file.url] = { n: 0, task: null };
			d.promise = loadPdf().then(function (lib) {
				d.task = lib.getDocument({ url: file.url, standardFontDataUrl: cfg.pdf.fonts, isEvalSupported: false });
				return d.task.promise;
			});
			d.promise.catch(function () { if (docs[file.url] === d) { delete docs[file.url]; } });
		}
		d.n++;
		return d;
	}

	function letGo(file, d) {
		d.n--;
		if (d.n > 0) { return; }
		if (docs[file.url] === d) { delete docs[file.url]; }
		if (d.task) { d.task.destroy(); }
	}

	/*
	 * The PDF reader, shared by the dialog and the reader embedded in the page.
	 * Pages are sized to the width of the box (fit width) and refit when the
	 * box changes width; each page is drawn when it comes near the view and
	 * gives its canvas back when it goes far away.
	 */
	function pdfReader(file, opts) {
		opts = opts || {};
		var box = h('div', { class: 'tt2-pdf', tabindex: '0' }, [h('p', { class: 'tt2-viewer__notice', role: 'status', text: t.loading || '' })]);
		var held = acquire(file);
		var doc = null, io = null, ro = null, pages = [], scale = 1, base = 1, gone = false, current = 0, width = 0, tick = null;
		var DPR = Math.min(window.devicePixelRatio || 1, 2);

		function fail() {
			if (gone) { return; }
			box.textContent = '';
			box.appendChild(h('p', { class: 'tt2-viewer__notice', text: t.unreadable || '' }));
			box.appendChild(h('a', { class: 'tt2-viewer__link tt2-viewer__link--solid', href: file.url, target: '_blank', rel: 'noopener', text: t.original || '' }));
		}

		function size(p) {
			p.box.style.width = Math.floor(p.w * scale * base) + 'px';
			p.box.style.height = Math.floor(p.h * scale * base) + 'px';
		}

		function draw(p) {
			if (p.canvas || p.busy || gone) { return; }
			p.busy = true;
			doc.getPage(p.n).then(function (page) {
				if (gone) { return; }
				var own = page.getViewport({ scale: 1 });
				if (own.width !== p.w || own.height !== p.h) { p.w = own.width; p.h = own.height; size(p); }
				var vp = page.getViewport({ scale: scale * base * DPR });
				var canvas = h('canvas', { class: 'tt2-viewer__canvas' });
				canvas.width = Math.floor(vp.width);
				canvas.height = Math.floor(vp.height);
				p.render = page.render({ canvasContext: canvas.getContext('2d'), viewport: vp });
				return p.render.promise.then(function () {
					p.busy = false;
					if (gone || !p.visible) { canvas.width = 0; return; }
					p.box.textContent = '';
					p.box.appendChild(canvas);
					p.canvas = canvas;
				});
			}).catch(function () { p.busy = false; });
		}

		// Far pages give their canvas back: a long PDF never holds hundreds of bitmaps.
		function release(p) {
			if (p.render && p.busy) { try { p.render.cancel(); } catch (e) { /* finished */ } }
			p.busy = false;
			if (p.canvas) { p.canvas.width = 0; p.canvas.remove(); p.canvas = null; }
		}

		function layout() {
			var at = current;
			pages.forEach(function (p) { size(p); release(p); });
			pages.forEach(function (p) { if (p.visible) { draw(p); } });
			if (at > 1) { goPage(at); }
		}

		// Fit width. The box reserves the scrollbar gutter (CSS), so its inner width
		// does not change when the pages make it scroll.
		function fit() {
			var cs = window.getComputedStyle(box);
			width = box.clientWidth;
			base = Math.max(0.2, (width - parseFloat(cs.paddingLeft) - parseFloat(cs.paddingRight) - 2) / pages[0].w0);
		}

		function track() {
			tick = null;
			var n = 1, scrolls = box.scrollHeight > box.clientHeight + 2;
			if (scrolls && box.scrollTop + box.clientHeight >= box.scrollHeight - 2) {
				n = pages.length; // at the bottom: the last page, however short
			} else {
				var line = box.scrollTop + box.clientHeight / 3;
				for (var i = 0; i < pages.length; i++) { if (pages[i].box.offsetTop <= line) { n = i + 1; } else { break; } }
			}
			current = n;
			if (opts.onPage) { opts.onPage(n, pages.length, scrolls); }
		}

		function goPage(n) {
			var p = pages[Math.min(pages.length, Math.max(1, n)) - 1];
			if (p) { box.scrollTop = p.box.offsetTop - 8; }
		}

		held.promise.then(function (d) {
			doc = d;
			return d.getPage(1);
		}).then(function (first) {
			if (gone) { return; }
			var vp = first.getViewport({ scale: 1 });
			box.textContent = '';
			for (var n = 1; n <= doc.numPages; n++) {
				// Pages are sized like the first until drawn; mixed sizes adjust on draw.
				var pb = h('div', { class: 'tt2-viewer__pdf-page', 'data-page': String(n) });
				pages.push({ n: n, w: vp.width, h: vp.height, w0: vp.width, box: pb, canvas: null, busy: false, visible: false });
				box.appendChild(pb);
			}
			fit();
			layout();
			io = new IntersectionObserver(function (entries) {
				entries.forEach(function (en) {
					var p = pages[Number(en.target.getAttribute('data-page')) - 1];
					p.visible = en.isIntersecting;
					if (en.isIntersecting) { draw(p); } else { release(p); }
				});
			}, { root: box, rootMargin: '600px 0px' });
			pages.forEach(function (p) { io.observe(p.box); });
			box.addEventListener('scroll', function () { if (!tick) { tick = window.requestAnimationFrame(track); } }, { passive: true });
			if (typeof ResizeObserver !== 'undefined') {
				var wait = null;
				ro = new ResizeObserver(function () {
					if (Math.abs(box.clientWidth - width) < 4) { return; }
					clearTimeout(wait);
					wait = setTimeout(function () { if (!gone) { fit(); layout(); track(); } }, 150);
				});
				ro.observe(box);
			}
			if (opts.start > 1) { goPage(opts.start); }
			track();
			if (opts.onReady) { opts.onReady(pages.length); }
		}).catch(fail);

		return {
			el: box,
			kind: 'pdf',
			zoom: function (f) {
				if (!doc) { return; }
				scale = Math.min(4, Math.max(0.5, scale * f));
				layout();
			},
			reset: function () { if (doc) { scale = 1; layout(); } },
			goPage: goPage,
			page: function () { return current; },
			destroy: function () {
				if (gone) { return; }
				gone = true;
				if (io) { io.disconnect(); }
				if (ro) { ro.disconnect(); }
				pages.forEach(release);
				letGo(file, held);
			}
		};
	}

	// In the dialog: the reader plus the floating page label.
	function pdfView(file, start) {
		var label = h('span', { class: 'tt2-viewer__page', 'aria-live': 'polite' });
		var reader = pdfReader(file, { start: start, onPage: function (n, total) { label.textContent = fmt(t.page, [n, total]); } });
		reader.el.classList.add('tt2-viewer__pdf');
		var wrap = h('div', { class: 'tt2-viewer__pdf-wrap' }, [reader.el, label]);
		return { el: wrap, kind: 'pdf', zoom: reader.zoom, reset: reader.reset, destroy: reader.destroy };
	}

	/* ---------- PDF embedded in the page ---------- */

	// Downloads without an <a href> in the slide: Tainacan takes the first link of a
	// slide as its lightbox trigger and rewrites it.
	function download(file) {
		var a = h('a', { href: file.url, download: file.filename || '' });
		document.body.appendChild(a);
		a.click();
		a.remove();
	}

	function mountReader(shell) {
		var id = shell.getAttribute('data-tt2-reader');
		var file = files[id];
		if (!file || file.kind !== 'pdf') { return; }

		// The copy Tainacan prints for its lightbox stays a plain link.
		if (shell.closest('.media-full-content')) {
			shell.textContent = '';
			shell.appendChild(h('a', { href: file.url, target: '_blank', rel: 'noopener', text: t.original || '' }));
			return;
		}

		// Full screen is the viewer: only where the viewer opens (a marked slide, or a document outside a gallery).
		var slide = shell.closest('[data-tt2-file]');
		var viewer = !!slide || !shell.closest('.tainacan-media-component');
		var reader = null, editing = false;
		var pageIn = h('input', { class: 'tt2-reader__page', type: 'number', min: '1', value: '1', inputmode: 'numeric', 'aria-label': t.pageNumber || '' });
		var total = h('span', { class: 'tt2-reader__total', text: '/ …' });
		var prev = button('tt2-reader__prev', t.prevPage || '', '‹', function () { if (reader) { reader.goPage(reader.page() - 1); } }, 'tt2-reader__btn');
		var next = button('tt2-reader__next', t.nextPage || '', '›', function () { if (reader) { reader.goPage(reader.page() + 1); } }, 'tt2-reader__btn');
		var tail = [button('tt2-reader__download', t.download || '', '⤓', function () { download(file); }, 'tt2-reader__btn')];
		if (viewer) {
			tail.push(button('tt2-reader__full', t.fullscreen || '', '⛶', function () {
				open(id, slide ? sequenceFrom(slide.closest('.tainacan-media-component')) : null, tail[1], reader ? reader.page() : 1);
			}, 'tt2-reader__btn'));
		}
		var bar = h('div', { class: 'tt2-reader__bar', role: 'toolbar', 'aria-label': t.reader || '' }, [
			h('div', { class: 'tt2-reader__group' }, [prev, pageIn, total, next]),
			h('div', { class: 'tt2-reader__group' }, [
				button('tt2-reader__zoom-out', t.zoomOut || '', '−', function () { if (reader) { reader.zoom(1 / 1.25); } }, 'tt2-reader__btn'),
				button('tt2-reader__fit', t.fitWidth || '', '↔', function () { if (reader) { reader.reset(); } }, 'tt2-reader__btn'),
				button('tt2-reader__zoom-in', t.zoomIn || '', '+', function () { if (reader) { reader.zoom(1.25); } }, 'tt2-reader__btn')
			]),
			h('div', { class: 'tt2-reader__group' }, tail)
		]);
		var body = h('div', { class: 'tt2-reader__body' });
		shell.textContent = '';
		shell.appendChild(bar);
		shell.appendChild(body);
		shell.classList.add('is-mounted');

		// The slide's lightbox and carousel must not see what happens in the reader.
		['click', 'keydown', 'pointerdown'].forEach(function (type) {
			shell.addEventListener(type, function (e) { e.stopPropagation(); });
		});

		pageIn.addEventListener('input', function () { editing = true; });
		pageIn.addEventListener('blur', function () { editing = false; if (reader) { pageIn.value = String(reader.page() || 1); } });
		pageIn.addEventListener('change', function () {
			editing = false;
			if (!reader) { return; }
			var n = Math.min(Number(pageIn.max) || 1, Math.max(1, parseInt(pageIn.value, 10) || 1));
			pageIn.value = String(n);
			reader.goPage(n);
		});

		function start() {
			reader = pdfReader(file, {
				onReady: function (n) { total.textContent = '/ ' + n; pageIn.max = String(n); },
				onPage: function (n, count, scrolls) {
					if (!editing) { pageIn.value = String(n); }
					prev.setAttribute('aria-disabled', !scrolls || n <= 1 ? 'true' : 'false');
					next.setAttribute('aria-disabled', !scrolls || n >= count ? 'true' : 'false');
				}
			});
			body.appendChild(reader.el);
		}

		// The PDF is fetched only when the reader comes near the view.
		if (typeof IntersectionObserver === 'undefined') { start(); return; }
		var seen = new IntersectionObserver(function (entries) {
			if (entries.some(function (en) { return en.isIntersecting; })) { seen.disconnect(); start(); }
		}, { rootMargin: '300px 0px' });
		seen.observe(shell);
	}

	/* ---------- dialog ---------- */

	function viewFor(file, start) {
		if (file.kind === 'image') { return imageView(file); }
		if (file.kind === 'video' || file.kind === 'audio') { return mediaView(file); }
		return pdfView(file, start);
	}

	function teardown() {
		if (view) { view.destroy(); view = null; }
		stage.textContent = '';
		document.documentElement.classList.remove('tt2-viewer-open');
		if (state.opener && document.contains(state.opener)) { state.opener.focus({ preventScroll: true }); }
	}

	function show() {
		var id = state.seq[state.index];
		var file = files[id];
		if (view) { view.destroy(); }
		stage.textContent = '';
		view = viewFor(file, state.start);
		state.start = 0; // only the file it was opened on starts at a page
		stage.appendChild(view.el);

		titleEl.textContent = file.title || file.filename;
		captionEl.textContent = file.caption || '';
		captionEl.hidden = !file.caption;
		var many = state.seq.length > 1;
		countEl.textContent = many ? fmt(t.count, [state.index + 1, state.seq.length]) : '';
		prevBtn.hidden = nextBtn.hidden = !many;
		prevBtn.setAttribute('aria-disabled', state.index === 0 ? 'true' : 'false');
		nextBtn.setAttribute('aria-disabled', state.index === state.seq.length - 1 ? 'true' : 'false');
		zoomBar.hidden = !view.zoom;
		downloadA.href = file.url;
		downloadA.setAttribute('download', file.filename || '');
		originalA.href = file.url;
		originalA.hidden = file.kind !== 'pdf';

		// Neighbours' images arrive before they are asked for.
		[state.index - 1, state.index + 1].forEach(function (k) {
			var n = files[state.seq[k]];
			if (n && n.kind === 'image') { var pre = new Image(); pre.decoding = 'async'; pre.src = n.src; }
		});

		var hash = '#tt2-file-' + id;
		if (state.pushed && window.location.hash !== hash) { history.replaceState({ tt2Viewer: id }, '', hash); }
	}

	function open(id, seq, opener, page) {
		if (!has(id)) { return; }
		if (!dialog) { build(); }
		if (dialog.open) { return; }
		state.seq = seq && seq.indexOf(String(id)) >= 0 ? seq : [String(id)];
		state.index = state.seq.indexOf(String(id));
		state.opener = opener || null;
		state.start = page || 0;
		document.documentElement.classList.add('tt2-viewer-open');
		dialog.showModal();
		if (!state.pushed) {
			history.pushState({ tt2Viewer: id }, '', '#tt2-file-' + id);
			state.pushed = true;
		}
		show();
		dialog.querySelector('.tt2-viewer__close').focus({ preventScroll: true });
	}

	function go(dir) {
		var k = state.index + dir;
		if (!dialog || !dialog.open || k < 0 || k >= state.seq.length) { return; }
		state.index = k;
		show();
	}

	function close() {
		if (!dialog || !dialog.open) { return; }
		if (state.pushed && history.state && history.state.tt2Viewer) {
			history.back(); // popstate closes
			return;
		}
		state.pushed = false;
		dialog.close();
	}

	window.addEventListener('popstate', function () {
		if (dialog && dialog.open) {
			state.pushed = false;
			dialog.close();
		}
	});

	/* ---------- gallery ---------- */

	// The gallery's own order: main carousel when there is one, else the thumbnails.
	function sequenceFrom(component) {
		var main = component.querySelector('.tainacan-media-component__swiper-main');
		var scope = main && main.querySelector('[data-tt2-file]') ? main : component;
		var seq = [];
		scope.querySelectorAll('[data-tt2-file]').forEach(function (el) {
			var v = el.getAttribute('data-tt2-file');
			if (has(v) && seq.indexOf(v) < 0) { seq.push(v); }
		});
		return seq;
	}

	document.addEventListener('pointerdown', function (e) { pressed = { x: e.clientX, y: e.clientY, id: e.pointerId }; }, true);
	document.addEventListener('pointercancel', function () { pressed = null; }, true);

	// Capture phase on document: runs before the Tainacan lightbox listener and
	// before the stopPropagation() Tainacan puts on its own controls.
	document.addEventListener('click', function (e) {
		var target = e.target;
		if (!target.closest || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return; }
		// The embedded reader has its own controls, the full-screen one included.
		if (target.closest('.tt2-reader')) { return; }
		var opener = target.closest('.tt2-viewer-open');
		var slide = target.closest('.tainacan-media-component [data-tt2-file]');
		if (!slide) { return; }
		var component = slide.closest('.tainacan-media-component');
		if (!opener) {
			// A thumbnail under a main carousel only selects the slide.
			if (slide.closest('.tainacan-media-component__swiper-thumbs') && component.querySelector('.tainacan-media-component__swiper-main')) { return; }
			// Carousel controls, download links, captions with links and inline players keep their behaviour.
			if (target.closest('.swiper-button-prev, .swiper-button-next, .swiper-pagination, .tainacan-item-file-download, .swiper-slide-metadata a, .tainacan-media-item-actions a, video, audio')) { return; }
			// A drag of the carousel is not a click (keyboard clicks have detail 0 and no pointer).
			if (e.detail !== 0 && pressed && Math.abs(e.clientX - pressed.x) + Math.abs(e.clientY - pressed.y) > 8) { return; }
		}
		e.preventDefault();
		e.stopPropagation();
		var focusable = opener || target.closest('a, button, [tabindex]') || slide;
		open(slide.getAttribute('data-tt2-file'), sequenceFrom(component), focusable);
	}, true);

	// Enter on a focused slide opens it too.
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Enter' || !e.target.matches || !e.target.matches('.tainacan-media-component [data-tt2-file]')) { return; }
		e.preventDefault();
		open(e.target.getAttribute('data-tt2-file'), sequenceFrom(e.target.closest('.tainacan-media-component')), e.target);
	});

	document.querySelectorAll('[data-tt2-reader]').forEach(mountReader);

	document.querySelectorAll('.tainacan-media-component [data-tt2-file]').forEach(function (el) {
		var id = el.getAttribute('data-tt2-file');
		if (!has(id) || el.querySelector('.tt2-reader')) { return; }
		el.classList.add('tt2-viewer-target');
		if (!el.hasAttribute('tabindex') && !el.querySelector('a, button, iframe, video, audio')) { el.setAttribute('tabindex', '0'); }
		// A PDF in an iframe and an inline player take the click for themselves, and a
		// click inside an iframe never reaches this page: they get a button of their own.
		if (files[id].kind !== 'image' && !el.closest('.tainacan-media-component__swiper-thumbs')) {
			var b = h('button', { type: 'button', class: 'tt2-viewer-open' }, [
				h('span', { 'aria-hidden': 'true', text: '⤢ ' }),
				h('span', { text: t.openViewer || '' })
			]);
			var box = el.querySelector('.tainacan-media-item-content, .swiper-slide-content') || el;
			box.appendChild(b);
		}
	});

	// Deep link: #tt2-file-<id> opens that file.
	var m = HASH.exec(window.location.hash);
	if (m && has(m[1])) {
		var host = document.querySelector('.tainacan-media-component [data-tt2-file="' + m[1] + '"]');
		history.replaceState(null, '', window.location.pathname + window.location.search);
		open(m[1], host ? sequenceFrom(host.closest('.tainacan-media-component')) : null, host);
	}
}());
