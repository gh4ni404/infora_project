/**
 * INFORA - Main Application Client Scripts
 */

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
    }
});
