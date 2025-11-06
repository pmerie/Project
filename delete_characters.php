<?php
require 'db_connect.php';



// Instead of just session_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Character ID is missing");
}

$char_id = $_GET['id'];

// Check if character exists
$stmt = $db->prepare("SELECT * FROM characters WHERE character_id = ?");
$stmt->execute([$char_id]);
$char = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$char) {
    die("Character not found.");
}

// Delete character
$stmt = $db->prepare("DELETE FROM characters WHERE character_id = ?");
$stmt->execute([$char_id]);

// Redirect back to list
header("Location: list_characters.php");
exit;

?>