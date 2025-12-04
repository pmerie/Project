<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

$fieldErrors = [];
$success = "";

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    // VALIDATION 
    if ($username === '') {
        $fieldErrors['username'] = "Username is required.";
    }

    if ($email === '') {
        $fieldErrors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = "Invalid email format.";
    }

    if ($password === '' || $confirm === '') {
        $fieldErrors['password'] = "Password and confirm password are required.";
    } elseif ($password !== $confirm) {
        $fieldErrors['password'] = "Passwords do not match.";
    }

    // Check if username or email already exists
    if (empty($fieldErrors)) {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $fieldErrors['username'] = "Username or email already taken.";
        }
    }

    // INSERT USER 
    if (empty($fieldErrors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, role)
            VALUES (?, ?, ?, 'user')
        ");
        $stmt->execute([$username, $email, $hash]);

        $success = "Account created successfully! You can now log in.";
        $_POST = []; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>
<link rel="stylesheet" href="style.css">
<style>
.error { color: red; font-size: 0.9em; margin-top: 2px; }
</style>
</head>
<body>
<div class="page-box">
    <h1>Create Account</h1>

    <?php if ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username:</label><br>
        <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        <div class="error"><?= $fieldErrors['username'] ?? '' ?></div>
        <br>

        <label>Your Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <div class="error"><?= $fieldErrors['email'] ?? '' ?></div>
        <br>

        <label>Password:</label><br>
        <input type="password" name="password">
        <div class="error"><?= $fieldErrors['password'] ?? '' ?></div>
        <br>

        <label>Confirm Password:</label><br>
        <input type="password" name="confirm">
        <div class="error"><?= $fieldErrors['password'] ?? '' ?></div>
        <br>

        <button type="submit">Create Account</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>
