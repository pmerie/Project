<?php
session_start();
require 'db_connect.php';

// Admin-only access
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
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Validation
    if ($username === '') $errors['username'] = "Username is required.";
    if ($password === '') $errors['password'] = "Password is required.";
    if (!in_array($role, ['admin', 'user'])) $errors['role'] = "Invalid role selected.";

    // Check if username exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) $errors['username'] = "Username already exists.";

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password_hash, $role]);
        $message = "✅ User added successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Add User</h1>
<?php if($message) echo "<p style='color:green;'>$message</p>"; ?>
<form method="post">
    Username: <input type="text" name="username"> <?= $errors['username'] ?? '' ?><br>
    Password: <input type="password" name="password"> <?= $errors['password'] ?? '' ?><br>
    Role: 
    <select name="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select> <?= $errors['role'] ?? '' ?><br>
    <button type="submit">Add User</button>
</form>
<p><a href="list_users.php">Back to Users List</a></p>
</body>
</html>
