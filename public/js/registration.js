const registrationForm = document.getElementById('registrationForm');
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirmPassword');
const message = document.getElementById('message');

registrationForm.addEventListener('submit', (event) => {
    message.textContent = '';
    if (password.value !== confirmPassword.value) {
        event.preventDefault();
        message.textContent = 'Passwords do not match.';
        confirmPassword.focus();
    }
});
