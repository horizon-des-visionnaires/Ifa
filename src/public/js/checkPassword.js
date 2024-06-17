document.addEventListener("DOMContentLoaded", function() {
    const passwordInput = document.getElementById('password');
    const requirementsList = document.getElementById('password-requirements').getElementsByTagName('li');

    passwordInput.addEventListener('input', function() {
        const password = passwordInput.value;

        // Vérification de la longueur du mot de passe
        const lengthValid = password.length >= 8;

        // Vérification de la présence d'au moins une lettre majuscule
        const uppercaseValid = /[A-Z]/.test(password);

        // Vérification de la présence d'au moins une lettre minuscule
        const lowercaseValid = /[a-z]/.test(password);

        // Vérification de la présence d'au moins un chiffre
        const numberValid = /\d/.test(password);

        // Vérification de tous les critères
        const allValid = lengthValid && uppercaseValid && lowercaseValid && numberValid;

        // Mettre à jour les couleurs des critères
        requirementsList[0].style.color = lengthValid ? 'green' : 'inherit';
        requirementsList[1].style.color = uppercaseValid ? 'green' : 'inherit';
        requirementsList[2].style.color = lowercaseValid ? 'green' : 'inherit';
        requirementsList[3].style.color = numberValid ? 'green' : 'inherit';

        // Changer la couleur de <span>Password</span> seulement si tous les critères sont remplis
        const passwordSpan = document.querySelector('.password-label');
        passwordSpan.style.color = allValid ? 'green' : 'inherit';
    });
});
