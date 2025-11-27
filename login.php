<?php
session_start();
require 'db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = "Please enter both username and password.";
    } else {
        try {
            // Fetch user by username
            $stmt = $db->prepare("SELECT user_id, username, password_hash, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password using password_verify()
            if ($user && password_verify($password, $user['password_hash'])) {

                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: index.php');
                }
                exit;

            } else {
                $message = "Invalid username or password.";
            }

        } catch (PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            $message = "An error occurred. Please try again later.";
        }
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
<body class="login-page">

<div class="login-box">
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

<p>Don't have an account? <a href="register.php">Register here</a></p>

</div>
</body>
</html>
