<?php
require 'db_connect.php';

// Fetch all characters with film name
$stmt = $db->query("
    SELECT characters.*, films.film_name 
    FROM characters 
    LEFT JOIN films ON characters.film_id = films.film_id
    ORDER BY characters.created_at DESC
");
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghibli World Archive</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .character-card { border: 1px solid #ccc; border-radius: 8px; padding: 15px; margin-bottom: 15px; max-width: 400px; }
        .character-card img { max-width: 100%; height: auto; display: block; margin-bottom: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Ghibli World Archive</h1>
    <p><a href="list_characters.php">Manage Characters</a> | <a href="admin.php">Add New Character</a></p>

    <?php if (empty($characters)): ?>
        <p>No characters added yet.</p>
    <?php else: ?>
        <?php foreach ($characters as $char): ?>
            <div class="character-card">
                <?php if (!empty($char['image_url'])): ?>
                    <img src="<?= htmlspecialchars($char['image_url']) ?>" alt="<?= htmlspecialchars($char['name']) ?>">
                <?php endif; ?>
                <h2><?= htmlspecialchars($char['name']) ?></h2>
                <p><strong>Character Type:</strong> <?= htmlspecialchars($char['character_type']) ?></p>
                <p><?= htmlspecialchars($char['description']) ?></p>
                <p><em>Film:</em> <?= htmlspecialchars($char['film_name'] ?? 'Unknown') ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
