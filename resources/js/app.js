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

    // Sidebar menu real-time search
    const menuSearchInput = document.getElementById('sidebarMenuSearch');
    const menuSearchClear = document.getElementById('sidebarSearchClear');
    const sidebarNavMenu = document.getElementById('sidebarNavMenu');
    const menuSearchEmpty = document.getElementById('menuSearchEmpty');

    if (menuSearchInput && sidebarNavMenu) {
        const navLinks = sidebarNavMenu.querySelectorAll('.nav-link-item');
        const categoryLabels = sidebarNavMenu.querySelectorAll('.menu-category-label');

        const filterMenu = () => {
            const query = menuSearchInput.value.trim().toLowerCase();
            let matchCount = 0;

            if (menuSearchClear) {
                menuSearchClear.classList.toggle('hidden', query.length === 0);
            }

            navLinks.forEach((link) => {
                const text = link.textContent.trim().toLowerCase();
                const matches = query === '' || text.includes(query);
                link.classList.toggle('hidden', !matches);
                if (matches) {
                    matchCount++;
                }
            });

            // Handle category labels visibility
            categoryLabels.forEach((label) => {
                if (query === '') {
                    label.classList.remove('hidden');
                } else {
                    label.classList.toggle('hidden', matchCount === 0);
                }
            });

            if (menuSearchEmpty) {
                menuSearchEmpty.classList.toggle('hidden', matchCount > 0 || navLinks.length === 0);
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

    // Sidebar collapsible toggle & state persistence
    const sidebarToggleBtn = document.getElementById('sidebarToggle');
    const layoutSidebar = document.getElementById('layoutSidebar');

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
