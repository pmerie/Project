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

// Fetch all categories
$categoryStmt = $db->query("SELECT * FROM categories ORDER BY category_name");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

$current_user_id = $_SESSION['user_id'];

// Check if character ID is provided
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

$success = '';
$fieldErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $film_name = trim($_POST['film_name'] ?? '');
    $character_type = trim($_POST['character_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $category_id = $_POST['category_id'] ?? '';

    // ----------------- VALIDATION -----------------
    if ($name === '') $fieldErrors['name'] = "Name is required.";
    elseif (strlen($name) > 255) $fieldErrors['name'] = "Name cannot exceed 255 characters.";

    if ($film_name === '') $fieldErrors['film_name'] = "Film name is required.";
    elseif (strlen($film_name) > 255) $fieldErrors['film_name'] = "Film name cannot exceed 255 characters.";

    // Character type is optional
    if ($character_type && strlen($character_type) > 100) {
        $fieldErrors['character_type'] = "Character Type cannot exceed 100 characters.";
    }

    if ($description === '') $fieldErrors['description'] = "Description is required.";
    elseif (strlen($description) > 1000) $fieldErrors['description'] = "Description cannot exceed 1000 characters.";

    // Image URL is required
    if ($image_url === '') $fieldErrors['image_url'] = "Image URL is required.";
    elseif (!filter_var($image_url, FILTER_VALIDATE_URL)) $fieldErrors['image_url'] = "Invalid Image URL.";

    if ($category_id === '') $fieldErrors['category_id'] = "Category is required.";
    elseif (!ctype_digit($category_id)) $fieldErrors['category_id'] = "Invalid category selected.";

    // ----------------- DATABASE UPDATE -----------------
    if (empty($fieldErrors)) {
        $stmt = $db->prepare("SELECT film_id FROM films WHERE film_name = ? LIMIT 1");
        $stmt->execute([$film_name]);
        $film = $stmt->fetch(PDO::FETCH_ASSOC);

        $film_id = $film ? $film['film_id'] : null;
        if (!$film_id) {
            $stmt = $db->prepare("INSERT INTO films (film_name, year, image_url, description) VALUES (?, 0, NULL, '')");
            $stmt->execute([$film_name]);
            $film_id = $db->lastInsertId();
        }

        $stmt = $db->prepare("
            UPDATE characters
            SET name = ?, film_id = ?, character_type = ?, description = ?, image_url = ?, category_id = ?
            WHERE character_id = ?
        ");
        $stmt->execute([$name, $film_id, $character_type ?: '', $description, $image_url, $category_id, $char_id]);

        $success = "✅ Character updated successfully!";

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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Character</title>
<link rel="stylesheet" href="style.css">
<style>
    .error { color: red; font-size: 0.9em; margin-top: 2px; }
</style>
</head>
<body>
<div class="page-box">
<h1>Edit Character</h1>

<?php if ($success): ?>
<p style="color:green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post">
    <label>Name:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($char['name'] ?? '') ?>">
    <div class="error"><?= $fieldErrors['name'] ?? '' ?></div>
    <br>

    <label>Film Name:</label><br>
    <input type="text" name="film_name" value="<?= htmlspecialchars($char['film_name'] ?? '') ?>">
    <div class="error"><?= $fieldErrors['film_name'] ?? '' ?></div>
    <br>

    <label>Character Type (optional):</label><br>
    <input type="text" name="character_type" value="<?= htmlspecialchars($char['character_type'] ?? '') ?>">
    <div class="error"><?= $fieldErrors['character_type'] ?? '' ?></div>
    <br>

    <label>Description:</label><br>
    <textarea name="description" rows="5" cols="40"><?= htmlspecialchars($char['description'] ?? '') ?></textarea>
    <div class="error"><?= $fieldErrors['description'] ?? '' ?></div>
    <br>

    <label>Image URL:</label><br>
    <input type="text" name="image_url" value="<?= htmlspecialchars($char['image_url'] ?? '') ?>">
    <div class="error"><?= $fieldErrors['image_url'] ?? '' ?></div>
    <br>

    <label>Category:</label>
    <select name="category_id">
        <option value="">-- Select Category --</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['category_id']?>"
                <?= (isset($char['category_id']) && $char['category_id'] == $cat['category_id']) ? 'selected' : '' ?> >
                <?= htmlspecialchars($cat['category_name'])?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="error"><?= $fieldErrors['category_id'] ?? '' ?></div>
    <br><br>

    <button type="submit">Update Character</button>
</form>

<p><a href="list_characters.php">Back to Character List</a></p>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fields = ['name','film_name','character_type','description','image_url','category_id'];
    fields.forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.addEventListener('input', () => {
                const errorDiv = field.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('error')) errorDiv.textContent = '';
            });
            field.addEventListener('change', () => {
                const errorDiv = field.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('error')) errorDiv.textContent = '';
            });
        }
    });
});
</script>
</body>
</html>
