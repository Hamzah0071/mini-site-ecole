document.addEventListener("DOMContentLoaded", function() {
    const input = document.querySelector("#telephone");
    const iti = window.intlTelInput(input, {
        initialCountry: "mg",  // "mg" = Madagascar (le téléphone sera pré-rempli avec +261)
        preferredCountries: ["mg", "fr", "us"],  // pays en haut de la liste
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.16/build/js/utils.js",
        separateDialCode: true,  // affiche +261 à gauche
    });
    
    // Optionnel : si tu veux récupérer seulement le numéro sans le +261 dans ton PHP
    // Ajoute un champ caché
    input.addEventListener("input", function() {
        document.getElementById("phone_full").value = iti.getNumber();  // ex: +261341234567
    });
});

    
function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Validation client-side pour la date de naissance (au moins 13 ans)
document.getElementById('date_de_naissance').addEventListener('change', function() {
    const birthDate = new Date(this.value);
    const today = new Date('2026-01-18'); // Date actuelle simulée
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    const errorEl = document.getElementById('date-error');
    const input = this;
    
    if (age < 13) {
        input.classList.add('error');
        errorEl.style.display = 'block';
        input.setCustomValidity('Âge minimum: 13 ans');
    } else {
        input.classList.remove('error');
        errorEl.style.display = 'none';
        input.setCustomValidity('');
    }
});

// Validation pour confirmation mot de passe
document.getElementById('password_confirm').addEventListener('input', function() {
    const pass1 = document.getElementById('password').value;
    const pass2 = this.value;
    const errorEl = document.getElementById('confirm-error');
    const input = this;
    
    if (pass1 && pass2 && pass1 !== pass2) {
        input.classList.add('error');
        errorEl.style.display = 'block';
        input.setCustomValidity('Les mots de passe ne correspondent pas');
    } else {
        input.classList.remove('error');
        errorEl.style.display = 'none';
        input.setCustomValidity('');
    }
});

// Validation globale avant soumission
document.querySelector('form').addEventListener('submit', function(e) {
    const birthInput = document.getElementById('date_de_naissance');
    const confirmInput = document.getElementById('password_confirm');
    
    // Vérif âge (comme ci-dessus)
    const birthDate = new Date(birthInput.value);
    const today = new Date('2026-01-18');
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    if (age < 13) {
        e.preventDefault();
        birthInput.classList.add('error');
        document.getElementById('date-error').style.display = 'block';
        alert('Vous devez avoir au moins 13 ans pour vous inscrire.');
        return;
    }
    
    // Vérif mots de passe
    if (confirmInput.value && document.getElementById('password').value !== confirmInput.value) {
        e.preventDefault();
        confirmInput.classList.add('error');
        document.getElementById('confirm-error').style.display = 'block';
        alert('Les mots de passe ne correspondent pas.');
        return;
    }
    
    // Si tout est OK, soumission
    this.classList.remove('error');
});
