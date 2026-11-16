/**
 * "Show more" for the collection and term descriptions.
 *
 * The original cut the description's HTML as a string and put the two halves
 * back with .html(): a cut inside a tag or an attribute re-parsed the text as
 * new markup. Here the DOM is cut instead, and nothing is parsed again: the
 * preview is a copy of the description with the visible text trimmed after the
 * limit. Only one of the two is in the page at a time, so ids, embeds and
 * players are never doubled.
 *
 * Same jQuery plugins, classes and texts as before, so the theme styles and
 * any child theme that calls them keep working.
 */
(function ($) {
	var HIDDEN = /^(SCRIPT|STYLE|TEMPLATE|NOSCRIPT)$/;

	// Text nodes a reader sees, in order.
	function visibleText(root) {
		return document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
			acceptNode: function (node) {
				for (var p = node.parentNode; p && p !== root; p = p.parentNode) {
					if (HIDDEN.test(p.nodeName)) { return NodeFilter.FILTER_REJECT; }
				}
				return NodeFilter.FILTER_ACCEPT;
			}
		});
	}

	function textLength(root) {
		var walker = visibleText(root);
		var length = 0;
		while (walker.nextNode()) { length += walker.currentNode.data.length; }
		return length;
	}

	// A copy of the children of el with at most max characters of visible text.
	function preview(el, max) {
		var copy = el.cloneNode(true);
		var walker = visibleText(copy);
		var used = 0;
		var node;
		while ((node = walker.nextNode())) {
			if (used + node.data.length > max) {
				node.data = node.data.slice(0, max - used);
				break;
			}
			used += node.data.length;
		}
		// Everything after the cut goes, at every level up to the copy.
		for (var n = node; n && n !== copy; n = n.parentNode) {
			while (n.nextSibling) { n.parentNode.removeChild(n.nextSibling); }
		}
		return copy;
	}

	// Where the "..." and the links go: inside the last paragraph, as the original put them.
	function tail(box) {
		var at = box;
		while (at.lastElementChild && /^(P|DIV|BLOCKQUOTE|H[1-6])$/.test(at.lastElementChild.tagName)) {
			at = at.lastElementChild;
		}
		return at;
	}

	function link(cls, text) {
		var a = document.createElement('a');
		a.href = '#';
		a.className = cls;
		a.textContent = '[ ' + text + ' ]';
		return a;
	}

	function truncate(elements, count, suffix, settings) {
		var config = $.extend(
			{
				showChars: count,
				minChars: count,
				ellipsesText: '...',
				moreText: tainacan_trucanteVars.moreText,
				lessText: tainacan_trucanteVars.lessText
			},
			settings || {}
		);

		elements.each(function () {
			var el = this;
			if (el.hasAttribute('data-tt-truncated') || textLength(el) <= config.minChars) {
				return;
			}
			el.setAttribute('data-tt-truncated', '');

			var short = document.createElement('span');
			var copy = preview(el, config.showChars);
			while (copy.firstChild) { short.appendChild(copy.firstChild); }
			var dots = document.createElement('span');
			dots.textContent = config.ellipsesText + ' ';
			var more = link('tainacan-interface-more' + suffix, config.moreText);
			tail(short).append(dots, more);

			// The original nodes leave the page while the preview is shown.
			var full = document.createElement('span');
			while (el.firstChild) { full.appendChild(el.firstChild); }
			var less = link('tainacan-interface-less' + suffix, config.lessText);
			tail(full).append(' ', less);

			el.appendChild(short);

			more.addEventListener('click', function (event) {
				event.preventDefault();
				el.classList.add('full-story');
				el.replaceChild(full, short);
			});
			less.addEventListener('click', function (event) {
				event.preventDefault();
				el.classList.remove('full-story');
				el.replaceChild(short, full);
			});
		});
		return elements;
	}

	$.fn.tainacan_interface_truncate = function (settings) {
		return truncate($(this), window.innerWidth <= 576 ? 150 : 350, '', settings);
	};

	$.fn.tainacan_interface_truncate_term = function (settings) {
		var w = window.innerWidth;
		var count = 1400;
		if (w <= 1860) { count = 890; }
		if (w <= 1365) { count = 580; }
		if (w <= 920) { count = 500; }
		if (w <= 768) { count = 280; }
		return truncate($(this), count, '-term', settings);
	};
})(jQuery);
