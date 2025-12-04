<?php
// Show all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? '';

    // Validation
    if ($username === '') $errors['username'] = "Username is required.";
    if ($email === '') $errors['email'] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format.";
    if ($password === '') $errors['password'] = "Password is required.";
    if (!in_array($role, ['admin', 'user'])) $errors['role'] = "Invalid role selected.";

    // Check if username exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) $errors['username'] = "Username already exists.";

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash, $role]);

        $message = "✅ User added successfully!";

        $username = $email = $password = $role = '';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error { color: red; font-size: 0.9em; margin-top: 2px; margin-bottom: 10px; }
    </style>
</head>
<body>
<h1>Add User</h1>

<?php if ($message) echo "<p style='color:green;'>$message</p>"; ?>

<form method="post">
    <label>Username:</label><br>
    <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>">
    <?php if(!empty($errors['username'])): ?>
        <div class="error"><?= $errors['username'] ?></div>
    <?php endif; ?><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
    <?php if(!empty($errors['email'])): ?>
        <div class="error"><?= $errors['email'] ?></div>
    <?php endif; ?><br>

    <label>Password:</label><br>
    <input type="password" name="password">
    <?php if(!empty($errors['password'])): ?>
        <div class="error"><?= $errors['password'] ?></div>
    <?php endif; ?><br>

    <label>Role:</label><br>
    <select name="role">
        <option value="user" <?= (($role ?? '') === 'user') ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= (($role ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
    </select>
    <?php if(!empty($errors['role'])): ?>
        <div class="error"><?= $errors['role'] ?></div>
    <?php endif; ?>

    <br><br>
    <button type="submit">Add User</button>
</form>

<p><a href="list_users.php">Back to Users List</a></p>
</body>
</html>
