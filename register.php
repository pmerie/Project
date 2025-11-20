<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

$errors = [];
$success = "";

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    // ----------------- VALIDATION -----------------
    if ($username === '') {
        $errors[] = "Username is required.";
    }

    if ($email === '') {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password === '' || $confirm === '') {
        $errors[] = "Password and confirm password are required.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // Check if username or email already exists
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $errors[] = "Username or email already taken.";
        }
    }

    // ----------------- INSERT USER -----------------
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, role)
            VALUES (?, ?, ?, 'user')
        ");
        $stmt->execute([$username, $email, $hash]);

        $success = "Account created successfully! You can now log in.";
        $_POST = []; // clear form
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
</head>
<body>
<div class="page-box">
    <h1>Create Account</h1>

    <!-- Success message -->
    <?php if ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <!-- Error messages -->
    <?php foreach ($errors as $e): ?>
        <p style="color:red;"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <form method="post">
        <label>Username:</label><br>
        <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"><br><br>

        <label>Your Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"><br><br>

        <label>Password:</label><br>
        <input type="password" name="password"><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="confirm"><br><br>

        <button type="submit">Create Account</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>
