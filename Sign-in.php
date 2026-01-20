<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/style/style.css">
</head>
<body>

<div class="form-container">

<?php
session_start();
require_once __DIR__ . '/base-donne.php';

$message = '';

if (isset($_POST['connecter'])) {

    if (!empty($_POST['email']) && !empty($_POST['password'])) {

        try {
            $sql = "SELECT id, nom, prenom, email, password_hash 
                    FROM utilisateurs 
                    WHERE email = :email 
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => trim($_POST['email'])]);

            $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($utilisateur && password_verify($_POST['password'], $utilisateur['password_hash'])) {
                
                // Connexion réussie
                $_SESSION['user_id']    = $utilisateur['id'];
                $_SESSION['user_nom']   = $utilisateur['nom'];
                $_SESSION['user_prenom']= $utilisateur['prenom'];
                $_SESSION['user_email'] = $utilisateur['email'];

                // Redirection (à adapter selon ton projet)
                header("Location: liste.php");
                exit;

            } else {
                $message = "<p style='color:red;'>Email ou mot de passe incorrect.</p>";
            }

        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
        }

    } else {
        $message = "<p style='color:red;'>Veuillez remplir tous les champs.</p>";
    }
}
?>

<h1>Connexion</h1>

<?php if ($message) echo $message; ?>

<form action="" method="post" novalidate>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required 
               placeholder="votre@email.com" autocomplete="email">
    </div>

    <div class="form-group password-wrapper">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required 
               placeholder="Votre mot de passe" minlength="8">
        <i class="fa-solid fa-eye toggle-password" 
           onclick="togglePassword('password')"></i>
    </div>

    <div class="form-options">
        <label class="remember-me">
            <input type="checkbox" name="remember"> Se souvenir de moi
        </label>
        <a href="mot-de-passe-oublie.php" class="forgot-password">Mot de passe oublié ?</a>
    </div>

    <button type="submit" class="btn" name="connecter">Se connecter</button>

</form>

<div class="form-footer">
    <p>Pas encore de compte ? <a href="./Sign-up.php">S'inscrire</a></p>
</div>

</div>

<script>
// Même fonction que dans l'inscription (tu peux la mettre dans un fichier commun)
function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>