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

// Default values
$username = $edit_user['username'];
$email = $edit_user['email'];
$role = $edit_user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect posted values
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Validation
    if ($username === '') $errors['username'] = "Username is required.";

    if ($email === '') $errors['email'] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format.";

    if (!in_array($role, ['admin','user'])) $errors['role'] = "Invalid role.";

    $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $stmt->execute([$username, $edit_id]);
    if ($stmt->fetch()) $errors['username'] = "Username already exists.";

    if (empty($errors)) {

        if ($password !== '') {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("UPDATE users 
                                  SET username=?, email=?, password_hash=?, role=? 
                                  WHERE user_id=?");
            $stmt->execute([$username, $email, $password_hash, $role, $edit_id]);
        } else {
            $stmt = $db->prepare("UPDATE users 
                                  SET username=?, email=?, role=? 
                                  WHERE user_id=?");
            $stmt->execute([$username, $email, $role, $edit_id]);
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

<div class="page-box">
<h1>Edit User</h1>

<?php if($message): ?>
    <p style="color:green; font-weight:bold;"><?= $message ?></p>
<?php endif; ?>

<form method="post">

    <div class="form-group">
        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($username) ?>">
        <span class="error"><?= $errors['username'] ?? '' ?></span>
    </div>

    <div class="form-group">
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
        <span class="error"><?= $errors['email'] ?? '' ?></span>
    </div>

    <div class="form-group">
        <label>Password: <small>(leave blank to keep current)</small></label>
        <input type="password" name="password">
        <span class="error"><?= $errors['password'] ?? '' ?></span>
    </div>

    <div class="form-group">
        <label>Role:</label>
        <select name="role">
            <option value="user" <?= $role=='user'?'selected':'' ?>>User</option>
            <option value="admin" <?= $role=='admin'?'selected':'' ?>>Admin</option>
        </select>
        <span class="error"><?= $errors['role'] ?? '' ?></span>
    </div>

    <button type="submit">Update User</button>
</form>

<p><a href="list_users.php">Back to Users List</a></p>
</div>

</body>
</html>
