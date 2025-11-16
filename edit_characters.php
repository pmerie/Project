<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

// Check if user is logged in and admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $db->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    die("❌ Access denied. Admins only.");
}

// Fetch all categories for the dropdown
$categoryStmt = $db->query("SELECT * FROM categories ORDER BY category_name");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

$current_user_id = $_SESSION['user_id'];

// Check if the character ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Character ID is missing.");
}

$char_id = $_GET['id'];

// Fetch the character with film name
$stmt = $db->prepare("
    SELECT c.*, f.film_name 
    FROM characters c
    LEFT JOIN films f ON c.film_id = f.film_id
    WHERE c.character_id = ?
");
$stmt->execute([$char_id]);
$char = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$char) {
    die("Character not found.");
}

// Update the character when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $film_name = trim($_POST['film_name']);
    $character_type = trim($_POST['character_type']);
    $description = $_POST['description'];
    $image_url = trim($_POST['image_url']);
    $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;

    // Check if the film already exists
    $stmt = $db->prepare("SELECT film_id FROM films WHERE film_name = ? LIMIT 1");
    $stmt->execute([$film_name]);
    $film = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($film) {
        $film_id = $film['film_id'];
    } else {
        // Insert new film safely
        $stmt = $db->prepare("
            INSERT INTO films (film_name, year, image_url, description)
            VALUES (?, 0, NULL, '')
        ");
        $stmt->execute([$film_name]);
        $film_id = $db->lastInsertId();
    }

    // Update character
    $stmt = $db->prepare("
        UPDATE characters
        SET name = ?, film_id = ?, character_type = ?, description = ?, image_url = ?, category_id = ?
        WHERE character_id = ?
    ");
    $stmt->execute([$name, $film_id, $character_type, $description, $image_url, $category_id, $char_id]);

    echo "<p>✅ Character updated successfully!</p>";

    // Refresh data
    $stmt = $db->prepare("
        SELECT c.*, f.film_name 
        FROM characters c
        LEFT JOIN films f ON c.film_id = f.film_id
        WHERE c.character_id = ?
    ");
    $stmt->execute([$char_id]);
    $char = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Character</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    tinymce.init({
        selector: '#description',
        menubar: false,
        plugins: 'lists link',
        toolbar: 'undo redo | bold italic | bullist numlist | link'
    });
    </script>

</head>
<body>
    <h1>Edit Character</h1>

    <form method="post">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($char['name']) ?>" required><br><br>

        <label>Film Name:</label><br>
        <input type="text" name="film_name" value="<?= htmlspecialchars($char['film_name'] ?? '') ?>" required><br><br>

        <label>Character Type:</label><br>
        <input type="text" name="character_type" value="<?= htmlspecialchars($char['character_type']) ?>"><br><br>

        <label>Description:</label><br>
        <textarea id="description "name="description" rows="5" cols="40"><?= $char['description'] ?></textarea><br><br>

        <label>Image URL:</label><br>
        <input type="text" name="image_url" value="<?= htmlspecialchars($char['image_url']) ?>"><br><br>

        <!-- Adding select for categories -->
        <label>Category:</label>
        <select name="category_id">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id']?>" 
                    <?= (isset($char['category_id']) && $char['category_id'] == $cat['category_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['category_name'])?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Update Character</button>
    </form>

    <p><a href="list_characters.php">Back to Character List</a></p>
</body>
</html>
