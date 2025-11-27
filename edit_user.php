<?php
session_start();
require 'db_connect.php';

// Admin-only access
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$stmt = $db->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || $user['role'] !== 'admin') {
    die("❌ Access denied. Admins only.");
}

// Get user ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) die("Invalid user ID.");
$edit_id = (int) $_GET['id'];

// Fetch user
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$edit_id]);
$edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$edit_user) die("User not found.");

$errors = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if ($username === '') $errors['username'] = "Username is required.";
    if (!in_array($role, ['admin','user'])) $errors['role'] = "Invalid role.";

    // Check if username exists for another user
    $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $stmt->execute([$username, $edit_id]);
    if ($stmt->fetch()) $errors['username'] = "Username already exists.";

    if (empty($errors)) {
        if ($password) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET username=?, password_hash=?, role=? WHERE user_id=?");
            $stmt->execute([$username, $password_hash, $role, $edit_id]);
        } else {
            $stmt = $db->prepare("UPDATE users SET username=?, role=? WHERE user_id=?");
            $stmt->execute([$username, $role, $edit_id]);
        }
        $message = "✅ User updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Edit User</h1>
<?php if($message) echo "<p style='color:green;'>$message</p>"; ?>
<form method="post">
    Username: <input type="text" name="username" value="<?= htmlspecialchars($edit_user['username']) ?>"> <?= $errors['username'] ?? '' ?><br>
    Password: <input type="password" name="password"> <?= $errors['password'] ?? '' ?><br>
    Role: 
    <select name="role">
        <option value="user" <?= $edit_user['role']=='user'?'selected':'' ?>>User</option>
        <option value="admin" <?= $edit_user['role']=='admin'?'selected':'' ?>>Admin</option>
    </select> <?= $errors['role'] ?? '' ?><br>
    <button type="submit">Update User</button>
</form>
<p><a href="list_users.php">Back to Users List</a></p>
</body>
</html>
