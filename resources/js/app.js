/**
 * INFORA - Main Application Client Scripts
 */

import './infora-progress';

document.addEventListener('DOMContentLoaded', () => {
    // Password visibility toggle
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePasswordBtn && passwordInput) {
        const eyeIcon = togglePasswordBtn.querySelector('.icon-eye');
        const eyeOffIcon = togglePasswordBtn.querySelector('.icon-eye-off');

        togglePasswordBtn.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

            if (eyeIcon && eyeOffIcon) {
                eyeIcon.classList.toggle('hidden', isPassword);
                eyeOffIcon.classList.toggle('hidden', !isPassword);
            }

            const newTitle = isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi';
            togglePasswordBtn.setAttribute('title', newTitle);
            togglePasswordBtn.setAttribute('aria-label', newTitle);

            passwordInput.focus();
        });
    }

    // Login form submission loading indicator & anti-spam click
    const loginForm = document.querySelector('form[action*="login"]');
    const btnLogin = document.getElementById('btnLogin');

    if (loginForm && btnLogin) {
        loginForm.addEventListener('submit', (e) => {
            // Respect HTML5 form validation (required, etc.)
            if (loginForm.checkValidity && !loginForm.checkValidity()) {
                return;
            }

            // Prevent spam clicks if already submitted
            if (btnLogin.classList.contains('btn-loading')) {
                e.preventDefault();
                return;
            }

            const spinner = btnLogin.querySelector('.spinner-icon');
            const btnText = btnLogin.querySelector('.btn-text');

            if (spinner) {
                spinner.classList.remove('hidden');
            }
            if (btnText) {
                btnText.textContent = 'Memproses...';
            }

            btnLogin.classList.add('btn-loading');

            // Asynchronously set disabled attribute so browser native form submission is not cancelled
            setTimeout(() => {
                btnLogin.disabled = true;
            }, 0);
        });

        // Reset button state if user navigates back via bfcache
        window.addEventListener('pageshow', () => {
            btnLogin.classList.remove('btn-loading');
            btnLogin.disabled = false;
            const spinner = btnLogin.querySelector('.spinner-icon');
            const btnText = btnLogin.querySelector('.btn-text');
            if (spinner) {
                spinner.classList.add('hidden');
            }
            if (btnText) {
                btnText.textContent = 'Masuk ke Dashboard';
            }
        });
    }

    // Sidebar menu real-time search & accordion interaction
    const menuSearchInput = document.getElementById('sidebarMenuSearch');
    const menuSearchClear = document.getElementById('sidebarSearchClear');
    const sidebarNavMenu = document.getElementById('sidebarNavMenu');
    const menuSearchEmpty = document.getElementById('menuSearchEmpty');
    const sidebarToggleBtn = document.getElementById('sidebarToggle');
    const layoutSidebar = document.getElementById('layoutSidebar');

    if (sidebarNavMenu) {
        // Nav group accordion toggling
        const navGroupTriggers = sidebarNavMenu.querySelectorAll('.nav-group-trigger');
        navGroupTriggers.forEach((trigger) => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const group = trigger.closest('.nav-group-item');
                if (!group) {
                    return;
                }

                // If sidebar is collapsed, expand it first so submenus are accessible
                if (layoutSidebar && layoutSidebar.classList.contains('is-collapsed')) {
                    layoutSidebar.classList.remove('is-collapsed');
                    if (sidebarToggleBtn) {
                        sidebarToggleBtn.classList.remove('active');
                    }
                    localStorage.setItem('infora_sidebar_collapsed', 'false');
                    group.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');

                    return;
                }

                const isOpen = group.classList.toggle('is-open');
                trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });

        // Search filtering logic
        if (menuSearchInput) {
            const categoryLabels = sidebarNavMenu.querySelectorAll('.menu-category-label');

            const filterMenu = () => {
                const query = menuSearchInput.value.trim().toLowerCase();
                let totalMatches = 0;

                if (menuSearchClear) {
                    menuSearchClear.classList.toggle('hidden', query.length === 0);
                }

                if (query === '') {
                    // Reset to default state
                    categoryLabels.forEach((label) => label.classList.remove('hidden'));

                    sidebarNavMenu.querySelectorAll('.nav-link-item').forEach((link) => {
                        link.classList.remove('hidden');
                    });

                    sidebarNavMenu.querySelectorAll('.nav-group-item').forEach((group) => {
                        group.classList.remove('hidden');
                        group.querySelectorAll('.nav-submenu-item').forEach((sub) => {
                            sub.classList.remove('hidden');
                        });
                        // Restore closed state unless active-parent
                        if (!group.classList.contains('active-parent')) {
                            group.classList.remove('is-open');
                            const trigger = group.querySelector('.nav-group-trigger');
                            if (trigger) {
                                trigger.setAttribute('aria-expanded', 'false');
                            }
                        } else {
                            group.classList.add('is-open');
                            const trigger = group.querySelector('.nav-group-trigger');
                            if (trigger) {
                                trigger.setAttribute('aria-expanded', 'true');
                            }
                        }
                    });

                    if (menuSearchEmpty) {
                        menuSearchEmpty.classList.add('hidden');
                    }

                    return;
                }

                // Process each category section
                categoryLabels.forEach((label) => {
                    const labelMatches = label.textContent.trim().toLowerCase().includes(query);
                    let categoryHasMatch = false;

                    let currentEl = label.nextElementSibling;
                    while (
                        currentEl &&
                        !currentEl.classList.contains('menu-category-label') &&
                        !currentEl.classList.contains('empty-state')
                    ) {
                        if (currentEl.classList.contains('nav-link-item')) {
                            const text = currentEl.textContent.trim().toLowerCase();
                            const matches = labelMatches || text.includes(query);
                            currentEl.classList.toggle('hidden', !matches);
                            if (matches) {
                                categoryHasMatch = true;
                                totalMatches++;
                            }
                        } else if (currentEl.classList.contains('nav-group-item')) {
                            const triggerTitle = currentEl.querySelector('.nav-item-title')?.textContent.trim().toLowerCase() || '';
                            const subItems = currentEl.querySelectorAll('.nav-submenu-item');
                            let groupMatches = false;

                            if (labelMatches || triggerTitle.includes(query)) {
                                currentEl.classList.remove('hidden');
                                currentEl.classList.add('is-open');
                                subItems.forEach((sub) => sub.classList.remove('hidden'));
                                groupMatches = true;
                                totalMatches++;
                            } else {
                                let subMatchCount = 0;
                                subItems.forEach((sub) => {
                                    const subText = sub.textContent.trim().toLowerCase();
                                    const isSubMatch = subText.includes(query);
                                    sub.classList.toggle('hidden', !isSubMatch);
                                    if (isSubMatch) {
                                        subMatchCount++;
                                    }
                                });

                                if (subMatchCount > 0) {
                                    currentEl.classList.remove('hidden');
                                    currentEl.classList.add('is-open');
                                    groupMatches = true;
                                    totalMatches++;
                                } else {
                                    currentEl.classList.add('hidden');
                                    currentEl.classList.remove('is-open');
                                }
                            }

                            if (groupMatches) {
                                categoryHasMatch = true;
                            }
                        }

                        currentEl = currentEl.nextElementSibling;
                    }

                    label.classList.toggle('hidden', !categoryHasMatch);
                });

                if (menuSearchEmpty) {
                    menuSearchEmpty.classList.toggle('hidden', totalMatches > 0);
                }
            };

            menuSearchInput.addEventListener('input', filterMenu);

            if (menuSearchClear) {
                menuSearchClear.addEventListener('click', () => {
                    menuSearchInput.value = '';
                    filterMenu();
                    menuSearchInput.focus();
                });
            }

            // Clear on Escape key
            menuSearchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && menuSearchInput.value.length > 0) {
                    e.preventDefault();
                    menuSearchInput.value = '';
                    filterMenu();
                }
            });
        }
    }

    // Sidebar collapsible toggle & state persistence
    if (sidebarToggleBtn && layoutSidebar) {
        const isCollapsed = localStorage.getItem('infora_sidebar_collapsed') === 'true';
        if (isCollapsed) {
            layoutSidebar.classList.add('is-collapsed');
            sidebarToggleBtn.classList.add('active');
        }

        sidebarToggleBtn.addEventListener('click', () => {
            const nowCollapsed = layoutSidebar.classList.toggle('is-collapsed');
            sidebarToggleBtn.classList.toggle('active', nowCollapsed);
            localStorage.setItem('infora_sidebar_collapsed', nowCollapsed ? 'true' : 'false');
        });

        // Click search box when sidebar is collapsed to auto-expand & focus input
        const sidebarSearchBox = document.getElementById('sidebarSearchBox');
        if (sidebarSearchBox) {
            sidebarSearchBox.addEventListener('click', () => {
                if (layoutSidebar.classList.contains('is-collapsed')) {
                    layoutSidebar.classList.remove('is-collapsed');
                    sidebarToggleBtn.classList.remove('active');
                    localStorage.setItem('infora_sidebar_collapsed', 'false');
                    setTimeout(() => {
                        if (menuSearchInput) {
                            menuSearchInput.focus();
                        }
                    }, 180);
                }
            });
        }

        // Global shortcut (Ctrl+K or /) to focus search
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey && e.key === 'k') || (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName))) {
                if (menuSearchInput) {
                    e.preventDefault();
                    if (layoutSidebar.classList.contains('is-collapsed')) {
                        layoutSidebar.classList.remove('is-collapsed');
                        sidebarToggleBtn.classList.remove('active');
                        localStorage.setItem('infora_sidebar_collapsed', 'false');
                    }
                    setTimeout(() => {
                        menuSearchInput.focus();
                    }, 180);
                }
            }
        });
    }

    // Sidebar User Profile Dropup Menu
    const userProfileTrigger = document.getElementById('userProfileTrigger');
    const userDropupMenu = document.getElementById('userDropupMenu');

    if (userProfileTrigger && userDropupMenu) {
        const toggleUserMenu = (forceState) => {
            const shouldOpen = typeof forceState === 'boolean' 
                ? forceState 
                : userDropupMenu.classList.contains('hidden');
            
            userDropupMenu.classList.toggle('hidden', !shouldOpen);
            userProfileTrigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            userProfileTrigger.classList.toggle('active', shouldOpen);
        };

        userProfileTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleUserMenu();
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!userDropupMenu.contains(e.target) && !userProfileTrigger.contains(e.target)) {
                toggleUserMenu(false);
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !userDropupMenu.classList.contains('hidden')) {
                toggleUserMenu(false);
                userProfileTrigger.focus();
            }
        });
    }

    // =========================================================================
    // Auto Text Transformation System (UPPERCASE for Modules, Title Case for Menus)
    // =========================================================================
    const textTransformAcronyms = new Set([
        'smk', 'sma', 'smp', 'sd', 'sim', 'pkl', 'kbm', 'gtk',
        'ban-sm', 'rpp', 'it', 'tu', 'uks', 'osis', 'bk', 'bkk',
        'id', 'api', 'crud', 'ui', 'ux', 'ipk', 'sk', 'kd', 'cp', 'tp', 'atp', 'p5',
        'nip', 'nisn', 'nis', 'nuptk'
    ]);

    const formatTitleCaseText = (str) => {
        if (!str) return str;
        return str.replace(/[^\s-]+/g, (word) => {
            const lower = word.toLowerCase();
            if (textTransformAcronyms.has(lower)) {
                return lower.toUpperCase();
            }
            return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
        });
    };

    // Live uppercase transform on input with caret position preserved
    document.addEventListener('input', (e) => {
        const target = e.target;
        if (!(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement)) {
            return;
        }

        if (target.getAttribute('data-transform') === 'uppercase') {
            const start = target.selectionStart;
            const end = target.selectionEnd;
            target.value = target.value.toUpperCase();
            if (start !== null && end !== null) {
                target.setSelectionRange(start, end);
            }
        }
    });

    // Format title-case on blur when user finishes editing the field
    document.addEventListener('blur', (e) => {
        const target = e.target;
        if (!(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement)) {
            return;
        }

        const transform = target.getAttribute('data-transform');
        if (transform === 'title-case') {
            target.value = formatTitleCaseText(target.value);
        } else if (transform === 'uppercase') {
            target.value = target.value.toUpperCase();
        }
    }, true);

    // Final sweep on form submission so submitted values are cleanly formatted
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const transformInputs = form.querySelectorAll('[data-transform]');
        transformInputs.forEach((input) => {
            if (!(input instanceof HTMLInputElement || input instanceof HTMLTextAreaElement)) {
                return;
            }
            const transform = input.getAttribute('data-transform');
            if (transform === 'uppercase') {
                input.value = input.value.toUpperCase();
            } else if (transform === 'title-case') {
                input.value = formatTitleCaseText(input.value);
            }
        });
    });
});

// Lightweight global toast feedback notification
window.showToast = function (message) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast-pill';
    toast.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        <span>${message}</span>
    `;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 250);
    }, 3000);
};
