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
});
