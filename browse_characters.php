<?php
session_start();
require 'db_connect.php';

// Fetch all characters with their film name
$stmt = $db->prepare("
    SELECT c.character_id, c.name, f.film_name
    FROM characters c
    LEFT JOIN films f ON c.film_id = f.film_id
    ORDER BY c.name ASC
");
$stmt->execute();
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Characters</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>All Studio Ghibli Characters</h1>

    <?php if (empty($characters)): ?>
        <p>No characters available yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($characters as $char): ?>
                <li>
                    <a href="view_character.php?id=<?= $char['character_id'] ?>">
                        <?= htmlspecialchars($char['name']) ?>
                        (<?= htmlspecialchars($char['film_name'] ?? 'Unknown Film') ?>)
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p><a href="index.php">Back to Home</a></p>
</body>
</html>
