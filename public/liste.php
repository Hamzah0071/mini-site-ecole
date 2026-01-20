<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des utilisateurs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        h2 {
            margin-bottom: 10px;
        }
        .search-container {
            margin: 20px 0;
        }
        .search-container input {
            padding: 10px;
            width: 300px;
            max-width: 100%;
            font-size: 16px;
        }
        .search-container button {
            padding: 10px 16px;
            font-size: 16px;
            background: #0066cc;
            color: white;
            border: none;
            cursor: pointer;
        }
        .search-container button:hover {
            background: #0055aa;
        }
        .user-list {
            list-style: none;
            padding: 0;
        }
        .user-list li {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 1.1em;
        }
        .user-list li:hover {
            background: #f8f9fa;
        }
        .no-result {
            color: #777;
            font-style: italic;
            padding: 20px 0;
        }
        .count {
            color: #555;
            font-size: 0.95em;
        }
    </style>
</head>
<body>

    <h1>Gestion des utilisateurs</h1>

    <?php
    require_once __DIR__ . '/base-donne.php';

    // Récupération du terme de recherche (sécurisé)
    $search = trim($_GET['search'] ?? '');
    $search = htmlspecialchars($search); // pour l'affichage

    // Construction de la requête
    $sql = "SELECT nom, prenom FROM utilisateurs";
    $params = [];

    if (!empty($search)) {
        $sql .= " WHERE nom LIKE :search OR prenom LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }

    $sql .= " ORDER BY nom, prenom";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = count($users);

    } catch (PDOException $e) {
        echo "<p style='color:red;'>Erreur de base de données.</p>";
        // error_log($e->getMessage());  ← en production
        $users = [];
        $count = 0;
    }
    ?>

    <!-- Formulaire de recherche -->
    <div class="search-container">
        <form method="get" action="">
            <input 
                type="search" 
                name="search" 
                placeholder="Rechercher par nom ou prénom..." 
                value="<?= htmlspecialchars($search) ?>"
            >
            <button type="submit">Rechercher</button>
            <?php if (!empty($search)): ?>
                <a href="?">× Effacer</a>
            <?php endif; ?>
        </form>
    </div>

    <h2>
        Liste des utilisateurs 
        <span class="count">(<?= $count ?> trouvé<?= $count > 1 ? 's' : '' ?>)</span>
    </h2>

    <?php if ($count > 0): ?>
        <ul class="user-list">
            <?php foreach ($users as $user): ?>
                <li>
                    <?= htmlspecialchars($user['nom']) ?> 
                    <?= htmlspecialchars($user['prenom']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="no-result">
            <?= !empty($search) 
                ? "Aucun utilisateur ne correspond à « $search »." 
                : "Aucun utilisateur inscrit pour le moment." ?>
        </p>
    <?php endif; ?>

</body>
</html>