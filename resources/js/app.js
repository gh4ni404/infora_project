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
});
