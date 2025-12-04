<?php
session_start();
require 'db_connect.php';

// Admin-only
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$stmt = $db->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || $user['role'] !== 'admin') die("❌ Access denied. Admins only.");

// Get user ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) die("Invalid user ID.");
$del_id = (int) $_GET['id'];


if ($del_id === $_SESSION['user_id']) die("❌ You cannot delete your own account.");

// Delete user
$stmt = $db->prepare("DELETE FROM users WHERE user_id=?");
$stmt->execute([$del_id]);

header('Location: list_users.php');
exit;

