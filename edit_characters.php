<?php
require 'db_connect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if the character ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Character ID is missing.");
}

$char_id = $_GET['id'];

// Fetch the character
$stmt = $db->prepare("SELECT * FROM characters WHERE character_id = ?");
$stmt->execute([$char_id]);
$char = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$char) {
    die("Character not found.");
}

// Update the record when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $film_id = $_POST['film_id'];
    $character_type = $_POST['character_type'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];

    // Update character in the database
    $stmt = $db->prepare("UPDATE characters 
                          SET name = ?, film_id = ?, character_type = ?, description = ?, image_url = ? 
                          WHERE character_id = ?");
    $stmt->execute([$name, $film_id, $character_type, $description, $image_url, $char_id]);

    echo "<p>✅ Character updated successfully!</p>";

    // Refresh data to display the latest updates
    $stmt = $db->prepare("SELECT * FROM characters WHERE character_id = ?");
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
</head>
<body>
    <h1>Edit Character</h1>

    <form method="post">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($char['name']) ?>" required><br><br>

        <label>Film ID:</label><br>
        <input type="number" name="film_id" value="<?= htmlspecialchars($char['film_id']) ?>" required><br><br>

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
