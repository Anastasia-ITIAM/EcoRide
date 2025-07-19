<?php
session_start();
require_once '../templates/header.php';

if (isset($_GET['redirect'])) {
    $_SESSION['redirect_after_register'] = $_GET['redirect'];
}


// Récupère les anciennes valeurs en cas d'erreur
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
?>

<div class="container my-5">
    <h2 class="text-center mb-4" style="color: var(--eco-text);">C’est parti pour des trajets plus écolos🌿</h2>

    <?php if (!empty($_SESSION['form_erreurs'])): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($_SESSION['form_erreurs'] as $erreur): ?>
                    <li><?= htmlspecialchars($erreur) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['form_erreurs']); ?>
    <?php endif; ?>

    <form action="../config/traitement_inscription.php" method="POST" class="row g-3 col-md-6 mx-auto">
        <div class="col-12">
            <label for="email" class="form-label">Adresse Email</label>
            <input type="email" class="form-control" id="email" name="email" required
                value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        </div>
        <div class="col-12">
            <label for="pseudo" class="form-label">Pseudo</label>
            <input type="text" class="form-control" id="pseudo" name="pseudo" required
                value="<?= htmlspecialchars($old['pseudo'] ?? '') ?>">
        </div>
        <div class="col-12">
            <label for="motdepasse" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="motdepasse" name="motdepasse" required>
        </div>
        <div class="col-12">
            <label for="confirmer_motdepasse" class="form-label">Confirmer le mot de passe</label>
            <input type="password" class="form-control" id="confirmer_motdepasse" name="confirmer_motdepasse" required>
        </div>
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input eco-checkbox" type="checkbox" id="conditions" name="conditions" required>
                <label class="form-check-label" for="conditions">
                        J’accepte les 
                    <a href="#" class="eco-link" data-bs-toggle="modal" data-bs-target="#mentionsModal">conditions d’utilisation</a>
                </label>
            </div>
        </div>
        <div class="col-12 text-center">
            <button type="submit" class="btn custom-btn px-4">S'inscrire</button>
        </div>
        <div class="col-12 text-center">
            <p class="mt-3">Déjà un compte ? <a href="/EcoRide/connexion.php" class="eco-link">Se connecter</a></p>
        </div>
    </form>
</div>

<?php require_once '../templates/footer.php'; ?>
