<?php
session_start();
require 'db_connect.php';

$category_id = $_GET['id'] ?? null;
if (!$category_id) {
    die("Invalid category.");
}

// Fetch category name
$cat = $db->prepare("SELECT category_name FROM categories WHERE category_id = ?");
$cat->execute([$category_id]);
$category = $cat->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die("Category not found.");
}

// Fetch characters in this category
$stmt = $db->prepare("
    SELECT c.character_id, c.name, f.film_name
    FROM characters c
    LEFT JOIN films f ON c.film_id = f.film_id
    WHERE c.category_id = ?
    ORDER BY c.name ASC
");
$stmt->execute([$category_id]);
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($category['category_name']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Category: <?= htmlspecialchars($category['category_name']) ?></h1>

<?php if (empty($characters)): ?>
    <p>No characters in this category yet.</p>
<?php else: ?>
    <ul>
        <?php foreach ($characters as $char): ?>
            <li>
                <a href="view_character.php?id=<?= $char['character_id'] ?>">
                    <?= htmlspecialchars($char['name']) ?> (<?= htmlspecialchars($char['film_name']) ?>)
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- <p><a href="browse_categories.php">Back to Categories</a></p> -->
<p><a href="index.php">← Back to Home</a></p>

</body>
</html>
