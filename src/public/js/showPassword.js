document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM fully loaded and parsed');
    var togglePasswordButton = document.getElementById('togglePassword');
    var passwordField = document.getElementById('password');

    if (togglePasswordButton && passwordField) {
        togglePasswordButton.addEventListener('click', function () {
            console.log('Toggle button clicked');
            var passwordFieldType = passwordField.getAttribute('type');
            if (passwordFieldType === 'password') {
                passwordField.setAttribute('type', 'text');
                this.textContent = 'Hide';
            } else {
                passwordField.setAttribute('type', 'password');
                this.textContent = 'Show';
            }
        });
    } else {
        console.error('Toggle button or password field not found');
    }
});