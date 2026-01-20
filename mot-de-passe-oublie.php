<?php
session_start();
require_once __DIR__ . '/base-donne.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email invalide.";
    } else {
        // Vérifier si l'utilisateur existe
        $stmt = $pdo->prepare("SELECT id, email FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Toujours dire "email envoyé" (anti-énumération)
        $message = "Si cet email existe, un lien de réinitialisation vous a été envoyé.";
        $success = true;

        if ($user) {
            // Générer token sécurisé
            $token = bin2hex(random_bytes(32));           // ~64 caractères hex
            $token_hash = hash('sha256', $token);         // ou password_hash($token, PASSWORD_DEFAULT)

            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 heure

            // Supprimer anciens tokens pour cet email
            $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

            // Insérer nouveau
            $stmt = $pdo->prepare("
                INSERT INTO password_resets (email, token_hash, expires_at)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$email, $token_hash, $expires]);

            // Lien
            $resetLink = "https://tondomaine.com/reset-password.php?token=" . urlencode($token);

            // -------------------------------
            // Envoi email (exemple avec mail() – à remplacer par PHPMailer !)
            $subject = "Réinitialisation de votre mot de passe";
            $body = "Bonjour,\n\n"
                  . "Vous avez demandé à réinitialiser votre mot de passe.\n"
                  . "Cliquez sur le lien suivant (valable 1 heure) :\n"
                  . $resetLink . "\n\n"
                  . "Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.\n";

            $headers = "From: no-reply@tondomaine.com\r\n";
            mail($email, $subject, $body, $headers);
            // -------------------------------
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="./assets/style/style.css">
</head>
<body>
<div class="form-container">
    <h1>Mot de passe oublié ?</h1>

    <?php if ($message): ?>
        <p style="color: <?= $success ? 'green' : 'red' ?>; text-align:center;">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="email">Votre adresse email</label>
            <input type="email" id="email" name="email" required placeholder="exemple@domaine.com">
        </div>
        <button type="submit" class="btn">Envoyer le lien de réinitialisation</button>
    </form>

    <div class="form-footer">
        <p><a href="login.php">Retour à la connexion</a></p>
    </div>
</div>
</body>
</html>