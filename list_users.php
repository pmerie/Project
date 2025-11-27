<?php
session_start();
require 'db_connect.php';

//Admins Security
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

$users = $db->query("SELECT user_id, username, email, role FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Users</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Admin - All Registered Users</h1>
    <p>
        <a href="add_user.php">Add New User</a> | <a href="admin.php">Admin Home</a>
    </p>

    <table border="1" cellpadding="8">
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php foreach($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['username'])?></td>
                <td><?= htmlspecialchars($u['email'])?></td>
                <td><?= htmlspecialchars($u['role'])?></td>
                <td>
                    <a href="edit_user.php?id=<?= $u['user_id'] ?>">Edit</a> |
                    <a href="delete_user.php?id=<?= $u['user_id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
    </table>
</body>
</html>