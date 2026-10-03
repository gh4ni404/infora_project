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
 *   // Bind action confirmation buttons or toggle checkboxes
 *   InforaModals.bindConfirmButtons('.btn-confirm-action', 'modalConfirm', { title: 'Konfirmasi', badge: 'Status' });
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

        const firstInput = modal.querySelector('input:not([type="hidden"]):not([disabled]), textarea:not([disabled]), select:not([disabled])');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 50);
        }
    }

    function close(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.add('hidden');
        getBody().classList.remove('modal-open');

        const state = registry.get(modalId);
        if (state && Array.isArray(state.onCloseCallbacks)) {
            state.onCloseCallbacks.forEach((cb) => {
                try {
                    cb(modal);
                } catch (err) {
                    console.error('Error in InforaModals onClose callback:', err);
                }
            });
        }
    }

    function closeAll() {
        document.querySelectorAll('.modal-backdrop:not(.hidden)').forEach((modal) => {
            close(modal.id);
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

        const existingState = registry.get(modalId);
        const state = {
            id: modalId,
            element: modal,
            closeBtnId: options.closeBtnId || `btnClose${toStudly(modalId)}`,
            cancelBtnId: options.cancelBtnId || null,
            editData: null,
            onCloseCallbacks: existingState?.onCloseCallbacks || [],
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
     * Register a callback to execute whenever a modal is closed (cancel, backdrop, or Escape).
     * @param {string} modalId
     * @param {function} callback — function(modalElement)
     */
    function onClose(modalId, callback) {
        if (typeof callback !== 'function') return;

        if (!registry.has(modalId)) {
            setup(modalId);
        }

        const state = registry.get(modalId);
        if (state) {
            state.onCloseCallbacks = state.onCloseCallbacks || [];
            state.onCloseCallbacks.push(callback);
        }
    }

    /**
     * Bind a button to open a modal.
     */
    function bindOpenButton(btnId, modalId) {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.addEventListener('click', () => {
                if (btn.disabled || btn.hasAttribute('disabled')) return;
                open(modalId);
            });
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
                let data = {};
                for (const key of Object.keys(this.dataset)) {
                    if (key === 'action') continue;
                    try {
                        const parsed = JSON.parse(this.dataset[key]);
                        if (parsed && typeof parsed === 'object') {
                            data = parsed;
                            break;
                        }
                    } catch (_) {}
                }

                // Fallback: jika tidak ada atribut bertipe JSON object, gunakan seluruh atribut dataset (selain action)
                if (Object.keys(data).length === 0) {
                    data = Object.assign({}, this.dataset);
                    delete data.action;
                }

                const actionUrl = this.dataset.action || '';

                if (formEdit && actionUrl) {
                    formEdit.action = actionUrl;
                }

                if (registry.has(modalId)) {
                    registry.get(modalId).editData = data;
                }

                populateFn(document.getElementById(modalId), data, formEdit, this);
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
                    if (callback(modal, this, formDelete) === false) {
                        return;
                    }
                }

                open(modalId);
            });
        });
    }

    /**
     * Bind multiple buttons or checkboxes to configure and open an action confirmation modal.
     * Supports both button 'click' and checkbox 'change'.
     * Automatically reverts checkbox state if modal is cancelled, closed via backdrop, or Escape key.
     *
     * @param {string} selector — CSS selector for trigger buttons or checkboxes
     * @param {string} modalId — ID of the confirm modal
     * @param {object|function} [optionsOrCallback] — Options object or custom callback function(modal, triggerEl, form)
     * @param {string} [optionsOrCallback.formId] — Custom form ID inside modal
     * @param {string} [optionsOrCallback.titleId] — Element ID for modal heading title (defaults to modalId + 'Title')
     * @param {string} [optionsOrCallback.messageId] — Element ID for confirmation message
     * @param {string} [optionsOrCallback.badgeId] — Element ID for badge
     * @param {string} [optionsOrCallback.itemTitleId] — Element ID for target item title
     * @param {string} [optionsOrCallback.hintId] — Element ID for hint text
     * @param {string|function} [optionsOrCallback.title] — Static title or callback(triggerEl)
     * @param {string|function} [optionsOrCallback.message] — Static message or callback(triggerEl)
     * @param {string|function} [optionsOrCallback.badge] — Static badge or callback(triggerEl)
     * @param {string|function} [optionsOrCallback.itemTitle] — Static item title or callback(triggerEl)
     * @param {string|function} [optionsOrCallback.hint] — Static hint or callback(triggerEl)
     * @param {function} [optionsOrCallback.onOpen] — Callback before opening: function(modal, triggerEl, form)
     */
    function bindConfirmButtons(selector, modalId, optionsOrCallback = {}) {
        const isCallback = typeof optionsOrCallback === 'function';
        const options = isCallback ? {} : optionsOrCallback;
        const callback = isCallback ? optionsOrCallback : options.onOpen;

        let activeTrigger = null;
        let activeTriggerPreviousChecked = false;

        // Auto-revert checkbox if modal is closed without submitting
        onClose(modalId, () => {
            if (activeTrigger && activeTrigger.type === 'checkbox') {
                activeTrigger.checked = activeTriggerPreviousChecked;
            }
            activeTrigger = null;
        });

        document.querySelectorAll(selector).forEach((el) => {
            const isCheckbox = el.matches('input[type="checkbox"]');
            const eventName = isCheckbox ? 'change' : 'click';

            el.addEventListener(eventName, function () {
                if (isCheckbox) {
                    const triggerOn = options.triggerOn || (options.mode === 'deactivate' ? 'uncheck' : 'check');
                    if (triggerOn === 'check' && !this.checked) return;
                    if (triggerOn === 'uncheck' && this.checked) return;
                }

                const modal = document.getElementById(modalId);
                if (!modal) return;

                activeTrigger = this;
                activeTriggerPreviousChecked = isCheckbox ? !this.checked : false;

                const form = options.formId
                    ? document.getElementById(options.formId)
                    : modal.querySelector('form');

                const actionUrl = this.dataset.action || '';
                if (form && actionUrl) {
                    form.action = actionUrl;
                }

                const resolveVal = (val) => {
                    if (typeof val === 'function') return val(this);
                    if (typeof val === 'string') return val;
                    return null;
                };

                const titleText = resolveVal(options.title) || this.dataset.confirmTitle || this.dataset.title;
                const messageText = resolveVal(options.message) || this.dataset.confirmMessage || this.dataset.message;
                const badgeText = resolveVal(options.badge) || this.dataset.confirmBadge || this.dataset.badge;
                const itemTitleText = resolveVal(options.itemTitle)
                    || this.dataset.confirmItemTitle
                    || this.dataset.tahun
                    || this.dataset.nama
                    || this.dataset.name
                    || this.dataset.label;
                const hintText = resolveVal(options.hint) || this.dataset.confirmHint || this.dataset.hint;

                const titleEl = options.titleId
                    ? document.getElementById(options.titleId)
                    : document.getElementById(`${modalId}Title`);
                const messageEl = options.messageId ? document.getElementById(options.messageId) : null;
                const badgeEl = options.badgeId ? document.getElementById(options.badgeId) : null;
                const itemTitleEl = options.itemTitleId ? document.getElementById(options.itemTitleId) : null;
                const hintEl = options.hintId ? document.getElementById(options.hintId) : null;

                if (titleEl && titleText !== null && titleText !== undefined) titleEl.textContent = titleText;
                if (messageEl && messageText !== null && messageText !== undefined) messageEl.textContent = messageText;
                if (badgeEl && badgeText !== null && badgeText !== undefined) badgeEl.textContent = badgeText;
                if (itemTitleEl && itemTitleText !== null && itemTitleText !== undefined) itemTitleEl.textContent = itemTitleText;
                if (hintEl && hintText !== null && hintText !== undefined) hintEl.textContent = hintText;

                const confirmBtnText = resolveVal(options.confirmText) || this.dataset.confirmText;
                const confirmBtnClass = resolveVal(options.confirmBtnClass) || this.dataset.confirmBtnClass;
                const confirmBtn = options.confirmBtnId
                    ? document.getElementById(options.confirmBtnId)
                    : modal.querySelector('button[type="submit"]');

                if (confirmBtn && confirmBtnText) {
                    confirmBtn.textContent = confirmBtnText;
                }
                if (confirmBtn && confirmBtnClass) {
                    confirmBtn.className = confirmBtnClass;
                }

                if (typeof callback === 'function') {
                    if (callback(modal, this, form) === false) {
                        if (isCheckbox) {
                            this.checked = activeTriggerPreviousChecked;
                        }
                        activeTrigger = null;
                        return;
                    }
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
        onClose,
        bindOpenButton,
        bindEditButtons,
        bindDeleteButtons,
        bindConfirmButtons,
        reopenOnError,
    };
})();
