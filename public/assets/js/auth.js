/**
 * ARS Authentication Scripts
 */

document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('bi-eye');
                this.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('bi-eye-slash');
                this.classList.add('bi-eye');
            }
        });
    });

    // Client-side validation for Signup
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const passwordError = document.getElementById('password-match-error');

            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                passwordError.style.display = 'flex';
                confirmPassword.classList.add('is-invalid');
                confirmPassword.focus();
            } else {
                passwordError.style.display = 'none';
                confirmPassword.classList.remove('is-invalid');
            }
        });
    }

    // Loading state for buttons on submit
    const authForms = document.querySelectorAll('.auth-form');
    authForms.forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('.btn-auth');
            if (btn) {
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...`;
            }
        });
    });
});
