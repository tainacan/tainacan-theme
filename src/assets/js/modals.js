/**
 * Tainacan Interface - Frontend Modals & Tooltips
 *
 * Handles the display of contextual help modals, tooltips,
 * and the "do not show again" persistence.
 *
 * @package Tainacan_Interface
 * @since 2.9.0
 */
(function () {
	'use strict';

	var STORAGE_KEY = 'tainacan_dismissed_modals';
	var helpData = {};
	var overlay, titleEl, contentEl, iconEl, dismissCheck;

	/**
	 * Get dismissed modals from localStorage
	 */
	function getDismissed() {
		try {
			return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
		} catch (e) {
			return {};
		}
	}

	/**
	 * Save dismissed modal
	 */
	function setDismissed(id) {
		var dismissed = getDismissed();
		dismissed[id] = true;
		try {
			localStorage.setItem(STORAGE_KEY, JSON.stringify(dismissed));
		} catch (e) {
			// localStorage not available
		}
	}

	/**
	 * Open modal with content
	 */
	function openModal(modalId) {
		if (!helpData[modalId] || !overlay) return;

		var dismissed = getDismissed();
		if (dismissed[modalId]) return;

		var item = helpData[modalId];
		titleEl.textContent = item.title || '';
		contentEl.textContent = item.content || '';

		if (item.icon) {
			iconEl.className = 'tainacan-modal__icon ' + item.icon;
		} else {
			iconEl.className = 'tainacan-modal__icon';
		}

		dismissCheck.checked = false;
		overlay.setAttribute('aria-hidden', 'false');
		overlay.style.display = 'flex';
		overlay.classList.add('tainacan-modal-overlay--active');

		// Focus trap
		var closeBtn = overlay.querySelector('.tainacan-modal__close');
		if (closeBtn) closeBtn.focus();

		// Prevent body scroll
		document.body.style.overflow = 'hidden';
	}

	/**
	 * Close modal
	 */
	function closeModal() {
		if (!overlay) return;

		var currentId = overlay.getAttribute('data-current-modal') || '';

		if (dismissCheck && dismissCheck.checked && currentId) {
			setDismissed(currentId);
		}

		overlay.setAttribute('aria-hidden', 'true');
		overlay.classList.remove('tainacan-modal-overlay--active');
		setTimeout(function () {
			overlay.style.display = 'none';
		}, 200);

		document.body.style.overflow = '';
	}

	/**
	 * Initialize
	 */
	function init() {
		overlay = document.getElementById('tainacan-modal-overlay');
		if (!overlay) return;

		titleEl = overlay.querySelector('.tainacan-modal__title');
		contentEl = overlay.querySelector('.tainacan-modal__content');
		iconEl = overlay.querySelector('.tainacan-modal__icon');
		dismissCheck = overlay.querySelector('.tainacan-modal__dismiss-check');

		// Load help data
		var dataEl = document.getElementById('tainacan-help-data');
		if (dataEl) {
			try {
				helpData = JSON.parse(dataEl.textContent);
			} catch (e) {
				helpData = {};
			}
		}

		// Click handlers for help buttons
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.tainacan-help-btn');
			if (btn) {
				var modalId = btn.getAttribute('data-modal-id');
				overlay.setAttribute('data-current-modal', modalId);
				openModal(modalId);
				return;
			}

			// Close button
			if (e.target.closest('.tainacan-modal__close') || e.target.closest('.tainacan-modal__ok-btn')) {
				closeModal();
				return;
			}

			// Click on overlay background
			if (e.target === overlay) {
				closeModal();
			}
		});

		// Keyboard: Escape to close
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && overlay.getAttribute('aria-hidden') === 'false') {
				closeModal();
			}
		});
	}

	// DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
