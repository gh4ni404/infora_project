/**
 * INFORA Modal Manager
 *
 * Reusable modal open/close controller that eliminates duplicated
 * modal JS boilerplate across every index page.
 *
 * Usage:
 *   InforaModals.init()               — auto-init all modals with [data-modal]
 *   InforaModals.open('modalId')      — open a modal by ID
 *   InforaModals.close('modalId')     — close a modal by ID
 *   InforaModals.closeAll()           — close all open modals
 *   InforaModals.getModal('modalId')  — get a modal instance
 *
 *   // Register edit-data population callback
 *   InforaModals.bindEditButtons('.btn-open-edit', 'modalEdit', function(modal, data, form) { ... });
 *
 *   // Bind delete confirmation buttons
 *   InforaModals.bindDeleteButtons('.btn-open-delete', 'modalDelete', { titleId: 'deleteTitle' });
 *
 *   // Auto-reopen on validation errors
 *   InforaModals.reopenOnError({ create: 'modalCreateX', edit: 'modalEditX' });
 */

window.InforaModals = (function () {
    const registry = new Map();
    let escapeHandlerAttached = false;

    function getBody() {
        return document.body;
    }

    function open(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.remove('hidden');
        getBody().classList.add('modal-open');

        const firstInput = modal.querySelector('input:not([type="hidden"]), textarea, select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 50);
        }
    }

    function close(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.add('hidden');
        getBody().classList.remove('modal-open');
    }

    function closeAll() {
        document.querySelectorAll('.modal-backdrop:not(.hidden)').forEach((modal) => {
            modal.classList.add('hidden');
        });
        getBody().classList.remove('modal-open');
    }

    function isOpen(modalId) {
        const modal = document.getElementById(modalId);
        return modal && !modal.classList.contains('hidden');
    }

    function getOpenModals() {
        return Array.from(document.querySelectorAll('.modal-backdrop:not(.hidden)'));
    }

    /**
     * Setup a single modal with close button, backdrop click, and escape key handlers.
     * @param {string} modalId
     * @param {object} options
     * @param {string} [options.closeBtnId] — ID of the close button
     * @param {string} [options.cancelBtnId] — ID of the cancel button
     */
    function setup(modalId, options = {}) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const state = {
            id: modalId,
            element: modal,
            closeBtnId: options.closeBtnId || `btnClose${toStudly(modalId)}`,
            cancelBtnId: options.cancelBtnId || null,
            editData: null,
        };

        registry.set(modalId, state);

        const closeBtn = document.getElementById(state.closeBtnId);
        const cancelBtn = state.cancelBtnId ? document.getElementById(state.cancelBtnId) : null;

        if (closeBtn) {
            closeBtn.addEventListener('click', () => close(modalId));
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => close(modalId));
        }

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                close(modalId);
            }
        });

        attachEscapeHandler();
        return state;
    }

    /**
     * Bind a button to open a modal.
     */
    function bindOpenButton(btnId, modalId) {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.addEventListener('click', () => open(modalId));
        }
    }

    /**
     * Bind multiple edit buttons that populate a modal from JSON data-* attributes.
     * @param {string} selector — CSS selector for edit buttons
     * @param {string} modalId
     * @param {function} populateFn — function(modalElement, data, formEditElement)
     */
    function bindEditButtons(selector, modalId, populateFn) {
        const formEdit = document.querySelector(`#${modalId} form`);

        document.querySelectorAll(selector).forEach((btn) => {
            btn.addEventListener('click', function () {
                let data;
                try {
                    const rawKey = Object.keys(this.dataset).find((k) => k !== 'action');
                    data = JSON.parse(this.dataset[rawKey] || '{}');
                } catch (_) {
                    data = {};
                }

                const actionUrl = this.dataset.action || '';

                if (formEdit && actionUrl) {
                    formEdit.action = actionUrl;
                }

                if (registry.has(modalId)) {
                    registry.get(modalId).editData = data;
                }

                populateFn(document.getElementById(modalId), data, formEdit);
                open(modalId);
            });
        });
    }

    /**
     * Bind multiple delete buttons to configure and open a confirmation delete modal.
     * @param {string} selector — CSS selector for delete trigger buttons
     * @param {string} modalId — ID of the delete modal
     * @param {object|function} [optionsOrCallback] — Options object or custom callback function(modal, buttonElement, formDelete)
     * @param {string} [optionsOrCallback.titleId] — Element ID to update with the item title/name
     * @param {string} [optionsOrCallback.titleKey] — Dataset key to use for title (defaults to title, nama, name, or label)
     * @param {string} [optionsOrCallback.formId] — Custom form ID (defaults to form inside modal)
     * @param {function} [optionsOrCallback.onOpen] — Callback before opening: function(modal, buttonElement, formDelete)
     */
    function bindDeleteButtons(selector, modalId, optionsOrCallback = {}) {
        const isCallback = typeof optionsOrCallback === 'function';
        const options = isCallback ? {} : optionsOrCallback;
        const callback = isCallback ? optionsOrCallback : options.onOpen;

        document.querySelectorAll(selector).forEach((btn) => {
            btn.addEventListener('click', function () {
                const modal = document.getElementById(modalId);
                if (!modal) return;

                const formDelete = options.formId
                    ? document.getElementById(options.formId)
                    : modal.querySelector('form');

                const actionUrl = this.dataset.action || '';
                if (formDelete && actionUrl) {
                    formDelete.action = actionUrl;
                }

                const titleKey = options.titleKey || ['title', 'nama', 'name', 'label'].find((k) => this.dataset[k]);
                const itemTitle = titleKey ? this.dataset[titleKey] : '';

                const titleEl = options.titleId
                    ? document.getElementById(options.titleId)
                    : (modal.querySelector('#deleteItemTitle') || document.getElementById('deleteItemTitle'));

                if (titleEl && itemTitle) {
                    titleEl.textContent = itemTitle;
                }

                if (typeof callback === 'function') {
                    callback(modal, this, formDelete);
                }

                open(modalId);
            });
        });
    }

    /**
     * Auto-reopen modal on validation errors (for store/update failures).
     */
    function reopenOnError(modalIds) {
        if (typeof window.hasValidationErrors !== 'undefined' && window.hasValidationErrors) {
            if (window.errorMethod === 'PUT' || window.errorMethod === 'PATCH') {
                if (modalIds.edit) open(modalIds.edit);
            } else {
                if (modalIds.create) open(modalIds.create);
            }
        }
    }

    function attachEscapeHandler() {
        if (escapeHandlerAttached) return;
        escapeHandlerAttached = true;

        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;

            const iconPickerOpen = document.querySelector('.icon-picker-modal-backdrop:not(.hidden)');
            if (iconPickerOpen) return;

            const openModals = getOpenModals();
            if (openModals.length > 0) {
                const topModal = openModals[openModals.length - 1];
                close(topModal.id);
            }
        });
    }

    function toStudly(str) {
        return str
            .replace(/[-_]+(.)/g, (_, c) => c.toUpperCase())
            .replace(/^(.)/, (_, c) => c.toUpperCase());
    }

    return {
        init: setup,
        open,
        close,
        closeAll,
        isOpen,
        getModal: (id) => registry.get(id),
        setup,
        bindOpenButton,
        bindEditButtons,
        bindDeleteButtons,
        reopenOnError,
    };
})();
