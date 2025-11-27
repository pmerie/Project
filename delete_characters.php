<?php
// delete_characters.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//Session and check login
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'db_connect.php';

// Admin-only access check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    // Fetch user role
    $stmt = $db->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        die("❌ Access denied. Admins only.");
    }

    // Validate id
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        http_response_code(400);
        die("Character ID missing or invalid.");
    }

    $char_id = (int) $_GET['id'];

    // Start transaction
    $db->beginTransaction();

    // Optional: delete related comments first (if you don't have ON DELETE CASCADE)
    $delComments = $db->prepare("DELETE FROM comments WHERE character_id = ?");
    $delComments->execute([$char_id]);

    // Delete character
    $delChar = $db->prepare("DELETE FROM characters WHERE character_id = ?");
    $delChar->execute([$char_id]);

    // Check that something was deleted
    if ($delChar->rowCount() === 0) {
        // Nothing deleted — maybe the ID didn't exist
        $db->rollBack();
        http_response_code(404);
        die("Character not found or already deleted.");
    }

    $db->commit();

    // Redirect back to the list
    header("Location: list_characters.php");
    exit;

} catch (PDOException $e) {
    // Roll back if in transaction
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    // Show a helpful error message (escape for safety)
    http_response_code(500);
    echo "<h2>Database error while deleting character</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='list_characters.php'>Back to list</a></p>";
    exit;
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo "<h2>Unexpected error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='list_characters.php'>Back to list</a></p>";
    exit;
}
 