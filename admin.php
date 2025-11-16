<?php
session_start();
require 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user role
$stmt = $db->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    die("❌ Access denied. Admins only.");
}

//Fetch all categories for the dropdown
$categoryStmt = $db->query("SELECT * FROM categories ORDER BY category_name");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

$user_id = $_SESSION['user_id']; // Logged-in admin ID

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $film_name = trim($_POST['film_name']); // typed film name
    $character_type = $_POST['character_type'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];

    if (empty($film_name)) {
        echo "<p style='color:red;'>Please enter a film name.</p>";
    } else {
        // Try to find the film by name
        $stmt = $db->prepare("SELECT film_id FROM films WHERE film_name = ?");
        $stmt->execute([$film_name]);
        $film = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($film) {
            $film_id = $film['film_id'];
        } else {
            // Insert new film with minimal default values
            $stmt = $db->prepare("
                INSERT INTO films (film_name, year, image_url, description, director_id, genre_id, user_id)
                VALUES (?, 0, NULL, '', 1, 1, ?)
            ");
            $stmt->execute([$film_name, $user_id]);
            $film_id = $db->lastInsertId();
        }

        // Insert character
        $category_id = $_POST['category_id'] ?? null;
        $stmt = $db->prepare("
            INSERT INTO characters (name, film_id, character_type, description, image_url, category_id) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $film_id, $character_type, $description, $image_url, $category_id]);

        echo "<p style='color:green;'>✅ Character added successfully!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - Add Character</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Admin - Add New Character</h1>

<p>
    <a href="list_characters.php">View All Characters</a> |
    <a href="categories.php">Manage Categories</a> |
    <a href="comments.php">Moderate Comments</a> |
    <a href="index.php">Home</a>
</p>

<form method="post">
    <label>Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Film Name:</label><br>
    <input type="text" name="film_name" required placeholder="Type film name here"><br><br>

    <label>Character Type:</label><br>
    <input type="text" name="character_type"><br><br>

    <label>Description:</label><br>
    <textarea name="description" rows="5" cols="40"></textarea><br><br>

    <label>Image URL:</label><br>
    <input type="text" name="image_url"><br><br>

    <!--Adding select for categories-->
    <label>Category:</label>
    <select name="category_id">
        <option value="">-- Select Category --</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['category_id']?>">
                <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['category_id']) ? 'selected' : '' ?>
                <?= htmlspecialchars($cat['category_name'])?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Add Character</button>
</form>

        <!-- Logout button -->
    <form action="logout.php" method="post" style="display:inline;">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
