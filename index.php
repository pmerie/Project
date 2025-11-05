<?php
require 'db_connect.php';

// Fetch all characters (main content)
$stmt = $db->query("SELECT * FROM characters");
$characters = $stmt->fetchALL();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Ghibli World Archive</title>
</head>
<body>
    <h1>Ghibli World Archive</h1>
    <ul>
        <?php foreach($characters as $char): ?>
            <li>
                <strong><?= htmlspecialchars($char['name']) ?></strong>
                (Film ID: <?= htmlspecialchars($char['film_id']) ?>)
            </li>
            <?php endforeach; ?>
    </ul>
</body>
</html>