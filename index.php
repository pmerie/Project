<?php
require 'db_connect.php';

// Fetch all characters
$stmt = $db->query("SELECT * FROM characters ORDER BY created_at DESC");
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghibli World Archive</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .character-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            max-width: 400px;
        }
        .character-card img {
            max-width: 100%;
            height: auto;
            display: block;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .character-card h2 {
            margin: 0 0 5px 0;
        }
        .character-card p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h1>Ghibli World Archive</h1>

    <?php if (empty($characters)): ?>
        <p>No characters added yet. Visit the <a href="admin.php">Admin Panel</a> to add some!</p>
    <?php else: ?>
        <?php foreach ($characters as $char): ?>
            <div class="character-card">
                <?php if (!empty($char['image_url'])): ?>
                    <img src="<?= htmlspecialchars($char['image_url']) ?>" alt="<?= htmlspecialchars($char['name']) ?>">
                <?php endif; ?>
                <h2><?= htmlspecialchars($char['name']) ?></h2>
                <p><strong>Species:</strong> <?= !empty($char['species']) ? htmlspecialchars($char['species']) : 'Unknown' ?></p>
                <p><?= !empty($char['description']) ? htmlspecialchars(substr($char['description'], 0, 150)) . (strlen($char['description']) > 150 ? '...' : '') : 'No description available.' ?></p>
                <p><em>Film ID:</em> <?= htmlspecialchars($char['film_id']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
