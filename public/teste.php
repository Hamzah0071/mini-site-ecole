<?php
// register.php  (ou le nom que tu veux)

require_once __DIR__ . '/base-donne.php';

$errors = [];
$old = $_POST; // pour resaisir les valeurs en cas d'erreur

if (isset($_POST['enregistre'])) {

    // ─────────────────────────────────────────────
    // 1. Nettoyage de base + validation
    // ─────────────────────────────────────────────

    $nom      = trim($_POST['nom'] ?? '');
    $prenom   = trim($_POST['prenom'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telephone= trim($_POST['telephone'] ?? ''); // ou $_POST['telephone_full'] si tu utilises intl-tel-input
    $adresse  = trim($_POST['adresse'] ?? '');
    $date_naiss = $_POST['date_de_naissance'] ?? '';
    $sexe     = $_POST['sexe'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $terms    = isset($_POST['terms']);

    // ─────────────────────────────────────────────
    // Validation
    // ─────────────────────────────────────────────

    if (empty($nom)) {
        $errors['nom'] = "Le nom est obligatoire.";
    } elseif (strlen($nom) < 2 || strlen($nom) > 60) {
        $errors['nom'] = "Le nom doit contenir entre 2 et 60 caractères.";
    }

    if (empty($prenom)) {
        $errors['prenom'] = "Le prénom est obligatoire.";
    } elseif (strlen($prenom) < 2 || strlen($prenom) > 60) {
        $errors['prenom'] = "Le prénom doit contenir entre 2 et 60 caractères.";
    }

    if (empty($email)) {
        $errors['email'] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'email invalide.";
    } else {
        // Vérifier unicité email (très important !)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = "Cet email est déjà utilisé.";
        }
    }

    // Téléphone (selon le pays, ici on accepte format international basique)
    if (empty($telephone)) {
        $errors['telephone'] = "Le numéro de téléphone est obligatoire.";
    } elseif (!preg_match('/^\+?[1-9]\d{8,14}$/', $telephone)) { // très permissif
        $errors['telephone'] = "Numéro de téléphone invalide (8 à 15 chiffres).";
    }

    if (empty($adresse)) {
        $errors['adresse'] = "L'adresse est obligatoire.";
    } elseif (strlen($adresse) < 5 || strlen($adresse) > 255) {
        $errors['adresse'] = "L'adresse semble trop courte ou trop longue.";
    }

    if (empty($date_naiss)) {
        $errors['date_de_naissance'] = "La date de naissance est obligatoire.";
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $date_naiss);
        if (!$date || $date->format('Y-m-d') !== $date_naiss) {
            $errors['date_de_naissance'] = "Format de date invalide.";
        } else {
            $age = (new DateTime())->diff($date)->y;
            if ($age < 13) {
                $errors['date_de_naissance'] = "Vous devez avoir au moins 13 ans.";
            }
        }
    }

    if (empty($sexe) || !in_array($sexe, ['homme', 'femme', 'autre'])) {
        $errors['sexe'] = "Veuillez sélectionner un sexe valide.";
    }

    if (empty($password)) {
        $errors['password'] = "Le mot de passe est obligatoire.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = "Le mot de passe doit contenir au moins une majuscule et un chiffre.";
    }

    if ($password !== $password_confirm) {
        $errors['password_confirm'] = "Les mots de passe ne correspondent pas.";
    }

    if (!$terms) {
        $errors['terms'] = "Vous devez accepter les conditions d'utilisation.";
    }

    // ─────────────────────────────────────────────
    // Si aucune erreur → enregistrement
    // ─────────────────────────────────────────────
    if (empty($errors)) {

        try {
            $sql = "INSERT INTO utilisateurs
                    (nom, prenom, email, telephone, adresse, date_de_naissance, sexe, password_hash, date_inscription)
                    VALUES
                    (:nom, :prenom, :email, :telephone, :adresse, :date_naiss, :sexe, :pass, NOW())";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':nom'         => $nom,
                ':prenom'      => $prenom,
                ':email'       => $email,
                ':telephone'   => $telephone,   // ou $POST['telephone_full'] si intl-tel-input
                ':adresse'     => $adresse,
                ':date_naiss'  => $date_naiss,
                ':sexe'        => $sexe,
                ':pass'        => password_hash($password, PASSWORD_DEFAULT)
            ]);

            // Option : redirection + message flash
            // header("Location: inscription-reussie.php");
            // exit;

            $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            $old = []; // on vide les anciens inputs

        } catch (PDOException $e) {
            // En production : ne jamais afficher $e->getMessage()
            $errors['global'] = "Erreur technique lors de l'inscription. Réessayez plus tard.";
            // error_log($e->getMessage());  ← à faire en prod
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.16/build/css/intlTelInput.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="./assets/style/style.css"> -->
    <style>
        .error { color: #dc3545; font-size: 0.9em; margin-top: 4px; }
        .success { color: #198754; font-weight: bold; }
        input.error-field { border: 1.5px solid #dc3545; }
    </style>
</head>
<body>

<div class="form-container">

    <?php if (!empty($success)): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if (!empty($errors['global'])): ?>
        <p style="color:red; font-weight:bold;"><?= htmlspecialchars($errors['global']) ?></p>
    <?php endif; ?>

    <h1>Inscription</h1>

    <form action="" method="post" novalidate>

        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($old['nom']??'') ?>" required class="<?= isset($errors['nom']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['nom'])): ?>
                <div class="error"><?= htmlspecialchars($errors['nom']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($old['prenom']??'') ?>" required class="<?= isset($errors['prenom']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['prenom'])): ?>
                <div class="error"><?= htmlspecialchars($errors['prenom']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($old['email']??'') ?>" required class="<?= isset($errors['email']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['email'])): ?>
                <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="telephone">Téléphone</label>
            <div class="iti iti--allow-dropdown">
                <input type="tel" id="telephone" name="telephone" placeholder="341 23 456 78"
                       value="<?= htmlspecialchars($old['telephone']??'') ?>" required autocomplete="off"
                       class="<?= isset($errors['telephone']) ? 'error-field' : '' ?>">
            </div>
            <?php if (isset($errors['telephone'])): ?>
                <div class="error"><?= htmlspecialchars($errors['telephone']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="adresse">Adresse</label>
            <input type="text" id="adresse" name="adresse" placeholder="Votre adresse complète"
                   value="<?= htmlspecialchars($old['adresse']??'') ?>" required class="<?= isset($errors['adresse']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['adresse'])): ?>
                <div class="error"><?= htmlspecialchars($errors['adresse']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Date de naissance</label>
            <input type="date" name="date_de_naissance" value="<?= htmlspecialchars($old['date_de_naissance']??'') ?>" required class="<?= isset($errors['date_de_naissance']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['date_de_naissance'])): ?>
                <div class="error"><?= htmlspecialchars($errors['date_de_naissance']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Sexe</label>
            <select name="sexe" required class="<?= isset($errors['sexe']) ? 'error-field' : '' ?>">
                <option value="">-- Choisir --</option>
                <option value="homme"   <?= ($old['sexe']??'') === 'homme'   ? 'selected' : '' ?>>Homme</option>
                <option value="femme"   <?= ($old['sexe']??'') === 'femme'   ? 'selected' : '' ?>>Femme</option>
                <option value="autre"   <?= ($old['sexe']??'') === 'autre'   ? 'selected' : '' ?>>Autre</option>
            </select>
            <?php if (isset($errors['sexe'])): ?>
                <div class="error"><?= htmlspecialchars($errors['sexe']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group password-wrapper">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="Au moins 8 caractères" required minlength="8" class="<?= isset($errors['password']) ? 'error-field' : '' ?>">
            <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('password')"></i>
            <?php if (isset($errors['password'])): ?>
                <div class="error"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group password-wrapper">
            <label for="password_confirm">Confirmation</label>
            <input type="password" id="password_confirm" name="password_confirm" required class="<?= isset($errors['password_confirm']) ? 'error-field' : '' ?>">
            <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('password_confirm')"></i>
            <?php if (isset($errors['password_confirm'])): ?>
                <div class="error"><?= htmlspecialchars($errors['password_confirm']) ?></div>
            <?php endif; ?>
        </div>

        <div class="checkbox-field">
            <input type="checkbox" id="terms" name="terms" <?= isset($_POST['terms']) ? 'checked' : '' ?> required>
            <label for="terms"> J'accepte les <a href="conditions.php" target="_blank">conditions d'utilisation</a></label>
            <?php if (isset($errors['terms'])): ?>
                <div class="error"><?= htmlspecialchars($errors['terms']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn" name="enregistre">S'inscrire</button>

    </form>
</div>

<script src="./assets/script/srcipt.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.16/build/js/intlTelInput.min.js"></script>

<script>
// Initialisation intl-tel-input (très recommandé)
const input = document.querySelector("#telephone");
const iti = window.intlTelInput(input, {
    initialCountry: "mg",          // Madagascar par défaut
    preferredCountries: ["mg", "fr", "re"],
    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.16/build/js/utils.js",
});

// Option : remplir le champ caché avec le numéro complet
document.querySelector("form").addEventListener("submit", function() {
    document.getElementById("phone_full").value = iti.getNumber();
});
</script>

</body>
</html>