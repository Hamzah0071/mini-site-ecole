<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.16/build/css/intlTelInput.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/style/style.css">
</head>
<body>

<div class="form-container">

<?php
session_start();
require_once __DIR__ . '/base-donne.php';

if (isset($_POST['enregistre'])) {

    if (
        !empty($_POST['nom']) &&
        !empty($_POST['prenom']) &&
        !empty($_POST['email']) &&
        !empty($_POST['telephone']) &&
        !empty($_POST['adresse']) &&
        !empty($_POST['date_de_naissance']) &&
        !empty($_POST['sexe']) &&
        !empty($_POST['password']) &&
        !empty($_POST['password_confirm']) &&
        isset($_POST['terms'])
    ) {

        if ($_POST['password'] !== $_POST['password_confirm']) {
            echo "<p style='color:red;'>Les mots de passe ne correspondent pas.</p>";
        } else {

            try {
                $sql = "INSERT INTO utilisateurs
                        (nom, prenom, email, telephone, adresse, date_de_naissance, sexe, password_hash)
                        VALUES
                        (:nom, :prenom, :email, :telephone, :adresse, :date_de_naissance, :sexe, :password_hash)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ':nom' => trim($_POST['nom']),
                    ':prenom' => trim($_POST['prenom']),
                    ':email' => trim($_POST['email']),
                    ':telephone' => trim($_POST['telephone']),
                    ':adresse' => trim($_POST['adresse']),
                    ':date_de_naissance' => $_POST['date_de_naissance'],
                    ':sexe' => $_POST['sexe'],
                    ':password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT)
                ]);

                echo "<p style='color:green;'>Inscription réussie.</p>";

            } catch (PDOException $e) {
                echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
            }
        }

    } else {
        echo "<p style='color:red;'>Tous les champs sont obligatoires.</p>";
    }
}
?>

<h1>Inscription</h1>

<form action="" method="post" novalidate>

    <div class="form-group">
        <label>Nom</label>
        <input type="text" name="nom" required>
    </div>

    <div class="form-group">
        <label>Prénom</label>
        <input type="text" name="prenom" required>
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
    </div>

    <div class="form-group"> 
        <label for="telephone">Téléphone</label> 
            <div class="iti iti--allow-dropdown"> 
            <input type="tel" id="telephone" name="telephone" placeholder="341 23 456 78" required autocomplete="off"> 
            </div> 
</div> <!-- Champ caché pour récupérer le numéro complet avec +261 (facultatif) --> 
<input type="hidden" name="telephone_full" id="phone_full"> 
<div class="form-group"> 
    <label for="adresse">Adresse</label> 
<input type="text" id="adresse" name="adresse" placeholder="Votre adresse complète" required> 
</div>

    <div class="form-group">
        <label>Date de naissance</label>
        <input type="date" name="date_de_naissance" required>
    </div>

    <div class="form-group">
        <label>Sexe</label>
        <select name="sexe" required>
            <option value="">-- Choisir --</option>
            <option value="homme">Homme</option>
            <option value="femme">Femme</option>
            <option value="autre">Autre</option>
        </select>
    </div>

    <div class="form-group password-wrapper">
        <label for="password">Mot de passe</label> 
        <input type="password" id="password" name="password" placeholder="Au moins 8 caractères" required minlength="8"> 
        <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('password')"></i> 
    </div> 

    <div class="form-group password-wrapper"> 
        <label for="password_confirm">Confirmation</label> 
        <input type="password" id="password_confirm" name="password_confirm" 
               placeholder="Confirmez le mot de passe" required> 
        <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('password_confirm')"></i> 
        <div class="error-message" id="confirm-error">Les mots de passe ne correspondent pas.</div> 
    </div> 

    <div class="checkbox-field"> 
        <input type="checkbox" id="terms" name="terms" required> 
        <label for="terms"> 
            J'accepte les <a href="conditions.php" target="_blank">conditions d'utilisation</a>
        </label> 
    </div> 

    <button type="submit" class="btn" name="enregistre">S'inscrire</button>

</form>

<div class="form-footer">
    <p>Déjà un compte ? <a href="./Sign-in.php">Se connecter</a></p>
</div>
 
</form> 
</div>
<script src="./assets/script/srcipt.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.16/build/js/intlTelInput.min.js"></script>
</body> 
</html>