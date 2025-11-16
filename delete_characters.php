<?php
session_start();
require 'db_connect.php';

// ✅ Admin-only access check
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

// Check if character ID is provided
if (!isset($_GET['id'])) {
    die("Character ID missing");
}

$char_id = $_GET['id'];

// Delete character
$stmt = $db->prepare("DELETE FROM characters WHERE character_id = ?");
$stmt->execute([$char_id]);

// Redirect back to the character list
header("Location: list_characters.php");
exit;
?>
