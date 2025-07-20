document.addEventListener('DOMContentLoaded', () => {

    // Apparition progressive de tous les formulaires
    window.addEventListener('load', () => {
    console.log('Page loaded');
    const forms = document.querySelectorAll('form');
    console.log('Forms found:', forms.length);
    forms.forEach(form => {
        form.classList.add('loaded');
        console.log('Class "loaded" added to:', form);
    });
});

  // Formulaire d'inscription
    const formInscription = document.querySelector('form[action="../config/traitement_inscription.php"]');
    if (formInscription) {
    formInscription.addEventListener('submit', function (event) {
        const email = formInscription.email.value.trim();
        const pseudo = formInscription.pseudo.value.trim();
        const motdepasse = formInscription.motdepasse.value;
        const confirmer = formInscription.confirmer_motdepasse.value;
        const conditions = formInscription.conditions.checked;

        let erreurs = [];

      // 1. Email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
        erreurs.push("Email invalide.");
    }

      // 2. Pseudo
        if (pseudo.length < 3) {
        erreurs.push("Le pseudo doit contenir au moins 3 caractères.");
    }

      // 3. Mot de passe
        const mdpRegex = /^(?=.*\d).{6,}$/;
        if (!mdpRegex.test(motdepasse)) {
        erreurs.push("Le mot de passe doit contenir au moins 6 caractères et au moins un chiffre.");
    }

      // 4. Confirmation
        if (motdepasse !== confirmer) {
        erreurs.push("Les mots de passe ne correspondent pas.");
    }

      // 5. Conditions
        if (!conditions) {
        erreurs.push("Vous devez accepter les conditions d’utilisation.");
    }
        if (erreurs.length > 0) {
        event.preventDefault();
        alert(erreurs.join("\n"));
    }
    });
}

  // Toggle mot de passe
    const togglePassword = document.getElementById('togglePassword');
    const motdepasse = document.getElementById('motdepasse');
    if (togglePassword && motdepasse) {
    togglePassword.addEventListener('click', () => {
        const type = motdepasse.getAttribute('type') === 'password' ? 'text' : 'password';
        motdepasse.setAttribute('type', type);
        togglePassword.textContent = type === 'password' ? '👁️' : '🙈';
    });
}

  // Toggle confirmation mot de passe
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmerMotdepasse = document.getElementById('confirmer_motdepasse');
    if (toggleConfirmPassword && confirmerMotdepasse) {
    toggleConfirmPassword.addEventListener('click', () => {
        const type = confirmerMotdepasse.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmerMotdepasse.setAttribute('type', type);
        toggleConfirmPassword.textContent = type === 'password' ? '👁️' : '🙈';
    });
}
});

  // Logo "brand-name" animation au clic
    const logoContainer = document.querySelector('.logo-container');
    if (logoContainer) {
    const brandName = logoContainer.querySelector('.brand-name');
    logoContainer.addEventListener('click', (e) => {
        e.preventDefault(); 
    if (brandName) {
        brandName.classList.add('enlarge');
        setTimeout(() => {
        brandName.classList.remove('enlarge');
        window.location.href = logoContainer.href;
        }, 300); 
    }
    });
}

// Formulaire de connexion
    const formConnexion = document.getElementById('FormConnexion');
    const pseudoInput = document.getElementById('pseudo');
    const mdpInput = document.getElementById('motdepasse');
    const togglePassword = document.getElementById('togglePassword');

if (formConnexion) {
    formConnexion.addEventListener('submit', (e) => {
    const erreurs = [];
    const pseudo = pseudoInput.value.trim();
    const motdepasse = mdpInput.value;
    if (pseudo.length < 3) {
        erreurs.push("Le pseudo doit contenir au moins 3 caractères.");
    }
    const mdpRegex = /^(?=.*\d).{6,}$/;
    if (!mdpRegex.test(motdepasse)) {
        erreurs.push("Le mot de passe doit contenir au moins 6 caractères et au moins un chiffre.");
    }
    if (erreurs.length > 0) {
        e.preventDefault();
        alert(erreurs.join("\n"));
    }
});
}
    
// Fonction d’échange d’adresses(page recherche)
    const depart = document.getElementById('depart');
    const arrivee = document.getElementById('arrivee');
    if (depart && arrivee) {
    window.echangerAdresses = function () {
        const temp = depart.value;
        depart.value = arrivee.value;
        arrivee.value = temp;
    };
}



