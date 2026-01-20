<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des utilisateurs - Upload capture</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        h1, h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .message {
            padding: 12px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .success { background: #d4edda; color: #155724; }
        .error   { background: #f8d7da; color: #721c24; }
        .form-group {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="file"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            max-width: 400px;
        }
        button {
            padding: 10px 20px;
            background: #0066cc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0055aa;
        }
    </style>
</head>
<body>

    <h1>Gestion des utilisateurs</h1>

    <?php
    require_once __DIR__ . '/base-donne.php';

    // ─────────────────────────────────────────────
    // 1. Affichage de la liste des utilisateurs
    // ─────────────────────────────────────────────
    try {
        $stmt = $pdo->query("SELECT nom, prenom FROM utilisateurs ORDER BY nom, prenom");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = count($users);
    } catch (PDOException $e) {
        echo '<div class="message error">Erreur lors de la lecture de la base de données.</div>';
        $users = [];
        $count = 0;
    }
    ?>

    <h2>Liste des utilisateurs (<?= $count ?>)</h2>

    <?php if ($count > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nom complet</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="font-style: italic; color: #666;">Aucun inscrit pour le moment.</p>
    <?php endif; ?>

    <!-- ───────────────────────────────────────────── -->
    <!-- 2. Formulaire d'upload de capture d'écran -->
    <!-- ───────────────────────────────────────────── -->
    <?php
    $message = '';
    $message_type = 'error';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['screenshot'])) {

        $file = $_FILES['screenshot'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5 Mo

        // Vérifications
        if ($file['error'] === UPLOAD_ERR_OK) {

            if (!in_array($file['type'], $allowed_types)) {
                $message = "Seuls les fichiers JPG, PNG et GIF sont autorisés.";
            }
            elseif ($file['size'] > $max_size) {
                $message = "Le fichier est trop volumineux (max 5 Mo).";
            }
            else {
                // Création d'un dossier uploads s'il n'existe pas
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Nom de fichier sécurisé + unique
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'capture_' . time() . '_' . uniqid() . '.' . $extension;
                $destination = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $message = "Capture envoyée avec succès ! ($filename)";
                    $message_type = 'success';

                    // Option : enregistrer le chemin en base de données
                    // $stmt = $pdo->prepare("INSERT INTO captures (fichier, date_upload) VALUES (?, NOW())");
                    // $stmt->execute([$filename]);
                } else {
                    $message = "Erreur lors du déplacement du fichier.";
                }
            }
        } 
        elseif ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            $message = "Le fichier dépasse la taille maximale autorisée.";
        } 
        else {
            $message = "Erreur lors de l'upload (code : " . $file['error'] . ").";
        }
    }
    ?>

    <?php if ($message): ?>
        <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <h2>Envoyer une capture d'écran</h2>

    <form action="" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="screenshot">Votre capture d'écran (JPG, PNG, GIF – max 5 Mo)</label>
            <input 
                type="file" 
                class="form-control" 
                id="screenshot" 
                name="screenshot" 
                accept="image/jpeg,image/png,image/gif" 
                required
            >
        </div>

        <button type="submit">Envoyer la capture</button>
    </form>

</body>
</html>