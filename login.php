<?php
session_start();
require 'db_connect.php'; // Make sure this file connects to your database

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
 
    try {
        // Fetch the user
        $stmt = $db->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
 
        // ===== DEBUG START =====
        echo "<pre>DEBUG: "; 
        if (!$user) {
            echo "No user found for username: " . htmlspecialchars($username);
        } else {
            echo "Fetched user:\n";
            print_r($user);
        }
        echo "</pre>";
        // ===== DEBUG END =====
 
        // Verify password if user exists
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header('Location: admin.php');
            exit;
        } else {
            $message = "Invalid username or password.";
        }
 
    } catch (PDOException $e) {
        echo "DB Error: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ghibli World Archive</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <h1>Login</h1>
    <?php if (!empty($message)): ?>
        <p style="color:red;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
