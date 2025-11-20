<?php
session_start();
require 'db_connect.php';

// ✅ Restrict access to admins only
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("❌ Access denied. Admins only.");
}

//  Sorting setup
$sort = $_GET['sort'] ?? 'name';
$order = $_GET['order'] ?? 'ASC';

// Only allow safe options
$allowedSort = ['name', 'film_name', 'created_at'];
$allowedOrder = ['ASC', 'DESC'];

if (!in_array($sort, $allowedSort)) $sort = 'name';
if (!in_array($order, $allowedOrder)) $order = 'ASC';

// Query with sorting
$stmt = $db->prepare("
    SELECT characters.*, films.film_name 
    FROM characters 
    LEFT JOIN films ON characters.film_id = films.film_id
    ORDER BY $sort $order
");
$stmt->execute();
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Characters</title>
    <link rel="stylesheet" href="style.css">

    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #618264; }
        img { max-width: 100px; height: auto; }
        a.sort-active { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Characters</h1>
    <p>
        <a href="admin.php">Add New Character</a> | 
        <a href="index.php">Home</a> |
        <a href="categories.php">Manage Categories</a> |
        <a href="browse_characters.php">Browse Characters</a>
    </p>

    <!-- ✅ Show current sorting -->
    <p>Currently sorted by: <strong><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $sort))) ?></strong> (<?= htmlspecialchars($order) ?>)</p>

    <?php if (empty($characters)): ?>
        <p>No characters added yet.</p>
    <?php else: ?>
        <table> 
            <tr>
                <th>
                    <a href="?sort=name&order=<?= $sort === 'name' && $order === 'ASC' ? 'DESC' : 'ASC' ?>" 
                       class="<?= $sort === 'name' ? 'sort-active' : '' ?>">Name</a>
                </th>
                <th>
                    <a href="?sort=film_name&order=<?= $sort === 'film_name' && $order === 'ASC' ? 'DESC' : 'ASC' ?>" 
                       class="<?= $sort === 'film_name' ? 'sort-active' : '' ?>">Film</a>
                </th>
                <th>
                    <a href="?sort=created_at&order=<?= $sort === 'created_at' && $order === 'ASC' ? 'DESC' : 'ASC' ?>" 
                       class="<?= $sort === 'created_at' ? 'sort-active' : '' ?>">created_at</a>
                </th>
                <th>Description</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($characters as $char): ?>
                <tr>
                    <td><?= htmlspecialchars($char['name']) ?></td>
                    <td><?= htmlspecialchars($char['film_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($char['created_at']) ?></td>
                    <td><?= htmlspecialchars(substr($char['description'], 0, 100)) ?><?= strlen($char['description']) > 100 ? '...' : '' ?></td>
                    <td>
                        <a href="view_character.php?id=<?= $char['character_id']?>">View Character</a>
                        <?php if (!empty($char['image_url'])): ?>
                            <img src="<?= htmlspecialchars($char['image_url']) ?>" alt="<?= htmlspecialchars($char['name']) ?>">
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_characters.php?id=<?= $char['character_id'] ?>">Edit</a> |
                        <a href="delete_characters.php?id=<?= $char['character_id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
