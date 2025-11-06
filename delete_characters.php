<?php
require 'db_connect.php';
if(session_status()==PHP_SESSION_NONE) session_start();
if (!isset($_GET['id'])) die("Character ID missing");
$char_id = $_GET['id'];

// Delete
$stmt=$db->prepare("DELETE FROM characters WHERE character_id=?");
$stmt->execute([$char_id]);
header("Location: list_characters.php");
exit;
?>
