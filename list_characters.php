<?php
require 'db_connect.php';

$stmt = $db->query("SELECT * FROM characters ORDER BY created_at DESC");
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Characters</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        img { max-width: 100px; height: auto;}
    </style>
</head>
<body>
    <h1>Characters</h1>
    <p><a href="adminphp">Add New Characters</a></p>

    <?php if (empty($characters)): ?>
        <p>No characters added yet.</p>
        <?php else: ?>
            <table> 
                <tr>
                    <th>Name</th>
                    <th>Film ID</th>
                    <th>Species</th>
                    <th>Descriptions</th>
                    <th>Images</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($characters as $char): ?>
                    <tr>
                        <td><?= htmlspecialchars($char['name'])?></td>
                        <td><?= htmlspecialchars($char['film_id'])?></td>
                        <td><?= htmlspecialchars($char['species'])?></td>
                        <td><?= htmlspecialchars(substr($char['description'], 0, 100))?><?= strlen($char['description']) > 10 ? '...' : '' ?></td>
                        <td>
                            <?php if (!empty($char['image_url'])): ?>
                                <img src="<?= htmlspecialchars($char['image_url']) ?>" alt="<? htmlspecialchars($char['name']) ?>">
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_character.php?id=<?= $char['character_id'] ?>">Edit</a> |
                            <a href="delete_character.php?id=<?= $char['character_id'] ?>" onclick="return confirm('Are you sure?')>Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </talbe>
        <?php endif; ?>

</body>
</html>