<?php
// Temporarly debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);



session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $film_id = $_POST['film_id'];
    $character_type = $_POST['character_type'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];

    $stmt = $db->prepare("INSERT INTO characters (name, film_id, character_type, description, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $film_id, $character_type, $description, $image_url]);
     
    echo"<p>Character add successfully!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Ghibli World Archive</title>
</head>
<body>
    <h1>Admin - Add New Character</h1>
    <form method="post">
        <!-- Name -->
        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <!-- Film ID -->
        <label>Film ID:</label>
        <input type="number" name="film_id" required><br><br>

        <!-- Character Type -->
        <label>Character Type:</label>
        <input type="text" name="character_type"><br><br>

        <!-- Description -->
        <label>Description</label>
        <textarea name="description" rows="5" cols="40"></textarea><br><br>

        <!-- Image URL -->
        <label>Image URL:</label>
        <input type="text" name="image_url"><br><br>

        <button type="submit">Add Character</button>
    </form>
</body>
</html>

