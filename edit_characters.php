<?php
require 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

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
    $name = $_POST['name'];
    $film_name = $_POST['film_name']; // typing the film name
    $character_type = $_POST['character_type'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];

    // Check if the film already exists
    $stmt = $db->prepare("SELECT film_id FROM films WHERE film_name = ? LIMIT 1");
    $stmt->execute([$film_name]);
    $film = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($film) {
        $film_id = $film['film_id'];
    } else {
        // Insert new film (minimal fields, associate with current user)
        $stmt = $db->prepare("
            INSERT INTO films (film_name, year, image_url, description, director_id, genre_id, user_id)
            VALUES (?, 0, NULL, '', 1, 1, ?)
        ");
        $stmt->execute([$film_name, $current_user_id]);
        $film_id = $db->lastInsertId();
    }

    // Update character
    $stmt = $db->prepare("
        UPDATE characters 
        SET name = ?, film_id = ?, character_type = ?, description = ?, image_url = ? 
        WHERE character_id = ?
    ");
    $stmt->execute([$name, $film_id, $character_type, $description, $image_url, $char_id]);

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
        <textarea name="description" rows="5" cols="40"><?= htmlspecialchars($char['description']) ?></textarea><br><br>

        <label>Image URL:</label><br>
        <input type="text" name="image_url" value="<?= htmlspecialchars($char['image_url']) ?>"><br><br>

        <button type="submit">Update Character</button>
    </form>

    <p><a href="list_characters.php">Back to Character List</a></p>
</body>
</html>
