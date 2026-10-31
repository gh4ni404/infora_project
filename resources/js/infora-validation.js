/**
 * INFORA - Universal Form Validation Enhancement
 * Replaces default browser tooltips with custom vibration, red border, and hint below.
 * Colors adhere strictly to :root tokens (var(--infora-danger), etc.) without color hex.
 * Zero new CSS classes added (uses existing .border-danger, .form-error, .text-danger).
 */

function resolveErrorMessage(input) {
    if (input.dataset.errorMessage) {
        return input.dataset.errorMessage;
    }

    const formGroup = input.closest('.form-group');
    const label = formGroup?.querySelector('.form-label')?.textContent?.replace('*', '').trim() || 'Bidang ini';
    const val = input.validity;

    if (val.valueMissing || (input.hasAttribute('required') && !input.value.trim())) {
        if (input.name === 'nis' && input.getAttribute('pattern') === '[0-9]{18}') {
            return 'NISM (Nomor Induk Murid Madrasah) wajib diisi untuk madrasah di bawah naungan Kemenag.';
        }
        if (input.tagName === 'SELECT' || input.type === 'radio' || input.type === 'checkbox') {
            return `${label} wajib dipilih.`;
        }
        return `${label} wajib diisi.`;
    }

    if (val.typeMismatch && input.type === 'email') {
        return 'Format alamat email tidak valid.';
    }

    if (input.name === 'nis' && input.getAttribute('pattern') === '[0-9]{18}') {
        const nisVal = input.value.trim();
        if (nisVal && !/^\d{18}$/.test(nisVal)) {
            return 'NISM harus terdiri tepat 18 digit angka (12 digit NSM, 2 digit tahun masuk, dan 4 digit nomor urut).';
        }
    }

    if (input.name === 'nisn') {
        const nisnVal = input.value.trim();
        if (nisnVal && !/^\d{1,10}$/.test(nisnVal)) {
            return 'NISN maksimal terdiri dari 10 digit angka.';
        }
    }

    if (val.patternMismatch || val.tooLong || val.tooShort) {
        return `Format ${label} tidak valid atau jumlah karakter tidak sesuai.`;
    }

    return input.validationMessage || `${label} tidak valid.`;
}

export function showInputError(input, message) {
    // Re-trigger shake animation
    input.classList.remove('border-danger');
    void input.offsetWidth; // Force DOM reflow
    input.classList.add('border-danger');

    const formGroup = input.closest('.form-group');
    if (!formGroup) return;

    let errorEl = formGroup.querySelector('.form-error');
    if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.className = 'form-error';
        errorEl.setAttribute('data-client-error', 'true');
        errorEl.innerHTML = `
            <svg class="w-4 h-4 shrink-0 text-danger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span></span>
        `;
        const hintEl = formGroup.querySelector('.form-hint');
        if (hintEl) {
            hintEl.after(errorEl);
        } else {
            input.after(errorEl);
        }
    }

    const span = errorEl.querySelector('span');
    if (span) {
        span.textContent = message;
    }
    errorEl.classList.remove('hidden');
}

export function clearInputError(input) {
    input.classList.remove('border-danger');
    const formGroup = input.closest('.form-group');
    if (!formGroup) return;

    const clientError = formGroup.querySelector('.form-error[data-client-error="true"]');
    if (clientError) {
        clientError.remove();
    } else {
        const errorEl = formGroup.querySelector('.form-error');
        if (errorEl) {
            errorEl.classList.add('hidden');
        }
    }
}

export function validateForm(form) {
    const inputs = form.querySelectorAll('input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled])');
    let firstInvalid = null;
    let hasError = false;

    inputs.forEach((input) => {
        let isValid = true;

        if (input.hasAttribute('required')) {
            if (input.tagName === 'SELECT') {
                isValid = Boolean(input.value && input.value !== '');
            } else if (input.type === 'checkbox') {
                isValid = input.checked;
            } else {
                isValid = input.value.trim().length > 0;
            }
        }

        if (isValid && input.value.trim().length > 0) {
            if (input.type === 'email') {
                isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim());
            } else if (input.name === 'nis' && input.getAttribute('pattern') === '[0-9]{18}') {
                isValid = /^\d{18}$/.test(input.value.trim());
            } else if (input.name === 'nisn') {
                isValid = /^\d{1,10}$/.test(input.value.trim());
            } else if (input.hasAttribute('pattern')) {
                const regex = new RegExp(`^${input.getAttribute('pattern')}$`);
                isValid = regex.test(input.value.trim());
            }
        }

        if (!isValid) {
            hasError = true;
            const message = resolveErrorMessage(input);
            showInputError(input, message);
            if (!firstInvalid) {
                firstInvalid = input;
            }
        } else {
            clearInputError(input);
        }
    });

    if (firstInvalid) {
        firstInvalid.focus();
    }

    return !hasError;
}

// Global submit interception for validation
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!form || !form.matches('form')) return;

    if (form.hasAttribute('data-no-validate')) return;

    if (!validateForm(form)) {
        e.preventDefault();
        e.stopImmediatePropagation();
    }
}, true);

// Suppress any native browser validation bubble globally
document.addEventListener('invalid', (e) => {
    e.preventDefault();
    const input = e.target;
    if (input && input.matches('input, select, textarea')) {
        const message = resolveErrorMessage(input);
        showInputError(input, message);
    }
}, true);

// Realtime clear on input or change
document.addEventListener('input', (e) => {
    const input = e.target;
    if (input && input.matches('input, select, textarea')) {
        if (input.classList.contains('border-danger')) {
            let isValid = true;
            const val = input.value.trim();

            if (input.hasAttribute('required')) {
                isValid = val.length > 0;
            }

            if (isValid && val.length > 0) {
                if (input.type === 'email') {
                    isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
                } else if (input.name === 'nis' && input.getAttribute('pattern') === '[0-9]{18}') {
                    isValid = /^\d{18}$/.test(val);
                } else if (input.name === 'nisn') {
                    isValid = /^\d{1,10}$/.test(val);
                }
            }

            if (isValid) {
                clearInputError(input);
            }
        }
    }
}, true);

document.addEventListener('change', (e) => {
    const input = e.target;
    if (input && input.matches('input, select, textarea')) {
        if (input.classList.contains('border-danger')) {
            if (!input.hasAttribute('required') || (input.value && input.value !== '')) {
                clearInputError(input);
            }
        }
    }
}, true);

window.InforaValidation = {
    validateForm,
    showInputError,
    clearInputError,
    resolveErrorMessage,
};
