<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user role
$stmt = $db->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    die("❌ Access denied. Admins only.");
}

// Delete comment 
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM comments WHERE comment_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: comments.php");
    exit;
}

//  Hide comment 
if (isset($_GET['hide'])) {
    $stmt = $db->prepare("UPDATE comments SET is_visible = 0 WHERE comment_id = ?");
    $stmt->execute([$_GET['hide']]);
    header("Location: comments.php");
    exit;
}

if (isset($_GET['unhide'])) {
    $stmt = $db->prepare("UPDATE comments SET is_visible = 1 WHERE comment_id = ?");
    $stmt->execute([$_GET['unhide']]);
    header("Location: comments.php");
    exit;
}

// Disemvowel comment 
if (isset($_GET['disemvowel'])) {
    $stmt = $db->prepare("SELECT comment_text, original_text FROM comments WHERE comment_id = ?");
    $stmt->execute([$_GET['disemvowel']]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($comment) {
        if (empty($comment['original_text'])) {
            $stmt2 = $db->prepare("UPDATE comments SET original_text = ? WHERE comment_id = ?");
            $stmt2->execute([$comment['comment_text'], $_GET['disemvowel']]);
        }

        $disemvoweled = preg_replace('/[aeiouAEIOU]/', '', $comment['comment_text']);
        $stmt3 = $db->prepare("UPDATE comments SET comment_text = ? WHERE comment_id = ?");
        $stmt3->execute([$disemvoweled, $_GET['disemvowel']]);
    }
    header("Location: comments.php");
    exit;
}

// Restore original comment 
if (isset($_GET['restore'])) {
    $stmt = $db->prepare("SELECT original_text FROM comments WHERE comment_id = ?");
    $stmt->execute([$_GET['restore']]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($comment && !empty($comment['original_text'])) {
        $stmt2 = $db->prepare("UPDATE comments SET comment_text = ?, original_text = NULL WHERE comment_id = ?");
        $stmt2->execute([$comment['original_text'], $_GET['restore']]);
    }
    header("Location: comments.php");
    exit;
}

// Fetch all comments 
$stmt = $db->query("
    SELECT c.*, ch.name AS character_name
    FROM comments c
    JOIN characters ch ON c.character_id = ch.character_id
    ORDER BY c.created_at DESC
");
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderate Comments</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #618264; }
        a { text-decoration: none; color: #618264; }
    </style>
</head>
<body>
    <h1>Moderate Comments</h1>
    <p><a href="admin.php">Back to Admin Panel</a></p>

    <?php if (empty($comments)): ?>
        <p>No comments submitted yet.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Character</th>
                <th>User</th>
                <th>Comment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($comments as $c): ?>
                <tr>
                    <td><?= $c['comment_id'] ?></td>
                    <td><?= htmlspecialchars($c['character_name']) ?></td>
                    <td><?= htmlspecialchars($c['user_name'] ?? 'Guest') ?></td>
                    <td><?= htmlspecialchars($c['comment_text']) ?></td>
                    <td><?= $c['is_visible'] ? 'Visible' : 'Hidden' ?></td>
                    <td>
                        <?php if ($c['is_visible']): ?>
                            <a href="?hide=<?= $c['comment_id'] ?>">Hide</a> |
                        <?php else: ?>
                            <a href="?unhide=<?= $c['comment_id'] ?>">Unhide</a> |
                        <?php endif; ?>

                        <a href="?delete=<?= $c['comment_id'] ?>" onclick="return confirm('Delete this comment?')">Delete</a> |
                        <a href="?disemvowel=<?= $c['comment_id'] ?>" onclick="return confirm('Disemvowel this comment?')">Disemvowel</a>

                        <?php if (!empty($c['original_text'])): ?>
                            | <a href="?restore=<?= $c['comment_id'] ?>" onclick="return confirm('Restore original?')">Restore</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
